<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HostelPROMinStays {
	static function manage() {
		global $wpdb;
		
		$dateformat = get_option('date_format');
		
		// default start / end date
		$start_date = date("Y-m-d");
		$end_date = date("Y-m-d", strtotime("+1 month"));
		
		if(!empty($_POST['add']) and check_admin_referer('hostelpro_minstays')) {
			$wpdb->query($wpdb->prepare("INSERT INTO ".HOSTELPRO_MINSTAYS." SET start_date=%s, end_date=%s, days=%d",
				$_POST['start_date'], $_POST['end_date'], intval($_POST['days'])));
		}
		
		if(!empty($_POST['save']) and check_admin_referer('hostelpro_minstays')) {
			$wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_MINSTAYS." SET start_date=%s, end_date=%s, days=%d WHERE id=%d",
				$_POST['start_date'], $_POST['end_date'], intval($_POST['days']), intval($_POST['id'])));
		}
		
		if(!empty($_POST['del']) and check_admin_referer('hostelpro_minstays')) {
			$wpdb->query($wpdb->prepare("DELETE FROM ".HOSTELPRO_MINSTAYS." WHERE id=%d", intval($_POST['id'])));
		}
		
		// select current periods
		$periods = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_MINSTAYS." ORDER BY start_date");
		
		hostelpro_enqueue_datepicker();
		
		if(@file_exists(get_stylesheet_directory().'/hostelpro/minstays.html.php')) include get_stylesheet_directory().'/hostelpro/minstays.html.php';
		else include(HOSTELPRO_PATH."/views/minstays.html.php");
	}
	
	// get the minimum stay accordingly to the selected start date. If no period is found, return the global config
	static function find($from, $to) {
		global $wpdb;
		
		$period = $wpdb->get_row($wpdb->prepare("SELECT id, days FROM " . HOSTELPRO_MINSTAYS." 
			WHERE (start_date <= %s AND end_date>=%s) OR (start_date <= %s AND end_date>=%s)
			OR (start_date >= %s AND end_date<=%s )
			ORDER BY start_date LIMIT 1", $from, $from, $to, $to, $from, $to));
			
		if(!empty($period->id)) return $period->days;
		
		// global config, return if exists
		$min_stay = get_option('hostelpro_min_stay');
		if(!empty($min_stay)) return $min_stay;		
		
		return -1;	
	}
}