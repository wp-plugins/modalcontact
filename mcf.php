<?php
/*
Plugin Name: ModalContact (MCF)
Plugin URI: http://www.barisatasoy.com/modalcontact/
Description: Contact Form based on Eric Martin's awesome work, SMCF. The main difference is, ModalContact stores messages in a DB. If you have mail server problems, ModalContact will do the trick.
Version: 1.1
Author: Barış Atasoy
Author URI: http://www.barisatasoy.com
*/

/*	Copyright 2010 Barış Atasoy (b_atasoy@hotmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
function mcf_activate() {
	global $wpdb;
  			$query = '
				CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'messages (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `message` longtext NOT NULL,
  `email` varchar(128) NOT NULL,
  `ip` varchar(16) NOT NULL,
  `user_agent` varchar(250) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;';

			$wpdb->query($query); 
}

register_activation_hook( __FILE__, 'mcf_activate' );


$mcf_dir = preg_replace("/^.*[\/\\\]/", "", dirname(__FILE__));
define ("MCF_DIR", "/wp-content/plugins/" . $mcf_dir);

class ModalContactForm {

	var $version = "1.2.5";

	function init() {
		if (function_exists("load_plugin_textdomain")) {
			load_plugin_textdomain("mcf", MCF_DIR . "/lang/");
		}

		// add javascript files
		if (function_exists("wp_enqueue_script") && !is_admin()) {
			// load the jQuery version that comes with WordPress
			wp_enqueue_script("jquery");
			wp_enqueue_script("jquery-modal", get_option("siteurl") . MCF_DIR . "/js/jquery.modal.js", "jquery", "1.3", true);
			wp_enqueue_script("mcf", get_option("siteurl") . MCF_DIR . "/js/mcf.js", array("jquery", "jquery-modal"), $this->version, true);
		}

		// add styling
		if (function_exists("wp_enqueue_style")) {
			wp_enqueue_style("mcf", get_option("siteurl") . MCF_DIR . "/css/mcf.css", false, $this->version, "screen");
		}
	}

	function submenu() {
		if (function_exists("add_submenu_page")) {
			add_submenu_page("options-general.php", "ModalContact", "ModalContact", "manage_options", "mcf-config", array($this, "config_page"));
		}
	}

	function config_page() {
		$message = null;

		if (isset($_POST["action"]) && $_POST["action"] == "update") {
			// save options
			$message = _e("Options saved.", "mcf");
			update_option("mcf_link_url", $_POST["mcf_link_url"]); 
			update_option("mcf_link_title", $_POST["mcf_link_title"]);
			update_option("mcf_form_subject", $_POST["mcf_form_subject"]);

		}

	
		$mcf_form_title = get_option("mcf_form_title");
		$mcf_form_title = empty($mcf_form_title) ? __("Send me a message", "mcf") : $mcf_form_title;

		$mcf_link_url = get_option("mcf_link_url");
		$mcf_link_url = empty($mcf_link_url) ? "/contact" : $mcf_link_url;

		$mcf_link_title = get_option("mcf_link_title");
		$mcf_link_title = empty($mcf_link_title) ? "Contact" : $mcf_link_title;

		$mcf_subject = get_option("mcf_subject");
		$mcf_subject = empty($mcf_subject) ? "Modal Contact Form" : $mcf_subject;

?>
<?php if (!empty($message)) : ?>
<div id="message" class="updated fade"><p><strong><?php echo $message ?></strong></p></div>
<?php endif; ?>
<div class="wrap">
<h2><?php _e("ModalContact Configuration", "mcf"); ?></h2>
<form id="mcf_form" method="post" action="options.php">
<?php wp_nonce_field("update-options") ?>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e("Contact Link URL:", "mcf"); ?></th>
		<td><input type="text" id="mcf_link_url" name="mcf_link_url" value="<?php echo $mcf_link_url; ?>" size="40" class="code"/>
		<p><?php _e("The URL for the contact link to your contact form page. This is the URL that non-JavaScript users will be taken to.", "mcf"); ?></p></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e("Contact Link Title:", "mcf"); ?></th>
		<td><input type="text" id="mcf_link_title" name="mcf_link_title" value="<?php echo $mcf_link_title; ?>" size="40" class="code"/>
		<p><?php _e("The title for the contact link to your contact form page. If you are using wp_page_menu() or wp_list_pages() to build menus dynamically, mcf will look for a link with this title.", "mcf"); ?></p></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e("Form Title:", "mcf"); ?></th>
		<td><input type="text" id="mcf_form_title" name="mcf_form_title" value="<?php echo $mcf_form_title; ?>" size="40" class="code"/>
		<p><?php _e("Enter the title that you want displayed on your contact form.", "mcf"); ?></p></td>
	</tr>
	
	</table>
<p class="submit">
	<input type="submit" name="submit" value="<?php _e("Save Changes", "mcf"); ?>" />
</p>
<input type="hidden" name="action" value="update" />
<input type="hidden" name="page_options" value="mcf_link_url,mcf_link_title,mcf_form_title,mcf_form_subject,mcf_form_cc_sender,mcf_to_email,mcf_subject,mcf_ip,mcf_ua" />
</form>

</div>
<?php
	}

	function head() {
		/*
		 * WordPress 2.6.5 and below does not include the wp_print_styles filter in wp_head...
		 * So, we need to call it here, just in case
		 */
		if (function_exists("wp_print_styles")) {
			wp_print_styles("mcf");
		}
	}

	function footer() {
		$title = get_option("mcf_form_title");
		$title = empty($title) ? __("Send me a message", "mcf") : $title;

		$url = parse_url(get_bloginfo("wpurl") . MCF_DIR);

		$output = "
	<script type='text/javascript'>
		var mcf_messages = {
			loading: '" . addslashes(__("Loading...", "mcf")) . "',
			sending: '" . addslashes(__("Sending...", "mcf")) . "',
			thankyou: '" . addslashes(__("Thank You!", "mcf")) . "',
			error: '" . addslashes(__("Uh oh...", "mcf")) . "',
			goodbye: '" . addslashes(__("Goodbye...", "mcf")) . "',
			name: '" . addslashes(__("Name", "mcf")) . "',
			email: '" . addslashes(__("Email", "mcf")) . "',
			emailinvalid: '" . addslashes(__("Email is invalid.", "mcf")) . "',
			message: '" . addslashes(__("Message", "mcf")) . "',
			and: '" . addslashes(__("and", "mcf")) . "',
			is: '" . addslashes(__("is", "mcf")) . "',
			are: '" . addslashes(__("are", "mcf")) . "',
			required: '" . addslashes(__("required.", "mcf")) . "'
		}
	</script>";

		// create the contact form HTML
		$output .= "<div id='mcf-content' style='display:none'>
	<div class='mcf-top'></div>
	<div class='mcf-content'>
		<h1 class='mcf-title'>" . $title . "</h1>
		<div class='mcf-loading' style='display:none'></div>
		<div class='mcf-message' style='display:none'></div>
		<form action='" . $url["path"] . "/mcf_data.php' style='display:none'>
			<label for='mcf-name'>*" . __("Name", "mcf") . ":</label>
			<input type='text' id='mcf-name' class='mcf-input' name='name' value='' tabindex='1001' />
			<label for='mcf-email'>*" . __("Email", "mcf") . ":</label>
			<input type='text' id='mcf-email' class='mcf-input' name='email' value='' tabindex='1002' />";

		if (get_option("mcf_form_subject") == 1) {
			$output .= "<label for='mcf-subject'>" . __("Subject", "mcf") . ":</label>
			<input type='text' id='mcf-subject' class='mcf-input' name='subject' value='' tabindex='1003' />";
		}

		$output .= "<label for='mcf-message'>*" . __("Message", "mcf") . ":</label>
			<textarea id='mcf-message' class='mcf-input' name='message' cols='40' rows='4' tabindex='1004'></textarea><br/>";

		if (get_option("mcf_form_cc_sender") == 1) {
			$output .= "<label>&nbsp;</label>
			<input type='checkbox' id='mcf-cc' name='cc' value='1' tabindex='1005' /> <span class='mcf-cc'>" . __("Send me a copy", "mcf") . "</span>
			<br/>";
		}

		$output .= "<label>&nbsp;</label>
			<button type='submit' class='mcf-button mcf-send' tabindex='1006'>" . __("Send", "mcf") . "</button>
			<button type='submit' class='mcf-button mcf-cancel simplemodal-close' tabindex='1007'>" . __("Cancel", "mcf") . "</button>
			<input type='hidden' name='token' value='" . $this->token() . "'/>
			<br/>
		</form>
	</div>
	<div class='mcf-bottom'><a href='http://www.barisatasoy.com/modalcontact/'>" . __('Powered by', 'mcf') . " ModalContact</a></div>
</div>";

		echo $output;
	}

	function page_menu_list($page) {
		$title = get_option("mcf_link_title");
		$find = '/title="'.$title.'"/';
		$replace = 'title="'.$title.'" class="mcf-link"';
		return preg_replace($find, $replace, $page);
	}

	function token() {
		$admin_email = get_option("admin_email");
		return md5("mcf-" . $admin_email . date("WY"));
	}
}

