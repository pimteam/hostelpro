<?php
class HostelPROBookings {
	static function manage() {
		global $wpdb, $user_ID;
		$_booking = new HostelPROBooking();
		
		$multiuser_access = 'all';
		$multiuser_access = HostelPRORoles::check_access('bookings_access');		
		
		// select extra fields
		$fields = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_FIELDS." ORDER BY id");
		
		// select addon services if any
		$addons = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_ADDONS." WHERE is_inactive=0 ORDER BY id");
		
		$shortcode_id = "hostelpro";
		
		hostelpro_enqueue_datepicker();
		
		$email_options = get_option('hostelpro_email_options');
		
		// prepare some vars
		if(!empty($_POST['ok'])) {
					$_POST['from_date'] = $_POST['fromyear'].'-'.$_POST['frommonth'].'-'.$_POST['fromday'];
					$_POST['to_date'] = $_POST['toyear'].'-'.$_POST['tomonth'].'-'.$_POST['today'];
					
					// apply addon services
					$cost = 0; // here we don't need this var info
					$datefrom_time = strtotime($_POST['from_date']);
					$dateto_time = strtotime($_POST['to_date']);		
					$numdays = ($dateto_time   -  $datefrom_time) / (24 * 3600);	
					$addons_breakdown = 	HostelPROAddons :: apply($numdays, $cost);
					$_POST['addons'] = $addons_breakdown;
		}
		
		switch(@$_GET['do']) {
			case 'add':
				if(!empty($_POST['ok'])) {					
					$_POST['status'] = 'active';
					
					try {
						$bid = $_booking -> add($_POST);
						
						if(!empty($_POST['send_email_notice'])) $_booking->email($bid, 'user');
						
					} catch(Exception $e) {};
					hostelpro_redirect("admin.php?page=hostelpro_bookings&type=".$_GET['type']);
				}
							
				// select rooms for the dropdown
				$rooms = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_ROOMS." ORDER BY title");
				if(@file_exists(get_stylesheet_directory().'/hostelpro/booking.html.php')) include get_stylesheet_directory().'/hostelpro/booking.html.php';
				else include(HOSTELPRO_PATH."/views/booking.html.php");				
			break;
			
			case 'edit':
				$_GET['id'] = intval($_GET['id']);
				if($multiuser_access == 'own') {
					$booking = $_booking->get($_GET['id']);
					if(@$booking->editor_id != $user_ID) wp_die(__('You can manage only bookings you have added manually.', 'hostelpro'));
				}				
			
				if(!empty($_POST['del'])) {
					$_booking->delete($_GET['id']);
					hostelpro_redirect("admin.php?page=hostelpro_bookings&type=$_GET[type]&offset=$_GET[offset]");				
				}				
			
				if(!empty($_POST['ok'])) {					
					try {
						$_booking -> edit($_POST, $_GET['id']);
					} catch (Exception $e) {};
					hostelpro_redirect("admin.php?page=hostelpro_bookings&type=$_GET[type]&offset=$_GET[offset]");
				}			
			
				// select booking
				$booking = $_booking->get($_GET['id']);
				
				// unserialize addons if any
				$current_addons = unserialize($booking->addon_details);
				
				// select rooms for the dropdown
				$rooms = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_ROOMS." ORDER BY title");
				if(@file_exists(get_stylesheet_directory().'/hostelpro/booking.html.php')) include get_stylesheet_directory().'/hostelpro/booking.html.php';
				else include(HOSTELPRO_PATH."/views/booking.html.php");		
			break;
			
			// view/print booking details. Will allow also to confirm/cancel
			case 'view':
				$_GET['id'] = intval($_GET['id']);
				if($multiuser_access == 'own') {
					$booking = $_booking->get($_GET['id']);
					if(@$booking->editor_id != $user_ID) wp_die(__('You can view only bookings you have added manually.', 'hostelpro'));
				}	
				
				// select booking and room details
				$booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS." WHERE id=%d", $_GET['id']));
				$room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $booking['room_id']));	
			
				if(@file_exists(get_stylesheet_directory().'/hostelpro/view-booking.html.php')) include get_stylesheet_directory().'/hostelpro/view-booking.html.php';
				else include(HOSTELPRO_PATH."/views/view-booking.html.php");			
			break;			
			
			// list bookings
			default:
				// which bookings to show - upcoming or past?
				$type = empty($_GET['type']) ? 'upcoming' : $_GET['type'];
				$offset = empty($_GET['offset']) ? 0 : $_GET['offset'];
				$dir = empty($_GET['dir']) ? 'ASC' : $_GET['dir'];
				if($dir != 'ASC' and $dir != 'DESC') $dir = 'ASC';
				$odir = ($dir == 'ASC') ? 'DESC' : 'ASC';
				
				$owner_sql = '';
				if($multiuser_access == 'own') {
					$owner_sql = $wpdb->prepare(" AND tB.editor_id = %d ", $user_ID);
				}	
				
				// mark booking as fully paid	
				if(!empty($_GET['mark_paid'])) {
					if($multiuser_access == 'own') {
						$booking = $_booking->get($_GET['id']);
						if(@$booking->editor_id != $user_ID) wp_die(__('You can view only bookings you have added manually.', 'hostelpro'));
					}						
					
					$_booking->mark_paid($_GET['id']);
					// what was the idea of this $_GET['send_emails']??
					if(!empty($_GET['send_emails']) or true) {
						$_booking->email($_GET['id'], 'user');
					}	
					
					hostelpro_redirect("admin.php?page=hostelpro_bookings&type=".$type."&offset=".$offset);
				}
				
				// mass delete?
				if(!empty($_POST['mass_delete'])) {
					$bids = is_array($_POST['bids']) ? $_POST['bids'] : array(0);
					$bids = hostelpro_int_array($bids);
					$bid_sql = implode(", ", $bids);
					$wpdb->query("DELETE FROM ".HOSTELPRO_BOOKINGS." WHERE id IN ($bid_sql) $owner_sql");
				}
				
				// define $where_sql and orderby depending on the $type		
				$curdate = date("Y-m-d", current_time('timestamp'));		
				if($type == 'upcoming') {
					$where_sql = "AND from_date >=  '$curdate' ";
					$orderby = "ORDER BY from_date";
					
				}
				else {
					$where_sql = "AND from_date < '$curdate' ";
					$orderby = "ORDER BY from_date DESC";
				}
				
				// define limit (as it's paginated)				
				$page_limit = 20;
				$limit_sql = empty($_GET['export']) ? $wpdb->prepare("LIMIT %d, %d", $offset, $page_limit) : ''; 
				
				// search filter
				if(!empty($_GET['contact_email'])) {
					$_GET['contact_email'] = sanitize_text_field($_GET['contact_email']);
					$where_sql .= " AND contact_email LIKE '%".$_GET['contact_email']."%' ";
				}
				if(!empty($_GET['contact_name'])) {
					$_GET['contact_name'] = sanitize_text_field($_GET['contact_name']);
					$where_sql .= " AND contact_name LIKE '%".$_GET['contact_name']."%' ";
				}
				if(!empty($_GET['room_id'])) {
					$_GET['room_id'] = intval($_GET['room_id']);
					$where_sql .= $wpdb->prepare(" AND room_id = %d ", $_GET['room_id']);
				}
				if(!empty($_GET['status'])) {
					$_GET['status'] = sanitize_text_field($_GET['status']);
					$where_sql .= $wpdb->prepare(" AND status = %s ", $_GET['status']);
				}				 
				if(!empty($_GET['booking_id'])) {
					$_GET['booking_id'] = intval($_GET['booking_id']);
					$where_sql .= $wpdb->prepare(" AND tB.id = %d ", $_GET['booking_id']);
				}
				if(!empty($_GET['contact_email']) or !empty($_GET['contact_name']) 
					or !empty($_GET['room_id']) or !empty($_GET['status']) or !empty($_GET['booking_id'])) $filters_apply = true;	
					
				if(!empty($_GET['ob'])) {					
					$orderby = "ORDER BY ".sanitize_text_field($_GET['ob']) . ' ' . $dir;
				}
				
				$bookings = $wpdb->get_results("SELECT SQL_CALC_FOUND_ROWS tB.*, tR.title as room 
					FROM ".HOSTELPRO_BOOKINGS." tB JOIN ".HOSTELPRO_ROOMS." tR ON tR.id = tB.room_id
					WHERE is_static=0 $where_sql $owner_sql $orderby $limit_sql");
				$count = $wpdb->get_var("SELECT FOUND_ROWS()");	
				
				// select custom data if any
				$bids = array(0);
				foreach($bookings as $booking) $bids[] = $booking->id;
				$datas = $wpdb->get_results("SELECT tD.data as data, tD.booking_id as booking_id, 
					tD.field_id as field_id, tF.label as label FROM ".HOSTELPRO_DATAS." tD
					JOIN ".HOSTELPRO_FIELDS." tF ON tF.id = tD.field_id
					WHERE tD.booking_id IN (".implode(",", $bids).") ORDER BY tF.name");
					
				if(sizeof($datas)) {
					foreach($bookings as $cnt=>$booking) {
						$custom_data = '';
						foreach($datas as $data) {
							if($data->booking_id == $booking->id) {
								$custom_data .= sprintf(__("<b>%s:</b> %s<br>", 'hostelpro'), $data->label, $data->data);
							}
						} // end foreach data
						
						$bookings[$cnt]->custom_data = $custom_data;
					} // end foreach booking
				} // end if custom data	
				
				// select all rooms
				$rooms = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_ROOMS." ORDER BY title");
				
				$email_options = get_option('wphostel_email_options');
				$dateformat = get_option('date_format');
				$timeformat = $dateformat . ' ' . get_option('time_format');
				
				if(!empty($_GET['export'])) self :: export($bookings, $datas, $dateformat, $timeformat);
				
				$filters_str = '&contact_email='.sanitize_email(@$_GET['contact_email']).'&contact_name='.esc_attr(@$_GET['contact_name']).'&room_id='.intval(@$_GET['room_id'])
				.'&status='.esc_attr(@$_GET['status']).'&booking_id='.intval(@$_GET['booking_id']);
				
				if(@file_exists(get_stylesheet_directory().'/hostelpro/bookings.html.php')) include get_stylesheet_directory().'/hostelpro/bookings.html.php';
				else include(HOSTELPRO_PATH."/views/bookings.html.php");		
			break;
		}
	}
	
	// manage unavailable dates
	// they are entered as "static" booking. 
	// these bookings always have 1 DB record for each single date
	// from equals the date, to is the next day
	static function unavailable() {
		global $wpdb;
		$_booking = new HostelPROBooking();
		$_room = new HostelPRORoom();
		$dateformat = get_option('date_format');
		
		$multiuser_access = 'all';
		$multiuser_access = HostelPRORoles::check_access('unavailable_access');
		
		$date = empty($_POST['date']) ? date("Y-m-d") : $_POST['date'];
		$to_date = empty($_POST['to_date']) ? date("Y-m-d", strtotime($date) + 24*3600) : $_POST['to_date']; 
		
		// select all available rooms
		$rooms = $wpdb->get_results( "SELECT * FROM ".HOSTELPRO_ROOMS." ORDER BY title" );
		
		$unavailable_room_ids = (!empty($_POST['ids']) and is_array($_POST['ids'])) ? $_POST['ids'] : array(0);		
		if(!empty($_POST['set_dates'])) {
			foreach($rooms as $room) {
				if(in_array($room->id, $unavailable_room_ids)) {
					// make sure there is no static booking for the room on this date
					$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".HOSTELPRO_BOOKINGS." 
						WHERE room_id=%d AND from_date<=%s AND to_date>=%s AND is_static=1", $room->id, $date, $to_date));
					if(!$exists) {
						$wpdb->query($wpdb->prepare("INSERT INTO ".HOSTELPRO_BOOKINGS." SET
							room_id=%d, from_date=%s, to_date=%s, is_static=1", $room->id, $date, $to_date));
					}	
				}
				else {
					// delete any static bookings for this room on this exact period
					$wpdb->query($wpdb->prepare("DELETE FROM ".HOSTELPRO_BOOKINGS." 
						WHERE is_static=1 AND from_date=%s AND to_date=%s AND room_id=%d", $date, $to_date, $room->id));
						
					// but in case there is period that overlaps partially we'll need to break it up on parts
					$overlap_both = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS." 
						WHERE is_static=1 AND from_date<=%s AND to_date>=%s AND room_id=%d", $date, $to_date, $room->id));
						
					if(!empty($overlap_both->id)) {
						// delete the overlap and enter 2 other periods
						$wpdb->query($wpdb->prepare("DELETE FROM " . HOSTELPRO_BOOKINGS. " WHERE is_static=1 AND from_date<=%s AND to_date>=%s AND room_id=%d", 
						 $date, $to_date, $room->id));
						
						// 1st period
						$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".HOSTELPRO_BOOKINGS." 
							WHERE room_id=%d AND from_date<=%s AND to_date>=%s AND is_static=1", $room->id, $overlap_both->from_date, $date));						
						if(!$exists) {
							$wpdb->query($wpdb->prepare("INSERT INTO ".HOSTELPRO_BOOKINGS." SET
								room_id=%d, from_date=%s, to_date=%s, is_static=1", $room->id, $overlap_both->from_date, $date));
						}	
						
						// 2nd period
						$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".HOSTELPRO_BOOKINGS." 
							WHERE room_id=%d AND from_date<=%s AND to_date>=%s AND is_static=1", $room->id, $to_date, $overlap_both->to_date));						
						if(!$exists) {
							$wpdb->query($wpdb->prepare("INSERT INTO ".HOSTELPRO_BOOKINGS." SET
								room_id=%d, from_date=%s, to_date=%s, is_static=1", $room->id, $to_date, $overlap_both->to_date));
						}		
					}
				} // end unsetting
			}
		}
			
			
		// now select all static bookings on the given dates and feel new $unavailable_room_ids array
		$static_bookings = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS." 
			WHERE is_static=1 AND from_date<=%s AND to_date>=%s", $date, $to_date));
		$unavailable_room_ids = array();
		foreach($static_bookings as $booking) $unavailable_room_ids[] = $booking->room_id;
		
		// now select partially unavailable periods so we can show them
		$partially_unavailable = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS." 
			WHERE is_static=1 AND ((from_date < %s AND to_date >=%s AND to_date < %s)
			 OR (from_date >=%s AND from_date <= %s AND to_date > %s)
			 OR (from_date >=%s AND from_date <= %s AND to_date > %s AND to_date <= %s))", $date, $date, $to_date,
			 	$date, $to_date, $to_date,
			 	$date, $to_date, $date, $to_date));
				
		hostelpro_enqueue_datepicker();
		if(@file_exists(get_stylesheet_directory().'/hostelpro/unavailable-dates.html.php')) include get_stylesheet_directory().'/hostelpro/unavailable-dates.html.php';
		else include(HOSTELPRO_PATH."/views/unavailable-dates.html.php");		
	}
	
	// do the booking
	static function book() {
		global $wp, $wpdb, $post;
		
		$booking_mode = get_option('hostelpro_booking_mode');
		if($booking_mode == 'none') return __('Online booking is not enabled.', 'hostelpro');
		
		$multiple_bookings = get_option('hostelpro_multi_booking');
		
		// insert booking details
		$_booking = new HostelPROBooking();
		$_room = new HostelPRORoom();
		
		$from_date = $_POST['from_date'];
		$to_date = $_POST['to_date'];
		if(empty($_POST['beds'])) $_POST['beds'] = 1;
		
		// honeypot?
		if(get_option('hostelpro_honeypot') == 1) {
			if($_POST['hostelpro_ssid'] != '__'.md5('hostelprohoney'.$_SERVER['REMOTE_ADDR'])) {
				return '<!--BOOKERROR-->'.__('Sorry, we could not process your reservation. Please contact us to book manually	.','hostelpro');
			}
		}
		
		// captcha?
		if(get_option('hostelpro_text_captcha_enabled') == 1) {
			$verified = HostelPROTextCaptcha :: verify($_POST['hostelpro_text_captcha_question'], $_POST['hostelpro_text_captcha_answer']);
			if(!$verified) return '<!--BOOKERROR-->'.__('The answer to the verification question was not correct.','hostelpro');
		}
		
		// make sure it's not a duplicate
		$bid = $wpdb->get_var($wpdb->prepare("SELECT id FROM ".HOSTELPRO_BOOKINGS."
			WHERE room_id=%d AND from_date=%s AND to_date=%s AND contact_email=%s AND status='pending'",
			$_POST['room_id'], $from_date, $to_date, $_POST['contact_email']));
			
		// select the room
		$room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $_POST['room_id']));	
		$check_room = (array)$room;	
		
		// if($room->price_type == 'per-room') $_POST['beds'] = 1; 			
			
		// calculate cost
		$datefrom_time = strtotime($from_date);
		$dateto_time = strtotime($to_date);		
		$numdays = ($dateto_time   -  $datefrom_time) / (24 * 3600);	
		
		if($room->price_type == 'per-room')	$daily_cost = $room->price;
		else {
			// regular case
			$daily_cost = $_POST['beds'] * $room->price;
			
			// case with children beds
			if(!empty($room->allow_child_bed_price) and !empty($_POST['child_beds'])) {
				$adult_beds = $_POST['beds'] - $_POST['child_beds'];
				$daily_cost = $adult_beds * $room->price + $_POST['child_beds'] * $room->child_bed_price;
			}
		}
		
		// booked a whole room in a room that allows whole room price?
		if($room->rtype == 'dorm' and !empty($room->whole_dorm_price) and $room->whole_dorm_price > 0 and $_POST['beds'] == $room->beds) {
			$daily_cost = $room->whole_dorm_price;
		}
		
		
		
		// private room with partial occupancy?
		if($room->rtype == 'private' and $room->discount_part_occupancy) {
			$part_occupancy_prices = explode(",", $room->part_occupancy_prices);
			if(!empty($part_occupancy_prices[($_POST['beds'] - 1)])) {
				// do this only if a price is given matching the entered number of beds. Otherwise we calculate the price as per default behavior
				$daily_cost = $part_occupancy_prices[($_POST['beds'] - 1)];
			}
			else {
				$daily_cost = ($room->price_type == 'per-room') ? $room->price : $room->beds * $room->price;
			}
		}	
		
		$cost = $numdays * $daily_cost;	
		$booking_cost = $cost;
		
		// apply extra beds
		if(!empty($_POST['extra_beds']) and $room->extra_beds and $_POST['extra_beds'] > 0) {
			$extra_beds_cost = $numdays * $room->extra_bed_price * $_POST['extra_beds'];
			$cost += $extra_beds_cost;
		}
		
		// if(HOSTELPRO_NO_DECIMALS) $cost = hostelpro_number_format($cost);
		
		// apply addon services
		$addons_cost = 0;
		$addons_breakdown = 	HostelPROAddons :: apply($numdays, $cost, $addons_cost);
		
		// apply discounts
		list($discount, $date_discounts) = HostelPRODiscounts :: period_discount($datefrom_time, $dateto_time, $room, $_POST['beds'], @$_POST['coupon']);		
		$cost -= $discount;			
		
		// min. price discount?
		$curdate = date("Y-m-d", current_time('timestamp'));
		$min_price_discount = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_DISCOUNTS."
			WHERE discount_value > 0 AND min_price > 0 AND min_price < %f AND (date_condition=0 OR (date_condition=1 AND date_to >=%s))
				AND (coupon_condition=0 OR coupon=%s)
				AND (room_id=0 OR room_id=%d)
				AND (days_condition=0 OR days <= %d) 
				ORDER BY min_price DESC LIMIT 1", 
				$cost, $curdate, @$_POST['coupon'], $room->id, $numdays));
		if(!empty($min_price_discount->id)) {
			// apply this discount
			$discount_value = ($min_price_discount->disc_type == 'surcharge') ? 0 - $min_price_discount->discount_value : $min_price_discount->discount_value;
			if($min_price_discount->discount_type == 'amount') $price_discount += $min_price_discount->discount_value;
			else $price_discount += round( ($cost * ($discount_value/100)), 2);			
			$cost -= $price_discount;
			$discount += $price_discount;
		}		

		$_POST['discount'] = $discount;
		// end min price discount calculation
		
		$_POST['amount_due'] = $cost;	
		
		// prepare serialized info with price for each booked date
		$date_prices = array();
		foreach($date_discounts as $d=>$date_discount) {
			$date_prices[$d] = $daily_cost - $date_discount;
			if(HOSTELPRO_NO_DECIMALS) $date_prices[$d] = hostelpro_number_format($date_prices[$d]);
		}	
		$_POST['date_prices'] = serialize($date_prices);	
		
		// apply percentage
		$perc = get_option('hostelpro_advance_payment_percentage');
		$unit = get_option('hostelpro_advance_payment_unit');
		$full_cost = $cost;
		if(empty($unit) or $unit == '%') $cost = round($cost * ($perc / 100), 2);
		else {
			$cost = $perc; // in this case it's not % but fixed amount
			// any changce that the fixed is larger than the cost?
			if($cost > $full_cost) $cost = $full_cost;
		}			
		
		$_POST['amount_paid'] =  0;
		
		$_POST['status'] = 'pending';
									
		if(empty($bid)) {
			// minimum stay required?
			$min_stay = HostelPROMinStays :: find($from_date, $to_date);
			if(!empty($min_stay) and $min_stay > $numdays) {
				return '<!--BOOKERROR-->'.sprintf(__('Minimum stay of %d days is required.', 'hostelpro'), $min_stay);
			}			
			
			// maximum stay allowed?
			$max_stay = get_option('hostelpro_max_stay');
			if(!empty($max_stay) and $max_stay < $numdays) {
				return '<!--BOOKERROR-->'.sprintf(__('Maximum stay of %d days is allowed.', 'hostelpro'), $max_stay);
			}			
			
			if($_POST['beds'] > $room->beds) {
				return '<!--BOOKERROR-->'.sprintf(__('You are trying to book more beds than available. This room has %d beds.', 'hostelpro'), $room->beds);
			}
			
			if(@$_POST['extra_beds'] > $room->extra_beds) {				
				return '<!--BOOKERROR-->'.sprintf(__('You are trying to book more extra beds than available. This room offers up to %d extra beds.', 'hostelpro'), $room->extra_beds);
			}
						
			// if this is a private room, we cannot book less beds than the room has
			if($room->rtype == 'private' and $_POST['beds'] != $room->beds and $room->price_type != 'per-room' and !$room->discount_part_occupancy) {				
				return '<!--BOOKERROR-->'.sprintf(__('This is a private room. You have to book all the %d beds', 'hostelpro'), $room->beds);
			}				
			
			// select all bookings in the given period
			$bookings = $_booking->select_in_period($from_date, $to_date);
							
			// make sure all dates are available
			$check_room = $_room->availability($check_room, $bookings, $from_date, $to_date, $numdays, $datefrom_time, $dateto_time);
			foreach($check_room['days']	as $day) {
				if(!$day['available_beds'] or $day['available_beds'] < $_POST['beds']) return '<!--BOOKERROR-->'.__('In your selection there are dates when the room is not available or there are not enough free beds. Please check your selection.','hostelpro');
			}		
				
			try {		
				$_POST['addons'] = $addons_breakdown;
				$bid = $_booking->add($_POST);
			}
			catch(Exception $e) {
				wp_die($e->getMessage());
			}
		}
		else {
			// Booking ID $bid not empty
			// it's a duplicate, but maybe num beds and costs were changed?			
			$wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_BOOKINGS." SET 
				amount_paid=%s, amount_due=%s, beds=%d, addons=%s WHERE id=%d",
				$_POST['amount_paid'], $_POST['amount_due'], $_POST['beds'], $addons_breakdown, $bid));
		}
		
		$amount_now = HOSTELPRO_NO_DECIMALS ? hostelpro_number_format($cost) : number_format($cost,2,".","");
		$amount_arrival = HOSTELPRO_NO_DECIMALS ? hostelpro_number_format($full_cost - $cost) : number_format(($full_cost - $cost),2,".","");
		
		// when doing multiple bookings we have to insert it in the DB and load preview + confirm instead of all the stuff below "SINGLE BOOKING MODE"
		if($multiple_bookings == 1) {
			// set booking session_id if none and update
			if(empty($_COOKIE['hostelpro_booking_session'])) {
				$hostelpro_booking_session = uniqid('hostelpro', true);
				setcookie('hostelpro_booking_session', $hostelpro_booking_session, time() + 24*3600, '/');				
			}			
			else $hostelpro_booking_session = $_COOKIE['hostelpro_booking_session'];
			
			// update the booking's amount now and arrival
			$wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_BOOKINGS." 
				SET amount_now=%f, amount_arrival=%f, session_id=%s
				WHERE id=%d", $amount_now, $amount_arrival, $hostelpro_booking_session, $bid));
				
			// now select all bookings with this session
			$bookings = $wpdb->get_results($wpdb->prepare("SELECT tB.*, tR.title as room_name 
				FROM ".HOSTELPRO_BOOKINGS." tB JOIN ".HOSTELPRO_ROOMS." tR ON tR.id = tB.room_id
				WHERE tB.session_id=%s ORDER BY tB.id", $hostelpro_booking_session));
				
			$dateformat = get_option('date_format');	
						
			// load the preview form
			include(HOSTELPRO_PATH . '/views/multiple-bookings.html.php');	
			exit;
		}
		
		### SINGLE BOOKING MODE ###
		// if paypal display payment button otherwise display success message
		if(get_option('hostelpro_booking_mode') == 'paypal') {
			$paypal_host = "www.paypal.com";
			$paypal_sandbox = get_option('hostelpro_paypal_sandbox');
			if($paypal_sandbox == '1') $paypal_host = 'www.sandbox.paypal.com';		
			$paypal_thankyou = get_option('wphostel_paypal_return');
			// echo $paypal_thankyou;
			if(empty($paypal_thankyou)) $paypal_thankyou = get_permalink(@$post->ID);
			if(empty($paypal_thankyou)) $paypal_thankyou = site_url();
			$paypal_return = (get_option('hostelpro_use_pdt') == 1) ? esc_url(add_query_arg(array('hostelpro_pdt' => 1), site_url())) : $paypal_thankyou;	
			if(@file_exists(get_stylesheet_directory().'/hostelpro/pay-paypal.html.php')) include get_stylesheet_directory().'/hostelpro/pay-paypal.html.php';
			else include(HOSTELPRO_PATH."/views/pay-paypal.html.php");	
		}
		elseif(get_option('hostelpro_booking_mode') == 'stripe') {
			$stripe_public = get_option('hostelpro_stripe_public');
			$stripe_secret = get_option('hostelpro_stripe_secret');
			if(empty($stripe_public) or empty($stripe_secret)) wp_die('Your public or private Stripe key is empty, please visit the options page to enter them'); 
			if(@file_exists(get_stylesheet_directory().'/hostelpro/pay-stripe.html.php')) include get_stylesheet_directory().'/hostelpro/pay-stripe.html.php';
			else include(HOSTELPRO_PATH."/views/pay-stripe.html.php");	
		}
		else {			
			// send email if you have to
			$_booking->email($bid);
		}
		
		$instructions = get_option('hostelpro_payemnt_instructions');
		if(!empty($instructions)) {
			$instructions = stripslashes($instructions);
			
			$instructions = str_replace('{{{booking-id}}}', $bid, $instructions);
			$instructions = str_replace('{{{amount-now}}}', $amount_now, $instructions);
			$instructions = str_replace('{{{amount-arrival}}}', $amount_arrival, $instructions);
			if(strstr($instructions, '{{{costs-breakdown}}}')) {
				$breakdown_table = '<table class="hostelpro-breakdown">';
				$breakdown_table .= '<tr><th>'.__('Item', 'hostelpro').'</th><th>'.__('Quantity', 'hostelpro').'</th>
				<th>'.__('Unit price', 'hostelpro').'</th><th>'.__('Cost', 'hostelpro').'</th></tr>';
				$breakdown_table .= '<tr><td>'.stripslashes($room->title).'</td><td>'.sprintf(__('%d days', 'hostelpro'), $numdays).'</td>
					<td>'.sprintf(__('%s %s per day', 'hostelpro'), HOSTELPRO_CURRENCY, $daily_cost).'</td>
					<td>'.sprintf(__('%s %s', 'hostelpro'), HOSTELPRO_CURRENCY, $booking_cost).'</td></tr>';
				
				if(!empty($_POST['extra_beds']) and $extra_beds_cost) {
					$breakdown_table .= '<tr><td>'.__('Extra beds', 'hostelpro').'</td><td>'.$_POST['extra_beds'].'</td>
					<td>'.sprintf(__('%s %s per bed per day', 'hostelpro'), HOSTELPRO_CURRENCY, $room->extra_bed_price).'</td>
					<td>'.sprintf(__('%s %s', 'hostelpro'), HOSTELPRO_CURRENCY, $extra_beds_cost).'</td><tr>';
				}
				
				if(!empty($addons_breakdown)) $breakdown_table .= '<tr><td>'.__('Addon services:', 'hostelpro').'</td>
				<td>'.__('N/a', 'hostelpro').'</td><td>'.$addons_breakdown.'</td>
				<td>'.sprintf(__('%s %s', 'hostelpro'), HOSTELPRO_CURRENCY, $addons_cost).'</td></tr>';	
				
				if(!empty($discount)) {
					if($discount < 0) {
						$breakdown_table .= '<tr><td>'.__('Surcharge', 'hostelpro').'</td><td>1</td>
						<td>'.sprintf(__('%s %s', 'hostelpro'), HOSTELPRO_CURRENCY, abs($discount)).'</td>
						<td>'.sprintf(__('%s %s', 'hostelpro'), HOSTELPRO_CURRENCY, abs($discount)).'</td></tr>';
					}
					else {
						$breakdown_table .= '<tr><td>'.__('Discount', 'hostelpro').'</td><td>1</td>
						<td>'.sprintf(__('%s %s', 'hostelpro'), HOSTELPRO_CURRENCY, $discount).'</td>
						<td>'.sprintf(__('-%s %s', 'hostelpro'), HOSTELPRO_CURRENCY, abs($discount)).'</td></tr>';
					}
				}
					
				$breakdown_table .= '</table>';
				
				$instructions = str_replace('{{{costs-breakdown}}}', $breakdown_table, $instructions);
			}
			
			echo wpautop(do_shortcode($instructions));
		}	
		
		// conversion tracking?
		$convtrack_code = get_option('wphostel_convtrack_code');
		if(!empty($convtrack_code)) echo "|CONVTRACK|".stripslashes($convtrack_code);
	} // end book()
	
	// complete multiple booking
	static function multiple_book() {
		global $wp, $wpdb, $post;
		
		$_booking = new HostelPROBooking();
		$_room = new HostelPRORoom();		
		
		// now select all bookings with this session
		$bookings = $wpdb->get_results($wpdb->prepare("SELECT tB.*, tR.title as room_name 
				FROM ".HOSTELPRO_BOOKINGS." tB JOIN ".HOSTELPRO_ROOMS." tR ON tR.id = tB.room_id
				WHERE tB.status!='active' AND tB.session_id=%s ORDER BY tB.id", $_COOKIE['hostelpro_booking_session']));
		$dateformat = get_option('date_format');				
		$grand_total = $amount_now = $amount_arrival = 0; 
		foreach($bookings as $booking) {
			$grand_total += ($booking->amount_paid + $booking->amount_due);			
			$amount_now += $booking->amount_now;
			$amount_arrival += $booking->amount_arrival;
			$bid = 'M'.$booking->id;
		}		
		$cost = $grand_total;
		
		// apply percentage
		$perc = get_option('hostelpro_advance_payment_percentage');
		$unit = get_option('hostelpro_advance_payment_unit');
		$full_cost = $cost;
		if(empty($unit) or $unit == '%') $cost = round($cost * ($perc / 100), 2);
		else {
			$cost = $perc; // in this case it's not % but fixed amount
			// any changce that the fixed is larger than the cost?
			if($cost > $full_cost) $cost = $full_cost;
		}			
			
		// if paypal display payment button otherwise display success message
		if(get_option('hostelpro_booking_mode') == 'paypal') {
			$paypal_host = "www.paypal.com";
			$paypal_sandbox = get_option('hostelpro_paypal_sandbox');
			if($paypal_sandbox == '1') $paypal_host = 'www.sandbox.paypal.com';		
			$paypal_thankyou = get_option('wphostel_paypal_return');
			// echo $paypal_thankyou;
			if(empty($paypal_thankyou)) $paypal_thankyou = get_permalink(@$post->ID);
			if(empty($paypal_thankyou)) $paypal_thankyou = site_url();
			$paypal_return = (get_option('hostelpro_use_pdt') == 1) ? esc_url(add_query_arg(array('hostelpro_pdt' => 1), site_url())) : $paypal_thankyou;	
			if(@file_exists(get_stylesheet_directory().'/hostelpro/pay-paypal.html.php')) include get_stylesheet_directory().'/hostelpro/pay-paypal.html.php';
			else include(HOSTELPRO_PATH."/views/pay-paypal.html.php");	
		}
		elseif(get_option('hostelpro_booking_mode') == 'stripe') {
			$stripe_public = get_option('hostelpro_stripe_public');
			$stripe_secret = get_option('hostelpro_stripe_secret');
			if(empty($stripe_public) or empty($stripe_secret)) wp_die('Your public or private Stripe key is empty, please visit the options page to enter them'); 
			if(@file_exists(get_stylesheet_directory().'/hostelpro/pay-stripe.html.php')) include get_stylesheet_directory().'/hostelpro/pay-stripe.html.php';
			else include(HOSTELPRO_PATH."/views/pay-stripe.html.php");	
		}
		else {			
			// send email if you have to
			//unset($_SESSION['hostelpro_booking_session']);
			setcookie('hostelpro_booking_session', '', time() - 24*3600, '/');
			$_booking->email($bid);
		}
		
		$instructions = get_option('hostelpro_payemnt_instructions');
		if(!empty($instructions)) {
			$instructions = stripslashes($instructions);
			
			$instructions = str_replace('{{{booking-id}}}', $bid, $instructions);
			$instructions = str_replace('{{{amount-now}}}', $amount_now, $instructions);
			$instructions = str_replace('{{{amount-arrival}}}', $amount_arrival, $instructions);
			if(strstr($instructions, '{{{costs-breakdown}}}')) {
				ob_start();
				$no_delete = true;
				include(HOSTELPRO_PATH . '/views/partial/multiple-table.html.php');
				$breakdown_table = ob_get_clean();
								
				$instructions = str_replace('{{{costs-breakdown}}}', $breakdown_table, $instructions);
			}
			
			echo wpautop($instructions);
		}	
	}
	
	// export booking logs
	static function export($bookings, $datas, $dateformat, $timeformat) {
		global $wpdb;	 	
 		$newline = hostelpro_define_newline();
		$rows = array();
		$has_data = count($datas);
		
		$delim = get_option('hostelpro_csv_delim');
		if(empty($delim) or !in_array($delim, array(",", "tab"))) $delim = ",";
		if($delim == 'tab') $delim = "\t";
		
		$titlerow = __('ID', 'hostelpro').','.__('Rooms/beds', 'hostelpro').','.__('Contact name', 'hostelpro').','.
			__('Contact email', 'hostelpro').','.__('Booking dates', 'hostelpro').','.__('Time of booking', 'hostelpro').','
			.__('Amount paid/due', 'hostelpro') . ',' . __('Status', 'hostelpro'); 
		if($has_data) $titlerow .= ',' . __('Custom fields', 'hostelpro');
		$rows[] = $titlerow;
			
		foreach($bookings as $booking) {
			$booking_beds = $booking->extra_beds ? sprintf(__("%d + %d", 'hostelpro'), $booking->beds, $booking->extra_beds) : $booking->beds;
			$row = $booking->id. ',"' . sprintf(__('%s beds in %s', 'hostelpro'), $booking_beds, stripslashes($booking->room)). '","'
				. $booking->contact_name . '","' . $booking->contact_email . '","' 
				. date($dateformat, strtotime($booking->from_date)).' - '.date($dateformat, strtotime($booking->to_date)) .'","' 
				. ($booking->created_time ? date($timeformat, strtotime($booking->created_time)) : __('n/a', 'hostelpro')) . '",'
				. HOSTELPRO_CURRENCY." ".$booking->amount_paid." / ".HOSTELPRO_CURRENCY.' '.$booking->amount_due . ',';
				
			switch($booking->status) {
				case 'active': $row .=  __('Active', 'hostelpro'); break;
				case 'pending': $row .=  __('Pending', 'hostelpro'); break;
				case 'cancelled': $row .= __('Cancelled', 'hostelpro'); break;
			} // end switch
			
			if($has_data) {
				$row .= ',"' . $booking->custom_data .'"';
			}
			
			$rows[] = $row;
		}	
		
		$csv = implode($newline,$rows);
			
		// credit to http://yoast.com/wordpress/users-to-csv/	
		$now = gmdate('D, d M Y H:i:s') . ' GMT';
	
		header('Content-Type: ' . hostelpro_get_mime_type());
		header('Expires: ' . $now);
		header('Content-Disposition: attachment; filename="bookings.csv"');
		header('Pragma: no-cache');
		echo $csv;
		exit;
	} // export
	
	// calendar overview by month/year
	static function calendar_overview($atts = null, $in_shortcode = false) {
		global $wpdb, $post;
		$_room = new HostelPRORoom();
		$_booking = new HostelPROBooking();
		
		$multiuser_access = 'all';
		$multiuser_access = HostelPRORoles::check_access('overview_access');
		
		$month = empty($_GET['month']) ? date('m') : sprintf('%02d', intval($_GET['month']));
		$year = empty($_GET['y']) ? date('Y') : intval($_GET['y']);
		
		$target_url = 'admin.php?page=hostelpro_calendar_overview';
		if($in_shortcode) {
			$permalink = get_permalink($post->ID);
			$target_url = $permalink."?1=1";
		}
		
		// define next & prev month & year		
		if($month  == '12') {
			$next_month = '01';
			$next_year = $year + 1;
		}
		else {
			$next_month = sprintf('%02d', intval($month) + 1);
			$next_year = $year;
		}
		
		if($month == '01') {
			$prev_month = '12';
			$prev_year = $year - 1;
		}
		else {
			$prev_month = sprintf('%02d', intval($month) - 1);
			$prev_year = $year;
		} 
		
		// num days this month
		$num_days = cal_days_in_month(CAL_GREGORIAN, intval($month), $year);
		
		// select all rooms in the system and run $_room->availability on them
		$rooms = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_ROOMS." ORDER BY title", ARRAY_A);
		$datefrom = $year.'-'.$month.'-01';
		$dateto = $year.'-'.$month.'-'.sprintf('%02d', $num_days);
		$bookings = $_booking->select_in_period($datefrom, $dateto);
		
		foreach($rooms as $cnt=>$room) {
			$rooms[$cnt] = $_room->availability($room, $bookings, $datefrom, $dateto, $num_days, strtotime($datefrom), strtotime($dateto));
		}
		
		if(@file_exists(get_stylesheet_directory().'/hostelpro/calendar-overview.html.php')) include get_stylesheet_directory().'/hostelpro/calendar-overview.html.php';
		else include(HOSTELPRO_PATH."/views/calendar-overview.html.php");
	} // end overview
}