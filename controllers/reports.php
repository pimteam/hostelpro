<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HostelProReports {
	// for earnings we'll use the booked amount everywhere (not the actually paid one)
	// unless specified otherwise
	static function main() {
		global $wpdb;
		
		$multiuser_access = 'all';
		$multiuser_access = HostelPRORoles::check_access('reports_access');
		
		// set start and end date
		$start_date = empty($_GET['start_date']) ? date("Y-m").'-01' : $_GET['start_date'];
		$end_date = empty($_GET['end_date']) ? date("Y-m-d") : $_GET['end_date'];
		$from_time = strtotime($start_date);
		$to_time = strtotime($end_date);
		
		// get earnings per room chart and per room type		
		list($rooms, $bookings) = self :: per_room($start_date, $end_date);		
		$types = array("private" => 0, "dorm" => 0);
		foreach($rooms as $room) {
			$types[$room->rtype] += $room->money;
		}
		
		// % occupancy per room
		$orooms = self :: occupancy($start_date, $end_date);
		
		// % occupancy per room type
		$otypes = array("private" => 0, "dorm" => 0);
		foreach($otypes as $key=>$otype) {			
			$occupancy = $no_rooms = 0;
			foreach($orooms as $room) {
				if($room->rtype != $key) continue;
				$no_rooms++;
				$occupancy += $room->occupancy;
			}
			
			$otypes[$key] = $no_rooms ? round($occupancy / $no_rooms): 0;
		}
		
		
		// table money earned per day
		$report_dates = array();
		for ($i = $from_time; $i < $to_time; $i = $i + 86400) {
			$report_dates[date("Y-m-d", $i)] = 0;				
		}
		
		foreach($report_dates as $date=>$cash) {
			foreach($bookings as $booking) {
				foreach($booking->date_prices_arr as $booking_date=>$booking_cash) {
					//echo $booking_date.'-'.$booking_cash.'-'.$date.'<Br>';
					if($booking_date == $date) $report_dates[$date] += $booking_cash;
				}
			}
		}
		
		// total amount booked
		$total_booked = 0;
		foreach($report_dates as $date=>$cash) $total_booked += $cash;
	
		// new bookings made in the given period
		$no_bookings = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ".HOSTELPRO_BOOKINGS."
			WHERE is_static=0 AND DATE(created_time) >= %s AND DATE(created_time) <= %s", $start_date, $end_date ));
		
		$dateformat = get_option('date_format');		
		wp_register_script('jquery.peity', HOSTELPRO_URL."js/jquery.peity.min.js", false, '2.0.3');
		wp_enqueue_script('jquery.peity');
		hostelpro_enqueue_datepicker();
		if(@file_exists(get_stylesheet_directory().'/hostelpro/reports.html.php')) include get_stylesheet_directory().'/hostelpro/reports.html.php';
		else include(HOSTELPRO_PATH."/views/reports.html.php");		
	}
	
	// get earnings per room
	// get top 9 rooms + "others"
	// maybe use the same method to return earnings per room type
	static function per_room($start_date, $end_date) {
		global $wpdb;
		
		$multiuser_access = 'all';
		$multiuser_access = HostelPRORoles::check_access('reports_access');
				
		$from_time = strtotime($start_date);
		$to_time = strtotime($end_date);
		
		$report_dates = array();
		for ($i = $from_time; $i < $to_time; $i = $i + 86400) {
			$report_dates[] = date("Y-m-d", $i);				
		}
		
		// select all bookings that fall into the given perid
		$bookings = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS." 
			WHERE is_static = 0 AND (from_date >= %s AND from_date <= %s) 
			OR (to_date > %s AND to_date <= %s) OR (from_date <= %s AND to_date > %s) ", 
			$start_date, $end_date, $start_date, $end_date, $start_date, $end_date));
			
			
		// make sure we have cost per date breakdown for each booking
		foreach($bookings as $cnt=>$booking) {			
			// match dates to calculate the money
			$booking_start_time = strtotime($booking->from_date);
			$booking_end_time = strtotime($booking->to_date);
			$num_days = round(($booking_end_time - $booking_start_time) / 86400);
			if($num_days == 0) $num_days = 1;
			$avg_price = round(($booking->amount_paid + $booking->amount_due) / $num_days, 2);
			
			// Now we need to fill all dates for the booking in case they are not pre-filled when saving
			if(empty($booking->date_prices)) {
				$date_prices = array();
				for($i = $booking_start_time; $i < $booking_end_time; $i = $i + 86400) {
					$booking_date = date("Y-m-d", $i);
					
					if(!in_array($booking_date, $report_dates)) continue; // we are not interested in dates that are not in the report selection
					$date_prices[$booking_date] = $avg_price;
				}
			}				
			else $date_prices = unserialize(stripslashes($booking->date_prices));
			
			$bookings[$cnt]->date_prices_arr = $date_prices; // store unserialized in the object
		}	// end foreach booking				
			
		// select all rooms that are in these bookings
		$rids = array(0);
		foreach($bookings as $booking) {
			if(!in_array($booking->room_id, $rids)) $rids[] = $booking->room_id;
		} 
		$rooms = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id IN (".implode(',', $rids).")");
		
		// for each room go through all bookings. For each booking calculate the amount that
		// belongs to the given period
		$colors = array('green', 'red', 'blue', 'yellow', 'orange', 'black', 'pink', 'brown', 'navy', 'maroon');
		$other = (object)array("title"=>__('Other rooms', 'hostelpro'), "money"=>0);
		foreach($rooms as $cnt=>$room) {
			$money = 0;
			
			// here we already have date_prices constrained for the report, so we only need to add
			foreach($bookings as $booking) {
				if($booking->room_id != $room->id) continue;
				
				foreach($booking->date_prices_arr as $cash) $money += $cash;
			} // end foreach booking
			
			// assign color and money to the room object			
			$colorindex = $cnt;
			if($colorindex > 10) $colorindex = 0;
			
			// if rooms are more than 10, add money to the "other" rooms object
			if($cnt <= 9) {
				$rooms[$cnt]->money = $money;				
				$rooms[$cnt]->color = $colors[$colorindex];
			}
			else {
				 $other->money += $money;
				 $other->color = $colors[$colorindex];
				 unset($rooms[$cnt]);
			}			
		}	// end foreach rooms
		
		if($other->money) $rooms[] = $other;		
		return array($rooms, $bookings);
	} // end per_room
	
	// calculate % occupancy per room
	static function occupancy($start_date, $end_date) {
		global $wpdb;
				
		$from_time = strtotime($start_date);
		$to_time = strtotime($end_date);
		$num_days = round(($to_time - $from_time) / 86400);
		
		$report_dates = array();
		for ($i = $from_time; $i < $to_time; $i = $i + 86400) {
			$report_dates[] = date("Y-m-d", $i);				
		}
		
		// select all bookings that fall into the given perid
		$bookings = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS." 
			WHERE is_static = 0 AND (from_date >= %s AND from_date <= %s) 
			OR (to_date > %s AND to_date <= %s) OR (from_date <= %s AND to_date > %s) ", 
			$start_date, $end_date, $start_date, $end_date, $start_date, $end_date));
			
		// now for each booking calculate booked beds that fall into the selected dates
		foreach($bookings as $cnt=>$booking) {
			$booking_start_time = strtotime($booking->from_date);
			$booking_end_time = strtotime($booking->to_date);
			$booked_beds = 0;
			for($i = $booking_start_time; $i < $booking_end_time; $i = $i + 86400) {
				$booking_date = date("Y-m-d", $i);					
				if(!in_array($booking_date, $report_dates)) continue; // we are not interested in dates that are not in the report selection
				$booked_beds += $booking->beds;	
			}
			$bookings[$cnt]->total_booked_beds = $booked_beds;
		}	// end foreach booking
	
		// select all rooms that are in these bookings
		$rids = array(0);
		foreach($bookings as $booking) {
			if(!in_array($booking->room_id, $rids)) $rids[] = $booking->room_id;
		} 
		$rooms = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id IN (".implode(',', $rids).")");	
		
		// now calcualte total beds and booked beds for each room
		$colors = array('green', 'red', 'blue', 'yellow', 'orange', 'black', 'pink', 'brown', 'navy', 'maroon');
		$other = (object)array("title"=>__('Other rooms', 'hostelpro'), "booked_beds"=>0);
		foreach($rooms as $cnt=>$room) {
			// all beds
			$beds = $room->beds * $num_days;
			$booked_beds = 0;			
			
			foreach($bookings as $booking) {
				if($booking->room_id != $room->id) continue;
				$booked_beds += $booking->total_booked_beds;
			}
			
			// assign color and money to the room object			
			$colorindex = $cnt;
			if($colorindex > 10) $colorindex = 0;
			
			// if rooms are more than 10, add money to the "other" rooms object
			if($cnt <= 9) {
				$rooms[$cnt]->booked_beds = $booked_beds;				
				$rooms[$cnt]->color = $colors[$colorindex];
			}
			else {
				 $other->booked_beds += $booked_beds;
				 $other->color = $colors[$colorindex];
				 unset($rooms[$cnt]);
			}			
			
			$occupancy = $beds ? round(100 * $booked_beds / $beds) : 0;			
			$rooms[$cnt]->occupancy = $occupancy;
		} // end foreach room
		
		if($other->booked_beds) $rooms[] = $other;		
		return $rooms;
	} // end occupancy()
	
	static function email_log() {
		global $wpdb;

		$multiuser_access = 'all';
		$multiuser_access = HostelPRORoles::check_access('emaillog_access');		
		
		$date = empty($_POST['date']) ? date('Y-m-d') : $_POST['date'];
		if(!empty($_POST['cleanup'])) update_option('hostelpro_cleanup_email_log', $_POST['cleanup_days']);
		
		$emails = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".HOSTELPRO_EMAILLOG." WHERE date=%s ORDER BY id", $date));
		
		$cleanup_raw_log = get_option('hostelpro_cleanup_email_log');
		if(empty($cleanup_raw_log)) $cleanup_raw_log = 7;
		
		hostelpro_enqueue_datepicker();
		if(@file_exists(get_stylesheet_directory().'/hostelpro/email-log.html.php')) include get_stylesheet_directory().'/hostelpro/email-log.html.php';
		else include(HOSTELPRO_PATH."/views/email-log.html.php");
	}
}