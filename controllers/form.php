<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HostelPROBookingForm {	
	// manage the custom fields
	// and display shortocdes for visual editor
	static function manage() {		
		global $wpdb;	
		$_field=new HostelProField();
		
		$multiuser_access = 'all';
		$multiuser_access = HostelPRORoles::check_access('form_access');

		switch(@$_GET['do']) {
			case 'add':
				if(!empty($_POST['ok'])) {					
					$_field->add($_POST);
					hostelpro_redirect("admin.php?page=hostelpro_booking_form");
				}
				
				if(@file_exists(get_stylesheet_directory().'/hostelpro/form-field.html.php')) include get_stylesheet_directory().'/hostelpro/form-field.html.php';
				else include(HOSTELPRO_PATH."/views/form-field.html.php");
			break;
	
			case 'edit':
				if(!empty($_POST['del'])) {
					$_field->delete($_GET['id']);
					hostelpro_redirect("admin.php?page=hostelpro_booking_form&message=deleted");				
				}		
			
				if(!empty($_POST['ok'])) {
					$_field->save($_POST, $_GET['id']);
					hostelpro_redirect("admin.php?page=hostelpro_booking_form&message=saved");
				}		
			
				$field = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_FIELDS." WHERE id=%d", $_GET['id']));
				
				if($field->ftype == 'file') {
					list($filesize, $filetypes) = explode('|', $field->fvalues);
				}
				if(@file_exists(get_stylesheet_directory().'/hostelpro/form-field.html.php')) include get_stylesheet_directory().'/hostelpro/form-field.html.php';
				else include(HOSTELPRO_PATH."/views/form-field.html.php");
			break;		
			
			default:
				// update any fields that don't have sort_order yet
				$wpdb->query("UPDATE ".HOSTELPRO_FIELDS." SET sort_order=id WHERE sort_order=0");
				
				if(!empty($_GET['move'])) {
					// select field
					$field = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_FIELDS." WHERE id=%d", $_GET['move']));
					
					if($_GET['dir'] == 'up') {
						$new_order = $field->sort_order - 1;
						if($new_order<0) $new_order = 0;
						
						// shift others
						$wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_FIELDS." SET sort_order=sort_order+1 
						  WHERE id!=%d AND sort_order=%d", $_GET['move'], $new_order));
					}
					else {
						$new_order = $field->sort_order+1;			
			
						// shift others
						$wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_FIELDS." SET sort_order=sort_order-1 
			  				WHERE id!=%d AND sort_order=%d", $_GET['move'], $new_order));
					}
					
					// change this one
					$wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_FIELDS." SET sort_order=%d WHERE id=%d", 
						$new_order, $_GET['move']));
						
					// redirect 	
					hostelpro_redirect('admin.php?page=hostelpro_booking_form');
				}
				
				if(!empty($_POST['save_design'])) {
					update_option('hostelpro_booking_form_design', $_POST['booking_form_design']);
				}
					
				// custom fields management and shortcode format on the same page				
				$fields = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_FIELDS." ORDER BY sort_order, id");
				$count = sizeof($fields);
				
				// addon services
				$addons = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_ADDONS." WHERE is_inactive=0 AND room_id=0 ORDER BY id");
				
				// any rooms that allow extra beds?
				$any_extra_beds = $wpdb->get_var("SELECT COUNT(id) FROM ".HOSTELPRO_ROOMS." WHERE extra_beds > 0");	
						
				if(@file_exists(get_stylesheet_directory().'/hostelpro/form-fields.html.php')) include get_stylesheet_directory().'/hostelpro/form-fields.html.php';
				else include(HOSTELPRO_PATH."/views/form-fields.html.php");
			break;
		}	
	}
}