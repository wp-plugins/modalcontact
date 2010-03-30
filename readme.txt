=== Plugin Name ===
Contributors: batasoy
Donate link: http://www.barisatasoy.com/donate/
Tags: contact, contact form, modal, ajax, plugin, jquery, javascript, mail, email,database
Requires at least: ?
Tested up to: 2.9.2
Stable tag: 1.1

ModalContact is a database based contact form: it does use WP database for storage, instead of mailing form contents. 

== Description ==
ModalContact is a database based contact form: it does use WP database for storage, instead of mailing form contents. The rationale is, sometimes, especially cheap hosting servers can be very restrictive. Most contact form plugins have issues with these servers.

Besides this approach, as its name suggests, ModalContact has a modal form, so user does not have to leave the page. Contact Form pop ups on a overlay, just like the lightboxes. 

This plugin is heavily based on work from Eric Martin, SimpleModal Contact Form.



*Translations*

Since this plugin is heavily based on SMCF, you can easily translate it (if it has a SMCF translation in your language). .pot file is included.

Thank you to all who have contributed these translations.

== Installation ==

Activate as any other plugin.

You have 3 options to integrate form into your site:

a) Add the "mcf-link" to your existing contact link:

	<a href="/contact" class="mcf-link">Contact</a>
	

b) Use the "mcf()" function in one of your theme files (`sidebar.php`, for example):

	<?php if (function_exists('mcf')) : ?>
		<?php mcf(); ?>
	<?php endif; ?> 
	
c) If your contact link is generated using `wp_page_menu()` or `wp_list_pages()`, you can enter the contact link title in the MCF Options under "Contact Link Title" and MCF will automatically attempt to add the smcf-link class for that link.

== Frequently Asked Questions ==

= How do I change the styling of the contact form? =

Open `modal-contact-form-smcf/css/mcf.css` and modify the CSS to fit your needs.

*Note*: There are some browser specific CSS values that are set in the JavaScript (`modal-contact-form-mcf/js/mcf.js`).

= Can I run MCF and SMCF at the same time?

You should be, but I didnt tried that. And it doesnt make sense!


== Screenshots ==

1. Message Backend
2. Contact Form


== Changelog ==


* Version 1.0a
	* Initial release