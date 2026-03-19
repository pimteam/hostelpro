<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HostelPRODiscount {
	function __construct() {
		$this->weekdays = array('mon' => __('Mon', 'hostelpro'), 'tue' => __('Tue', 'hostelpro'), 
			'wed' => __('Wed', 'hostelpro'), 'thu' => __('Thu', 'hostelpro'), 'fri' => __('Fri', 'hostelpro'),
			'sat' => __('Sat', 'hostelpro'), 'sun' => __('Sun', 'hostelpro'));
	}	
	
	function add($vars) {
		global $wpdb, $user_ID;
		
		$this->prepare_vars($vars);
		
		$wpdb->query($wpdb->prepare("INSERT INTO ".HOSTELPRO_DISCOUNTS." SET
			name=%s, date_condition=%d, date_from=%s, date_to=%s, weekdays_condition=%d,
			weekdays=%s, coupon_condition=%d, coupon=%s, discount_type=%s, discount_value=%s,
			room_id=%d, disc_type=%s, days_condition=%d, days=%d, min_price=%f, editor_id=%d",
			$vars['name'], $vars['date_condition'], $vars['date_from'], $vars['date_to'],
			$vars['weekdays_condition'], $vars['weekdays'], @$vars['coupon_condition'],
			$vars['coupon'], $vars['discount_type'], $vars['discount_value'], 
			$vars['room_id'], $vars['type'], $vars['days_condition'], $vars['days'], 
			$vars['min_price'], $user_ID));
			
		$id = $wpdb->insert_id;
		do_action('hostelpro_discount_added', $id);
		
		return $id;	
	} // end add()
	
	function edit($vars, $id) {
		global $wpdb;
		
		$this->prepare_vars($vars);
		
		$wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_DISCOUNTS." SET
			name=%s, date_condition=%d, date_from=%s, date_to=%s, weekdays_condition=%d,
			weekdays=%s, coupon_condition=%d, coupon=%s, discount_type=%s, discount_value=%s,
			room_id=%d, days_condition=%d, days=%d, min_price=%f WHERE id=%d",
			$vars['name'], $vars['date_condition'], $vars['date_from'], $vars['date_to'],
			$vars['weekdays_condition'], $vars['weekdays'], $vars['coupon_condition'],
			$vars['coupon'], $vars['discount_type'], $vars['discount_value'], 
			$vars['room_id'], $vars['days_condition'], $vars['days'], $vars['min_price'], $id));
			
		do_action('hostelpro_discount_edited', $id, $vars);	
	}
	
	function delete($id) {
		global $wpdb;
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".HOSTELPRO_DISCOUNTS." WHERE id=%d", $id));
		
		do_action('hostelpro_discount_deleted', $id);
	}
	
	// prepare variables
	function prepare_vars(&$vars) {
		// prepare weekdays
		$vars['weekdays'] = empty($vars['weekdays']) ? '' : '|'.implode("|",$vars['weekdays']).'|'; 
		
		// discount or surcharge? This comes in get
		$vars['type'] = empty($_GET['type']) ? 'discount' : $_GET['type'];
		
	   if(empty($vars['min_price_condition'])) $vars['min_price'] = 0;
	   
	   // sanitize vars
	   $vars['name'] = sanitize_text_field($vars['name']);
	   $vars['date_condition'] = empty($vars['date_condition']) ? 0 : 1;
	   $vars['date_from'] = sanitize_text_field($vars['date_from']);
		$vars['date_to'] = sanitize_text_field($vars['date_to']);
		$vars['weekdays_condition'] = empty($vars['weekdays_condition']) ? 0 : 1;
		$vars['coupon_condition'] = empty($vars['coupon_condition']) ? 0 : 1;
		$vars['coupon'] = sanitize_text_field(@$vars['coupon']);
		$vars['discount_type'] = sanitize_text_field($vars['discount_type']);
		$vars['discount_value'] = is_numeric($vars['discount_value']) ? $vars['discount_value'] : 0;
		$vars['room_id'] = intval($vars['room_id']);
		$vars['days_condition'] = empty($vars['days_condition']) ? 0 : 1;
		$vars['days'] = intval($vars['days']);
		$vars['min_price'] = floatval($vars['min_price']);
	}	
	
	// prettify some properties in human-redable format
	function prettify($discount, $property) {
		switch($property) {
			case 'weekdays':
				$weekdays = explode('|', $discount->weekdays);
				$weekdays = array_filter($weekdays);
				if(empty($weekdays)) return __('Every day');
				
				$weekdays_str = array();
				foreach($weekdays as $weekday) $weekdays_str[] = $this->weekdays[$weekday];
				
				return implode(', ', $weekdays_str);
			break;
		}
	} // end prettify
}