<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// manage hostel rooms controller
class HostelPRORooms {
	static function manage() {
		global $wpdb, $user_ID;
		$_room = new HostelPRORoom();
		
		$multiuser_access = 'all';
		$multiuser_access = HostelPRORoles::check_access('rooms_access');
		
		$action = empty($_GET['action'])?'list':$_GET['action'];
		switch($action) {
			case 'add':
				if(!empty($_POST['ok'])) {
					$_room -> add($_POST);
					$success = __('Room added.', 'hostelpro');
					hostelpro_redirect("admin.php?page=hostelpro_rooms&action=list");
				}			
			
				if(@file_exists(get_stylesheet_directory().'/hostelpro/room.php')) include get_stylesheet_directory().'/hostelpro/room.php';
				else include(HOSTELPRO_PATH."/views/room.php");
			break;
			
			case 'edit':
				if($multiuser_access == 'own') {
					$room = $_room->get($_GET['id']);
					if(@$room->editor_id != $user_ID) wp_die(__('You can manage only your own rooms.', 'hostelpro'));
				}	
				
				if(!empty($_POST['ok'])) {
					$_room->edit($_POST, $_GET['id']);
					$success = __('Room details saved.', 'hostelpro');
					hostelpro_redirect("admin.php?page=hostelpro_rooms&action=list");
				}
				
				$room = $_room->get($_GET['id']);
				
				if(!empty($room->discount_part_occupancy)) {
					$part_occupancy_prices = explode(',', $room->part_occupancy_prices);
				}
				
				if(@file_exists(get_stylesheet_directory().'/hostelpro/room.php')) include get_stylesheet_directory().'/hostelpro/room.php';
				else include(HOSTELPRO_PATH."/views/room.php");
			break;
			
			case 'delete':
				if($multiuser_access == 'own') {
					$room = $_room->get($_GET['id']);
					if(@$room->editor_id != $user_ID) wp_die(__('You can manage only your own rooms.', 'hostelpro'));
				}				
			
				$_room->delete($_GET['id']);
				$success = __("Room deleted.", 'hostelpro');
				hostelpro_redirect("admin.php?page=hostelpro_rooms&action=list");
			break;			
			
			case 'list':
			default:
				$owner_sql = '';
				if($multiuser_access == 'own') {
					$owner_sql = $wpdb->prepare(" AND editor_id = %d ", $user_ID);
				}				
			
				$offset = empty($_GET['offset']) ? 0 : intval($_GET['offset']);
				$page_limit = 20;
				$rooms = $_room->find($owner_sql);
				
				$count = $wpdb->get_var("SELECT COUNT(id) FROM ".HOSTELPRO_ROOMS);
				
				if(@file_exists(get_stylesheet_directory().'/hostelpro/rooms.php')) include get_stylesheet_directory().'/hostelpro/rooms.php';
				else include(HOSTELPRO_PATH."/views/rooms.php");
			break;
		}
	}
	
	// displays the availability table of all rooms by given dates
	// $atts defines which fields to show (info comes from the shortcode or from ajax)
	static function availability_table($atts) {
		global $wpdb;
		
		$_room = new HostelPRORoom();
		$_booking = new HostelPROBooking();
		$dateformat = get_option('date_format');
		$booking_mode = get_option('hostelpro_booking_mode');
		$min_stay = get_option('hostelpro_min_stay');
		$booking_start = get_option('hostelpro_booking_start');
		if(empty($booking_start)) $booking_start = 'tomorrow';
		$book_to_date = ($booking_start == 'tomorrow') ? '+2 days' : 'tomorrow';
		
		// which fields to show?
		$show_titles = empty($atts['show_titles']) ? 0 : $atts['show_titles'];
		$show_descriptions = empty($atts['show_descriptions']) ? 0 : $atts['show_descriptions'];
		$show_types = isset($atts['show_types']) ?  $atts['show_types'] : 1;
		$show_bathrooms = isset($atts['show_bathrooms']) ? $atts['show_bathrooms'] : 1;	
		$group_rooms = isset($atts['group_rooms']) ? $atts['group_rooms'] : 0;
		$group_text = isset($atts['group_text']) ? $atts['group_text'] : __('To ensure you stay together, Dorm bookings are limited to the number of beds in one room. If you require more beds, then please place another booking', 'hostelpro');
		$shortcode_id = $atts['shortcode_id'];	
		$vertical_after = isset($atts['vertical_after']) ? intval($atts['vertical_after']) : 0;
		$hide_dates = isset($atts['hide_dates']) ? intval($atts['hide_dates']) : 0;
		$orderby = isset($atts['orderby']) ? $atts['orderby'] : 'price';
		if(!in_array($orderby, array('title', 'price'))) $orderby = 'price';
		$orderdir = isset($atts['orderdir']) ? $atts['orderdir'] : 'asc';
		if(!in_array($orderdir, array('asc', 'desc'))) $orderby = 'asc';
		
		// when we have clicked the booking button load the booking form
		// will be removed when called by ajax
		if(!empty($_GET['in_booking_mode'])) {
			return self :: booking();
		} 
		
		// the dropdown defaults to "from tomorrow to 1 day after"
		$default_dateto_diff = $min_stay ? strtotime("+ ".(intval($min_stay)+1)." days") : strtotime($book_to_date);
		$datefrom = empty($_POST['hostelpro_from']) ? date("Y-m-d", strtotime($booking_start)) : $_POST['hostelpro_from'];
		$dateto = empty($_POST['hostelpro_to']) ? date("Y-m-d", $default_dateto_diff) : $_POST['hostelpro_to'];
		
		// select all rooms
		$rooms = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_ROOMS." ORDER BY $orderby $orderdir", ARRAY_A);
		
