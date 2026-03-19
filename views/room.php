<h1><?php _e('Create/Edit Hostel Room', 'hostelpro')?></h1>

<p><a href="admin.php?page=hostelpro_rooms"><?php _e('Back to rooms', 'hostelpro')?></a></p>

<div class="wrap hostelpro-box postbox">
	<form class='hostelpro-form' onsubmit="return validateHostelPROForm(this);" method="post" id="roomForm">
		<p><label><?php _e('Room title*', 'hostelpro')?></label> <input type="text" name="title" value="<?php echo stripslashes(@$room->title)?>" size="40">
		<div class="hostelpro-help"><?php _e('For management purposes', 'hostelpro')?></div></p>	
		<p><label><?php _e('Room type', 'hostelpro')?></label> <select name="rtype" onchange="hostelPROChangeRoomType(this.value);">
			<option value="private" <?php if(!empty($room->rtype) and $room->rtype=='private') echo 'selected'?>><?php _e('Private', 'hostelpro')?></option>		
			<option value="dorm" <?php if(!empty($room->rtype) and $room->rtype=='dorm') echo 'selected'?>><?php _e('Dorm', 'hostelpro')?></option>
		</select>
		
		<select name="dorm_gender" style="visibility:<?php echo (!empty($room->id) and $room->rtype == 'dorm') ? 'visible' : 'hidden';?>" id="dormGender">
			<option value="mixed" <?php if(!empty($room->id) and $room->dorm_gender == 'mixed') echo 'selected'?>><?php _e('Mixed', 'hostelpro')?></option>
			<option value="male" <?php if(!empty($room->id) and $room->dorm_gender == 'male') echo 'selected'?>><?php _e('Male', 'hostelpro')?></option>
			<option value="female" <?php if(!empty($room->id) and $room->dorm_gender == 'female') echo 'selected'?>><?php _e('Female', 'hostelpro')?></option>
		</select>		
		</p>
		<p><label><?php _e('Number of beds', 'hostelpro')?></label> <input type="text" name="beds" size="4" value="<?php echo @$room->beds?>" id="roomBeds" onkeyup="discountPartialOccupancy(this.form.discount_part_occupancy.checked);">
		&nbsp; <?php _e('Extra beds available:', 'hostelpro')?> <input type="text" name="extra_beds" size="4" value="<?php echo @$room->extra_beds?>">  <?php _e('at price per bed:', 'hostelpro')?> <?php echo HOSTELPRO_CURRENCY?>  <input type="text" name="extra_bed_price" size="6" value="<?php echo @$room->extra_bed_price?>"></p>
		<p><label><?php _e('Allow overbooking up to', 'hostelpro')?>&nbsp;</label> <input type="text" name="overbook_beds" value="<?php echo @$room->overbook_beds?>" size="6"> <?php _e('beds', 'hostelpro')?> <a href="http://blog.calendarscripts.info/rooms-with-overbooking-in-hostel-pro/" target="_blank"><?php _e("(What's this?)", 'hostelpro')?></a></p>
		<p><label><?php _e('Bathroom', 'hostelpro')?></label> <select name="bathroom">
			<option value="ensuite" <?php if(!empty($room->bathroom) and $room->bathroom=='ensuite') echo 'selected'?>><?php _e('Ensuite', 'hostelpro')?></option>		
			<option value="shared" <?php if(!empty($room->bathroom) and $room->bathroom=='shared') echo 'selected'?>><?php _e('Shared', 'hostelpro')?></option>
		</select></p>
		<p><label><?php _e('Price:', 'hostelpro')?></label> <?php echo HOSTELPRO_CURRENCY?> <input type="text" name="price" size="6" value="<?php echo @$room->price;?>"> &nbsp;
			<select name="price_type" onchange="hostelPROChangeRoomType(this.form.rtype.value);">
				<option  value="per-bed" <?php if(empty($room->price_type) or $room->price_type == 'per-bed') echo 'selected'?>><?php _e('Per person per night', 'hostelpro')?></option>
				<option  value="per-room" <?php if(!empty($room->price_type) and $room->price_type == 'per-room') echo 'selected'?>> <?php _e('Per night for the whole room', 'hostelpro')?></option>
			</select>			
			
		</p>
		
		<div id="partialOccupancy" style="display:<?php echo (empty($room) or $room->rtype == 'private') ? 'block':'none';?>">
			<p><input type="checkbox" name="discount_part_occupancy" value="1" <?php if(!empty($room->discount_part_occupancy)) echo 'checked'?> onclick="discountPartialOccupancy(this.checked);"> <?php _e('Discount partial occupancy', 'hostelpro')?></p>
			<p id="partialOccupancyInfo"><?php _e('By default private rooms require booking all beds regardless how many persons will really stay in the room. By checking the above checkbox you can allow partial booking at discounted price.', 'hostelpro')?></p>
			<div id="partialOccupancyPrices"><?php if(!empty($part_occupancy_prices)):
				printf(__('Price in %s when booked by:', 'hostelpro'), HOSTELPRO_CURRENCY); 
				foreach($part_occupancy_prices as $cnt=>$price):
					echo ' ';
					printf(__('%d guests:', 'hostelpro'), $cnt+1);?>
					&nbsp; <input type="text" name="part_occupancy_prices[]" size="4" value="<?php echo $price?>">
				<?php endforeach;
			 endif;?></div>
		</div>
		
		<div id="childOptions" style="display:<?php echo ( $room->price_type == 'per-bed') ? 'block' : 'none';?>">
					<input type="checkbox" name="allow_child_bed_price" value="1" <?php if(!empty($room->allow_child_bed_price)) echo 'checked'?> onclick="this.checked ? jQuery('#childBedsPrice').show() : jQuery('#childBedsPrice').hide();"> <?php _e('Set different price for child beds.', 'hostelpro');?>
			<div id="childBedsPrice" style="display:<?php echo empty($room->allow_child_bed_price) ? 'none' : 'block';?>">
				<?php printf(__('Price in %s per child bed:', 'hostelpro'), HOSTELPRO_CURRENCY);?> <input type="text" name="child_bed_price" size="4" value="<?php echo $room->child_bed_price?>">
				<?php _e('Label of the field (ex. "Number of children under 12:")', 'hostelpro');?> <input type="text" name="child_bed_label" value="<?php echo empty($room->id) ? '' : stripslashes($room->child_bed_label);?>">
				<p><?php _e('The number of children is included in the number of total beds booked.', 'hostelpro');?></p>
				<p><?php printf(__('Accept maximum of %s children in the room and require at least %s adult with them.', 'hostelpro'), 
					'<input type="text" name="max_children" size="4" value="'.@$room->max_children.'">', '<input type="text" name="adults_with_children" size="4" value="'.@$room->adults_with_children.'">');?></p>
			</div>
		</div>
		
		<div id="dormOptions" style="display:<?php echo (!empty($room->rtype) and $room->rtype == 'dorm' and $room->price_type == 'per-bed') ? 'block' : 'none';?>">			
		  <p><?php printf(__('Allow booking the whole room at a discounted price of %1$s%2$s. Leave blank or enter 0 to not allow such option.', 'hostelpro'), HOSTELPRO_CURRENCY, 
		  	'<input type="text" name="whole_dorm_price" value="'.@$room->whole_dorm_price.'" size="5">');?></p>
		</div>
		
		<p><label><?php _e('Short notes / resume:', 'hostelpro')?></label> <textarea rows="4" cols="60" name="notes"><?php echo stripslashes(@$room->notes)?></textarea><br>
		<?php _e('The short notes will appear in the rooms table when you use the [hostelpro-list] shortcode. You can use them to add extra information for the room, for example about extra beds available etc.', 'hostelpro')?><br>
		<?php printf(__('It can also be displayed on the booking form using the shortcode %s.', 'hostelpro'), '[hostelpro-field-static notes]')?></p>
		
		<p><label><?php _e('Room description (optional):', 'hostelpro')?></label> <br>
		<?php if(!empty($room->id)): printf(__('You can use the shortcode %s to show the room description anywhere on the site.', 'hostelpro'),
			'<input type="text" size="30" value="[hostelpro-room-description room_id='.$room->id.']" onclick="this.select();" readonly="readonly">');
		endif;?>
		 <?php wp_editor(stripslashes(@$room->description), 'description')?></p>
		
		<p><label><?php _e('Import external calendar (optional):', 'hostelpro')?></label> <textarea name="ical_import" rows="5" cols="50"><?php echo empty($room->ical_import) ? '' : $room->ical_import;?></textarea> <?php _e('URLs of iCal / ics files, one per line', 'hostelpro');?></p>
		
		<?php do_action ('hostelpro_room_form', @$room);?>		
		
		<p><input type="submit" value="<?php _e('Save room details', 'hostelpro')?>"></p>
		<input type="hidden" name="ok" value="1">
	</form>
