<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// fine-tune role access and restrict it
class HostelPRORoles {
	static function manage() {
		global $wpdb, $wp_roles;
		$roles = $wp_roles->roles;
		
		// this sets the setting of a selected role
		if(!empty($_POST['config_role'])) {
			$role_settings = unserialize(get_option('hostelpro_role_settings'));	
			
			// overwrite the settings for the selected role
			$role_settings[$_POST['role_key']] = array("settings_access" => sanitize_text_field($_POST['settings_access']), 
				"rooms_access" => sanitize_text_field($_POST['rooms_access']), "addons_access" => sanitize_text_field($_POST['addons_access']),
				"bookings_access" => sanitize_text_field($_POST['bookings_access']), "overview_access" => sanitize_text_field($_POST['overview_access']), 
				"form_access" => sanitize_text_field($_POST['form_access']), "unavailable_access" => sanitize_text_field($_POST['unavailable_access']), 
				"discounts_access" => sanitize_text_field($_POST['discounts_access']), "reports_access" => sanitize_text_field($_POST['reports_access']), 
				"emaillog_access" => sanitize_text_field($_POST['emaillog_access']));
				
			update_option('hostelpro_role_settings', serialize($role_settings));	
		} // end config_role
		
		$role_settings = unserialize(get_option('hostelpro_role_settings'));
		
		// get the currently enabled roles
		$enabled_roles = array();
		foreach($roles as $key => $role) {
			$r=get_role($key);
			if(!empty($r->capabilities['hostelpro_manage'])) $enabled_roles[] = $key;
		}
		
		require(HOSTELPRO_PATH."/views/roles.html.php");
	}
	
	// checks the access of the current user
	static function check_access($what, $noexit = false) {
		global $user_ID, $wp_roles;
		
		$role_settings = unserialize(get_option('hostelpro_role_settings'));
		$roles = $wp_roles->roles;
		// get all the currently enabled roles
		$enabled_roles = array();
		foreach($roles as $key => $role) {
			$r=get_role($key);
			if(!empty($r->capabilities['hostelpro_manage'])) $enabled_roles[] = $key;
		}
				
		// admin can do everything
		if(current_user_can('administrator')) return 'all';		
		$user = new WP_User( $user_ID );
				
		$has_access = false;
		foreach($user->roles as $role) {
			if(!empty($role_settings[$role])) {				
				// empty is also true because we have to keep the defaults
				if(empty($role_settings[$role][$what]) or $role_settings[$role][$what] == 'all') {
					return 'all';
				}
				elseif($role_settings[$role][$what] == 'own') $has_access = 'own';
				// when none of the above, we just leave $has_access as false			
			}
			elseif(in_array($role, $enabled_roles)) $has_access = 'all'; // role was not specified in fine-tune so we just use the default full access
		}
		
		// if we are here, it means none of his roles had 'all'
		if($has_access) return $has_access;
		
		// when no access, die
		if($noexit) return false;
		else wp_die(__('You are not allowed to do this.', 'hostelpro'));
	}
}