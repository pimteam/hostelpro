<div class="wrap">
	<h1><?php _e('Add/Edit Reservation', 'hostelpro')?></h1>
	
	<div class="wrap hostelpro-box postbox">
		<form class='hostelpro-form' method="post">
			<p><label><?php _e('Select room:', 'hostelpro')?></label> <select name="room_id">
				<?php foreach($rooms as $room):?>
					<option value="<?php echo $room->id?>" <?php if(!empty($booking->room_id) and $booking->room_id == $room->id) echo 'selected'?>><?php echo stripslashes($room->title);?></option>
				<?php endforeach;?>
			</select></p>
			<p><label><?php _e('No. beds to book:', 'hostelpro')?></label> <input type="text" name="beds" value="<?php echo empty($booking->beds) ? 1 : $booking->beds?>" size="4"></p>
			<p><label><?php _e('Extra beds:', 'hostelpro')?></label> <input type="text" name="extra_beds" value="<?php echo empty($booking->extra_beds) ? 0 : $booking->extra_beds?>" size="4"></p>
			<p><label><?php _e('Child beds:', 'hostelpro')?></label> <input type="text" name="child_beds" value="<?php echo empty($booking->child_beds) ? 0 : $booking->child_beds?>" size="4"></p>
			<p><label><?php _e('From date:', 'hostelpro')?></label> <?php echo HostelPROQUickDDDate('from', @$booking->from_date, NULL, NULL, date("Y")-10, date("Y") + 10);?></p>
			<p><label><?php _e('To date:', 'hostelpro')?></label> <?php echo HostelPROQuickDDDate('to', @$booking->to_date, NULL, NULL, date("Y")-10, date("Y") + 10);?></p>
			<p><label><?php _e('Amount paid:', 'hostelpro')?></label> <?php echo HOSTELPRO_CURRENCY?> <input type="text" name="amount_paid" value="<?php echo empty($booking->amount_paid) ? '' : $booking->amount_paid?>" size="6"></p>
			<p><label><?php _e('Amount due:', 'hostelpro')?></label> <?php echo HOSTELPRO_CURRENCY?> <input type="text" name="amount_due" value="<?php echo empty($booking->amount_due) ? '' : $booking->amount_due?>" size="6"></p>
			<p><label><?php _e('Discount:', 'hostelpro')?></label> <?php echo HOSTELPRO_CURRENCY?> <input type="text" name="discount" value="<?php echo empty($booking->discount) ? '' : $booking->discount?>" size="6"></p>
			<p><label><?php _e('Contact name:', 'hostelpro')?></label> <input type="text" name="contact_name" value="<?php echo empty($booking->contact_name) ? '' : $booking->contact_name?>"></p>
			<p><label><?php _e('Contact email:', 'hostelpro')?></label> <input type="text" name="contact_email" value="<?php echo empty($booking->contact_email) ? '' : $booking->contact_email?>"></p>
			<p><label><?php _e('Contact phone:', 'hostelpro')?></label> <input type="text" name="contact_phone" value="<?php echo empty($booking->contact_phone) ? '' : $booking->contact_phone?>"></p>
			<p><label><?php _e('Visitors type:', 'hostelpro')?></label> <select name="contact_type">
			<option value="male" <?php if(!empty($booking->id) and $booking->contact_type=='male') echo "selected"?>><?php _e('Male(s)', 'hostelpro')?></option>
			<option value="female" <?php if(!empty($booking->id) and $booking->contact_type=='female') echo "selected"?>><?php _e('Female(s)', 'hostelpro')?></option>
			<option value="couple" <?php if(!empty($booking->id) and $booking->contact_type=='couple') echo "selected"?>><?php _e('Couple', 'hostelpro')?></option>
			<option value="mixed" <?php if(!empty($booking->id) and $booking->contact_type=='mixed') echo "selected"?>><?php _e('Mixed', 'hostelpro')?></option>
			</select></p>
			
			<?php if(sizeof($addons)):
				foreach($addons as $addon):
					$show_label = true;
					$priceinfo = '';
					if($addon->per_person) $priceinfo .= ' '.__('per person', 'hostelpro').' ';
					if($addon->per_day) $priceinfo .= ' '.__('per day', 'hostelpro').' ';
					include(HOSTELPRO_PATH."/views/addon-display.html.php");
				endforeach; 
			endif;?>			
			
			<?php if(!empty($booking->id)):?>
				<p><label><?php _e('Booking status:', 'hostelpro')?></label> <select name="status">
				<option value="active" <?php if($booking->status == 'active') echo 'selected'?>><?php _e('Active', 'hostelpro')?></option>
				<option value="pending" <?php if($booking->status == 'pending') echo 'selected'?>><?php _e('Pending (not confirmed)', 'hostelpro')?></option>
				<option value="cancelled" <?php if($booking->status == 'cancelled') echo 'selected'?>><?php _e('Cancelled', 'hostelpro')?></option>
				</select></p>
			<?php endif;?>
			
			<?php $autop = true; 
			require(HOSTELPRO_PATH."/views/form-field-display.html.php"); ?>
			
			<?php do_action ('hostelpro_booking_form', @$booking);?>
			
			<?php if(empty($_GET['id']) and $email_options['do_email_user']):?>
				<p><input type="checkbox" name="send_email_notice" value="1"> <?php printf(__('Send email notice to the guest about this booking. Email notices are configurable in the <a href="%s" target="_blank">Settings page</a>.', 'hostelpro'), 'admin.php?page=hostelpro_options');?></p>
			<?php endif;?>
			
			<p align="center">
				<input type="submit" value="<?php _e('Save Reservation', 'hostelpro')?>">			
				<?php if(!empty($booking->id)):?>
					<input type="button" value="<?php _e('Delete booking', 'hostelpro');?>" onclick="hostelPROConfirmDelete(this.form);">
				<?php endif;?>
				<input type="button" value="<?php _e('Go Back', 'hostelpro')?>" onclick="window.location='admin.php?page=hostelpro_bookings&type=<?php echo $_GET['type']?>&offset=<?php echo $_GET['offset']?>';">
			</p>
			<input type="hidden" name="ok" value="1">
			<input type="hidden" name="del" value="0">
		</form>
	</div>
</div>

<script type="text/javascript" >
function hostelPROConfirmDelete(frm) {
	if(confirm("<?php _e('Are you sure?','hostelpro');?>")) {
			frm.del.value=1;
			frm.submit();
	}
}

jQuery(function(){
	jQuery('.hostelproDatePicker').datepicker({
        dateFormat : '<?php echo dateformat_PHP_to_jQueryUI(get_option('date_format'));?>',        
        altFormat : "yy-mm-dd",           
    });
    
    jQuery(".hostelproDatePicker").each(function (idx, el) { 
	    jQuery(this).datepicker("option", "altField", "#alt_" + jQuery(this).attr("id"));
	});
});	
</script>