</div>

<script type="text/javascript" >
function validateHostelPROForm(frm) {
	if(frm.title.value=="") {
		alert("<?php _e('Please enter room title. This is important so you can recognize the room when editing it and when viewing its bookings', 'hostelpro')?>");
		frm.title.focus();
		return false;
	}

	if(frm.beds.value=="" || isNaN(frm.beds.value)) {
		alert("<?php _e('Please enter number of beds in the room. Use only numbers.', 'hostelpro')?>");
		frm.beds.focus();
		return false;
	}	
	
	if(frm.price.value=="" || isNaN(frm.price.value)) {
		alert("<?php _e('Please enter room price, numbers only', 'hostelpro')?>");
		frm.price.focus();
		return false;
	}	
	
	return true;
}

function hostelPROChangeRoomType(val) {
	if(val == 'dorm') {
		jQuery('#dormGender').show();
		jQuery('#partialOccupancy').hide();
	}
	else {
		jQuery('#dormGender').hide();
		jQuery('#partialOccupancy').show();
	}
	
	priceType = jQuery('#roomForm select[name=price_type]').val();
	if(val == 'dorm' && priceType == 'per-bed') {
		jQuery('#dormOptions').show();		
	}
	else jQuery('#dormOptions').hide();
	
	if(priceType == 'per-bed') jQuery('#childOptions').show();
	else jQuery('#childOptions').hide();
}

// when partial occupancy is discount, show extra info and boxes to enter prices
function discountPartialOccupancy(status) {
	 if(!status) {
	 	jQuery('#partialOccupancyInfo').hide();
	 	jQuery('#partialOccupancyPrices').html('');
	 	return false;
	 }
	 
	 jQuery('#partialOccupancyInfo').show();
	 
	 // now prepare boxes based on the number of beds
	 var beds = jQuery('#roomBeds').val();
	 if(beds <= 1) {
	 	jQuery('#partialOccupancyPrices').html("<?php _e('Partial occupancy is possible for rooms with at least 2 beds.', 'hostelpro')?>");
	 	return false;
	 }
	 
	 var partialHTML = "<?php printf(__('Price in %s when booked by:', 'hostelpro'), HOSTELPRO_CURRENCY)?> ";
	 for(i=1; i < beds; i++) {
	 	partialHTML += ' ' + i + ' ' + "<?php _e('guests:', 'hostelpro')?>" + ' ';
	 	partialHTML += '<input type="text" name="part_occupancy_prices[]" size="4"> ';
	 }
	 
	 jQuery('#partialOccupancyPrices').html(partialHTML);
}
</script>