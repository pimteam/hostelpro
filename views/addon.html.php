<div class="wrap">
	<h1><?php _e('Add/Edit Addon Service', 'hostelpro')?></h1>
	
	<p><a href="admin.php?page=hostelpro_addons"><?php _e('Back to manage addon services', 'hostelpro')?></a></p>
	
	<div class="wrap hostelpro-box postbox">
		<form class='hostelpro-form' method="post" onsubmit="return HostelPROValidateAddon(this);">
			<p><label><?php _e('Service name:', 'hostelpro')?></label> <input type="text" name="name" value="<?php echo stripslashes(@$addon->name)?>"></p>
			<p><label><?php _e('Room:', 'hostelpro')?></label> <select name="room_id">
				<option value="0"><?php _e('All rooms', 'hostelpro')?></option>
				<?php foreach($rooms as $room):
					$selected = (!empty($addon->room_id) and $addon->room_id == $room->id) ? ' selected' : '';?>
					<option value="<?php echo $room->id?>"<?php echo $selected?>><?php echo stripslashes($room->title);?></option>
				<?php endforeach;?>
			</select></p>
			<p><label><?php _e('price:', 'hostelpro')?></label> <input type="text" name="price" size="6" value="<?php echo HOSTELPRO_NO_DECIMALS ? hostelpro_number_format(@$addon->price) : @$addon->price?>"> <?php echo HOSTELPRO_CURRENCY?>
				&nbsp;
				<input type="checkbox" name="per_person" value="1" <?php if(!empty($addon->per_person)) echo 'checked'?>> <?php _e('Per person', 'hostelpro')?>  			
				<input type="checkbox" name="per_day" value="1" <?php if(!empty($addon->per_day)) echo 'checked'?>> <?php _e('Per day', 'hostelpro')?>
			</p>
			<p><label><?php _e('Maximum availability', 'hostelpro')?></label>  <input type="text" name="max_available" size="6" value="<?php echo @$addon->max_available?>"> <?php _e('(Leave blank or enter 0 for no limit)', 'hostelpro')?></p>
			
			<p><?php _e('If you select "per person" the price will be automatically multiplied by the number of persons who book. In this case the user will have the choice to select or deselect the addon, but not to change the number.', 'hostelpro')?><br>
			<?php _e('If you select "per day" the price will automatically be multiplied by the number of booked days.', 'hostelpro')?></p>
			
			<p><label><?php _e('Optional description', 'hostelpro')?></label> 
			<textarea name="description" rows="4" cols="50"><?php echo stripslashes(@$addon->description)?></textarea></p>
			
			<p><input type="checkbox" name="is_inactive" value="1" <?php if(!empty($addon->is_inactive)) echo 'checked'?>> <?php _e('This addon service is currently inactive and will not be offered.', 'hostelpro')?></p>
			
			<?php do_action ('hostelpro_addon_form', @$addon);?>
			
			<p><input type="submit" name="ok" value="<?php _e('Save Addon Service', 'hostelpro')?>"></p>
			<input type="hidden" name="ok" value="1">
		</form>
	</div>	
</div>

<script type="text/javascript" >
function HostelPROValidateAddon(frm) {
	if(frm.name.value == '') {
		alert("<?php _e('Please enter service name', 'hostelpro')?>");
		frm.name.focus();
		return false;
	}
	
	if(frm.price.value == '' || isNaN(frm.price.value) || frm.price.value <= 0) {
		alert("<?php _e('Please enter service price', 'hostelpro')?>");
		frm.price.focus();
		return false;
	}
	
	return true;
}
</script>