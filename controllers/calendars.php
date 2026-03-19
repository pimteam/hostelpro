<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// handles room availability calendars
class HostelPROCalendars {
	// lets user generate a shortcode calendar for a selected room
	static function get_shortcode() {
		global $wpdb;
		
		// select room
		$room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $_GET['id']));
		
		$months = empty($_POST['months']) ? 1 : intval($_POST['months']);  
		
		if(@file_exists(get_stylesheet_directory().'/hostelpro/room-calendar-shortcode.html.php')) include get_stylesheet_directory().'/hostelpro/room-calendar-shortcode.html.php';
		else include(HOSTELPRO_PATH."/views/room-calendar-shortcode.html.php");	
	}
}