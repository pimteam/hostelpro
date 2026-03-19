<form method="post" id="form-hostelPRORoomsTable<?php echo $shortcode_id?>">

	<p><?php _e('Arriving:', 'hostelpro')?> <input type="text" value="<?php echo date_i18n($dateformat, strtotime($datefrom))?>" class="hostelproDatePicker" id="hostelproDatePickerFrom<?php echo $shortcode_id?>" readonly='true'> <?php if(empty($atts['form_horizontal'])):?></p>	
	<p><?php endif; // end if form not horizontal?><?php _e('Leaving:', 'hostelpro')?> <input type="text" value="<?php echo date_i18n($dateformat, strtotime($dateto))?>" class="hostelproDatePicker" id="hostelproDatePickerTo<?php echo $shortcode_id?>" readonly='true'> <?php if(empty($atts['form_horizontal'])):?></p>		
	<p><?php endif; // end if form not horizontal ?><input type="button" value="<?php _e('Show availability', 'hostelpro')?>" onclick="validateHostelPROForm(this.form);"></p>
	<input type="hidden" name="hostelpro_to" value="<?php echo $dateto?>" id="alt_hostelproDatePickerTo<?php echo $shortcode_id?>">
	<input type="hidden" name="hostelpro_from" value="<?php echo $datefrom?>" id="alt_hostelproDatePickerFrom<?php echo $shortcode_id?>">
</form>

<div id="hostelPRORoomsTable<?php echo $shortcode_id?>" <?php if(empty($show_table)):?>style="display:none;"<?php endif;?>>
	<?php HostelPRORooms :: availability_table($atts);?>
</div>	

<script type="text/javascript">
function validateHostelPROForm(frm) {
	startParts = frm.hostelpro_from.value.split('-');
	var startDate = new Date(startParts[0], (startParts[1]-1), startParts[2]);
	endParts = frm.hostelpro_to.value.split('-');
	var endDate = new Date(endParts[0], (endParts[1]-1), endParts[2]);
	daydiff = (endDate - startDate) / (1000*60*60*24);	
	if(daydiff > <?php echo $max_days?>) {
		 alert("<?php printf(__('Please select up to %d days interval.', 'hostelpro'), $max_days);?>");
		 return false;
	}	
	if(hostelPROCheckMinStay(daydiff, frm.hostelpro_from.value, frm.hostelpro_to.value)) {
		jQuery('#hostelPRORoomsTable<?php echo $shortcode_id?>').html("<?php _e('Please wait...', 'hostelpro');?>");
		jQuery('#hostelPRORoomsTable<?php echo $shortcode_id?>').show();
		data = {'action': 'hostelpro_ajax', 'type': 'list_rooms', 'hostelpro_from' : frm.hostelpro_from.value, 
			'hostelpro_to' : frm.hostelpro_to.value, 'show_titles': '<?php echo $show_titles?>',
			'show_descriptions' : '<?php echo $show_descriptions?>', 'show_types': '<?php echo $show_types?>',
			'show_bathrooms': '<?php echo $show_bathrooms?>', 'shortcode_id' : '<?php echo $shortcode_id?>',
			'group_rooms': '<?php echo $group_rooms?>', 'vertical_after' : '<?php echo $vertical_after?>',  'hide_dates' : <?php echo $hide_dates?>};
		jQuery.post(hostelpro_i18n.ajax_url, data, function(msg){
				jQuery('#hostelPRORoomsTable<?php echo $shortcode_id?>').html(msg);
			});
	} // end if
}
jQuery(document).ready(function() {
    jQuery('.hostelproDatePicker').datepicker({
        dateFormat : "<?php echo dateformat_PHP_to_jQueryUI($dateformat);?>",
        altFormat: 'yy-mm-dd',
        minDate: '<?php echo $min_date?>',
        maxDate: '+<?php echo HOSTELPRO_MAX_DATE?>',
    });    
    jQuery(".hostelproDatePicker").each(function (idx, el) { 
	    jQuery(this).datepicker("option", "altField", "#alt_" + jQuery(this).attr("id"));
	});	
	jQuery('#hostelproDatePickerFrom<?php echo $shortcode_id?>').datepicker('option', 'onSelect', function(dateText, inst) {
			var toDate = jQuery('#hostelproDatePickerFrom<?php echo $shortcode_id?>').datepicker('getDate', '+1d');
			toDate.setDate(toDate.getDate()+1); 
			jQuery('#hostelproDatePickerTo<?php echo $shortcode_id?>').datepicker("setDate", toDate);
		});
});	
</script>