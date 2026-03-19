<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HostelPROInvoices {
	// manage the invoice template
	static function template() {
		if(!empty($_POST['ok'])) {
			update_option('hostelpro_invoice_template', $_POST['template']);
		}
		
		$template = stripslashes(get_option('hostelpro_invoice_template'));
		
		if(empty($template)) {
			ob_start();
			if(@file_exists(get_stylesheet_directory().'/hostelpro/invoice-template.html.php')) include get_stylesheet_directory().'/hostelpro/invoice-template.html.php';
			else include(HOSTELPRO_PATH."/views/invoice-template.html.php");
			$template = ob_get_clean();			 
		}
		
		if(@file_exists(get_stylesheet_directory().'/hostelpro/manage-invoice-template.html.php')) include get_stylesheet_directory().'/hostelpro/manage-invoice-template.html.php';
		else include(HOSTELPRO_PATH."/views/manage-invoice-template.html.php");		
	}
	
	// display invoice
	static function display() {
		global $wpdb;
		
		if(empty($_GET['hostelpro_invoice'])) return true;
		
		// find the booking
		$booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS." 
			WHERE id=%d AND invoice_code=%s", $_GET['id'], $_GET['hostelpro_invoice']));
		
		if(empty($booking->id)) wp_die(__('Invoice not found!', 'hostelpro'));
		
		$room = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ROOMS." WHERE id=%d", @$booking->room_id));	
		$_room = new HostelPRORoom();
		
		// now get the content and replace the vars
		$content = stripslashes(get_option('hostelpro_invoice_template'));
		if(empty($content)) {
			ob_start();	
			if(@file_exists(get_stylesheet_directory().'/hostelpro/invoice-template.html.php')) include get_stylesheet_directory().'/hostelpro/invoice-template.html.php';
			else include(HOSTELPRO_PATH."/views/invoice-template.html.php");	
			$content = ob_get_clean();
		}
		
		$date_prices = unserialize(stripslashes($booking->date_prices));
		$num_days = is_array($date_prices) ? count($date_prices) : 0;		
		$dateformat = get_option('date_format');
		
		$amount_due = $booking->amount_paid + $booking->amount_due;
		
		
		$content = str_replace('{{css-url}}', HOSTELPRO_URL."/css/invoice.css", $content);
		$content = str_replace('{{invoice-num}}', sprintf('%08d', $booking->id), $content);
		$content = str_replace('{{client-name}}', stripslashes($booking->contact_name), $content);		
		$content = str_replace('{{invoice-date}}', date_i18n($dateformat, strtotime($booking->created_time)), $content);
		$content = str_replace('{{amount}}', number_format($booking->amount_paid + $booking->amount_due, 2), $content);
		$content = str_replace('{{currency}}', HOSTELPRO_CURRENCY, $content);
		$content = str_replace('{{amount-paid}}', $booking->amount_paid + 0, $content);
		$content = str_replace('{{amount-due}}', $booking->amount_due + 0, $content);
		
		// get $item_row from the template and replace it in $content
		$parts = explode('<!-- items -->', $content);		
		$item_row = $parts[1];
		$start_content = $parts[0];
		$end_content = $parts[2];
		$middle_content = '';
		
		// always add the booking to invoice
		$row = $item_row;
		$row = str_replace('{{item}}', __('Booking', 'hostelpro'), $row);		
		$row = str_replace('{{from-date}}', date_i18n($dateformat, strtotime($booking->from_date)), $row);
		$row = str_replace('{{to-date}}', date_i18n($dateformat, strtotime($booking->to_date)), $row);
		$row = str_replace('{{item-amount}}', $amount_due + floatval($booking->discount), $row);
		$row = str_replace('{{num-beds}}', ($booking->beds + $booking->extra_beds), $row);
		$row = str_replace('{{room-type}}', $_room->prettify('rtype', $room->rtype, $room), $row);
		$row = str_replace('{{addons}}', stripslashes($booking->addons), $row);
		$row = str_replace('{{room-name}}', stripslashes($room->title), $row);
		$row = str_replace('{{num-days}}', $num_days, $row);
		$middle_content .= $row;
		
		if(!empty($booking->discount)) {
				$row = $item_row;
				$parts = explode('<!-- item-description-->', $row);
				$row = $parts[0].__('Discount', 'hostelpro').$parts[2];
				$row = str_replace('{{item}}', __('Discount', 'hostelpro'), $row);		
				$row = str_replace('{{item-amount}}', -$booking->discount, $row);
				$middle_content .= $row;
		}
		
		echo $start_content . $middle_content .  $end_content; 
		exit;	 
	}
}