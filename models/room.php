<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HostelPRORoom {
	function add($vars) {
		global $wpdb, $user_ID;
		
		$this->prepare_vars($vars);
		
		$result = $wpdb->query($wpdb->prepare("INSERT INTO ".HOSTELPRO_ROOMS." SET
			title=%s, rtype=%s, beds=%d, bathroom=%s, price=%s, description=%s, 
			dorm_gender=%s, price_type=%s, extra_beds=%d, extra_bed_price=%f, 
			notes=%s, discount_part_occupancy=%d, part_occupancy_prices=%s,
			overbook_beds=%d, ical_import=%s, editor_id=%d, whole_dorm_price=%f,
			allow_child_bed_price=%d, child_bed_price=%f, child_bed_label=%s,
			max_children=%d, adults_with_children=%d", 
			$vars['title'], $vars['rtype'], $vars['beds'], $vars['bathroom'], $vars['price'], 
			$vars['description'], @$vars['dorm_gender'], $vars['price_type'], $vars['extra_beds'], 
			$vars['extra_bed_price'], $vars['notes'], $vars['discount_part_occupancy'], 
			@$vars['part_occupancy_prices'], $vars['overbook_beds'], $vars['ical_import'], $user_ID,
			$vars['whole_dorm_price'], $vars['allow_child_bed_price'], $vars['child_bed_price'], 
			$vars['child_bed_label'], $vars['max_children'], $vars['adults_with_children']));
			
		if($result===false) return false;
		$id = $wpdb->insert_id;
		do_action('hostelpro_room_added', $id);
		return true;	
	}
	
	function edit($vars, $id) {
		global $wpdb;
		
		$this->prepare_vars($vars);
		
		$result = $wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_ROOMS." SET
			title=%s, rtype=%s, beds=%d, bathroom=%s, price=%s, description=%s, 
			dorm_gender=%s, price_type=%s, extra_beds=%d, extra_bed_price=%f,
			notes=%s, discount_part_occupancy=%d, part_occupancy_prices=%s, overbook_beds=%d, ical_import=%s,
			whole_dorm_price = %f, allow_child_bed_price=%d, child_bed_price=%f, child_bed_label=%s,
			max_children=%d, adults_with_children=%d 
			WHERE id=%d", 
			$vars['title'], $vars['rtype'], $vars['beds'], $vars['bathroom'], $vars['price'], 
			$vars['description'], $vars['dorm_gender'], $vars['price_type'], 
			$vars['extra_beds'], $vars['extra_bed_price'], $vars['notes'],
			$vars['discount_part_occupancy'], @$vars['part_occupancy_prices'], $vars['overbook_beds'], $vars['ical_import'], 
			$vars['whole_dorm_price'], $vars['allow_child_bed_price'], $vars['child_bed_price'], $vars['child_bed_label'],
			$vars['max_children'], $vars['adults_with_children'], $id));
			
		if($result === false) return false;
		
		do_action('hostelpro_room_edited', $id, $vars);
		return true;	
	}
	
	function prepare_vars(&$vars) {
		if(!empty($vars['discount_part_occupancy'])) {
			$vars['part_occupancy_prices'] = implode(",", @$vars['part_occupancy_prices']);
		}
		
		// always enter at least one bed
		if(isset($vars['beds']) and $vars['beds'] <= 0) $vars['beds'] = 1;
		
		// saitize vars
		$vars['title'] = sanitize_text_field($vars['title']);
		$vars['rtype'] = sanitize_text_field($vars['rtype']);
		$vars['beds'] = intval($vars['beds']);
		$vars['bathroom'] = sanitize_text_field($vars['bathroom']);
	   $vars['price'] = floatval($vars['price']);
		$vars['description'] = hostelpro_strip_tags($vars['description']);
		$vars['dorm_gender'] = sanitize_text_field($vars['dorm_gender']);
		$vars['price_type'] = sanitize_text_field($vars['price_type']);
		$vars['extra_beds'] = intval($vars['extra_beds']);
		$vars['extra_bed_price'] = floatval($vars['extra_bed_price']);
		$vars['notes'] = hostelpro_strip_tags($vars['notes']);
		$vars['discount_part_occupancy'] = empty($vars['discount_part_occupancy']) ? 0 : 1;
		$vars['overbook_beds'] = intval($vars['overbook_beds']);
		$vars['ical_import'] = strip_tags($vars['ical_import']);
		$vars['whole_dorm_price'] = floatval($vars['whole_dorm_price']);
		$vars['allow_child_bed_price'] = empty($vars['allow_child_bed_price']) ? 0 : 1;
		$vars['child_bed_price'] = floatval($vars['child_bed_price']);
		$vars['child_bed_label'] = sanitize_text_field($vars['child_bed_label']);
		$vars['max_children'] = intval($vars['max_children']);
		$vars['adults_with_children'] = intval($vars['adults_with_children']);
	}
	
	function delete($id) {
		global $wpdb;
		
		$result = $wpdb->query($wpdb->prepare("DELETE FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $id));
		
		if($result) {
			// delete also bookings
			$wpdb->query($wpdb->prepare("DELETE FROM ".HOSTELPRO_BOOKINGS." WHERE room_id=%d", $id));
		}
		
		if(!$result) return false;
		
		do_action('hostelpro_room_deleted', $id);
		return true;
	}
	
	// list all rooms, paginated. 
	// allow filters
	function find($filters = '') {
		global $wpdb;
		
		$ob = "id";
		$dir = "DESC";
		$offset = empty($_GET['offset']) ? 0 : $_GET['offset'];
		$limit = 20;
		
		$rooms = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." 
			WHERE 1 $filters			
			ORDER BY %s %s LIMIT %d, %d",
			$ob, $dir, $offset, $limit));
			
		return $rooms;	
	}
	
	// return specific room details
	function get($id) {
		global $wpdb;
		
		$room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $id));
		
		return $room;	
	}
	
	// prettify/translate some of the room's properties to be human friendly
	// $room is the $room object, used sometimes (for example on "Rtype" to define the dorm gender type
	function prettify($property, $value, $room = null) {
		switch($property) {
			case 'rtype':
				switch($value) {
					case 'private': return __('Private', 'hostelpro'); break;
					case 'dorm':
						switch($room->dorm_gender) {
							case 'male': return __('Male Dorm', 'hostelpro'); break;
							case 'female': return __('Female Dorm', 'hostelpro'); break;
							case 'mixed': default: return __('Mixed Dorm', 'hostelpro'); break;
						} 
					break;
				}	
			break;
			
			case 'bathroom':
				switch($value) {
					case 'ensuite': return __('Ensuite', 'hostelpro'); break;
					case 'shared': return __('Shared', 'hostelpro'); break;
				}
			break;
			
			case 'dorm_gender':
				switch($value) {
					case 'male': return __('Male', 'hostelpro'); break;
					case 'female': return __('Female', 'hostelpro'); break;
					case 'mixed': default: return __('Mixed', 'hostelpro'); break;
				}	
			break;
			
			case 'price_type':
				switch($value) {
					case 'per-bed': return __('Per person per night', 'hostelpro'); break;
					case 'per-room': return __('Per night for the whole room', 'hostelpro'); break;
				}
			break;
		}
	}

	// figure out availability of a room in given period
	// room has to be array, not object
	// $sync means whether the sync with any iCal URLs. We usually do but not when checking for max_sel_beds because this slows down too much
	function availability($room, $bookings, $datefrom, $dateto, $numdays, $datefrom_time, $dateto_time, $sync = true) {
		// some rooms may allow overbooking. Because of this we will use the overbook_beds instead of the beds 
		// property but still won't show more than $room['beds'] in the table
		$available_beds = (empty($room['overbook_beds']) or $room['overbook_beds'] <= $room['beds']) ? $room['beds'] : $room['overbook_beds'];		
		
		// import bookings from external calendar?
		if(!empty($room['ical_import']) and $sync) {			
			HostelPROSync :: import((object)$room, $datefrom, $dateto, $available_beds);
		}		
		
		for($i=0; $i < $numdays; $i++) {
				// lets store number of available beds. When they reach 0 the whole room is not available
				$room['days'][$i]['available_beds'] = $available_beds;
				// current day timestamp				
				$curday_time = $datefrom_time + $i*24*3600;
				foreach($bookings as $booking) {
					if($booking->room_id == $room['id']) {
						$booking_from_time = strtotime($booking->from_date);
						$booking_to_time = strtotime($booking->to_date) - 24*3600;
						
						if($booking_from_time <= $curday_time and $booking_to_time>=$curday_time) {
							$room['days'][$i]['available_beds'] -= $booking->beds;
							if($room['days'][$i]['available_beds'] < 0) $room['days'][$i]['available_beds'] = 0; 
							if($booking->is_static or ($room['rtype'] == 'private'
								and $room['overbook_beds'] <= $room['beds'])) $room['days'][$i]['available_beds'] = 0;
							if($room['days'][$i]['available_beds'] <= 0) break;
						}
					} // end if this booking is for this room
				} // end foreach booking
				
				// make sure we don't show more beds than the room has (for rooms that allow overbooking)
				if($room['days'][$i]['available_beds'] > $room['beds']) $room['days'][$i]['available_beds'] = $room['beds'];
			} // end for i		
			
			return $room;
	} // end availability
	
	// returns the array of 5 parts bed-related information for the room
	// for dorm rooms and "per room" price return 1
	// for private rooms return max beds
	// outputs also 0 or 1 after the | to show whether the user can change or not the number of rooms
	// explanation of the whole returned result:
	// parts[0] = number of beds. When calc_max is true we have to calculate based on selected dates
	// parts[1] - whether the number of beds can be changed or not 
	// parts[2] - price per room or per bed
	// parts[3] - extra beds available?
	// parts[4] - the price of the extra bed
	// parts[5] - room type
	function default_beds($id, $calc_max = false, $from_date = null, $to_date = null) {
		global $wpdb;
		
		// select room
		$room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $id));
		$parts = array();
		$parts[0] = $room->beds;	
		
		if($calc_max) {
			$check_room = (array)$room;
			$max_sel_beds = $this->max_sel_beds($check_room, $from_date, $to_date);
			$parts[0] = $max_sel_beds;
		}
		
		$parts[1] = ($room->rtype == 'dorm' or $room->discount_part_occupancy) ? 1 : 0;
		
		// in case price is per-room output also information to hide the beds 
		if($room->price_type == 'per-room' and !$room->discount_part_occupancy) $parts[2] = 1;
		else $parts[2] = 0;	
		
		// show extra beds info
		if($room->extra_beds) {
			$parts[3] = $room->extra_beds;
			$parts[4] = $room->extra_bed_price;
		} 
		else $parts[3] = $parts[4] = 0;
		
		$parts[5] = $room->rtype;		
		
		//print_r($parts);
		return $parts;
	}
	
	// max number of beds for a selected period - it's the number of beds in the busiest day for the room
	// @param $check_room is array
	function max_sel_beds($check_room, $from_date, $to_date) {
		$_booking = new HostelPROBooking();
		$datefrom_time = strtotime($from_date);
		$dateto_time = strtotime($to_date);		
		$numdays = ($dateto_time   -  $datefrom_time) / (24 * 3600);				
		
		// select all bookings in the given period
		$bookings = $_booking->select_in_period($from_date, $to_date, $check_room['id']);
		
		// make sure all dates are available
		$check_room = $this->availability($check_room, $bookings, $from_date, $to_date, $numdays, $datefrom_time, $dateto_time, false);
		
		$max_sel_beds = $check_room['beds'];
		if(empty($check_room['days'])) return $max_sel_beds;		
		
		foreach($check_room['days'] as $day) {				
			if($day['available_beds'] < $max_sel_beds) $max_sel_beds = $day['available_beds'];				
		}		
		
		return $max_sel_beds;
	} // end max_sel_beds
}