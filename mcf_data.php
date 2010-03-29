<?php

require_once("../../../wp-config.php");
@error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
$mcf_dir = preg_replace("/^.*[\/\\\]/", "", dirname(__FILE__));
define ("MCF_DIR", "/wp-content/plugins/" . $mcf_dir);

// process
$action = isset($_POST["action"]) ? $_POST["action"] : "";
if ($action == "send") {
	// send the email
	$name = isset($_POST["name"]) ? $_POST["name"] : "";
	$email = isset($_POST["email"]) ? $_POST["email"] : "";
	$subject = isset($_POST["subject"]) ? $_POST["subject"] : "";
	$message = isset($_POST["message"]) ? $_POST["message"] : "";
	$cc = isset($_POST["cc"]) ? $_POST["cc"] : "";
	$token = isset($_POST["token"]) ? $_POST["token"] : "";

		$ip=$_SERVER["REMOTE_ADDR"];
 		$user_agent=$_SERVER["HTTP_USER_AGENT"];
 
 
		_e("Your message was successfully sent.", "mcf");
 sm_write_form($name,$email,$message,$ip,$user_agent);
}


function sm_write_form($name,$email,$message,$ip,$user_agent)
{
	global $wpdb;
	$r=$wpdb->query("INSERT INTO ".$wpdb->prefix."messages (name,message,email,ip,user_agent) VALUES ('$name','$message','$email','$ip','$user_agent')"); 
	}

function filter($value) {
	$pattern = array("/\n/", "/\r/", "/content-type:/i", "/to:/i", "/from:/i", "/cc:/i");
	$value = preg_replace($pattern, "", $value);
	return $value;
}

function validateEmail($email) {
	$at = strrpos($email, "@");

	if ($at && ($at < 1 || ($at + 1) == strlen($email)))
		return false;

	if (preg_match("/(\.{2,})/", $email))
		return false;

	$local = substr($email, 0, $at);
	$domain = substr($email, $at + 1);

	$locLen = strlen($local);
	$domLen = strlen($domain);
	if ($locLen < 1 || $locLen > 64 || $domLen < 4 || $domLen > 255)
		return false;

	if (preg_match("/(^\.|\.$)/", $local) || preg_match("/(^\.|\.$)/", $domain))
		return false;
	if (!preg_match('/^"(.+)"$/', $local)) {

		if (!preg_match('/^[-a-zA-Z0-9!#$%*\/?|^{}`~&\'+=_\.]*$/', $local))
			return false;
	}
	if (!preg_match('/^[-a-zA-Z0-9\.]*$/', $domain) || !strpos($domain, "."))
		return false;	

	return true;
}

exit;

?>