$mcf = new ModalContactForm();

// Initialize textdomain - L10n and load scripts
add_action("init", array($mcf, "init"));

// Place a 'Modal Contact Form' sub menu item on the Options page
add_action("admin_menu", array($mcf, "submenu"));

// Include Modal Contact Form code to a page
add_action("wp_head", array($mcf, "head"));
add_action("wp_footer", array($mcf, "footer"), 10);

// Look for a contact link in the page menus/list
add_filter('wp_page_menu', array($mcf, "page_menu_list"));
add_filter('wp_list_pages', array($mcf, "page_menu_list"));

/*
 * Public function to create a link for the contact form
 * This can be called from any file in your theme
 */
function mcf() {
	$url = get_option("mcf_link_url");
	$url = empty($url) ? "/contact" : $url;

	$title = get_option("mcf_link_title");
	$title = empty($title) ? __("Contact", "mcf") : $title;

	echo "<a href='$url'class='mcf-link'>$title</a>";
}



add_action('admin_menu', 'modalcontact_messages');

function modalcontact_messages() {
    add_menu_page('Modal Contact Inbox', 'ModalContact', 8, __FILE__, 'modalcontact_inbox');
}

class PS_Pagination {
	

	var $php_self;
	var $rows_per_page = 10; //Number of records to display per page
	var $total_rows = 0; //Total number of rows returned by the query
	var $links_per_page = 5; //Number of links to display per page
	var $append = ""; //Paremeters to append to pagination links
	var $debug = false;
	var $conn = false;
	var $zpage = 1;
	var $max_pages = 0;
	var $offset = 0;

