<div class="wrap">
	<h1><?php _e('Manage periods for minimum stay', 'hostelpro');?></h1>
	
	<p><?php _e('If the <b>start date</b> selected by the guest falls within some of the defined periods, the corresponding minimum stay requirement will be applied. If multiple periods match, the system will use the first one. If no period match, the system will use the global minimum stay requirement defined on your Settings page, if any.', 'hostelpro');?></p>
	
	<form method="post" onsubmit="return hostelProValidate(this);">
		<p><?php _e('From:', 'hostelpro');?> <input type="text" value="<?php echo date_i18n($dateformat, strtotime($start_date))?>" class="hostelproDatePicker" id="hostelproDatePickerStart">
		<?php _e('To:', 'hostelpro');?> <input type="text" value="<?php echo date_i18n($dateformat, strtotime($end_date))?>" class="hostelproDatePicker" id="hostelproDatePickerEnd">
		<?php _e('Min. stay:', 'hostelpro');?> <input type="text" name="days" size="4"> <?php _e('days', 'hostelpro');?>
		<input type="submit" name="add" value="<?php _e('Add Period', 'hostelpro');?>"></p>
		<input type="hidden" name="start_date" value="<?php echo $start_date?>" id="alt_hostelproDatePickerStart">
		<input type="hidden" name="end_date" value="<?php echo $end_date?>" id="alt_hostelproDatePickerEnd">
		<?php wp_nonce_field('hostelpro_minstays');?>
	</form>
	
	<?php foreach($periods as $period):?>
		<form method="post" onsubmit="return hostelProValidate(this);">
			<p><?php _e('From:', 'hostelpro');?> <input type="text" value="<?php echo date_i18n($dateformat, strtotime($period->start_date))?>" class="hostelproDatePicker" id="hostelproDatePickerStart<?php echo $minstay->id?>">
			<?php _e('To:', 'hostelpro');?> <input type="text" value="<?php echo date_i18n($dateformat, strtotime($period->end_date))?>" class="hostelproDatePicker" id="hostelproDatePickerEnd<?php echo $minstay->id?>">
			<?php _e('Min. stay:', 'hostelpro');?> <input type="text" name="days" size="4" value="<?php echo $period->days?>"> <?php _e('days', 'hostelpro');?>
			<input type="submit" name="save" value="<?php _e('Save', 'hostelpro');?>">
			<input type="button" value="<?php _e('Delete', 'hostelpro')?>" onclick="hostelProConfirmDelete(this.form)"></p>
			<input type="hidden" name="start_date" value="<?php echo $start_date?>" id="alt_hostelproDatePickerStart<?php echo $minstay->id?>">
			<input type="hidden" name="end_date" value="<?php echo $end_date?>" id="alt_hostelproDatePickerEnd<?php echo $minstay->id?>">
			<?php wp_nonce_field('hostelpro_minstays');?>
			<input type="hidden" name="id" value="<?php echo $period->id?>">
			<input type="hidden" name="del" value="0">
		</form>
	<?php endforeach;?>
</div>

<script type="text/javascript" >
function hostelProValidate(frm) {
	if(frm.days.value == "" || isNaN(frm.days.value) || frm.days.value < 1) {
		alert("<?php _e('Please enter positive number of days.', 'hostelpro');?>");
		frm.days.focus();
		return false;
	}
	
	return true;
}

function hostelProConfirmDelete(frm) {
	if(confirm("<?php _e('Are you sure?', 'hostelpro');?>")) {
		frm.del.value=1;
		frm.submit();
	}
}

jQuery(document).ready(function() {
	jQuery('.hostelproDatePicker').datepicker({
        dateFormat : '<?php echo dateformat_PHP_to_jQueryUI($dateformat);?>',
        altFormat: 'yy-mm-dd',
    });
    
    jQuery(".hostelproDatePicker").each(function (idx, el) { 
	    jQuery(this).datepicker("option", "altField", "#alt_" + jQuery(this).attr("id"));
	});
});	
</script>