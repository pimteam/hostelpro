<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HostelPROAddons {
	static function manage() {
		global $wpdb, $user_ID;
		$_addon = new HostelPROAddon();
		
		$do = empty($_GET['do']) ? 'list' : $_GET['do'];
		
		$multiuser_access = 'all';
		$multiuser_access = HostelPRORoles::check_access('addons_access');
		
		// select rooms
		$rooms = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_ROOMS." ORDER BY id");
		
		switch($do) {
			case 'add':
				if(!empty($_POST)) {
					$_addon->add($_POST);
					hostelpro_redirect("admin.php?page=hostelpro_addons");
				}
				
				if(@file_exists(get_stylesheet_directory().'/hostelpro/addon.html.php')) include get_stylesheet_directory().'/hostelpro/addon.html.php';
				else include(HOSTELPRO_PATH."/views/addon.html.php");
			break;
			
			case 'edit':
				$_GET['id'] = intval($_GET['id']);
				if($multiuser_access == 'own') {
					$addon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ADDONS." WHERE id=%d", $_GET['id'])); 
					if(@$addon->editor_id != $user_ID) wp_die(__('You can manage only your own addon services.', 'hostelpro'));
				}	
				
				if(!empty($_POST)) {
					$_addon->edit($_POST, $_GET['id']);
					hostelpro_redirect("admin.php?page=hostelpro_addons");
				}
				
				$addon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ADDONS." WHERE id=%d", $_GET['id'])); 
				if(@file_exists(get_stylesheet_directory().'/hostelpro/addon.html.php')) include get_stylesheet_directory().'/hostelpro/addon.html.php';
				else include(HOSTELPRO_PATH."/views/addon.html.php");
			break;
				
			case 'list':
				if(!empty($_GET['del'])) {
					$_GET['id'] = intval($_GET['id']);
					if($multiuser_access == 'own') {
						$addon = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_ADDONS." WHERE id=%d", $_GET['id'])); 
						if(@$addon->editor_id != $user_ID) wp_die(__('You can manage only your own addon services.', 'hostelpro'));
					}	
					
					$wpdb->query($wpdb->prepare("DELETE FROM ".HOSTELPRO_ADDONS." WHERE id=%d", $_GET['id']));
					hostelpro_redirect("admin.php?page=hostelpro_addons");
				}			
			
				$owner_sql = '';
				if($multiuser_access == 'own') {
					$owner_sql = $wpdb->prepare(" AND tA.editor_id = %d ", $user_ID);
				}	
				
				$addons = $wpdb->get_results("SELECT tA.*, tR.title as room_title 
					FROM ".HOSTELPRO_ADDONS." tA LEFT JOIN ".HOSTELPRO_ROOMS." tR ON tR.id = tA.room_id 
					WHERE 1 $owner_sql	
					ORDER BY tA.id");
				if(@file_exists(get_stylesheet_directory().'/hostelpro/addons.html.php')) include get_stylesheet_directory().'/hostelpro/addons.html.php';
				else include(HOSTELPRO_PATH."/views/addons.html.php");
			break;
		}
 	} // end manage
 	
 	// apply services to the booking cost
 	// for now the services data comes from $_POST
 	// returns textual breakdown for the emails etc
 	// populate also $_POST variable addon_details
 	static function apply($numdays, &$cost, &$addons_cost = 0) {
 		global $wpdb;
 		
 		// select existing addons
 		$addons = $wpdb->get_results("SELECT * FROM ".HOSTELPRO_ADDONS." WHERE is_inactive=0 ORDER BY id");
 		
 		$addons_breakdown = '';
 		$_POST['addon_details'] = array();
 		
 		foreach($addons as $addon) {
			if(HOSTELPRO_NO_DECIMALS) $addon->price = hostelpro_number_format($addon->price); 			
 			
 			if(!empty($_POST['addon-'.$addon->id])) {
 				$addon_cost = $addon->price;
 				$addons_breakdown .= stripslashes($addon->name)." (".$_POST['addon-'.$addon->id]."): ".HOSTELPRO_CURRENCY.$addon->price;
 				if($addon->per_person) {
 					$addons_breakdown .= ' '.__('per person', 'hostelpro').' ';
 					$addon_cost = $addon_cost * $_POST['beds'];
 				}
 				if($addon->per_day) { 					
 					$addons_breakdown .= ' '.__('per day', 'hostelpro').' ';
 					$addon_cost = $addon_cost * $numdays * $_POST['addon-'.$addon->id]; 					
 				} 		
 				
 				$cost += $addon_cost;
 				$addons_cost += $addon_cost;
 				$addons_breakdown .= '<br>';	
 				
 				$_POST['addon_details'][$addon->id] = $_POST['addon-'.$addon->id];
 			}
 		}
 		
 		return $addons_breakdown;
	} // end apply()
}