	function PS_Pagination($table,$rows_per_page = 10, $links_per_page = 5, $append = "") {
		$this->conn = $connection;
		$this->rows_per_page = (int)$rows_per_page;
		$this->table=$table;
		$this->sql = "select * from $this->table";
		if (intval($links_per_page ) > 0) {
			$this->links_per_page = (int)$links_per_page;
		} else {
			$this->links_per_page = 5;
		}
		$this->append = $append;
		$this->php_self = htmlspecialchars($_SERVER['PHP_SELF'] );
		if (isset($_GET['zpage'] )) {
			$this->page = intval($_GET['zpage'] );
		}
	}

	function paginate() {
		global $wpdb;
		$this->total_rows = $wpdb->get_var("select count(id) from $this->table");
		if ($this->total_rows == 0) {
			if ($this->debug)
				echo "Query returned zero rows.";
			return FALSE;
		}
		
		//Max number of pages
		$this->max_pages = ceil($this->total_rows / $this->rows_per_page );
		if ($this->links_per_page > $this->max_pages) {
			$this->links_per_page = $this->max_pages;
		}
		
		//Check the page value just in case someone is trying to input an aribitrary value
		if ($this->page > $this->max_pages || $this->page <= 0) {
			$this->page = 1;
		}
		
		//Calculate Offset
		$this->offset = $this->rows_per_page * ($this->page - 1);
		
		//Fetch the required result set
	 
		$rs = @$wpdb->get_results($this->sql . " LIMIT {$this->offset}, {$this->rows_per_page}" );
		

		
		if (! $rs) {
			if ($this->debug)
				echo "Pagination query failed. Check your query.<br /><br />Error Returned: " . mysql_error();
			return false;
		}
		return $rs;
	}

