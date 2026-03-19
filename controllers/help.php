<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HostelPROHelp {
	static function index() {
		if(@file_exists(get_stylesheet_directory().'/hostelpro/help.html.php')) include get_stylesheet_directory().'/hostelpro/help.html.php';
		else include(HOSTELPRO_PATH."/views/help.html.php");		
	}
}