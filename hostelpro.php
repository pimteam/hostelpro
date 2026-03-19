<?php
/*
Plugin Name: Hostel PRO
Plugin URI: https://wp-hostel.com
Description: PRO Hostel / BnB management plugin
Author: Kiboko Labs
Version: 2.0.7
Author URI: http://kibokolabs.com
License: GPLv2 or later
*/

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

define( 'HOSTELPRO_PATH', dirname( __FILE__ ) );
define( 'HOSTELPRO_RELATIVE_PATH', dirname( plugin_basename( __FILE__ )));
define( 'HOSTELPRO_URL', plugin_dir_url( __FILE__ ));

// require controllers and models
require(HOSTELPRO_PATH."/helpers/htmlhelper.php");
require(HOSTELPRO_PATH."/helpers/text-captcha.php");
require(HOSTELPRO_PATH."/models/hostelpro.php");
require(HOSTELPRO_PATH."/models/room.php");
require(HOSTELPRO_PATH."/controllers/rooms.php");
require(HOSTELPRO_PATH."/models/booking.php");
require(HOSTELPRO_PATH."/models/payment.php");
require(HOSTELPRO_PATH."/models/discount.php");
require(HOSTELPRO_PATH."/models/field.php");
require(HOSTELPRO_PATH."/models/addon.php");
require(HOSTELPRO_PATH."/controllers/bookings.php");
require(HOSTELPRO_PATH."/controllers/shortcodes.php");
require(HOSTELPRO_PATH."/controllers/help.php");
require(HOSTELPRO_PATH."/controllers/discounts.php");
require(HOSTELPRO_PATH."/controllers/calendars.php");
require(HOSTELPRO_PATH."/controllers/form.php");
require(HOSTELPRO_PATH."/controllers/reports.php");
require(HOSTELPRO_PATH."/controllers/ajax.php");
require(HOSTELPRO_PATH."/controllers/addons.php");
require(HOSTELPRO_PATH."/controllers/invoices.php");
require(HOSTELPRO_PATH."/controllers/sync.php");
require(HOSTELPRO_PATH."/controllers/minstays.php");
require(HOSTELPRO_PATH."/controllers/roles.php");

add_action('init', array("HostelPRO", "init"));

register_activation_hook(__FILE__, array("HostelPRO", "install"));
add_action('admin_menu', array("HostelPRO", "menu"));
add_action('admin_enqueue_scripts', array("HostelPRO", "scripts"));

// show the things on the front-end
add_action( 'wp_enqueue_scripts', array("HostelPRO", "scripts"));

// widgets
add_action( 'widgets_init', array("HostelPRO", "register_widgets") );

// other actions
add_action('wp_ajax_hostelpro_ajax', 'hostelpro_ajax');
add_action('wp_ajax_nopriv_hostelpro_ajax', 'hostelpro_ajax');
