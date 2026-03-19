<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// procedural function to dispatch ajax requests
function hostelpro_ajax() {
	global $wpdb, $user_ID;

    // Verify nonce for security
    if (!isset($_POST['_wpnonce']) && !isset($_GET['_wpnonce'])) {
        // Allow nonce verification to be skipped for certain public AJAX actions
        // but validate capability where needed
    }
    
    // Verify nonce if provided
    if (isset($_POST['_wpnonce']) || isset($_GET['_wpnonce'])) {
        $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : $_GET['_wpnonce'];
        if (!wp_verify_nonce($nonce, 'hostelpro-ajax-nonce')) {
            wp_die(__('Security check failed', 'hostelpro'));
        }
    }

	$type = empty($_POST['type']) ? $_GET['type'] : $_POST['type'];
    $type = sanitize_text_field($type);

	switch($type) {
		case 'change_room':
			$_room = new HostelPRORoom();
            if(empty($_POST['room_id'])) die("0|0|1|0|0|0|0");

            // Sanitize all inputs
            $room_id = intval($_POST['room_id']);
            $from_date = sanitize_text_field($_POST['from_date']);
            $to_date = sanitize_text_field($_POST['to_date']);

			$parts = $_room->default_beds($room_id, true, $from_date, $to_date);
			echo implode("|", $parts);

			// output room static beds field by calling the shortcode
			echo '|';
			echo do_shortcode('[hostelpro-field-static beds nowrapper="1"]');

			// now output room notes of any
            $room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $room_id));
            if ($room) {
                echo '|'.wpautop(stripslashes($room->notes));

                // if the room has description, output it
                if(!empty($room->description)) {
                    echo '[DESCRIPTION]' . apply_filters('hostelpro_content', stripslashes($room->description));
                }
            } else {
                echo '|';
            }

			// if the room has addons, output them as well
			// now select the addon services for this specific room
            $addons = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ADDONS."
				WHERE is_inactive=0 AND room_id=%d ORDER BY id", $room_id));
			if(count($addons)) {
				echo '[ADDONS]';
				$show_label = true;
				foreach($addons as $addon) {
					$priceinfo = '';
					if($addon->per_person) $priceinfo .= ' '.__('per person', 'hostelpro').' ';
					if($addon->per_day) $priceinfo .= ' '.__('per day', 'hostelpro').' ';
					if(@file_exists(get_stylesheet_directory().'/hostelpro/addon-display.html.php')) include get_stylesheet_directory().'/hostelpro/addon-display.html.php';
					else include(HOSTELPRO_PATH."/views/addon-display.html.php");
				}
			}
		break;
		case 'book':
			// book a room
            $booking_mode = get_option('hostelpro_booking_mode');
            if($booking_mode == 'none') {
                wp_send_json_error(array('message' => __('Online booking is not enabled.', 'hostelpro')));
            }
            // Verify capability for booking
            if (!current_user_can('read')) {
                wp_send_json_error(array('message' => __('Unauthorized', 'hostelpro')));
            }
			echo HostelPROBookings :: book();
		break;
		case 'multiple_book':
			// book a room
            $booking_mode = get_option('hostelpro_booking_mode');
            if($booking_mode == 'none') {
                wp_send_json_error(array('message' => __('Online booking is not enabled.', 'hostelpro')));
            }
            if (!current_user_can('read')) {
                wp_send_json_error(array('message' => __('Unauthorized', 'hostelpro')));
            }
			echo HostelPROBookings :: multiple_book();
		break;
		case 'load_booking_form':
			// because the booking form expects them in $_GET but we send in ajax as $_POST
			// we have to transfer the vars
            $_GET['room_id'] = intval($_POST['room_id']);
            $_GET['in_booking_mode'] = 1;
            $_GET['from_date'] = sanitize_text_field($_POST['from_date']);
            $_GET['to_date'] = sanitize_text_field($_POST['to_date']);

			echo HostelPROShortcodes :: booking("roomID".$_GET['room_id']);
		break;
		case 'list_rooms':
            $atts = array(
                'show_titles' => intval($_POST['show_titles']),
                'show_descriptions' => intval($_POST['show_descriptions']),
                'show_types' => intval($_POST['show_types']),
                'show_bathrooms' => intval($_POST['show_bathrooms']),
                'shortcode_id' => sanitize_text_field($_POST['shortcode_id']),
                'group_rooms' => intval($_POST['group_rooms']),
                'vertical_after' => intval($_POST['vertical_after']),
                'hide_dates' => intval($_POST['hide_dates'])
            );
			HostelPRORooms :: availability_table($atts);
		break;

		// check minimum stay requirement for a given date selection
		case 'min_stay':
            $from_date = sanitize_text_field($_POST['from_date']);
            $to_date = sanitize_text_field($_POST['to_date']);
			$period = HostelPROMinStays :: find($from_date, $to_date);
			if($period == -1) {
				// no specific period defined. Let's use the global setting
				$period = get_option('hostelpro_min_stay');
				$period = intval($period);
			}
			echo $period;
		break;

		// maximum selectable beds for a room. Return drop-down when date or room is changed
		case 'max_sel_beds':
			$_room = new HostelPRORoom();
            $room_id = intval($_POST['room_id']);
            $from_date = sanitize_text_field($_POST['from_date']);
            $to_date = sanitize_text_field($_POST['to_date']);
            
            $check_room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $room_id), ARRAY_A);
            if ($check_room) {
                $max_sel_beds = $_room->max_sel_beds($check_room, $from_date, $to_date);
                $default_beds = ($check_room['private']) ? $check_room['beds'] : 1;

                $html = '';
                for($i=1; $i <= $max_sel_beds; $i++) {
                    $selected = ($i == $default_beds) ? 'selected' : '';
                    $html .='<option value="'.esc_attr($i).'" '.$selected.'>'.esc_html($i).'</option>';
                }
                echo $html;
            }
		break;

		// delete front-end booking in multiple booking mode
		case 'del_front_booking':
            if(get_option('hostelpro_multi_booking') != 1 or empty($_COOKIE['hostelpro_booking_session'])) {
                wp_send_json_error(array('message' => __('Invalid request', 'hostelpro')));
            }
            
            // Verify nonce for delete action
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'hostelpro-ajax-nonce')) {
                wp_send_json_error(array('message' => __('Security check failed', 'hostelpro')));
            }
            
            $booking_id = intval($_POST['id']);
            $session_id = sanitize_text_field($_COOKIE['hostelpro_booking_session']);
            
			$wpdb->query($wpdb->prepare("DELETE FROM ".HOSTELPRO_BOOKINGS." WHERE id=%d AND session_id=%s",
                $booking_id, $session_id));
		break;
	}
	exit;
}