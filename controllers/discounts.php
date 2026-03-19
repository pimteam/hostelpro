<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// manage discounts
// each discount can have at the same time a date-based condition, weekday condition and coupon condition
class HostelPRODiscounts {
	public static $all_discounts;	
	
	static function manage() {
		$action = empty($_GET['action']) ? 'list' : $_GET['action'];
		
		switch($action) {
			case 'add': self :: add(); break;
			case 'edit': self :: edit(); break;
			case 'list': default: self :: list_discounts(); break;
		}
	} // end manage()
	
	static function add() {
		global $wpdb;
		$_discount = new HostelPRODiscount();
		$dateformat = get_option('date_format');
		
		$multiuser_access = 'all';
		$multiuser_access = HostelPRORoles::check_access('discounts_access');	
		
		if(!empty($_POST['ok'])) {
			$_discount->add($_POST);
			hostelpro_redirect("admin.php?page=hostelpro_discounts");
		}
		
		// select rooms for the dropdown
		$rooms = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_ROOMS." ORDER BY title");		
		
		$type = empty($_GET['type']) ? 'discount' : $_GET['type'];
		$display_type = ($type == 'discount') ? __('Discount', 'hostelpro') : __('Surcharge', 'hostelpro'); 
		
		hostelpro_enqueue_datepicker();
		if(@file_exists(get_stylesheet_directory().'/hostelpro/discount.html.php')) include get_stylesheet_directory().'/hostelpro/discount.html.php';
		else include(HOSTELPRO_PATH."/views/discount.html.php");	
	} // end add()
	
	static function edit() {
		global $wpdb;
		$_discount = new HostelPRODiscount();
		$dateformat = get_option('date_format');
		
		$multiuser_access = 'all';
		$multiuser_access = HostelPRORoles::check_access('discounts_access');	
		
		$_GET['id'] = intval($_GET['id']);
		if($multiuser_access == 'own') {
			$discount = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_DISCOUNTS." WHERE id=%d", $_GET['id']));
			if(@$discount->editor_id != $user_ID) wp_die(__('You can manage only discounts/surcharges created by you.', 'hostelpro'));
		}		
		
		if(!empty($_POST['ok'])) {
			$_discount->edit($_POST, $_GET['id']);
			hostelpro_redirect("admin.php?page=hostelpro_discounts");
		}
		
		// select rooms for the dropdown
		$rooms = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_ROOMS." ORDER BY title");
		
		// select this discount
		$discount = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_DISCOUNTS." 
			WHERE id=%d", $_GET['id']));
		
		$type = ($discount->disc_type == 'discount') ? 'discount' : 'surcharge';	
		$display_type = ($type == 'discount') ? __('Discount', 'hostelpro') : __('Surcharge', 'hostelpro');
			
		hostelpro_enqueue_datepicker();
		if(@file_exists(get_stylesheet_directory().'/hostelpro/discount.html.php')) include get_stylesheet_directory().'/hostelpro/discount.html.php';
		else include(HOSTELPRO_PATH."/views/discount.html.php");	
	} // end add()
	
	// list discounts + delete
	static function list_discounts() {
		global $wpdb, $user_ID;
		$_discount = new HostelPRODiscount();
		
		$multiuser_access = 'all';
		$multiuser_access = HostelPRORoles::check_access('discounts_access');	
		
		if(!empty($_GET['del'])) {
			$_GET['id'] = intval($_GET['id']);
			if($multiuser_access == 'own') {
				$discount = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_DISCOUNTS." WHERE id=%d", $_GET['id']));
				if(@$discount->editor_id != $user_ID) wp_die(__('You can manage only discounts/surcharges created by you.', 'hostelpro'));
			}			
			
			$wpdb->query($wpdb->prepare("DELETE FROM ".HOSTELPRO_DISCOUNTS." WHERE id=%d", $_GET['id']));
			hostelpro_redirect("admin.php?page=hostelpro_discounts");
		}
		
		$date_format = get_option('date_format');
		
		$owner_sql = '';
		if($multiuser_access == 'own') {
			$owner_sql = $wpdb->prepare(" AND tD.editor_id = %d ", $user_ID);
		}	
		
		// select discounts join to rooms
		$discounts = $wpdb->get_results("SELECT tD.*, tR.title as room FROM ".
			HOSTELPRO_DISCOUNTS." tD LEFT JOIN ".HOSTELPRO_ROOMS." tR ON tR.id = tD.room_id
			WHERE 1 $owner_sql
			ORDER BY name, id");
		if(@file_exists(get_stylesheet_directory().'/hostelpro/discounts.html.php')) include get_stylesheet_directory().'/hostelpro/discounts.html.php';
		else include(HOSTELPRO_PATH."/views/discounts.html.php");		
	} // end list_discounts()
	
	// applies a discount to price 
	// per given room, date, weekday, price, coupon
	static function apply_discount($time, $room_id, $price, $num_days, $coupon = '') {
		// discounts fetched?
		self :: fetch_discounts();
		
		// if time is passed as mysql date, convert
		if(!is_numeric($time)) $time = strtotime($time);
		
		$total_discount = 0;		
		foreach(self :: $all_discounts as $discount) {
			
			if($discount->room_id and $discount->room_id != $room_id) continue;
			
			if($discount->coupon_condition and $discount->coupon != $coupon) continue;
			
			if($discount->min_price > 0) continue; // these discounts are handled only at the booking end
			
			if($discount->date_condition) {
				$from_time = strtotime($discount->date_from);
				$to_time = strtotime($discount->date_to);
				
				if($time < $from_time or $time > $to_time) continue;
			}
			
			if($discount->weekdays_condition) {
				$weekday = strtolower( date('D', $time ) );
				if(!strstr($discount->weekdays, '|'.$weekday.'|')) continue;
			}
			
			if($discount->days_condition) {
				if($discount->days and $discount->days > $num_days) continue;				
			}
			
			// if we reached this point, discount/surcharge applies!
			$discount_value = ($discount->disc_type == 'surcharge') ? 0 - $discount->discount_value : $discount->discount_value;
			if($discount->discount_type == 'amount') $total_discount += $discount_value;
			else $total_discount += round( ($price * ($discount_value/100)), 2); 
		} // end foreach
		
		if($total_discount > $price) $total_discount = $price;
		 
		// if(HOSTELPRO_NO_DECIMALS)  $total_discount = hostelpro_number_format($total_discount);
		
		return $total_discount;
	} // end apply_discount()
	
	// fetches all currently active discounts to avoid doing multiple DB queries	
	static function fetch_discounts() {
		global $wpdb;
		
		if(!empty(self :: $all_discounts)) return true;
		
		// select only the ones for future dates, if date condition applies
		$curdate = date("Y-m-d", current_time('timestamp'));
		$discounts = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_DISCOUNTS." 
			WHERE discount_value > 0 AND (date_condition=0 OR (date_condition=1 AND date_to >='$curdate'))");
			
		self :: $all_discounts = $discounts;	
	} // end fetch discounts
	
	// gets the total discount for a room, for time period
	static function period_discount($from_time, $to_time, $room, $beds, $coupon = '') {
		$total_discount = $this_discount = 0;
		$date_discounts = array(); // this will store the discount for each date
		
		// select number of days so we can pass to apply_discount and see if this condition is satisfied
		$num_days = 0;
		for ($i = $from_time; $i < $to_time; $i = $i + 86400) $num_days ++;
		
		$room_price = $room->price;

		// Loop between timestamps, 24 hours at a time
		// $i is current time		
		for ($i = $from_time; $i < $to_time; $i = $i + 86400) {
			// booked a whole room in a room that allows whole room price?
			if($room->rtype == 'dorm' and !empty($room->whole_dorm_price) and $_POST['beds'] == $room->beds) {
				$room_price = $room->whole_dorm_price;
				$room->price_type = 'per-room';
			}
						
		   $this_discount = self :: apply_discount($i, $room->id, $room_price, $num_days, $coupon);		   
		   if($room->price_type == 'per-bed') $this_discount = $this_discount * $beds;
			   
		   $this_date = date("Y-m-d", $i);
		   //if(HOSTELPRO_NO_DECIMALS) $this_discount = hostelpro_number_format($this_discount);
		   $date_discounts[$this_date] = $this_discount;
		   $total_discount += $this_discount;		   
		} // end for
			
		return array($total_discount, $date_discounts);
	} // end period_discouint
}