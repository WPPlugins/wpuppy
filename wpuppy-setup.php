<?php
if (!defined('ABSPATH'))
	die ('No script kiddies please');

header('Content-type: application/json; charset=utf-8');
header("access-control-allow-origin: *");

if (get_option('wpuppy_setup') !== "false") {
	?>{
		"type": "error",
		"message": "This website is already linked to a WPuppy account, please go into your website's Admin Panel and remove the link, or go to the linked WPuppy account and remove the website there."
	}<?php
	exit;
}

if (($publickey = get_option('wpuppy_key')) === false) {
	?>{
		"type": "error",
		"message": "There is no key generated yet! Please return to your site and create a new W-Pro Key"
	}<?php
	exit;
}

if ($publickey !== filter_input(INPUT_GET, "key")) {
	?>{
		"type": "error",
		"message": "The key provided does not match the key generated on your website. Please return to your site and copy the W-Pro Key"
	}<?php
	exit;
}

if (!update_option('wpuppy_setup', "true")) {
	?>{
		"type": "error",
		"message": "An error occurred while trying to finish setup."
	}<?php
	exit;
}

?>{
	"type": "success"
}
