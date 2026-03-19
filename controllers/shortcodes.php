<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HostelPROShortcodes {
	public static $shortcode_ids;
	// displays and processes the booking form
	// $shortcode_id might be passed by ajax (in which case it's the room ID). This is to avoid problems with multiple
	// room calendars on a page
	static function booking($shortcode_id = null) {		
		global $wpdb, $post;
		$_booking = new HostelPROBooking();
		$_room = new HostelPRORoom();
		if(empty($shortcode_id)) $shortcode_id = self :: get_id();
		$booking_start = get_option('hostelpro_booking_start');
		if(empty($booking_start)) $booking_start = 'tomorrow';
		$book_to_date = ($booking_start == 'tomorrow') ? '+2 days' : 'tomorrow';
		
		// handle successful Stripe payment 
		if(!empty($_POST['hostelpro_stripe_success'])) return HostelPROPayment :: stripe_success();
		
		ob_start();
		$booking_mode = get_option('hostelpro_booking_mode');
		if($booking_mode == 'none') return __('Online booking is not enabled.', 'hostelpro');
		$dateformat = get_option('date_format');
		
		if(!empty($_POST['hostelpro_book'])) {
			HostelPROBookings :: book();
		}
		else {			
			$multiple_bookings = get_option('hostelpro_multi_booking');
			if($multiple_bookings == 1 and !empty($_COOKIE['hostelpro_booking_session'])) {			
				$last_booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS."
					WHERE session_id=%s ORDER BY id DESC LIMIT 1", $_COOKIE['hostelpro_booking_session']));	
				$GLOBALS['hostelpro_last_booking'] = $last_booking; // so we can use in shortcodes without running queries	
					
				// dates will be prepared by latest booking but will be used only if GET is empty				
				$booking_start = $last_booking->from_date;
				$book_to_date = $last_booking->to_date;	
			}		
			
			// when coming from the list of rooms we have dates in GET
			$_GET['from_date'] = !empty($_POST['from_date']) ? $_POST['from_date'] : @$_GET['from_date'];	
			$_GET['to_date'] = !empty($_POST['to_date']) ? $_POST['to_date'] : @$_GET['to_date'];
			$from_date = empty($_GET['from_date']) ? date("Y-m-d", strtotime($booking_start)) : $_GET['from_date'];
			$to_date = empty($_GET['to_date']) ? date("Y-m-d", strtotime($book_to_date)) : $_GET['to_date'];	
			
			// verify min stay
			$datefrom_time = strtotime($from_date);
			$dateto_time = strtotime($to_date);		
			$numdays = ($dateto_time   -  $datefrom_time) / (24 * 3600);	
			$min_stay = HostelPROMinStays :: find($from_date, $to_date);
			if(!empty($min_stay) and $min_stay > $numdays) {
				return '<!--BOOKERROR-->'.sprintf(__('Minimum stay of %d days is required.', 'hostelpro'), $min_stay);
			}			
			
			// select all rooms		
			$rooms = $wpdb->get_results( "SELECT * FROM ".HOSTELPRO_ROOMS." ORDER BY title" );
			
			// select the custom fields
			$fields = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_FIELDS." ORDER BY sort_order, id");			
			
			if(!empty($_GET['room_id'])) {
				$this_room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $_GET['room_id']));				
			}
			
			// select addon services if any
			$addons = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_ADDONS." WHERE is_inactive=0 AND room_id=0 ORDER BY id");
			
			// load max beds of the first room or the selected room
			$check_room = empty($this_room) ? (array)$rooms[0] : (array)$this_room;
			
			// any rooms that allow extra beds?
			$any_extra_beds = $wpdb->get_var("SELECT COUNT(id) FROM ".HOSTELPRO_ROOMS." WHERE extra_beds > 0"); 
			
			// display the booking form
			hostelpro_enqueue_datepicker();
			$form_design = get_option('hostelpro_booking_form_design');
		   $form_design = stripslashes($form_design);
		   
		   if(!empty($form_design)) echo $form_design;
		   else {
		   	if(@file_exists(get_stylesheet_directory().'/hostelpro/raw-code-booking.html.php')) include get_stylesheet_directory().'/hostelpro/raw-code-booking.html.php';
				else include(HOSTELPRO_PATH."/views/raw-code-booking.html.php");
		   }		   
		}
		
		$content = ob_get_clean();
		$content = apply_filters('hostelpro_content', wpautop($content));		
		return $content;
	} // end booking
	
	// list all rooms along with availability dropdown
	// will show cells for every date selected
	static function list_rooms($atts) {
		global $wpdb, $post;
		$shortcode_id = self :: get_id();	
		$booking_start = get_option('hostelpro_booking_start');
		if(empty($booking_start)) $booking_start = 'tomorrow';
		$book_to_date = ($booking_start == 'tomorrow') ? '+2 days' : 'tomorrow';
		if(empty($atts)) $atts = array();
		
		// handle successful Stripe payment 
		if(!empty($_POST['hostelpro_stripe_success'])) return HostelPROPayment :: stripe_success();
		
		$atts['shortcode_id'] = $shortcode_id;
		$min_stay = get_option('hostelpro_min_stay');
		$default_dateto_diff = $min_stay ? strtotime("+ ".(intval($min_stay)+1)." days") : strtotime($book_to_date);
		$dateformat = get_option('date_format');
		
		// the dropdown defaults to "from tomorrow to 1 day after"
		$datefrom = empty($_POST['hostelpro_from']) ? date("Y-m-d", strtotime($booking_start)) : $_POST['hostelpro_from'];
		$dateto = empty($_POST['hostelpro_to']) ? date("Y-m-d", $default_dateto_diff) : $_POST['hostelpro_to'];
		
		// in multiple booking mode? If so, date from and date to will be taken from the latest booking with the given session ID
		$multiple_bookings = get_option('hostelpro_multi_booking');
		if($multiple_bookings == 1 and !empty($_COOKIE['hostelpro_booking_session'])) {			
			$last_booking = $wpdb->get_row($wpdb->prepare("SELECT from_date, to_date FROM ".HOSTELPRO_BOOKINGS."
				WHERE session_id=%s ORDER BY id DESC LIMIT 1", $_COOKIE['hostelpro_booking_session']));				
			$datefrom = $last_booking->from_date;
			$dateto = $last_booking->to_date;	
			$_POST['hostelpro_from'] = $datefrom;
			$_POST['hostelpro_to'] = $dateto;
		}
		
		// which fields to show?
		$show_titles = empty($atts['show_titles']) ? 0 : $atts['show_titles'];
		$show_descriptions = empty($atts['show_descriptions']) ? 0 : $atts['show_descriptions'];
		$show_types = isset($atts['show_types']) ?  $atts['show_types'] : 1;
		$show_table = isset($atts['show_table']) ?  $atts['show_table'] : 1;
		$show_bathrooms = isset($atts['show_bathrooms']) ? $atts['show_bathrooms'] : 1;			
		$group_rooms = isset($atts['group_rooms']) ? $atts['group_rooms'] : 0;
		$max_days = get_option('hostelpro_max_stay');
		if(empty($max_days)) $max_days = isset($atts['max_days']) ? intval($atts['max_days']) : 5;
		$vertical_after = isset($atts['vertical_after']) ? intval($atts['vertical_after']) : 0;
		if($max_days < 1) $max_days = 5;
		$hide_dates = empty($atts['hide_dates']) ? 0 : 1;
		$booking_start = get_option('hostelpro_booking_start');
		$min_date = ($booking_start == 'tomorrow') ? 1 : 0;
						
		hostelpro_enqueue_datepicker();
		
		ob_start();
		if(@file_exists(get_stylesheet_directory().'/hostelpro/list-rooms.html.php')) include get_stylesheet_directory().'/hostelpro/list-rooms.html.php';
		else include(HOSTELPRO_PATH."/views/list-rooms.html.php");
		$content = ob_get_clean();
		return $content;
	} // end list_rooms();	
	
	// displays a Book! button
	static function book($atts) {
		global $post;
		$room_id = $atts[0];
		$shortcode_id = self :: get_id();
		
		// handle successful Stripe payment 
		if(!empty($_POST['hostelpro_stripe_success'])) return HostelPROPayment :: stripe_success();
		
		// this if will be removed when bookiing by ajax is done 
		if(!empty($_GET['in_booking_mode']) and $_GET['room_id']==$room_id) {
			return self :: booking();
		}
		
		$text = empty($atts[1]) ? __('Book', 'hostelpro') : $atts[1];
		
		hostelpro_enqueue_datepicker();
		
		return '<div id="hostelPROBookForm'.$shortcode_id.'">
		<form method="post">
		<input type="hidden" name="from_date" value="'.date("Y-m-d", strtotime('tomorrow')).'">
				<input type="hidden" name="to_date" value="'.date("Y-m-d", strtotime('+2 days')).'">
				<input type="hidden" name="room_id" value="'.$room_id.'">				
				<input type="hidden" name="action" value="hostelpro_ajax">
				<input type="hidden" name="type" value="load_booking_form">
				<input type="hidden" name="in_booking_mode" value="1">		
		<input type="button"  onclick="hostelPROLoadBooking(this.form, '."'hostelPROBookForm".$shortcode_id."'".');" value="'.$text.'">
		</form></div>';
	}
	
	// displays a room calendar
	static function calendar($atts) {
		global $wpdb, $post;
		$shortcode_id = self :: get_id();
		$permalink = get_permalink($post->ID);
		
		$room_id = $atts['room_id'];
		
		$min_stay = get_option('hostelpro_min_stay');
		$max_days = get_option('hostelpro_max_stay');
		if(empty($max_days)) $max_days = isset($atts['max_days']) ? intval($atts['max_days']) : 5;
		$default_dateto_diff = $min_stay ? strtotime("+ ".(intval($min_stay)+1)." days") : strtotime("+ 2 days");
		
		// handle successful Stripe payment 
		if(!empty($_POST['hostelpro_stripe_success'])) return HostelPROPayment :: stripe_success();
		
		if(!empty($_GET['in_booking_mode']) and $_GET['room_id']==$room_id) {			
			return self :: booking();
		}		
		
		$months = empty($atts['months']) ? 1 : intval($atts['months']);
		$bookable = ('true' == @$atts['bookable']) ? true : false;
		$text = empty($atts['button_text']) ? __('Book', 'hostelpro') : $atts[1];
		
		// year range is limited for up to 1 year
		$yearfrom = date("Y");
		$yearto = $yearfrom+1;
		$today_time = strtotime(date("Y-m-d"));
		$next_year_time = strtotime( (date("Y")+1).'-'.date('m-d') );
		
		// select room
		$room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $room_id));
		$check_beds = $room->beds;
		if($room->overbook_beds > $room->beds) $check_beds = $room->overbook_beds;	
		
		// select all unavailable dates for this room
		// this will be used to make them disabled: http://davidwalsh.name/jquery-datepicker-disable-days
		$udates = array();
		$curdate = date("Y-m-d", current_time('timestamp'));
		$unavailable = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS." 
			WHERE room_id=%d AND to_date > %s AND from_date < %s + INTERVAL 1 YEAR", $room->id, $curdate, $curdate));
			
		// now fill the dates that fit in the range	
		foreach($unavailable as $un) {
			$from_time = strtotime($un->from_date);
			$to_time = strtotime($un->to_date);
			
			// Loop between timestamps, 24 hours at a time
			// $i is current time
			for ($i = $from_time; $i < $to_time; $i = $i + 86400) {
				if($i > $today_time or $i <= $next_year_time) {
					// we got unavailable date. For dorm rooms and rooms with overbooking must check if all beds are taken
					$checkdate = date("Y-m-d", $i);
					
					// echo $checkdate."<br>";
					if(($room->rtype == 'dorm' or $room->overbook_beds > $room->beds) and !$un->is_static) {	
						// overbooking allowed?
						$booked_beds = $wpdb->get_var($wpdb->prepare("SELECT SUM(beds) FROM ".HOSTELPRO_BOOKINGS." 
						WHERE room_id=%d AND to_date > %s AND from_date <= %s", $room->id, $checkdate, $checkdate));						
						
						if($booked_beds < $check_beds) continue;
					}
					
					// add it to the array	
					$udate = date("m-d-Y", $i);				
					$udates[] = $udate;
				} 
			}
		}
				
		hostelpro_enqueue_datepicker();
		$dateformat = get_option('date_format');
		ob_start();
		if(@file_exists(get_stylesheet_directory().'/hostelpro/room-calendar.html.php')) include get_stylesheet_directory().'/hostelpro/room-calendar.html.php';
		else include(HOSTELPRO_PATH."/views/room-calendar.html.php");
		$content = ob_get_clean();
		return $content;
	}
	
	// Form-related shortcodes
	static function form_start($atts) {
		global $wpdb;		
		$shortcode_id = self :: get_id();
		// if there is more than one shortcode on the page, the global will be rewritten to the latest one.
		// but since after the form the fields follow before a second form, they are supposed to have the right form ID
		$GLOBALS['hostelpro_form_id'] = $shortcode_id;

		if(!empty($_POST['hostelpro_book'])) {		
			ob_start();
			HostelPROBookings :: book();
			$content = ob_get_clean();
			$content .= '<div style="display:none;">';
			return $content;
		}		
		
		$content = '<div class="wrap hostelpro-box" id="hostelPROBooking'.$shortcode_id.'">
		<form method="post" class="hostelpro-front-form-visual" action="#" id="hostelpro-form-'.$shortcode_id.'">
		<input type="hidden" name="action" value="hostelpro_ajax">
			<input type="hidden" name="type" value="book">
			<input type="hidden" name="shortcode_id" value="'.$shortcode_id.'">';
		return $content;
	}	
	
	// outputs a static field
	static function static_field($atts) {
		if(!empty($_POST['hostelpro_book'])) return '';
		$shortcode_id = self :: get_id();
		$current_form_id = @$GLOBALS['hostelpro_form_id'];
		$dateformat = get_option('date_format');
		$booking_start = get_option('hostelpro_booking_start');
		if(empty($booking_start)) $booking_start = 'tomorrow';
		$book_to_date = ($booking_start == 'tomorrow') ? '+2 days' : 'tomorrow';
		$max_sel_beds  = 0;
		
		global $wpdb;

		// hidden fields?
		$text_type = empty($atts['hidden']) ? 'text' : 'hidden';
		$hidden_style = empty($atts['hidden']) ? '' : ' style="display:none;" ';
		
		// if called on Book button some datas come by POST
		$_GET['from_date'] = !empty($_POST['from_date']) ? $_POST['from_date'] : @$_GET['from_date'];	
		$_GET['to_date'] = !empty($_POST['to_date']) ? $_POST['to_date'] : @$_GET['to_date'];
		$_GET['room_id'] = !empty($_POST['room_id']) ? $_POST['room_id'] : @$_GET['room_id'];
		
		// let's default to thge first room
		if(empty($_GET['room_id'])) {
			$rooms = $wpdb->get_results( "SELECT * FROM ".HOSTELPRO_ROOMS." ORDER BY title" );
			$_GET['room_id'] = @$rooms[0]->id;
		}
		
		// for beds and extra beds select room info
		if($atts[0] == 'beds' or $atts[0] == 'extra_beds' or $atts[0] == 'notes') {
			$room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $_GET['room_id']));
			$_room = new HostelPRORoom();
			$parts = $_room->default_beds($room->id);
			$check_room = (array)$room;
			$max_sel_beds = $_room->max_sel_beds($check_room, $_GET['from_date'], $_GET['to_date']);
		}
		
		$max_child_beds = empty($room->max_children) ? $max_sel_beds : $room->max_children;
		
		$output = '';
		switch($atts[0]) {
			case 'room_id': 
				$rooms = $wpdb->get_results( "SELECT * FROM ".HOSTELPRO_ROOMS." ORDER BY title" );
				
				$output .= '<select name="room_id"'.$hidden_style.' onchange="HostelPROChangeRoom(this.value, this.form);">
					<option value="0">'.__('- Please select -', 'hostelpro').'</option>';
				foreach($rooms as $room):
					$selected = (!empty($_GET['room_id']) and $_GET['room_id'] == $room->id) ? ' selected' : '';
					$output .='<option value="'.$room->id.'"'.$selected.'>'.stripslashes($room->title).'</option>';
				endforeach;
				$output .= '</select>';
			break;
			case 'beds':				
				$main_onchange = $children_onchange = '';
				
				if(!empty($room->allow_child_bed_price)) {
					$main_onchange = 'if(this.value < this.form.child_beds.value + parseInt('.intval($room->adults_with_children).')) { this.form.child_beds.value = this.value - parseInt('.intval($room->adults_with_children).')};';
					$children_onchange = 'if((parseInt(this.value) + parseInt('.intval($room->adults_with_children).')) > this.form.beds.value) { this.form.beds.value = parseInt(this.value) + parseInt('.intval($room->adults_with_children).')};';
				}
				
				if(!empty($room->whole_dorm_price) and $room->whole_dorm_price > 0 and $max_sel_beds >= $room->beds) {
					$main_onchange .= 'if(this.value < '.$max_sel_beds.') {this.form.whole_dorm_book.checked = false};';
				}
				
				if(empty($atts['nowrapper'])) $output .= '<span class="hostelpro-beds-wrapper">';
				$output .= '<select name="beds" '.$hidden_style.' onchange="'.$main_onchange.'">';
				// what is the default num beds?
				$default_beds = ($room->rtype == 'private') ? $room->beds : 1;
				if(!empty($room->whole_dorm_price) and $room->whole_dorm_price > 0 and $max_sel_beds >= $room->beds) $default_beds = $max_sel_beds;				
				$changeable_beds = ($room->rtype == 'dorm' or $room->discount_part_occupancy) ? 1 : 0;
				
				for($i = 1; $i <= $max_sel_beds; $i++) {
					if(!$parts[1] and $i < $parts[0]) continue; // rooms that allow no change will show only 1 value
					
					$selected = ($i == $default_beds) ? 'selected' : '';
					$output .= '<option value="'.$i.'" '.$selected.'>'.$i.'</option>'."\n";
				}
				$output .= '</select>
				<input type="hidden" name="changeable_beds" value="'.$changeable_beds.'">';
				
				// room allows booking the whole room at discount?
				if(!empty($room->whole_dorm_price) and $room->whole_dorm_price > 0 and $max_sel_beds >= $room->beds) {
					$input = ' <input type="checkbox" name="whole_dorm_book" value="1" checked="checked" onclick="if(this.checked) {this.form.beds.value = '.$max_sel_beds.';}"> ';
					$output .= sprintf(__('%1$s Book the whole room for %2$s%3$s', 'hostelpro'), $input, HOSTELPRO_CURRENCY, $room->whole_dorm_price);
				}
				
				if(!empty($room->allow_child_bed_price)) {
					
					$select_children = '<select name="child_beds" onchange="'.$children_onchange.'">';
					for($i = 0; $i <= $max_child_beds; $i++) {						
						$select_children .= '<option value="'.$i.'">'.$i.'</option>'."\n";
					}
					$select_children .= '</select>';
					$output .= '<br><label>' . stripslashes($room->child_bed_label).'</label> '.$select_children;
				}
				
				if(empty($atts['nowrapper']))  $output .= '</span>';
			break;
			case 'extra_beds':
				$output .= '<select name="extra_beds"><option value="0">0</option>';
				for($i = 1; $i <= $parts[3]; $i++) {					
					$output .= '<option value="'.$i.'">'.$i.'</option>'."\n";
				}
				$output .= '</select>';
			break;
			case 'from_date':
				$from_date = empty($_GET['from_date']) ? date("Y-m-d", strtotime($booking_start)) : $_GET['from_date'];			
		
				$output .= '<input type="'.$text_type.'" value="'.date_i18n($dateformat, strtotime($from_date)).'" class="hostelproDatePicker" id="hostelPROStaticFromDate'.$current_form_id.'" readonly="true">
				<input type="hidden" name="from_date" value="'.@$_GET['from_date'].'" id="alt_hostelPROStaticFromDate'.$current_form_id.'">';
			break;
			case 'to_date':
				$to_date = empty($_GET['to_date']) ? date("Y-m-d", strtotime($book_to_date)) : $_GET['to_date'];
				$output .= '<input type="'.$text_type.'"  value="'.date_i18n($dateformat, strtotime($to_date)).'" class="hostelproDatePicker" id="hostelPROStaticToDate'.$current_form_id.'" readonly="true">
				<input type="hidden" name="to_date" value="'.@$_GET['to_date'].'" id="alt_hostelPROStaticToDate'.$current_form_id.'">';		
			break;	
			case 'contact_name':
				$value = '';
				if(!empty($GLOBALS['hostelpro_last_booking'])) $value = $GLOBALS['hostelpro_last_booking']->contact_name; 
				$output .= '<input type="'.$text_type.'" name="contact_name" value="'.$value.'">';
			break;		
			case 'contact_email':
				$value = '';
				if(!empty($GLOBALS['hostelpro_last_booking'])) $value = $GLOBALS['hostelpro_last_booking']->contact_email; 
				$output .= '<input type="'.$text_type.'" name="contact_email" value="'.$value.'">';
			break;
			case 'contact_phone':
				$value = '';
				if(!empty($GLOBALS['hostelpro_last_booking'])) $value = $GLOBALS['hostelpro_last_booking']->contact_phone; 
				$output .= '<input type="'.$text_type.'" name="contact_phone" value="'.$value.'">';
			break;
			case 'contact_type':
				$value = '';
				if(!empty($GLOBALS['hostelpro_last_booking'])) $value = $GLOBALS['hostelpro_last_booking']->contact_type; 
				$output .= '<select name="contact_type"'.$hidden_style.'>
					<option value="couple"'.($value == 'couple' ? ' selected' : '').'>'. __('Couple', 'hostelpro').'</option>
					<option value="male"'.($value == 'male' ? ' selected' : '').'>'.__('Male(s)', 'hostelpro').'</option>
					<option value="female"'.($value == 'female' ? ' selected' : '').'>'.__('Female(s)', 'hostelpro').'</option>
					<option value="mixed"'.($value == 'mixed' ? ' selected' : '').'>'. __('Mixed', 'hostelpro').'</option>
					</select>';
			break;
			case 'coupon':
				$output .= '<input type="'.$text_type.'" name="coupon" value="">';
			break;
			case 'notes':			
			   $output .= '<span id="hostelPRONotes'.$shortcode_id.'">';  
				if(!empty($room->notes)) $output .= "<p>".stripslashes($room->notes)."</p>";
				$output .= '</span><input type="hidden" name="notes_id" value="hostelPRONotes'.$shortcode_id.'">';
			break;
			case 'captcha':
				if(get_option('hostelpro_text_captcha_enabled') != 1) return '';
				$output .= HostelPROTextCaptcha :: generate();
			break;
		}
		return $output;
	}
	
	// non static field	
	static function field($atts) {
		if(!empty($_POST['hostelpro_book'])) return '';
		global $wpdb;
		if(empty($shortcode_id)) $shortcode_id = self :: get_id();
		
		$field = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_FIELDS." WHERE id=%d", $atts[0]));		
		$fields = array($field);
		ob_start();
		$nolabel = true;
		if(@file_exists(get_stylesheet_directory().'/hostelpro/form-field-display.html.php')) include get_stylesheet_directory().'/hostelpro/form-field-display.html.php';
		else include(HOSTELPRO_PATH."/views/form-field-display.html.php");
		$content = ob_get_clean();
		return $content;
	}
	
	// addon service field
	static function addon($atts) {
		if(!empty($_POST['hostelpro_book'])) return '';
		global $wpdb;
		
		$addon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ADDONS." WHERE id=%d", $atts['id'])); 
		$priceinfo = '';
		if($addon->per_person) $priceinfo .= ' '.__('per person', 'hostelpro').' ';
		if($addon->per_day) $priceinfo .= ' '.__('per day', 'hostelpro').' ';
		ob_start();
		$show_label = false;
		if(@file_exists(get_stylesheet_directory().'/hostelpro/addon-display.html.php')) include get_stylesheet_directory().'/hostelpro/addon-display.html.php';
		else include(HOSTELPRO_PATH."/views/addon-display.html.php");
		$content = ob_get_clean();
		return $content;
	}
	
	// closes the signup form
	static function form_end($atts) {
		$dateformat = get_option('date_format');
		$honeypot = $honeypot_js = '';
		$booking_start = get_option('hostelpro_booking_start');
		$min_date = ($booking_start == 'tomorrow') ? 1 : 0;
		if(empty($shortcode_id)) $shortcode_id = self :: get_id();
		$current_form_id = @$GLOBALS['hostelpro_form_id'];
		if(!empty($_POST['hostelpro_book'])) return '</div>';
		hostelpro_enqueue_datepicker();
		
		// use honeypot field?
		if(get_option('hostelpro_honeypot') == 1) {
			$honeypot = '<input type="text" class="hostelpro-beewax" name="hostelpro_ssid" value="">
			<input type="hidden" name="hostelpro_hive_ssid" value="_'.md5('hostelprohoney'.$_SERVER['REMOTE_ADDR']).'">';
		}		
		
		return '<input type="hidden" name="hostelpro_book" value="1">		
			<input type="hidden" name="required_fields[]" value="">
			'.$honeypot.'
		</form>
		<script type="text/javascript" >
		jQuery(document).ready(function() {
		    jQuery(".hostelproDatePicker").datepicker({
		        dateFormat : "'.dateformat_PHP_to_jQueryUI($dateformat).'",
        		  altFormat: "yy-mm-dd",
        		  minDate: '.$min_date.',
        		  maxDate: "+'.HOSTELPRO_MAX_DATE.'",
        		  onSelect: function(dateText, inst) {
        		  	  // change beds number depending on date selection
        		  	  var frm = this.form;
        		  	  
        		  	  // avoid query if the room has no changeable beds
        		  	  if(frm.changeable_beds.value!=1) return false;
						
					  // avoid the query if from date > to_date
					  if(!hostelPROCompareDates(this.form.from_date.value, this.form.to_date.value)) return false;	        		  	  
        		  	  frm.beds.innerHTML = "<option value=1>"+hostelpro_i18n.loading+"</option>";
        		  	  HostelPROChangeRoom(frm.room_id.value, frm);
        		  }
		    });
		    jQuery(".hostelproDatePicker").each(function (idx, el) { 
			    jQuery(this).datepicker("option", "altField", "#alt_" + jQuery(this).attr("id"));
			});
			jQuery("#hostelPROStaticFromDate'.$current_form_id.'").datepicker("option", "onSelect", function(dateText, inst) {
				var toDate = jQuery("#hostelPROStaticFromDate'.$current_form_id.'").datepicker("getDate", "+1d");
				toDate.setDate(toDate.getDate()+1); 
				jQuery("#hostelPROStaticToDate'.$current_form_id.'").datepicker("setDate", toDate);
			});
		});
		</script>
		</div>';
	}
	
	static function submit_button($atts) {
		if(!empty($_POST['hostelpro_book'])) return '';
		global $wpdb;
		$shortcode_id = self :: get_id();
		
		$text = empty($atts[0]) ? __('Make Reservation', 'hostelpro') : $atts[0];	
		
		return '<input type="button" value="'.$text.'" onclick="HostelPROValidateBooking(this.form);">';		
	}
	
	// if extra beds. Shows only if the chosen room has extra beds
	static function if_extra_beds($atts, $content = null) {
		global $wpdb;
		if(empty($shortcode_id)) $shortcode_id = self :: get_id();
		
		// if there is no post room ID the return nothing
		$_POST['room_id'] = empty($_POST['room_id']) ? @$_GET['room_id'] : $_POST['room_id'];
		if(empty($_POST['room_id'])) return '';
		
		// select the room extra beds
		$room = $wpdb->get_row($wpdb->prepare("SELECT extra_beds, extra_bed_price FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $_POST['room_id']));
		$style = $room->extra_beds ? '' : "style='display:none;'";
		
		$content = str_replace('{{price}}', '<span id="extraBedPrice'.$shortcode_id.'">'.$room->extra_bed_price.'</span><input type="hidden" name="extra_bed_price_id" value="extraBedPrice'.$shortcode_id.'">', $content);
		
		return do_shortcode("<div class='extra-beds' $style>".$content."</div>"); 
	} // end if_extra_beds
	
	// same as above but for beds.
	static function if_beds($atts, $content = null) {
		global $wpdb;
		
		// if there is no post room ID the return nothing
		// if(empty($_POST['room_id'])) return '';
		$_POST['room_id'] = empty($_POST['room_id']) ? @$_GET['room_id'] : $_POST['room_id'];
		
		$room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id=%d", @$_POST['room_id']));
		$style = (@$room->price_type == 'per-bed' or @$room->discount_part_occupancy) ? '' : "style='display:none;'";
		
		return do_shortcode("<div class='select-beds' $style>".$content."</div>"); 
	}
	
	// create unique ID for each shortcode on the page so at any time we know which shortcode we are working with
	// this is very important in case multiple shortcodes are used on a page
	static function get_id() {
		if( empty( self :: $shortcode_ids )) self :: $shortcode_ids = array();
		$current_id = sizeof(self :: $shortcode_ids);
		$current_id++;
		self :: $shortcode_ids[] = $current_id;
		return $current_id;
	}
	
	// displays addons for a specific room
	// the ID of the room may come as shortcode param or as $_POST['room_id']
	static function room_addons($atts) {
		global $wpdb;		
		if(empty($shortcode_id)) $shortcode_id = self :: get_id();
		if(!empty($atts['room_id'])) $room_id = $atts['room_id'];
		if(!empty($_POST['room_id'])) $room_id = $_POST['room_id'];
		if(empty($room_id)) return '';
		$show_label = true;
		
		// now select the addon services for this specific room
		$addons = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ADDONS." 
			WHERE is_inactive=0 AND room_id=%d ORDER BY id", $room_id));
			
		ob_start();	
		echo '<div class="hostelpro-room-addons">';
		foreach($addons as $addon) {
			$priceinfo = '';
			if($addon->per_person) $priceinfo .= ' '.__('per person', 'hostelpro').' ';
			if($addon->per_day) $priceinfo .= ' '.__('per day', 'hostelpro').' ';
			if(@file_exists(get_stylesheet_directory().'/hostelpro/addon-display.html.php')) include get_stylesheet_directory().'/hostelpro/addon-display.html.php';
			else include(HOSTELPRO_PATH."/views/addon-display.html.php");
		}	
		echo '</div>';
		
		$content = ob_get_clean();
		return $content;
	} // end room_addons()
	
		
	// displays description for a specific room
	// the ID of the room may come as shortcode param or as $_POST['room_id']
	static function room_description($atts) {
		global $wpdb;		
		if(empty($shortcode_id)) $shortcode_id = self :: get_id();
		if(!empty($atts['room_id'])) $room_id = $atts['room_id'];
		if(!empty($_POST['room_id'])) $room_id = $_POST['room_id'];
		if(empty($room_id)) return '';
				
		// now select the addon services for this specific room
		$room_desc = $wpdb->get_var($wpdb->prepare("SELECT description FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $room_id));
		$room_desc = apply_filters('hostelpro_content', $room_desc);
			
		$content = '<div class="hostelpro-room-description">';
		$content .= stripslashes($room_desc);
		$content .= '</div>';		
		
		return $content;
	} // end room_description()
	
	// show the calendar overview page
	static function calendar_overview($atts) {
		if(!is_user_logged_in() or (!current_user_can('manage_options') and !current_user_can('hostelpro_manage'))) return __('You need to be authorized to view this page.', 'hostelpro');		
		
		ob_start();		
		HostelPROBookings :: calendar_overview($atts, true);
		$content = ob_get_clean();
		return $content;
	} // end calendar_overview
}