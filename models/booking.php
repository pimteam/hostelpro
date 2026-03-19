<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HostelPROBooking {
	function add($vars) {
		global $wpdb, $user_ID;

		$this->prepare_vars($vars);

		// prepare from/to date
		$fromdate = empty($vars['from_date']) ? $vars['fromyear'].'-'.$vars['frommonth'].'-'.$vars['fromday'] : $vars['from_date'];
		$todate = empty($vars['to_date']) ? $vars['toyear'].'-'.$vars['tomonth'].'-'.$vars['today'] : $vars['to_date'];

		// Validate dates
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromdate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $todate)) {
			throw new Exception(__('Invalid date format', 'hostelpro'));
		}

		$now = current_time('mysql');

		$result = $wpdb->query($wpdb->prepare("INSERT INTO ".HOSTELPRO_BOOKINGS." SET
		 room_id=%d, from_date=%s, to_date=%s, amount_paid=%s, amount_due=%s,
		 is_static=%d, contact_name=%s, contact_email=%s, contact_phone=%s,
		 contact_type=%s, created_time='$now', status=%s, beds=%d, date_prices=%s, addons=%s,
		 extra_beds=%d, addon_details=%s, editor_id=%d, discount=%f, child_beds=%d",
		 $vars['room_id'], $fromdate, $todate, $vars['amount_paid'], $vars['amount_due'], @$vars['is_static'],
		 $vars['contact_name'], $vars['contact_email'], $vars['contact_phone'], $vars['contact_type'],
		 $vars['status'], $vars['beds'], @$vars['date_prices'], $vars['addons'],
		 @$vars['extra_beds'], serialize(@$vars['addon_details']), $user_ID, $vars['discount'], $vars['child_beds'] ));

		$id = $wpdb->insert_id;
		$this->save_data($vars, $id);

		// add the invoice code
		$invoice_code = substr(md5($id.time().$vars['contact_email']), 0, 10);
		$wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_BOOKINGS." SET invoice_code=%s WHERE id=%d", $invoice_code, $id));

	  	if($result === false) return false;

	  	do_action('hostelpro_booking_added', $id);
	  	return $id;
	}

	function edit($vars, $id) {
		global $wpdb;

		$this->prepare_vars($vars);

		// prepare from/to date
		$fromdate = $vars['fromyear'].'-'.$vars['frommonth'].'-'.$vars['fromday'];
		$todate = $vars['toyear'].'-'.$vars['tomonth'].'-'.$vars['today'];

		// Validate dates
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromdate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $todate)) {
			throw new Exception(__('Invalid date format', 'hostelpro'));
		}

		$result = $wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_BOOKINGS." SET
		 room_id=%d, from_date=%s, to_date=%s, amount_paid=%s, amount_due=%s,
		 contact_name=%s, contact_email=%s, contact_phone=%s,
		 contact_type=%s, status=%s, beds=%d, extra_beds=%d, addon_details=%s, addons=%s,
		 discount=%f, child_beds=%d
		 WHERE id=%d",
		 $vars['room_id'], $fromdate, $todate, $vars['amount_paid'], $vars['amount_due'],
		 $vars['contact_name'], $vars['contact_email'], $vars['contact_phone'],
		 $vars['contact_type'], $vars['status'], $vars['beds'], $vars['extra_beds'],
		 serialize(@$vars['addon_details']), $vars['addons'], $vars['discount'], $vars['child_beds'], $id ));
		$this->save_data($vars, $id);

	  	if($result === false) return false;

		do_action('hostelpro_booking_edited', $id, $vars);
	  	return true;
	}

	function prepare_vars(&$vars) {
		$vars['room_id'] = intval($vars['room_id']);
		$vars['amount_paid'] = floatval($vars['amount_paid']);
		$vars['amount_due'] = floatval($vars['amount_due']);
		$vars['contact_name'] = sanitize_text_field($vars['contact_name']);
		$vars['contact_email'] = sanitize_email($vars['contact_email']);
		$vars['contact_phone'] = sanitize_text_field($vars['contact_phone']);
		$vars['contact_type'] = sanitize_text_field($vars['contact_type']);
		$vars['status'] = sanitize_text_field($vars['status']);
		$vars['beds'] = intval($vars['beds']);
		$vars['extra_beds'] = intval(@$vars['extra_beds']);
		$vars['child_beds'] = intval(@$vars['child_beds']);
		$vars['addons'] = hostelpro_strip_tags(@$vars['addons']);
		$vars['discount'] = floatval($vars['discount']);
		
		// Sanitize date fields if present
		if (isset($vars['from_date'])) {
			$vars['from_date'] = preg_replace('/[^\d\-]/', '', $vars['from_date']);
		}
		if (isset($vars['to_date'])) {
			$vars['to_date'] = preg_replace('/[^\d\-]/', '', $vars['to_date']);
		}
		
		// Sanitize year/month/day fields
		foreach (array('fromyear', 'frommonth', 'fromday', 'toyear', 'tomonth', 'today') as $field) {
			if (isset($vars[$field])) {
				$vars[$field] = preg_replace('/[^\d]/', '', $vars[$field]);
			}
		}
	}

	// delete booking
	function delete($id) {
		global $wpdb;

		$result = $wpdb->query($wpdb->prepare("DELETE FROM ".HOSTELPRO_BOOKINGS." WHERE id=%d", $id));

		if($result === false) return false;
		do_action('hostelpro_booking_deleted', $id);
	  	return true;
	}

	// transfer amount due to amount paid
	function mark_paid($id) {
		global $wpdb;

		$result = $wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_BOOKINGS." SET
			amount_paid=amount_paid + amount_due, amount_due=0, status='active'
			WHERE id=%d", $id));

		if($result === false) return false;

		do_action('hostelpro_booking_paid', $id);
	  	return true;
	}

	// cancel booking - change status to cancelled
	function cancel($id) {
		global $wpdb;

		$wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_BOOKINGS." SET
			status='cancelled' WHERE id=%d", $id));

		if($result === false) return false;

		do_action('hostelpro_booking_cancelled', $id);
	  	return true;
	}

	// sends user and admin emails when booking is made
	function email($booking_id, $who = 'both') {
		global $wpdb;

		$admin_email = hostelpro_admin_email();
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$headers .= 'From: '. $admin_email . "\r\n";

		$email_options = get_option('hostelpro_email_options');
		if(!$email_options['do_email_admin'] and !$email_options['do_email_user']) return false;

		// select booking
		if(is_numeric($booking_id)) {
			$bookings = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS." WHERE id=%d", $booking_id));
		}
		else {
			$booking_id = str_replace('M', '', $booking_id);
			$session_id = $wpdb->get_var($wpdb->prepare("SELECT session_id FROM ".HOSTELPRO_BOOKINGS." WHERE id=%d", $booking_id));
			if(empty($session_id)) return false;

			$bookings = $wpdb->get_results($wpdb->prepare("SELECT tB.*, tR.title as room_name
				FROM ".HOSTELPRO_BOOKINGS." tB JOIN ".HOSTELPRO_ROOMS." tR ON tR.id = tB.room_id
				WHERE tB.session_id=%s ORDER BY tB.id", $session_id));
		}


		foreach($bookings as $cnt => $booking) {
			// fill these for each booking - NYI
			$from_date = date(get_option('date_format'), strtotime($booking->from_date));
			$to_date = date(get_option('date_format'), strtotime($booking->to_date));
			$timestamp = date(get_option('date_format').' '.get_option('time_format'), strtotime($booking->created_time));

			// select custom fields and datas
			$fields = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_FIELDS." ORDER BY id");
			$datas = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".HOSTELPRO_DATAS." WHERE booking_id=%d", $booking_id));

			// select room
			$room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id=%d", $booking->room_id));

			$invoice_url = site_url("?hostelpro_invoice=".$booking->invoice_code."&id=".$booking->id."&noheader=1");

			$bookings[$cnt]->from_date = $from_date;
			$bookings[$cnt]->to_date = $to_date;
			$bookings[$cnt]->timestamp = $timestamp;
			$bookings[$cnt]->fields = $fields;
			$bookings[$cnt]->datas = $datas;
			$bookings[$cnt]->room = $room;
			$bookings[$cnt]->invoice_url = $invoice_url;
		}

		if($email_options['do_email_admin'] and ($who == 'both' or $who == 'admin')) {

			$subject = $email_options["email_admin_subject"];
			$message = $email_options["email_admin_message"];
			$message = $this -> prepare_message($message, $bookings);

 			// echo $subject.'-'.$message.'<br>';
 			// echo "emailing admin at ".$email_options['admin_email']."<br>";
			$result = wp_mail( $email_options['admin_email'], $subject, $message, $headers );
			$status = $result ? 'OK' : "Error: ".$GLOBALS['phpmailer']->ErrorInfo;
	   	$wpdb->query($wpdb->prepare("INSERT INTO ".HOSTELPRO_EMAILLOG." SET
	   		sender=%s, receiver=%s, subject=%s, date=CURDATE(), status=%s",
	   		$admin_email, $email_options['admin_email'], $subject, $status));
		} // end do email admin

		if($email_options['do_email_user'] and ($who == 'both' or $who == 'user')) {
			$subject = $email_options["email_user_subject"];
			$message = $email_options["email_user_message"];
			$message = $this -> prepare_message($message, $bookings);

			//echo $subject.'-'.$message.'<br>';
			//echo "emailing user at ".$booking->contact_email."<br>";
			$result = wp_mail( $booking->contact_email, $subject, $message, $headers );
			$status = $result ? 'OK' : "Error: ".$GLOBALS['phpmailer']->ErrorInfo;
	   	$wpdb->query($wpdb->prepare("INSERT INTO ".HOSTELPRO_EMAILLOG." SET
	   		sender=%s, receiver=%s, subject=%s, date=CURDATE(), status=%s",
	   		$admin_email, $booking->contact_email, $subject, $status));
		} // end do email user
	} // end email

	// replace the booking variables in the message
	function prepare_message($message, $bookings) {
			$_room = new HostelPRORoom();

			$message = stripslashes($message);
			if(!strstr($message, '[bookings]')) $message = '[bookings]'.$message;
			if(!strstr($message, '[/bookings]')) $message = $message . '[/bookings]';

			// split message on parts
			preg_match("'\[bookings\](.*?)\[/bookings\]'si", $message, $original_match);
			//print_r($parts);
			$parts = explode('[bookings]', $message);
			$sparts = explode('[/bookings]', $parts[1]);

			$message = $parts[0];

			foreach($bookings as $booking) {
				$match = $original_match[1];
				$match = str_replace('{{from-date}}', $booking->from_date, $match);
				$match = str_replace('{{to-date}}', $booking->to_date, $match);
				$match = str_replace('{{url}}', admin_url("admin.php?page=hostelpro_bookings&do=edit&id=".$booking->id."&type=upcoming"), $match);
				$match = str_replace('{{contact-name}}', $booking->contact_name, $match);
				$match = str_replace('{{contact-email}}', $booking->contact_email, $match);
				$match = str_replace('{{contact-phone}}', $booking->contact_phone, $match);
				$match = str_replace('{{amount-paid}}', $booking->amount_paid, $match);
				$match = str_replace('{{amount-due}}', $booking->amount_due, $match);
				$match = str_replace('{{room-type}}', $_room->prettify("rtype", $booking->room->rtype, $booking->room), $match);
				$match = str_replace('{{room-name}}', stripslashes($booking->room->title), $match);
				$match = str_replace('{{num-beds}}', $booking->beds, $match);
				$match = str_replace('{{extra-beds}}', $booking->extra_beds, $match);
				$match = str_replace('{{timestamp}}', $booking->timestamp, $match);
				$match = str_replace('{{addons}}', stripslashes($booking->addons), $match);
				$match = str_replace('{{invoice-url}}', $booking->invoice_url, $match);
				$match = $this->replace_custom_fields($match, $booking->fields, $booking->datas);

				$message .= "\n" . $match;
			}

			$message .= $sparts[1];

			$message = wpautop($message);
			// echo $message;
			return $message;
	}

	// replace the custom fields in the messages
	function replace_custom_fields($message, $fields, $datas) {
		foreach($fields as $field) {
			if(strstr($message, '{{{custom-field-'.$field->name.'}}}')) {
				$custom_data = '';
				// find the data
				foreach($datas as $data) {
					if($data->field_id == $field->id) $custom_data = stripslashes($data->data);
				}

				$message = str_replace('{{{custom-field-'.$field->name.'}}}', $custom_data, $message);
			}
		}

		return $message;
	}

	// select all bookings for a given period - used to check for availability
	function select_in_period($datefrom, $dateto, $room_id = 0) {
		global $wpdb;

		$room_sql = '';
		if(!empty($room_id)) $room_sql = $wpdb->prepare(" room_id = %d AND ", $room_id);

		$bookings = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS." WHERE $room_sql ((from_date >= %s AND from_date <= %s)
			OR (to_date > %s AND to_date <= %s) OR (from_date <= %s AND to_date > %s)) ", $datefrom, $dateto, $datefrom, $dateto, $datefrom, $dateto));

		return $bookings;
	}

	// save custom fields data for the booking
	function save_data($vars, $id) {
		global $wpdb;
		$hostelpro_caps = current_user_can('manage_options') ? 'manage_options' : 'hostelpro_manage';

		// select fields in the given list ID
		$fields=$wpdb->get_results("SELECT * FROM ".HOSTELPRO_FIELDS);

		foreach($fields as $field) {
			$data=@$vars['field_'.$field->id];

			// required field?
			if($field->is_required and empty($data) and !current_user_can($hostelpro_caps)) throw new Exception(__('You have missed a required field', 'hostelpro'));

			if(empty($data)) continue;

			// Sanitize data based on field type
			$data = sanitize_text_field($data);

			// replace/insert data
			$wpdb->query($wpdb->prepare("REPLACE INTO ".HOSTELPRO_DATAS." (field_id, booking_id, data)
				VALUES (%d, %d, %s)", $field->id, $id, $data));
		}

		do_action('hostelpro_booking_data_saved', $id, $vars);
	}

	// gets a single booking along with their data
	function get($id) {
		global $wpdb;

		$booking=$wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS." WHERE id=%d", $id));

		// get data
		$datas=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".HOSTELPRO_DATAS." WHERE booking_id=%d", $id));

		foreach($datas as $data) {
			$booking->fields['field_'.$data->field_id]=$data->data;
		}

		return $booking;
	}
}