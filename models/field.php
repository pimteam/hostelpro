<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HostelPROField {
	function add($vars) {		
		global $wpdb;

		$this->cleanup($vars);		
				
		$wpdb->query($wpdb->prepare("INSERT INTO ".HOSTELPRO_FIELDS." SET 
			name=%s, ftype=%s, fvalues=%s, is_required=%d, label=%s", 
			$vars['name'], $vars['ftype'], $vars['fvalues'], @$vars['is_required'], $vars['label']));
		$id = $wpdb->insert_id;
		
		do_action('hostelpro_field_added', $id);
		return $id;	 
	}
	
	function save($vars, $id) {
		global $wpdb;
		
		$this->cleanup($vars);
						
		$wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_FIELDS." SET
		name=%s, ftype=%s, fvalues=%s, is_required=%d, label=%s WHERE id=%d", 
			$vars['name'], $vars['ftype'], $vars['fvalues'], @$vars['is_required'], $vars['label'], $id));
			
		do_action('hostelpro_field_edited', $id, $vars);	
			
		return false;	
	}
	
	function delete($id) {
		global $wpdb;
		
		$wpdb->query($wpdb->prepare("DELETE FROM ".HOSTELPRO_FIELDS." WHERE id=%d", $id));
		$wpdb->query($wpdb->prepare("DELETE FROM ".HOSTELPRO_DATAS." WHERE field_id=%d", $id));
		
		do_action('hostelpro_field_deleted', $id);
		
		return true;
	}
	
	// make sure $name is OK and maybe other things
	private function cleanup(&$vars) {
		$vars['name']=strtolower($vars['name']);
		$vars['name']=preg_replace("/[^a-z0-9]/","",$vars['name']);		
		
		// for file upload fields
		if($vars['ftype'] == 'file') {
			$vars['fvalues'] = $vars['filesize'].'|'.$vars['filetypes'];
		}
	}
}