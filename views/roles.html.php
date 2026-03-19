<div class="wrap">
	<h1><?php _e('Manage role access in Hostel Pro', 'hostelpro')?></h1>
	
	<?php if(empty($enabled_roles)):?>
		<p><?php printf(__('To edit this page you need to enable some roles to manage the plugin on the <a href="%s" target=_blank">Hostel PRO Settings page</a>.', 'hostelpro'), 'admin.php?page=hostelpro_options')?></p>
		</div>
	<?php return false;
	endif;?>
	
	<form method="post">
		<div class="bftpro">
		<p><?php _e('Please select role to configure:', 'hostelpro')?> <select name="role_key" onchange="this.form.submit();">
			<option value=""><?php _e('- Please select role -', 'hostelpro')?></option>
			<?php foreach($enabled_roles as $role):?>
				<option value="<?php echo $role?>" <?php if(!empty($_POST['role_key']) and $_POST['role_key'] == $role) echo 'selected'?>><?php echo $role?></option>
			<?php endforeach;?>
		</select></p>
		
		<?php if(!empty($_POST['role_key'])):
			$settings = @$role_settings[$_POST['role_key']];?>
			<p><label><?php _e('Manage Settings page:', 'hostelpro')?></label> <select name="settings_access">
				<option value="all" <?php if(!empty($settings['settings_access']) and $settings['settings_access'] == 'all') echo "selected"?>><?php _e('Manage settings','hostelpro')?></option>				
				<option value="no" <?php if(!empty($settings['settings_access']) and $settings['settings_access'] == 'no') echo "selected"?>><?php _e('No access to manage settings','hostelpro')?></option>
			</select> </p>		
			
				<p><label><?php _e('Manage Rooms:', 'hostelpro')?></label> <select name="rooms_access">
				<option value="all" <?php if(!empty($settings['rooms_access']) and $settings['rooms_access'] == 'all') echo "selected"?>><?php _e('Manage rooms','hostelpro')?></option>			
				<option value="own" <?php if(!empty($settings['rooms_access']) and $settings['rooms_access'] == 'own') echo "selected"?>><?php _e('Manage only rooms created by the user','hostelpro')?></option>	
				<option value="no" <?php if(!empty($settings['rooms_access']) and $settings['rooms_access'] == 'no') echo "selected"?>><?php _e('No access to manage rooms','hostelpro')?></option>
			</select> </p>			
			
			<p><label><?php _e('Manage Addon Services:', 'hostelpro')?></label> <select name="addons_access">
				<option value="all" <?php if(!empty($settings['addons_access']) and $settings['addons_access'] == 'all') echo "selected"?>><?php _e('Manage addons','hostelpro')?></option>			
				<option value="own" <?php if(!empty($settings['addons_access']) and $settings['addons_access'] == 'own') echo "selected"?>><?php _e('Manage only addons created by the user','hostelpro')?></option>	
				<option value="no" <?php if(!empty($settings['addons_access']) and $settings['addons_access'] == 'no') echo "selected"?>><?php _e('No access to manage addons','hostelpro')?></option>
			</select> </p>			
			
			<p><label><?php _e('Manage Bookings:', 'hostelpro')?></label> <select name="bookings_access">
				<option value="all" <?php if(!empty($settings['bookings_access']) and $settings['bookings_access'] == 'all') echo "selected"?>><?php _e('Manage all bookings','hostelpro')?></option>			
				<option value="own" <?php if(!empty($settings['bookings_access']) and $settings['bookings_access'] == 'own') echo "selected"?>><?php _e('Manage only bookings manually created by the user','hostelpro')?></option>	
				<option value="no" <?php if(!empty($settings['bookings_access']) and $settings['bookings_access'] == 'no') echo "selected"?>><?php _e('No access to manage bookings','hostelpro')?></option>
			</select> </p>		
			
			<p><label><?php _e('Calendar Overview page:', 'hostelpro')?></label> <select name="overview_access">
				<option value="all" <?php if(!empty($settings['overview_access']) and $settings['overview_access'] == 'all') echo "selected"?>><?php _e('Access calendar overview','hostelpro')?></option>				
				<option value="no" <?php if(!empty($settings['overview_access']) and $settings['overview_access'] == 'no') echo "selected"?>><?php _e('No access to calendar overview','hostelpro')?></option>
			</select> </p>		
			
			<p><label><?php _e('Manage Booking Form:', 'hostelpro')?></label> <select name="form_access">
				<option value="all" <?php if(!empty($settings['form_access']) and $settings['form_access'] == 'all') echo "selected"?>><?php _e('Manage booking form','hostelpro')?></option>				
				<option value="no" <?php if(!empty($settings['form_access']) and $settings['form_access'] == 'no') echo "selected"?>><?php _e('No access to manage booking form','hostelpro')?></option>
			</select> </p>		
			
			<p><label><?php _e('Unavailable Dates:', 'hostelpro')?></label> <select name="unavailable_access">
				<option value="all" <?php if(!empty($settings['unavailable_access']) and $settings['unavailable_access'] == 'all') echo "selected"?>><?php _e('Manage unavailable dates','hostelpro')?></option>				
				<option value="no" <?php if(!empty($settings['unavailable_access']) and $settings['unavailable_access'] == 'no') echo "selected"?>><?php _e('No access to manage unavailable dates','hostelpro')?></option>
			</select> </p>		
			
				<p><label><?php _e('Manage Discounts & Surcharges:', 'hostelpro')?></label> <select name="discounts_access">
				<option value="all" <?php if(!empty($settings['discounts_access']) and $settings['discounts_access'] == 'all') echo "selected"?>><?php _e('Manage discounts/surcharges','hostelpro')?></option>			
				<option value="own" <?php if(!empty($settings['discounts_access']) and $settings['discounts_access'] == 'own') echo "selected"?>><?php _e('Manage only discounts/surcharges created by the user','hostelpro')?></option>	
				<option value="no" <?php if(!empty($settings['discounts_access']) and $settings['discounts_access'] == 'no') echo "selected"?>><?php _e('No access to manage discounts/surcharges','hostelpro')?></option>
			</select> </p>	
			
			<p><label><?php _e('Advanced Reports page:', 'hostelpro')?></label> <select name="reports_access">
				<option value="all" <?php if(!empty($settings['reports_access']) and $settings['reports_access'] == 'all') echo "selected"?>><?php _e('Access advanced reports','hostelpro')?></option>				
				<option value="no" <?php if(!empty($settings['reports_access']) and $settings['reports_access'] == 'no') echo "selected"?>><?php _e('No access to advanced reports','hostelpro')?></option>
			</select> </p>		
			
				<p><label><?php _e('Advanced Email log:', 'hostelpro')?></label> <select name="emaillog_access">
				<option value="all" <?php if(!empty($settings['emaillog_access']) and $settings['emaillog_access'] == 'all') echo "selected"?>><?php _e('Access email log','hostelpro')?></option>				
				<option value="no" <?php if(!empty($settings['emaillog_access']) and $settings['emaillog_access'] == 'no') echo "selected"?>><?php _e('No access to email log','hostelpro')?></option>
			</select> </p>		
		
			<?php do_action('hostelpro-role-settings', $settings);?>
			
			<p><input type="submit" value="<?php _e('Save configuration for this role','hostelpro')?>" name="config_role"></p>
		<?php endif;?>
		</div>
	</form>
</div>