		// select all bookings in the given period
		$bookings = $_booking->select_in_period($datefrom, $dateto);
		
		$datefrom_time = strtotime($datefrom);
		$dateto_time = strtotime($dateto);		
		$numdays = ($dateto_time   -  $datefrom_time) / (24 * 3600);
		
		// match bookings to rooms so for each date we know if the room is booked or not
		foreach($rooms as $cnt=>$room) {
			$rooms[$cnt] = $_room->availability($room, $bookings, $datefrom, $dateto, $numdays, $datefrom_time, $dateto_time);			
		} // end foreach room
		
		// When hiding dates unavailable rooms should not display at all		
		if($hide_dates) {
			foreach($rooms as $cnt => $room) {
				$room_unavailability = false;
				for($i=0; $i < $numdays; $i++) {
					if(!$room['days'][$i]['available_beds']) $room_unavailability = true; 
				}
				
				if($room_unavailability) unset($rooms[$cnt]);
			}
		} // end if $hide_dates
		
		// shall we group rooms? if yes, we have to leave only 1 room of same type
		// and for dorm rooms this should be the room with most beds		
		if(!empty($atts['group_rooms'])) {
			$final_rooms = array();
			$used_types = array(); // will store types like "male-dorm-shared", "2-private-ensuite" to use in in_array()
			foreach($rooms as $room) {
				$typekey = ($room['rtype'] == 'private') ? $room['beds'].'-private-'.$room['bathroom'].'-price-'.$room['price'] 
					: $room['beds'].'-'.$room['dorm_gender'].'-dorm-'.$room['bathroom'].'-price-'.$room['price'];
				
				if(!in_array($typekey, $used_types)) {
					$add_room = self :: pick_best($rooms, $typekey);
					$final_rooms[] = $add_room;
					$used_types[] = $typekey;
				} 	 
			} // end foreach room
			
			$rooms = $final_rooms;
		} // end grouping
		
		// the minimum number of columns in the table is 1
		$numcols = 2;		
		
