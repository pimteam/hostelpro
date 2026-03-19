<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// main model containing general config and UI functions
class HostelPRO {
   static function install($update = false) {
   	global $wpdb;	
   	$wpdb -> show_errors();
   	
   	if(!$update) self::init();

	   // rooms
   	if($wpdb->get_var("SHOW TABLES LIKE '".HOSTELPRO_ROOMS."'") != HOSTELPRO_ROOMS) {        
			$sql = "CREATE TABLE `" . HOSTELPRO_ROOMS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `title` VARCHAR(100) NOT NULL DEFAULT 'room',
				  `rtype` VARCHAR(100) NOT NULL DEFAULT 'dorm',
				  `beds` TINYINT UNSIGNED NOT NULL DEFAULT 0,
				  `bathroom` VARCHAR(100) NOT NULL DEFAULT 'standard' /* ensuite, shared bathroom, etc goes here */,
				  `price` DECIMAL(8,2) NOT NULL DEFAULT '0.00',
				  `description` TEXT
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }
	  	
	  	// bookings - will also contain unavailable dates which admin will store as bookings too			
		if($wpdb->get_var("SHOW TABLES LIKE '".HOSTELPRO_BOOKINGS."'") != HOSTELPRO_BOOKINGS) {        
				$sql = "CREATE TABLE `" . HOSTELPRO_BOOKINGS . "` (
					  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
					  `room_id` INT UNSIGNED NOT NULL DEFAULT 0,
					  `from_date` DATE NOT NULL DEFAULT '2000-01-01',
					  `to_date` DATE NOT NULL DEFAULT '2000-01-01',
					  `beds` TINYINT UNSIGNED NOT NULL DEFAULT 1,
					  `amount_paid` DECIMAL(8,2) NOT NULL DEFAULT '0.00',
					  `amount_due` DECIMAL(8,2) NOT NULL DEFAULT '0.00',
					  `is_static` TINYINT UNSIGNED NOT NULL DEFAULT 0 /* When 1 means admin just disabled these dates */,
					  `contact_name` VARCHAR(255) NOT NULL DEFAULT '',
					  `contact_email` VARCHAR(255) NOT NULL DEFAULT '',
					  `contact_phone` VARCHAR(255) NOT NULL DEFAULT '',
					  `contact_type` VARCHAR(255) NOT NULL DEFAULT '' /* how many people & male/female/couple/mixed */,
					  `created_time` DATETIME /* When the reservation is made */,
					  `status` VARCHAR(100) NOT NULL DEFAULT 'active' /* pending, active or cancelled */					  
					) DEFAULT CHARSET=utf8;";
				
				$wpdb->query($sql);
		  }
		  
		// payment records	  
	  	if($wpdb->get_var("SHOW TABLES LIKE '".HOSTELPRO_PAYMENTS."'") != HOSTELPRO_PAYMENTS) {        
			$sql = "CREATE TABLE `" . HOSTELPRO_PAYMENTS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `booking_id` INT UNSIGNED NOT NULL DEFAULT 0,				  
				  `date` DATE NOT NULL DEFAULT '2001-01-01',
				  `amount` DECIMAL(8,2),
				  `status` VARCHAR(100) NOT NULL DEFAULT 'failed',
				  `paycode` VARCHAR(100) NOT NULL DEFAULT '',
				  `paytype` VARCHAR(100) NOT NULL DEFAULT 'paypal'
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  	 
	  
	  // discounts	and surcharges
	  	if($wpdb->get_var("SHOW TABLES LIKE '".HOSTELPRO_DISCOUNTS."'") != HOSTELPRO_DISCOUNTS) {        
			$sql = "CREATE TABLE `" . HOSTELPRO_DISCOUNTS . "` (
				  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `name` VARCHAR(255) NOT NULL DEFAULT '',				  
				  `date_condition` TINYINT UNSIGNED NOT NULL DEFAULT 0,
				  `date_from` DATE,
				  `date_to` DATE,
				  `weekdays_condition` TINYINT UNSIGNED NOT NULL DEFAULT 0,
				  `weekdays` VARCHAR(255) NOT NULL DEFAULT '',
				  `coupon_condition` TINYINT UNSIGNED NOT NULL DEFAULT 0,
				  `coupon` VARCHAR(255) NOT NULL DEFAULT '',
				  `discount_type` VARCHAR(100) NOT NULL DEFAULT 'percent' /* percent or amount */,
				  `discount_value` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
				  `room_id` INT UNSIGNED NOT NULL DEFAULT 0
				) DEFAULT CHARSET=utf8;";
			
			$wpdb->query($sql);
	  }  	 
	  