	function renderFirst($tag = 'First') {
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page == 1) {
			return "$tag ";
		} else {
			return '<a href="' . $this->php_self . '?zpage=1&' . $this->append . '">' . $tag . '</a> ';
		}
	}

	function renderLast($tag = 'Last') {
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page == $this->max_pages) {
			return $tag;
		} else {
			return ' <a href="' . $this->php_self . '?zpage=' . $this->max_pages . '&' . $this->append . '">' . $tag . '</a>';
		}
	}

	function renderNext($tag = '&gt;&gt;') {
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page < $this->max_pages) {
			return '<a href="' . $this->php_self . '?zpage=' . ($this->page + 1) . '&' . $this->append . '">' . $tag . '</a>';
		} else {
			return $tag;
		}
	}

	function renderPrev($tag = '&lt;&lt;') {
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page > 1) {
			return ' <a href="' . $this->php_self . '?page=' . ($this->page - 1) . '&' . $this->append . '">' . $tag . '</a>';
		} else {
			return " $tag";
		}
	}

	function renderNav($prefix = '<span class="page_link">', $suffix = '</span>') {
		if ($this->total_rows == 0)
			return FALSE;
		
		$batch = ceil($this->page / $this->links_per_page );
		$end = $batch * $this->links_per_page;
		if ($end == $this->page) {
		}
		if ($end > $this->max_pages) {
			$end = $this->max_pages;
		}
		$start = $end - $this->links_per_page + 1;
		$links = '';
		
		for($i = $start; $i <= $end; $i ++) {
			if ($i == $this->page) {
				$links .= $prefix . " $i " . $suffix;
			} else {
				$links .= ' ' . $prefix . '<a href="' . $this->php_self . '?zpage=' . $i . '&' . $this->append . '">' . $i . '</a>' . $suffix . ' ';
			}
		}
		
		return $links;
		 
	}

	function renderFullNav() {
		return $this->renderFirst() . '&nbsp;' . $this->renderPrev() . '&nbsp;' . $this->renderNav() . '&nbsp;' . $this->renderNext() . '&nbsp;' . $this->renderLast();
	}

	function setDebug($debug) {
		$this->debug = $debug;
	}
}
function modalcontact_inbox() {
    global $wpdb;
?>

	<h2><?php _e('Modal Contact Messages','mcf') ?></h2>
<?php

// get recod count

    if($_POST['delete']){
	
for($i=0;$i<count($_POST["checkbox"]);$i++)
{
	$id=$_POST['checkbox'][$i];
	$wpdb->query("delete from ".$wpdb->prefix."messages where id=$id");

     } 

}


$ct=$wpdb->get_var("select count(id) from ".$wpdb->prefix."messages");

if ($ct<1)
{
	?>
	<h3><?php _e('No messsages..yet!','mcf'); ?></h3>
	<?php
	exit();
	}
	$sql = "select name from ".$wpdb->prefix."messages";

	//Create a PS_Pagination object
	$pager = new PS_Pagination("$wpdb->prefix"."messages", 20, 3, 'page=modalcontact/mcf.php');
	//The paginate() function returns a mysql
	//result set for the current page
	$rs = $pager->paginate();
	//Loop through the result set	
	     	 	



    ?>

    <form method="post" action="<?php echo admin_url('admin.php?page=modalcontact/mcf.php') ?>">
    <table>
    <tr style='background-color:#B9C0C5'><td align='left' width='140'>Name</td><td align='left' width='180'>Email</td><td align='left' width='180'>Date</td><td align='left' width='40'>Read</td><td align='left' width='40'>Delete</td></tr>
    
    
   <?php
     	
 	foreach($rs as $row)
 	{
	$cons_message="<p class='info_sa'>Sender IP : ".$row->ip."</p>"
	."<p class='info_sa'>User Agent : ".$row->user_agent."</p>"
	."<p class='sender_message'>".$row->message."</p>";
		
		echo "<tr><td width='120'>".$row->name."</td>";
		echo "<td width='120'>".$row->email."</td>";
		echo "<td width='120'>".$row->date."</td>";
		?>
		<td width='40'><a href="javascript:toggleLayer('commentForm-<?php echo $row->id; ?>');" title="Read Message">Read</a></td>
		<td width='40'><input name="checkbox[]" type="checkbox" id="checkbox[]" value="<?php echo $row->id; ?>"></td>
		</tr>
		<tr><td colspan="5" width="400"><div class="m" id="commentForm-<?php echo $row->id; ?>"><?php echo $cons_message; ?></div> </td></tr>
		<?php
		
 	}
 
 
 	?>
 	</table><input name="delete" type="submit" id="delete" value="Delete" onClick="return confirmSubmit()"">
 	</form>
 	<?php
 	


 	

 	//Display the navigation
	echo '<div class="fullnav">'.$pager->renderFullNav().'</div>';

}

function loadscr()
 {
 	 ?> 
<script language="JavaScript">
function confirmSubmit()
{
var agree=confirm("Confirm delete ?");
if (agree)
	return true ;
else
	return false ;
}
// -->
 
</script> 	 
<script language='javascript'  type='text/javascript'>
function toggleLayer( whichLayer )
{
  var elem, vis;
  if( document.getElementById ) // this is the way the standards work
    elem = document.getElementById( whichLayer );
  else if( document.all ) // this is the way old msie versions work
      elem = document.all[whichLayer];
  else if( document.layers ) // this is the way nn4 works
    elem = document.layers[whichLayer];
  vis = elem.style;
  // if the style.display value is blank we try to figure it out here
  if(vis.display==''&&elem.offsetWidth!=undefined&&elem.offsetHeight!=undefined)
    vis.display = (elem.offsetWidth!=0&&elem.offsetHeight!=0)?'block':'none';
  vis.display = (vis.display==''||vis.display=='block')?'none':'block';
}</script> 
<style>
div#commentForm
{
  margin: 10px 20px 10px 20px;
  display: none;
  }
div .m {display:none;background-color:#FFFBCC;border:1px solid #E6DB55;padding-left:10px;margin:7px 0 7px 0
}
.fullnav {margin-top:10px}
.info_sa {font-size:11px;font-weight:bold;padding:0;margin-top:4px}
</style> 	  
 	  <?php } 
 	  add_action( 'admin_head','loadscr' ); 
?>