<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HostelPROAddon {
	function add($vars) {
		global $wpdb, $user_ID;
		
		$wpdb->query($wpdb->prepare("INSERT INTO ".HOSTELPRO_ADDONS." SET
			name=%s, price=%f, per_person=%d, per_day=%d, max_available=%d, is_inactive=%d, 
			description=%s, room_id=%d, editor_id=%d",
		$vars['name'], $vars['price'], @$vars['per_person'], @$vars['per_day'], $vars['max_available'],
			@$vars['is_inactive'], $vars['description'], $vars['room_id'], $user_ID));
		$id = $wpdb->insert_id;
		
		do_action('hostelpro_addon_added', $id);
		return $id;
	} // end add()
	
	function edit($vars, $id) {
		global $wpdb;
		
		$wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_ADDONS." SET
			name=%s, price=%f, per_person=%d, per_day=%d, max_available=%d, is_inactive=%d, description=%s, room_id=%d
			WHERE id=%d",
		$vars['name'], $vars['price'], @$vars['per_person'], @$vars['per_day'], $vars['max_available'],
		@$vars['is_inactive'], $vars['description'], $vars['room_id'], $id));
		
		do_action('hostelpro_addon_edited', $id, $vars);
		return true;
	}
}