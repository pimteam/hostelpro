<div class="wrap">
	<h1><?php _e('Manage Unavailable Dates', 'hostelpro')?></h1>
	
	<p><?php _e('Use this page to set up dates when specific rooms can not be booked. This might be due to your scheduled maintenance, holiday, or any other date when your property will not be working, or just a specific room will not be available.', 'hostelpro');?></p>
	
	<p><?php _e('Note that for simplicity bookings made will not be reflected here. Making dates available does not mean there are no already made reservations for it.', 'hostelpro');?></p>
	
	<p><?php _e('The table below will show a room as unavailable only if unavailability is set exactly for the selected period or is unavailable in a longer period that fully includes the selected one. Any partially overlapping period will be shown in additional column. There is no problem to overlap unavailable periods.', 'hostelpro')?></p>
	
	<form method="post" id="pickForm">
		<p><label><?php _e('From date:', 'hostelpro')?></label> <input type="text" value="<?php echo date_i18n($dateformat, strtotime($date))?>" class="hostelproDatePicker" id="unavDate">
		<label><?php _e('To date:', 'hostelpro')?></label> <input type="text" value="<?php echo date_i18n($dateformat, strtotime($to_date))?>" class="hostelproDatePicker" id="unavToDate">
		<input type="hidden" name="date" id="alt_unavDate" value="<?php echo $date?>">
		<input type="hidden" name="to_date" id="alt_unavToDate" value="<?php echo $to_date?>">
		
		<input type="submit" value="<?php _e('Change Dates', 'hostelpro')?>"> </p>
		
		<table class="widefat">
			<tr><th><?php _e('Room title', 'hostelpro')?></th><th><?php _e('Room type', 'hostelpro')?></th><th><?php _e('Make unavailable', 'hostelpro')?></th>
			<?php if(count($partially_unavailable)):?>
				<th><?php _e('Partially overlapping<br>unavailable periods', 'hostelpro');?></th>
			<?php endif;?>
			<?php foreach($rooms as $room):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>"><td><?php echo $room->title?></td><td><?php echo $_room->prettify('rtype', $room->rtype, $room);?></td>
				<td align="center"><input type="checkbox" name="ids[]" value="<?php echo $room->id?>" <?php if(in_array($room->id, $unavailable_room_ids)) echo 'checked'?>></td>
				<?php if(count($partially_unavailable)):?>
					<td><?php foreach($partially_unavailable as $part):
						if($part->room_id != $room->id) continue;?>
						<a href="#" onclick="hostelPROSetUnavaialblePeriod('<?php echo $part->from_date?>', '<?php echo $part->to_date?>');return false;"><?php echo date_i18n($dateformat, strtotime($part->from_date)) . ' - ' .date_i18n($dateformat, strtotime($part->to_date));?></a>
						<?php endforeach;?></td>
				<?php endif;?>				
				</tr>
			<?php endforeach;?>
		</table>
		
			<?php if(count($partially_unavailable)):?>
				<p><?php _e('You can click on any of the "Partially overlapping unavailable periods" to quickly reset the date slection to that period.', 'hostelpro');?></p>
			<?php endif;?>
		
		<p align="center"><input type="submit" name="set_dates" value="<?php _e('Save Unavailable Rooms', 'hostelpro')?>"></p>		
	</form>
</div>

<script type="text/javascript" >
jQuery(document).ready(function() {
    jQuery('.hostelproDatePicker').datepicker({
        dateFormat : '<?php echo dateformat_PHP_to_jQueryUI($dateformat);?>',        
        altFormat : "yy-mm-dd",           
    });
    
    jQuery(".hostelproDatePicker").each(function (idx, el) { 
	    jQuery(this).datepicker("option", "altField", "#alt_" + jQuery(this).attr("id"));
	});
	
	jQuery('#unavDate').datepicker('option', 'onSelect', function(dateText, inst) {
			var toDate = jQuery('#unavDate').datepicker('getDate', '+1d');
			toDate.setDate(toDate.getDate()+1); 
			jQuery('#unavToDate').datepicker("setDate", toDate);
		});
});	

function hostelPROSetUnavaialblePeriod(fromDate, toDate) {
	jQuery('#pickForm input[name=date]').val(fromDate);
	jQuery('#pickForm input[name=to_date]').val(toDate);
	jQuery('#pickForm').submit();
}
</script>