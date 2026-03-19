<style type="text/css">
<?php hostelpro_resp_table_css(1000);?>
</style>

<div class="wrap">
	<h1><?php _e('Manage Bookings / Reservations', 'hostelpro')?></h1>
	
	<form method="get" action="admin.php">
		<input type="hidden" name="page" value="hostelpro_bookings">
		<p><?php _e('Showing', 'hostelpro')?> <select name="type" onchange="this.form.submit();">
			<option value="upcoming" <?php if($type == 'upcoming') echo 'selected'?>><?php _e('Upcoming', 'hostelpro')?></option>
			<option value="past" <?php if($type == 'past') echo 'selected'?>><?php _e('Past', 'hostelpro')?></option>		
		</select> <?php _e('bookings', 'hostelpro')?></p>		
	</form>	
	
	<p><a href="#" onclick="jQuery('#hostelPROSearch').toggle();"><?php _e('Search bookings', 'hostelpro')?></a>
	| <a href="admin.php?page=hostelpro_invoice_template" target="_blank"><?php _e('Edit the invoice template', 'hostelpro')?></a></p>
	<form method="get" action="admin.php">
		<div class="hostelpro-form" style="display:<?php echo empty($filters_apply) ? 'none' : 'block'?>;" id="hostelPROSearch">
			<input type="hidden" name="page" value="hostelpro_bookings">
			<input type="hidden" name="type" value="<?php echo $type?>">
			<p><label><?php _e('Filter by booking ID:', 'hostelpro')?></label> <input type="text" name="booking_id" value="<?php echo @$_GET['booking_id']?>"></p>
			<p><label><?php _e('Filter by email:', 'hostelpro')?></label> <input type="text" name="contact_email" value="<?php echo @$_GET['contact_email']?>"></p>
			<p><label><?php _e('Filter by name:', 'hostelpro')?></label> <input type="text" name="contact_name" value="<?php echo @$_GET['contact_name']?>"></p>
			<p><label><?php _e('Filter by room:', 'hostelpro')?></label> <select name="room_id">
				<option value="0"><?php _e('Any room', 'hostelpro')?></option>
				<?php foreach($rooms as $room):?>
					<option value="<?php echo $room->id?>"<?php if(!empty($_GET['room_id']) and $room->id == $_GET['room_id']) echo 'selected'?>><?php echo $room->title?></option>
				<?php endforeach;?>
			</select></p>
			<p><label><?php _e('Filter by status:', 'hostelpro')?></label> <select name="status">
				<option value=""><?php _e('Any status', 'hostelpro')?></option>
				<option value="active" <?php if(!empty($_GET['status']) and $_GET['status'] == 'active') echo 'selected'?>><?php _e('Active', 'hostelpro')?></option>
				<option value="pending" <?php if(!empty($_GET['status']) and $_GET['status'] == 'pending') echo 'selected'?>><?php _e('Pending', 'hostelpro')?></option>
				<option value="cancelled" <?php if(!empty($_GET['status']) and $_GET['status'] == 'cancelled') echo 'selected'?>><?php _e('Cancelled', 'hostelpro')?></option>
			</select></p>
			<p><input type="submit" value="<?php _e('Search/filter bookings', 'hostelpro')?>">
			<?php if(!empty($filters_apply)):?><input type="button" value="<?php _e('Clear filters', 'hostelpro')?>" onclick="window.location='admin.php?page=hostelpro_bookings&type=<?php echo $type?>'"><?php endif;?></p>
		</div>
	</form>	
	
	<p><a href="admin.php?page=hostelpro_bookings&do=add&type=<?php echo $type?>&offset=<?php echo $offset?>"><?php _e('Click here to manually add a new booking', 'hostelpro')?></a></p>
	
	<?php if(!sizeof($bookings)):?>
		<p><?php _e('There are no bookings to show at the moment.', 'hostelpro')?></p>
	<?php return false; 
	endif;?>
	<p><a href="admin.php?page=hostelpro_bookings&type=<?php echo $type?>&contact_email=<?php echo @$_GET['contact_email']?>&contact_name=<?php echo @$_GET['contact_name']?>&room_id=<?php echo @$_GET['room_id']?>&status=<?php echo @$_GET['status']?>&booking_id=<?php echo @$_GET['booking_id']?>&export=1&noheader=1"><?php _e('Export this data', 'hostelpro');?></a></p>
	<form method="post">
	<table class="widefat hostelpro-table">
		<thead>
		<tr><th><input type="checkbox" onclick="HostelPROSelectAll(this);"></th>
		<th><a href="admin.php?page=hostelpro_bookings&type=<?php echo $type?>&ob=tB.id&dir=<?php echo $odir?><?php echo $filters_str?>"><?php _e('ID', 'hostelpro');?></a></th><th><?php _e('Room/beds', 'hostelpro')?></th><th><a href="admin.php?page=hostelpro_bookings&type=<?php echo $type?>&offset=<?php echo $offset?>&ob=tB.contact_name&dir=<?php echo $odir?><?php echo $filters_str?>"><?php _e('Contact name', 'hostelpro')?></a></th>
		<th><a href="admin.php?page=hostelpro_bookings&type=<?php echo $type?>&ob=tB.contact_email&dir=<?php echo $odir?><?php echo $filters_str?>"><?php _e('Contact email', 'hostelpro')?></a></th>
		<th><a href="admin.php?page=hostelpro_bookings&type=<?php echo $type?>&ob=tB.from_date&dir=<?php echo $odir?><?php echo $filters_str?>"><?php _e('Booking dates', 'hostelpro')?></a></th>
		<th><a href="admin.php?page=hostelpro_bookings&type=<?php echo $type?>&ob=tB.created_time&dir=<?php echo $odir?><?php echo $filters_str?>"><?php _e('Time of booking', 'hostelpro');?></a></th>
		<th><a href="admin.php?page=hostelpro_bookings&type=<?php echo $type?>&ob=tB.amount_paid&dir=<?php echo $odir?><?php echo $filters_str?>"><?php _e('Amount paid/due', 'hostelpro')?></a></th>
		<th><a href="admin.php?page=hostelpro_bookings&type=<?php echo $type?>&ob=tB.status&dir=<?php echo $odir?><?php echo $filters_str?>"><?php _e('Status', 'hostelpro')?></a></th>
		<?php if(sizeof($datas)):?>
			<th><?php _e('Custom fields', 'hostelpro')?></th>
		<?php endif;?>		
		<th><?php _e('Action', 'hostelpro')?></th></tr>
		</thead>
		<tbody>
		<?php foreach($bookings as $booking):
		   if(HOSTELPRO_NO_DECIMALS) {
		   	$booking->amount_paid = hostelpro_number_format($booking->amount_paid);
		   	$booking->amount_due = hostelpro_number_format($booking->amount_due);
		   }
			$class = ('alternate' == @$class) ? '' : 'alternate';
			$booking_beds = $booking->extra_beds ? sprintf(__("%d + %d", 'hostelpro'), $booking->beds, $booking->extra_beds) : $booking->beds;?>
			<tr class="<?php echo $class?>">
			<th><input type="checkbox" name="bids[]" value="<?php echo $booking->id?>" class="bids" onclick="HostelPROtoggleMassDelete();"></th>			
			<td><?php echo $booking->id?></td>
			<td><?php printf(__('%s beds in %s', 'hostelpro'), $booking_beds, stripslashes($booking->room));
			if(!empty($booking->addons)) echo "<p>".stripslashes($booking->addons);?></td>			
			<td><?php echo $booking->contact_name?></td><td><?php echo $booking->contact_email?></td>
			<td><?php echo date_i18n($dateformat, strtotime($booking->from_date)).' - '.date_i18n($dateformat, strtotime($booking->to_date))?></td>
			<td><?php echo $booking->created_time ? date_i18n($timeformat, strtotime($booking->created_time)) : __('n/a', 'hostelpro');?></td>
			<td><?php echo HOSTELPRO_CURRENCY." ".$booking->amount_paid." / ".HOSTELPRO_CURRENCY.' '.$booking->amount_due;?></td>			
			<td><?php switch($booking->status):
			case 'active': _e('Active', 'hostelpro'); break;
			case 'pending': _e('Pending', 'hostelpro'); break;
			case 'cancelled': _e('Cancelled', 'hostelpro'); break;
			endswitch;
			if(!empty($booking->invoice_code)):?>
				<br><a href="<?php echo site_url('?hostelpro_invoice='.$booking->invoice_code.'&id='.$booking->id.'&noheader=1');?>" target="_blank"><?php _e('view invoice', 'hostelpro')?></a></td>
			<?php endif; // end if invoice available 
			if(sizeof($datas)):?>
				<td><?php echo $booking->custom_data?></td>
		   <?php endif;?>		
			<td nowrap="true"><input type="button" value="<?php _e('Edit', 'hostelpro')?>" onclick="window.location='admin.php?page=hostelpro_bookings&do=edit&id=<?php echo $booking->id?>&type=<?php echo $type?>&offset=<?php echo $offset?>';">
			<?php if($booking->amount_due > 0 or $booking->status != 'active'):?>
				<input type="button" value="<?php _e('Mark as paid', 'hostelpro');?>" onclick="HostelPROMarkPaid(<?php echo $booking->id?>);">
				<?php if($email_options['do_email_user']):?>
					<br> <input type="checkbox" id="bookingEmail<?php echo $booking->id?>"> <?php _e('Send emails when marking paid.', 'hostelpro');?>
				<?php endif;?>
			<?php endif;?></td></tr>
		<?php endforeach;?>
		</tbody>
	</table>
	
	<p align="center"><?php if($offset > 0):?>
		<a href="admin.php?page=hostelpro_bookings&type=<?php echo $type?>&offset=<?php echo $offset - $page_limit?>&ob=<?php echo @$_GET['ob']?>&dir=<?php echo $dir?><?php echo $filters_str?>"><?php _e('[previous page]', 'hostelpro')?></a>
	<?php endif;?> 
	<?php if($count > ($page_limit + $offset)):?>
		<a href="admin.php?page=hostelpro_bookings&type=<?php echo $type?>&offset=<?php echo $offset + $page_limit?>&ob=<?php echo @$_GET['ob']?>&dir=<?php echo $dir?><?php echo $filters_str?>"><?php _e('[next page]', 'hostelpro')?></a>
	<?php endif;?>	
	</p>		
	
		<p align="center" style="display:none;" id="massDeleteBookings">
			<input type="hidden" name="type" value="<?php echo $type?>">
			<input type="submit" name="mass_delete" value="<?php _e('Delete Selected Reservations', 'hostelpro')?>" class="button-primary">
		</p>
	</form>
</div>

<script type="text/javascript">
function HostelPROMarkPaid(id) {
	if(confirm("<?php _e('Are you sure?', 'hostelpro')?>")) {
		var notice_str = '';
		if(jQuery('#bookingEmail' + id).length && jQuery('#bookingEmail' + id).is(':checked')) {
			notice_str = "&send_emails=1";
		}
		window.location = 'admin.php?page=hostelpro_bookings&type=<?php echo $type?>&offset=<?php echo $offset;?>&mark_paid=1&id='+id + notice_str;
	}
}


function HostelPROSelectAll(chk) {
	if(chk.checked) {
		jQuery(".bids").attr('checked',true);
	}
	else {
		jQuery(".bids").removeAttr('checked');
	}
	
	HostelPROtoggleMassDelete();
}

// shows or hides the mass delete button
function HostelPROtoggleMassDelete() {
	var len = jQuery(".bids:checked").length;
	
	if(len) jQuery('#massDeleteBookings').show();
	else jQuery('#massDeleteBookings').hide();
}
<?php hostelpro_resp_table_js();?>
</script>