		if(@file_exists(get_stylesheet_directory().'/hostelpro/partial/rooms-table.html.php')) include get_stylesheet_directory().'/hostelpro/partial/rooms-table.html.php';
		else include(HOSTELPRO_PATH."/views/partial/rooms-table.html.php");
	}
	
	// pick the most appropriate room from given type, when rooms are grouped by type.
	// This works like this:
	// The best room is the one that is available for the most of the required dates
	// in the case more one room have same number of available dates
	// we'll prefer the room that has higher minimal number of free beds
	// in case of a tie, we prefer the room that has more free beds total
	static function pick_best($rooms, $typekey) {
		$rooms_to_sort = array();
		
		foreach($rooms as $room) {
			$roomkey = ($room['rtype'] == 'private') ? $room['beds'].'-private-'.$room['bathroom'].'-price-'.$room['price']  
					: $room['beds'].'-'.$room['dorm_gender'].'-dorm-'.$room['bathroom'].'-price-'.$room['price'];
			if($roomkey == $typekey) $rooms_to_sort[] = $room;		
		}
				
		// only 1 room?
		if(sizeof($rooms_to_sort) == 1) return $rooms_to_sort[0];
		
		// let's now find best available dates
		$max_days = $max_low_number = $max_total = 0;
		foreach($rooms_to_sort as $cnt=>$room) {
			$no_days = $total_beds = 0;
			$min_beds = 1000;
			foreach($room['days'] as $day) {
				if(!empty($day['available_beds'])) {
					$no_days++;
					$total_beds += $day['available_beds'];
					if($day['available_beds'] < $min_beds) $min_beds = $day['available_beds'];
				}
			} // end foreach availability day
			
			$rooms_to_sort[$cnt]['no_days'] = $no_days;
			$rooms_to_sort[$cnt]['total_beds'] = $total_beds;
			$rooms_to_sort[$cnt]['min_beds'] = $min_beds;
			
			if($no_days > $max_days) $max_days = $no_days;
			if($min_beds > $max_low_number) $max_low_number = $min_beds;
			if($total_beds > $max_total) $max_total = $total_beds;
		} // end foreach room
		
		// now knowing $max days, remove all rooms who don't have them
		$temp_rooms = array();
		foreach($rooms_to_sort as $room) {
			if($room['no_days'] == $max_days) $temp_rooms[] = $room;
		}
		$rooms_to_sort = $temp_rooms;
		
		// only 1 room?
		if(sizeof($rooms_to_sort) == 1) return $rooms_to_sort[0];
		
		// now let's see which room has the highest low number of available beds
		$temp_rooms = array();
		foreach($rooms_to_sort as $room) {
			if($room['min_beds'] == $max_low_number) $temp_rooms[] = $room;
		}
		$rooms_to_sort = $temp_rooms;
		
		// only 1 room?
		if(sizeof($rooms_to_sort) == 1) return $rooms_to_sort[0];
		
		// still not restricted by only one room?
		// restricting the total number of beds as last resort
		// in case of a tie, or in either case just return the first room		
		$temp_rooms = array();
		foreach($rooms_to_sort as $room) {
			if($room['total_beds'] == $max_total) $temp_rooms[] = $room;
		}
		$rooms_to_sort = $temp_rooms;
		
		return $rooms_to_sort[0];
	} // end pick best
	
	// admin calendar view of room bookings
	static function bookings_calendar() {
			global $wpdb;		
		
			$room_id = intval($_GET['id']);
			
			// year range is limited for up to 1 year
			$yearfrom = date("Y") - 1;
			$yearto = $yearfrom+1;
			$today_time = strtotime(date("Y-m-d"));
			$next_year_time = strtotime( (date("Y")+1).'-'.date('m-d') );
			$year_from_time = strtotime( (date("Y")-1).'-'.date('m-d') );
			
			// select room
			$room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $room_id));
			
			if(!empty($room->ical_import)) {
				$available_beds = (empty($room->overbook_beds) or $room->overbook_beds <= $room->beds) ? $room->beds : $room->overbook_beds;	
				HostelPROSync :: import($room, $year_from_time, $next_year_time, $available_beds, true);	
			}
			
			// select all bookings for this room
			// this will be used to make them disabled: http://davidwalsh.name/jquery-datepicker-disable-days
			$udates = $month_divs = array();
			$curdate = date("Y-m-d", current_time('timestamp'));
			$bookings = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS." 
			WHERE room_id=%d AND to_date > %s - INTERVAL 1 YEAR AND from_date < %s + INTERVAL 1 YEAR", $room->id, $curdate, $curdate));
			
			// now fill the dates that fit in the range	
			foreach($bookings as $booking) {
				$from_time = strtotime($booking->from_date);
				$to_time = strtotime($booking->to_date);
				
				// Loop between timestamps, 24 hours at a time
				// $i is current time
				for ($i = $from_time; $i < $to_time; $i = $i + 86400) {
					if($i > $year_from_time or $i <= $next_year_time) {						
						// add it to the array	
						$bookdate = date("m-d-Y", $i);				
						$udates[] = $bookdate;
					} 
				}
				
				$parts = explode("-", $booking->from_date);
				$month = intval($parts[1]);
				$year = $parts[0];
				
				// put in months divs to show when moving the calendar
				if(!isset($month_divs[$year .'-'.$month])) $month_divs[$year .'-'.$month] = array("bookings"=>array());
				$month_divs[$year .'-'.$month]['bookings'][] = $booking;
			}
				
			hostelpro_enqueue_datepicker();
			$dateformat = get_option('date_format');			
			if(@file_exists(get_stylesheet_directory().'/hostelpro/room-booking-calendar.html.php')) include get_stylesheet_directory().'/hostelpro/room-booking-calendar.html.php';
			else include(HOSTELPRO_PATH."/views/room-booking-calendar.html.php");
	}
}