	  // custom fields	  
 	  if($wpdb->get_var("SHOW TABLES LIKE '".HOSTELPRO_FIELDS."'") != HOSTELPRO_FIELDS) {
	  
			$sql = "CREATE TABLE `" . HOSTELPRO_FIELDS . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `name` varchar(100) NOT NULL DEFAULT '',
				  `ftype` varchar(100) NOT NULL DEFAULT '',
				  `fvalues` text NOT NULL,
				  `is_required` tinyint(3) unsigned NOT NULL DEFAULT 0,
				  `label` varchar(255) NOT NULL DEFAULT ''				  	  
				) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }
	  
	  // custom fields data 
	  if($wpdb->get_var("SHOW TABLES LIKE '".HOSTELPRO_DATAS."'") != HOSTELPRO_DATAS) {
	  
			$sql = "CREATE TABLE `" . HOSTELPRO_DATAS . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `field_id` int(10) unsigned NOT NULL DEFAULT 0,
				  `booking_id` int(10) unsigned NOT NULL DEFAULT 0,
				  `data` text NOT NULL  
				) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
			
			$sql = "ALTER TABLE `" . HOSTELPRO_DATAS . "` ADD UNIQUE (
				`field_id` ,
				`booking_id` 
				)";
			$wpdb->query($sql);	
	  }
	  
	  // extra services (bikes, breakfast, linen etc)	  	  
 	  if($wpdb->get_var("SHOW TABLES LIKE '".HOSTELPRO_ADDONS."'") != HOSTELPRO_ADDONS) {
	  
			$sql = "CREATE TABLE `" . HOSTELPRO_ADDONS . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `name` varchar(100) NOT NULL DEFAULT '',
				  `price` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
				  `per_person` TINYINT UNSIGNED NOT NULL DEFAULT 0,
				  `per_day` TINYINT UNSIGNED NOT NULL DEFAULT 0,
				  `max_available` INT UNSIGNED NOT NULL DEFAULT 0
				) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }
	  
	   // this is email log of all the messages sent in the system 
	  if($wpdb->get_var("SHOW TABLES LIKE '".HOSTELPRO_EMAILLOG."'") != HOSTELPRO_EMAILLOG) {	  
			$sql = "CREATE TABLE `" . HOSTELPRO_EMAILLOG . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `sender` VARCHAR(255) NOT NULL DEFAULT '',
				  `receiver` VARCHAR(255) NOT NULL DEFAULT '',
				  `subject` VARCHAR(255) NOT NULL DEFAULT '',
				  `date` DATE,
				  `datetime` TIMESTAMP,
				  `status` VARCHAR(255) NOT NULL DEFAULT 'OK'				  
				) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }
		  
	 // minimum stays in different seasons
	  if($wpdb->get_var("SHOW TABLES LIKE '".HOSTELPRO_MINSTAYS."'") != HOSTELPRO_MINSTAYS) {	  
			$sql = "CREATE TABLE `" . HOSTELPRO_MINSTAYS . "` (
				  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				  `days` TINYINT UNSIGNED NOT NULL DEFAULT 0,
				  `start_date` DATE,
				  `end_date` DATE				  
				) DEFAULT CHARSET=utf8;";
			$wpdb->query($sql);
	  }		  
		  
		// if there's no currency, default it to USD
		$currency = get_option('hostelpro_currency');
		if(empty($currency)) update_option('hostelpro_currency', 'USD');  	  
		
		// add db fields
		hostelpro_add_db_fields(array(
			array("name"=>"dorm_gender", "type"=>"VARCHAR(100) NOT NULL DEFAULT 'mixed'"), /*male, female, mixed*/		
			array("name" => 'price_type', "type" => "VARCHAR(100) NOT NULL DEFAULT 'per-bed'"),  /*price per room or per bed*/
			array("name" => 'extra_beds', "type" => "TINYINT UNSIGNED NOT NULL DEFAULT 0"),  /*number of extra beds allowed*/
			array("name" => 'extra_bed_price', "type" => "DECIMAL (10,2) NOT NULL DEFAULT '0.00'"),  /* price per extra bed */
			array("name" => 'notes', "type" => "TEXT"),  /* short notes displayng in the rooms listing */
			array("name" => 'discount_part_occupancy', "type" => "TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* whether a private room can be partly booked with discount */
			array("name" => 'part_occupancy_prices', "type" => "VARCHAR(255) NOT NULL DEFAULT ''"), /* prices like: "7.50, 12.00" etc. resp for 1 bed, 2 beds etc*/
			array("name" => 'overbook_beds', "type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0"),
			array("name" => 'ical_import', "type" => "TEXT"),  /* URL of external iCal to sync with */
			array("name" => 'editor_id', "type" => "INT UNSIGNED NOT NULL DEFAULT 0"),
			array("name" => 'whole_dorm_price', "type" => "DECIMAL (10,2) NOT NULL DEFAULT '0.00'"), /* If a dorm room is offered at both per-bed and whole price */
			array("name" => 'allow_child_bed_price', "type" => "TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* Whether to set diff price for child beds (for dorm rooms) */
			array("name" => 'child_bed_price', "type" => "DECIMAL (10,2) NOT NULL DEFAULT '0.00'"), /* Child bed price can be different (or free) but it still takes a bed */ 
			array("name" => 'child_bed_label', "type" => "VARCHAR(255) NOT NULL DEFAULT ''"), /* Label of the child bed price field */   
			array("name" => 'max_children', "type" => "TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* Maximum children accepted in the room */  
			array("name" => 'adults_with_children', "type" => "TINYINT UNSIGNED NOT NULL DEFAULT 0"), /* Children must be with at least X adults? */
		),
		HOSTELPRO_ROOMS);
		
		hostelpro_add_db_fields(array(
			array("name"=>"disc_type", "type"=>"VARCHAR(100) NOT NULL DEFAULT 'discount'"), /* discount or surcharge */
			array("name"=>"days_condition", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
			array("name"=>"days", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
			array("name"=>"min_price", "type"=>"DECIMAL(10,2) NOT NULL DEFAULT '0.00'"),
			array("name" => 'editor_id', "type" => "INT UNSIGNED NOT NULL DEFAULT 0"),
		),
		HOSTELPRO_DISCOUNTS);
		
		hostelpro_add_db_fields(array(
			array("name"=>"date_prices", "type"=>"TEXT"), /* serialized info about money charged for each date - used for reports */
			array("name"=>"addons", "type"=>"TEXT"), /* textual info about purchased addon services */
			array("name"=>"extra_beds", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
			array("name"=>"invoice_code", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"),
			array("name"=>"addon_details", "type"=>"TEXT"), /* serialized info about purchased addon services */
			array("name"=>"created_time", "type"=>"DATETIME"), /* when reservation was made */
			array("name"=>"ical_uid", "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"), /* ID of externally imported iCal booking */
			array("name" => 'editor_id', "type" => "INT UNSIGNED NOT NULL DEFAULT 0"),
			array("name" => 'discount', "type" => "DECIMAL(10,2) NOT NULL DEFAULT '0.00'"),
			array("name" => 'child_beds', "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"),
			array("name" => 'session_id', "type"=>"VARCHAR(100) NOT NULL DEFAULT ''"), /* when doing multiple bookings at once all of them will have same session ID */
			array("name" => 'amount_now', "type"=>"DECIMAL(10,2) NOT NULL DEFAULT '0.00'"), /* amount to pay now */
			array("name" => 'amount_arrival', "type"=>"DECIMAL(10,2) NOT NULL DEFAULT '0.00'"), /* amount to pay on arrival */
		),
		HOSTELPRO_BOOKINGS);
		
		hostelpro_add_db_fields(array(
			array("name"=>"sort_order", "type"=>"SMALLINT UNSIGNED NOT NULL DEFAULT 0"), /* serialized info about money charged for each date - used for reports */
		),
		HOSTELPRO_FIELDS);
		
		hostelpro_add_db_fields(array(
			array("name"=>"filename", "type"=>"VARCHAR(255) NOT NULL default ''"), 
			array("name"=>"filesize", "type"=>"INT UNSIGNED NOT NULL default 0"), /* size in  KB */
			array("name"=>"filetype", "type"=>"VARCHAR(255) NOT NULL default ''"),
			array("name"=>"filecontents", "type"=>"LONGBLOB"),  
		),
		HOSTELPRO_DATAS);
		
		hostelpro_add_db_fields(array(
			array("name"=>"is_inactive", "type"=>"TINYINT UNSIGNED NOT NULL DEFAULT 0"), 
			array("name"=>"description", "type"=>"TEXT"),
			array("name"=>"room_id", "type"=>"INT UNSIGNED NOT NULL DEFAULT 0"),
			array("name" => 'editor_id', "type" => "INT UNSIGNED NOT NULL DEFAULT 0"),
		),
		HOSTELPRO_ADDONS);
		
		// change badly named field
		$version = get_option('hostelpro_version');
		if(!empty($version) and $version < 0.84) {
			$wpdb->query("ALTER TABLE ".HOSTELPRO_BOOKINGS." CHANGE `timestamp` `created_time` DATETIME");
		}
		
		if(!empty($version) and $version < 0.92) {
			$wpdb->query("ALTER TABLE ".HOSTELPRO_ROOMS." CHANGE `ical_import` `ical_import` TEXT");
		}
		
		// set advance to 100 if it's not yet set
		$advance_payment_percentage = get_option('hostelpro_advance_payment_percentage');
		if(empty($advance_payment_percentage)) update_option('hostelpro_advance_payment_percentage', 100);
		
		update_option('hostelpro_version', 1.0);
   }
   
   // main menu
   static function menu() {
		$hostelpro_caps = current_user_can('manage_options') ? 'manage_options' : 'hostelpro_manage';   	
   	
   	add_menu_page(__('Hostel PRO', 'hostelpro'), __('Hostel PRO', 'hostelpro'), $hostelpro_caps, "hostelpro_options", 
   		array(__CLASS__, "options"));

   	add_submenu_page('hostelpro_options', __('Settings', 'hostelpro'), __('Settings', 'hostelpro'), $hostelpro_caps, "hostelpro_options", 
   		array(__CLASS__, "options"));	
   		
   	$rooms_access = HostelPRORoles::check_access('rooms_access', true); 		
		if($rooms_access) add_submenu_page('hostelpro_options', __("Manage Rooms", 'hostelpro'), __("Manage Rooms", 'hostelpro'), $hostelpro_caps, 'hostelpro_rooms', array('HostelPRORooms', "manage"));
		$addons_access = HostelPRORoles::check_access('addons_access', true); 
		if($addons_access) add_submenu_page('hostelpro_options', __("Addon Services", 'hostelpro'), __("Addon Services", 'hostelpro'), $hostelpro_caps, 'hostelpro_addons', array('HostelPROAddons', "manage"));
		$bookings_access = HostelPRORoles::check_access('bookings_access', true); 
		if($bookings_access) add_submenu_page('hostelpro_options', __("Manage Bookings", 'hostelpro'), __("Manage Bookings", 'hostelpro'), $hostelpro_caps, 'hostelpro_bookings', array('HostelPROBookings', "manage")); 
		$overview_access = HostelPRORoles::check_access('overview_access', true);
		if($overview_access)add_submenu_page('hostelpro_options', __("Calendar Overview", 'hostelpro'), __("Calendar Overview", 'hostelpro'), $hostelpro_caps, 'hostelpro_calendar_overview', array('HostelPROBookings', "calendar_overview")); 
		$form_access = HostelPRORoles::check_access('form_access', true);
		if($form_access) add_submenu_page('hostelpro_options', __("Booking Form", 'hostelpro'), __("Booking Form", 'hostelpro'), $hostelpro_caps, 'hostelpro_booking_form', array('HostelPROBookingForm', "manage")); 
		$unavailable_access = HostelPRORoles::check_access('unavailable_access', true);
		if($unavailable_access) add_submenu_page('hostelpro_options', __("Unavailable Dates", 'hostelpro'), __("Unavailable Dates", 'hostelpro'), $hostelpro_caps, 'hostelpro_unavailable', array('HostelPROBookings', "unavailable")); 
		$discounts_access = HostelPRORoles::check_access('discounts_access', true);
		if($discounts_access) add_submenu_page('hostelpro_options', __("Discounts &amp; Surcharges", 'hostelpro'), __("Discounts &amp; Surcharges", 'hostelpro'), $hostelpro_caps, 'hostelpro_discounts', array('HostelPRODiscounts', "manage")); 
		$reports_access = HostelPRORoles::check_access('reports_access', true);
		if($reports_access) add_submenu_page('hostelpro_options', __("Reports &amp; Charts", 'hostelpro'), __("Reports &amp; Charts", 'hostelpro'), $hostelpro_caps, 'hostelpro_reports', array('HostelPROReports', "main")); 
		$emaillog_access = HostelPRORoles::check_access('emaillog_access', true);
		if($emaillog_access) add_submenu_page('hostelpro_options', __("Email Log", 'hostelpro'), __("Email Log", 'hostelpro'), $hostelpro_caps, 'hostelpro_emaillog', array('HostelPROReports', "email_log")); 
   	add_submenu_page('hostelpro_options', __("Help", 'hostelpro'), __("Help", 'hostelpro'), $hostelpro_caps, 'hostelpro_help', array('HostelPROHelp', "index")); 	
   	
   	// not in menu
   	add_submenu_page(null, __("Room Calendar", 'hostelpro'), __("Room Calendar", 'hostelpro'), $hostelpro_caps, 'hostelpro_room_calendar', array('HostelPROCalendars', "get_shortcode")); 	
   	add_submenu_page(null, __("Manage Invoice Template", 'hostelpro'), __("Manage Invoice Template", 'hostelpro'), $hostelpro_caps, 'hostelpro_invoice_template', array('HostelPROInvoices', "template"));
   	add_submenu_page(null, __("Room Bookings Calendar", 'hostelpro'), __("Room Bookings Calendar", 'hostelpro'), $hostelpro_caps, 'hostelpro_room_bookings', array('HostelPRORooms', "bookings_calendar")); 
   	add_submenu_page(null, __("Minimum Stay Periods", 'hostelpro'), __("Minimum Stay Periods", 'hostelpro'), $hostelpro_caps, 'hostelpro_minstays', array('HostelPROMinStays', "manage")); 
   	add_submenu_page(null, __("Fine-tune Roles Access", 'hostelpro'), __("Fine-tune Roles Access", 'hostelpro'), 'manage_options', 'hostelpro_roles', array('HostelPRORoles', "manage")); 
   	
   	do_action('hostelpro_admin_menu');		
	}
	
	// CSS and JS
	static function scripts() {
		// CSS
		wp_register_style( 'hostelpro-css', HOSTELPRO_URL.'css/main.css?v=1');
	  wp_enqueue_style( 'hostelpro-css' );
   
   	wp_enqueue_script('jquery');
	   
	   // Hostelpro's own Javascript
		wp_register_script(
				'hostelpro-common',
				HOSTELPRO_URL.'js/common.js',
				false,
				'1.5.6',
				false
		);
		wp_enqueue_script("hostelpro-common");
		
		$translation_array = array('email_required' => __('Please provide a valid email address', 'hostelpro'),
		'name_required' => __('Please provide name', 'hostelpro'),
		'beds_required' => __('Please enter number of beds, numbers only', 'hostelpro'),
		'from_date_required' => __('Please enter arrival date', 'hostelpro'),
		'to_date_required' => __('Please enter date of leaving', 'hostelpro'),
		'from_date_atleast_today' => __('Date of arrival cannot be in the past', 'hostelpro'),
		'from_date_before_to' => __('Date of arrival cannot be after date of leave', 'hostelpro'),
		'required_field' => __('This field is required', 'hostelpro'),
		'missed_text_captcha' => __('You need to answer the verification question', 'hostelpro'),
		'select_room' => __('Please select room', 'hostelpro'),
		'min_stay_required' => __('Minimum stay of %d days is required', 'hostelpro'),
		'max_stay_allowed' => __('Maximum stay of %d days is allowed', 'hostelpro'),
		'please_wait' => __('Please wait...', 'hostelpro'),
		'loading' => __('Loading...', 'hostelpro'),
		'are_you_sure' => __('Are you sure?', 'hostelpro'),
		'ajax_url' => admin_url('admin-ajax.php'),
		'ajax_nonce' => wp_create_nonce('hostelpro-ajax-nonce'));
		wp_localize_script( 'hostelpro-common', 'hostelpro_i18n', $translation_array );
		
		// jQuery Validator
		wp_enqueue_script(
				'jquery-validator',
				'//ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js',
				false,
				'0.1.0',
				false
		);
	}
	
	// initialization
	static function init() {
		global $wpdb;
		
		if(get_option('hostelpro_debug_mode'))  {			
			$wpdb->show_errors();
			if(!defined('DIEONDBERROR')) define( 'DIEONDBERROR', true );
		}		
		
		load_plugin_textdomain( 'hostelpro', false, HOSTELPRO_RELATIVE_PATH."/languages/" );
		/*if (!session_id() and (strstr(@$_GET['page'], 'watupro') or !is_admin()) ) {
				@session_start();
		}*/
		
		// define table names 
		// uses the same tables as the free Hostel plus some more. The name of the tables will still have the
		// wphostel_ prefix
		if(!defined('HOSTELPRO_ROOMS')) define( 'HOSTELPRO_ROOMS', $wpdb->prefix. "wphostel_rooms");
		if(!defined('HOSTELPRO_BOOKINGS')) define( 'HOSTELPRO_BOOKINGS', $wpdb->prefix. "wphostel_bookings");
		if(!defined('HOSTELPRO_PAYMENTS')) define( 'HOSTELPRO_PAYMENTS', $wpdb->prefix. "wphostel_payments");
		if(!defined('HOSTELPRO_DISCOUNTS')) define( 'HOSTELPRO_DISCOUNTS', $wpdb->prefix. "wphostel_discounts");
		if(!defined('HOSTELPRO_FIELDS')) define( 'HOSTELPRO_FIELDS', $wpdb->prefix. "wphostel_fields");
		if(!defined('HOSTELPRO_DATAS')) define( 'HOSTELPRO_DATAS', $wpdb->prefix. "wphostel_datas");
		if(!defined('HOSTELPRO_ADDONS')) define( 'HOSTELPRO_ADDONS', $wpdb->prefix. "wphostel_addons");
		if(!defined('HOSTELPRO_EMAILLOG')) define( 'HOSTELPRO_EMAILLOG', $wpdb->prefix. "wphostel_emaillog");
		if(!defined('HOSTELPRO_MINSTAYS')) define( 'HOSTELPRO_MINSTAYS', $wpdb->prefix. "wphostel_minstays");
	
		define( 'HOSTELPRO_VERSION', get_option('hostelpro_version'));
		
		// prepare the custom filter on the content
		hostelpro_define_filters();
		
		$currency = get_option('hostelpro_currency');
		if(empty($currency)) {
			$currency = 'USD';
			update_option('hostelpro_currency', 'USD');
		}  	
		define( 'HOSTELPRO_PAYMENT_CURRENCY', $currency);
		
		// make the front-end currency better human readable
		if($currency == 'USD') $currency = '$';
		if($currency == 'EUR') $currency = '&euro;';
		if($currency == 'GBP') $currency = '&pound;';
		if($currency == 'JPY') $currency = '&yen;';
		if($currency == 'INR') $currency = '&#x20B9;';
		define( 'HOSTELPRO_CURRENCY', $currency);
		
		// shortcodes
		add_shortcode('hostelpro-booking', array("HostelPROShortcodes", "booking"));
		add_shortcode('hostelpro-list', array("HostelPROShortcodes", "list_rooms"));
		add_shortcode('hostelpro-book', array("HostelPROShortcodes", "book"));
		add_shortcode('hostelpro-calendar', array("HostelPROShortcodes", "calendar"));
		add_shortcode('hostelpro-calendar-overview', array("HostelPROShortcodes", "calendar_overview"));
		add_shortcode( 'hostelpro-field-static', array("HostelPROShortcodes", 'static_field'));
		add_shortcode( 'hostelpro-field', array("HostelPROShortcodes", 'field'));
		add_shortcode( 'hostelpro-submit-button', array("HostelPROShortcodes", 'submit_button'));
		add_shortcode( 'hostelpro-form-start', array("HostelPROShortcodes", 'form_start'));
		add_shortcode( 'hostelpro-form-end', array("HostelPROShortcodes", 'form_end'));
		add_shortcode( 'hostelpro-addon', array("HostelPROShortcodes", 'addon'));
		add_shortcode( 'hostelpro-if-extra-beds', array("HostelPROShortcodes", 'if_extra_beds'));
		add_shortcode( 'hostelpro-if-beds', array("HostelPROShortcodes", 'if_beds'));
		add_shortcode( 'hostelpro-room-addons', array("HostelPROShortcodes", 'room_addons'));
		add_shortcode( 'hostelpro-room-description', array("HostelPROShortcodes", 'room_description'));
		
		// Paypal IPN
		add_filter('query_vars', array(__CLASS__, "query_vars"));
		add_action('parse_request', array("HostelPROPayment", "parse_request"));
		
		// invoice
		add_action('template_redirect', array("HostelPROInvoices", "display"));
		
		// ical
		add_action('template_redirect', array("HostelPROSync", "ical"));
		
		define('HOSTELPRO_NO_DECIMALS', get_option('hostelpro_no_decimals'));
		
		// default datepicker CSS
		if(get_option('wphostel_datepicker_css') == '') {
			update_option('wphostel_datepicker_css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		}
		
		$old_version = get_option('hostelpro_version');
		
		if(empty($old_version) or $old_version < 1.0) self :: install(true);	
		
		// cleanup unconfirmed bookings
		$booking_mode = get_option('hostelpro_booking_mode');
		if($booking_mode == 'paypal' or $booking_mode == 'stripe') {
			$cleanup_hours = get_option('hostelpro_cleanup_hours');
			if(!empty($cleanup_hours) and is_numeric($cleanup_hours)) {
				 $wpdb->query("DELETE FROM ".HOSTELPRO_BOOKINGS." WHERE
				 	created_time < '".current_time('mysql')."' - INTERVAL $cleanup_hours HOUR	
				 	AND amount_paid = 0 AND status != 'active'");
			}
		}	
		
		// cleanup email logs		
		$cleanup_raw_log = get_option('hostelpro_cleanup_email_log');
		if(empty($cleanup_raw_log)) $cleanup_raw_log = 7;
		if($wpdb->get_var("SHOW TABLES LIKE '".HOSTELPRO_EMAILLOG."'") == HOSTELPRO_EMAILLOG) {			
			$wpdb->query($wpdb->prepare("DELETE FROM ".HOSTELPRO_EMAILLOG." WHERE date < CURDATE() - INTERVAL %d DAY", $cleanup_raw_log));				
		}		
		
		// handle Stripe payment
		if(!empty($_POST['hostelpro_stripe_pay'])) HostelPROPayment :: Stripe(); // process Stripe payment if any
		
		// handle Paypal PDT payment
		if(!empty($_GET['hostelpro_pdt'])) HostelPROPayment::paypal_ipn(); // process PDT payment if any		
		
		// max date for the datepicker. Defaults to 1 year
		$max_date = get_option('hostelpro_max_date');
		if(empty($max_date) or $max_date == '0m' or $max_date == '0y') {
			update_option('hostelpro_max_date', '1y');
			$max_date = '1y';
		}
		define("HOSTELPRO_MAX_DATE", $max_date);
		
		do_action('hostelpro_init');
	}
	
	// handle Hostel vars in the request
	static function query_vars($vars) {
		$new_vars = array('hostelpro');
		$vars = array_merge($new_vars, $vars);
	   return $vars;
	} 	
		
	// parse Hostelpro vars in the request
	static function template_redirect() {		
		global $wp, $wp_query, $wpdb;
		$redirect = false;		
		 
	  if($redirect) {
	   	if(@file_exists(get_stylesheet_directory()."/".$template)) include get_stylesheet_directory()."/hostelpro/".$template;		
			else include(HOSTELPRO_PATH."/views/templates/".$template);
			exit;
	  }	   
	}	
			
	// manage general options
	static function options() {
		global $wpdb, $wp_roles;
		$is_admin = current_user_can('administrator');	
		
		$multiuser_access = 'all';
		$multiuser_access = HostelPRORoles::check_access('settings_access', true);		
		if(!$multiuser_access) wp_die(__('You cannot access the Settings page. Use the links in the menu to access the pages you have permissions to access.', 'hostelpro'));
		
		// copy settings from Hostel to Hostel PRO
		if(!empty($_POST['copy_settings'])) {
			update_option('hostelpro_currency', get_option('wphostel_currency'));
			update_option('hostelpro_booking_mode', get_option('wphostel_booking_mode'));
			update_option('hostelpro_email_options', get_option('wphostel_email_options'));
			update_option('hostelpro_paypal', get_option('wphostel_paypal'));
			update_option('hostelpro_use_pdt', get_option('wphostel_use_pdt'));
			update_option('hostelpro_pdt_token', get_option('wphostel_pdt_token'));
			update_option('hostelpro_min_stay', get_option('wphostel_min_stay'));
			update_option('hostelpro_cleanup_hours', get_option('wphostel_cleanup_hours'));
			update_option('hostelpro_debug_mode', get_option('wphostel_debug_mode'));
			update_option('hostelpro_max_date', get_option('wphostel_max_date'));
			
			if(!empty($_POST['convert_shortcodes'])) {				
				$wpdb->query("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, '[wphostel', '[hostelpro') WHERE 1");
			}					
		}		
		
		if(!empty($_POST['ok']) and check_admin_referer('hostelpro_settings')) {			
			if(empty($_POST['currency'])) $_POST['currency'] = $_POST['custom_currency'];
			update_option('hostelpro_currency', sanitize_text_field($_POST['currency']));
			update_option('hostelpro_booking_mode', $_POST['booking_mode']);
			$multi_booking = empty($_POST['multi_booking']) ? 0 : 1;
			update_option('hostelpro_multi_booking', $multi_booking);
			update_option('hostelpro_sender_email', sanitize_email($_POST['sender_email']));
			update_option('hostelpro_sender_name', sanitize_text_field($_POST['sender_name']));
			update_option('hostelpro_email_options', array("do_email_admin"=>intval(@$_POST['do_email_admin']), 
				"admin_email"=>sanitize_email($_POST['admin_email']), "do_email_user"=>intval(@$_POST['do_email_user']), 
				"email_admin_subject"=>sanitize_text_field($_POST['email_admin_subject']), 
					"email_admin_message"=>hostelpro_strip_tags($_POST['email_admin_message']),
				"email_user_subject"=>sanitize_text_field($_POST['email_user_subject']), 
				"email_user_message"=>hostelpro_strip_tags($_POST['email_user_message'])));
			update_option('hostelpro_paypal', sanitize_text_field($_POST['paypal']));
			update_option('wphostel_paypal_return', esc_url_raw($_POST['paypal_return']));
			update_option('hostelpro_paypal_sandbox', intval(@$_POST['paypal_sandbox']));
			update_option('hostelpro_use_pdt', intval(@$_POST['use_pdt']));
			update_option('hostelpro_pdt_token', sanitize_text_field($_POST['pdt_token']));
			update_option('hostelpro_stripe_public', sanitize_text_field($_POST['stripe_public']));							
			update_option('hostelpro_stripe_secret', sanitize_text_field($_POST['stripe_secret']));
			update_option('hostelpro_stripe_success', hostelpro_strip_tags($_POST['stripe_success']));
			update_option('hostelpro_stripe_return', esc_url_raw($_POST['stripe_return']));
			update_option('hostelpro_advance_payment_percentage', floatval($_POST['advance_payment_percentage'])); // can be percentage or fixed amount
			update_option('hostelpro_advance_payment_unit', sanitize_text_field($_POST['advance_payment_unit'])); // % or fixed?
			update_option('hostelpro_cleanup_hours', intval($_POST['cleanup_hours']));
			update_option('hostelpro_min_stay', intval($_POST['min_stay']));
			update_option('hostelpro_max_stay', intval($_POST['max_stay']));
			update_option('hostelpro_payemnt_instructions', hostelpro_strip_tags($_POST['instructions']));
			update_option('hostelpro_text_captcha', hostelpro_strip_tags($_POST['text_captcha']));
			update_option('hostelpro_text_captcha_enabled', intval(@$_POST['enable_text_captcha']));
			update_option('hostelpro_honeypot', intval(@$_POST['honeypot']));
			update_option('hostelpro_no_decimals', intval(@$_POST['no_decimals']));			
			update_option('hostelpro_debug_mode', intval(@$_POST['debug_mode']));
			update_option('hostelpro_booking_start', sanitize_text_field($_POST['booking_start']));
			update_option('hostelpro_max_date', intval($_POST['max_date_num']).sanitize_text_field($_POST['max_date_unit']));
		}		
		
		if(!empty($_POST['datepicker_settings'])) {
			// these will be the same for PRO and free versions
			// datepicker locale and CSS
			update_option('wphostel_locale_url', $_POST['locale_url']);
			update_option('wphostel_datepicker_css', $_POST['datepicker_css']);
		}
		
		if(!empty($_POST['convtrack_settings'])) {
			// these will be the same for PRO and free versions			
			update_option('wphostel_convtrack_code', $_POST['convtrack_code']);			
		}
		
		if(!empty($_POST['role_settings']) and $is_admin and check_admin_referer('role_settings')) {
			$roles = $wp_roles->roles;			
			
			foreach($roles as $key=>$r) {
				if($key == 'administrator') continue;
				
				$role = get_role($key);
	
				// manage Hostel(& Pro) - allow only admin change this
				if($is_admin) {
					if(@in_array($key, $_POST['manage_roles'])) {					
	    				if(!$role->has_cap('hostelpro_manage')) $role->add_cap('hostelpro_manage');
					}
					else $role->remove_cap('hostelpro_manage');
				}	// end if can_manage_options
			} // end foreach role 
		}
		
		$roles = $wp_roles->roles;
		
		$currency = get_option('hostelpro_currency');
		$currencies=array('USD'=>'$', "EUR"=>"&euro;", "GBP"=>"&pound;", "JPY"=>"&yen;", "AUD"=>"AUD",
		   "CAD"=>"CAD", "CHF"=>"CHF", "CZK"=>"CZK", "DKK"=>"DKK", "HKD"=>"HKD", "HUF"=>"HUF",
		   "ILS"=>"ILS", "INR" => "INR", "MXN"=>"MXN", "NOK"=>"NOK", "NZD"=>"NZD", "PLN"=>"PLN", "SEK"=>"SEK",
		   "SGD"=>"SGD", 'THB'=>'&#3647;', "ZAR"=>"ZAR");
		$currency_keys = array_keys($currencies);  
		   
		$booking_mode = get_option('hostelpro_booking_mode');   
		$email_options = get_option('hostelpro_email_options');
		$paypal = get_option('hostelpro_paypal');
		$advance_payment_percentage = get_option('hostelpro_advance_payment_percentage');
		$advance_payment_unit = get_option('hostelpro_advance_payment_unit');
		$cleanup_hours = get_option('hostelpro_cleanup_hours');
		$min_stay = get_option('hostelpro_min_stay');
		$max_stay = get_option('hostelpro_max_stay');
		$booking_start = get_option('hostelpro_booking_start');
		$use_pdt = get_option('hostelpro_use_pdt');
		
		$plugins = get_plugins();
		$hostel_installed = false;
		foreach($plugins as $plugin) {
			if($plugin['Name'] == 'Hostel') $hostel_installed = true;
		}
		
		// select custom fields to display in the email options
		$fields = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_FIELDS." ORDER BY id");
		
		// if user and admin email messages are empty, create default ones
		if(empty($email_options['email_admin_subject'])) {
			$email_options['email_admin_subject'] = __('A new booking was made', 'hostelpro');
		}
		
		if(empty($email_options['email_admin_message'])) {
			$email_options['email_admin_message'] = __('[bookings]Booking details: {{url}}<br>From date: {{from-date}}<br>To date: {{to-date}}<br>Amount paid: {{amount-paid}}<br>Amount due: {{amount-due}}<br>Room name: {{room-name}}<br>Room type: {{room-type}}<br>Num beds: {{num-beds}}<br>Contact email: {{contact-email}}<br>Contact name: {{contact-name}}<br>Contact phone: {{contact-phone}}<br>Timestamp: {{timestamp}}[/bookings]', 'hostelpro');
		}
		
		if(empty($email_options['email_user_subject'])) {
			$email_options['email_user_subject'] = __('Thank you for your booking!', 'hostelpro');
		}
		
		if(empty($email_options['email_user_message'])) {
			$email_options['email_user_message'] = __('Below is a confirmation for your booking:<br>[bookings]From date: {{from-date}}<br>To date: {{to-date}}<br>Amount paid: {{amount-paid}}<br>Amount due: {{amount-due}}<br>Room type: {{room-type}}<br>Num beds: {{num-beds}}[/bookings]', 'hostelpro');
		}
		
		// load 3 default questions in case nothing is loaded
		$text_captcha = get_option('hostelpro_text_captcha');
		$text_captcha_enabled = get_option('hostelpro_text_captcha_enabled');
		if(empty($text_captcha)) {
			$text_captcha = __('What is the color of the snow? = white', 'hostelpro').PHP_EOL.__('Is fire hot or cold? = hot', 'hostelpro') 
				.PHP_EOL. __('In which continent is France? = Europe', 'hostelpro'); 
		}
		
		$payment_errors = get_option('hostelpro_errorlog');
		$ical_errors = get_option('hostelpro_ical_import_error');
		
		$max_date = get_option('hostelpro_max_date');		
		$max_date_num = substr($max_date, 0, 1);
		$max_date_unit = substr($max_date, 1, 1);
		   	
		require(HOSTELPRO_PATH."/views/options.php");
	}	// end options()
	
	static function help() {
		require(HOSTELPRO_PATH."/views/help.php");
	}	
	
	static function register_widgets() {
		// register_widget('WPHostelWidget');
	}
}