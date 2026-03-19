<div class="wrap">
	<h1><?php echo empty($_GET['id'])?__("Add", 'hostelpro'):__("Edit", 'hostelpro')?> <?php _e("Custom Field In The Booking Form", 'hostelpro')?></h1>
	
	<p><a href="admin.php?page=hostelpro_booking_form"><?php _e('Back to the booking form', 'hostelpro')?></a></p>
	
	<form method="post" class="bftpro" onsubmit="return validateHostelProField(this);">
	<div class="postbox wp-admin" style="padding:5px;">
		<p><label><?php _e("Field Name:", 'hostelpro')?></label> <input type="text" name="name" value="<?php echo @$field->name?>"></p>
		<div class="help"><?php _e("Only small letters and numbers, no spaces.", 'hostelpro')?></div>	
		
		<p><label><?php _e("Field Label:", 'hostelpro')?></label> <input type="text" name="label" value="<?php echo @$field->label?>"></p>
		<div class="help"><?php _e("This is what the user will see on the form", 'hostelpro')?></div>
		
		<p><label><?php _e("Field Type:", 'hostelpro')?></label> <select name="ftype" onchange="HostelProdisplayValues(this.value);">
			<option value="textfield" <?php if(@$field->ftype=='textfield') echo 'selected'?>><?php _e("Text Field", 'hostelpro')?></option>
			<option value="textarea" <?php if(@$field->ftype=='textarea') echo 'selected'?>><?php _e("Text Area", 'hostelpro')?></option>
			<option value="checkbox" <?php if(@$field->ftype=='checkbox') echo 'selected'?>><?php _e("Checkbox", 'hostelpro')?></option>
			<option value="dropdown" <?php if(@$field->ftype=='dropdown') echo 'selected'?>><?php _e("Drop Down", 'hostelpro')?></option>
			<option value="radio" <?php if(@$field->ftype=='radio') echo 'selected'?>><?php _e("Radio Group", 'hostelpro')?></option>
			<option value="date" <?php if(@$field->ftype=='date') echo 'selected'?>><?php _e("Date Selector", 'hostelpro')?></option>
			<!--option value="file" <?php if(@$field->ftype=='file') echo 'selected'?>><?php _e("File upload", 'hostelpro')?></option-->
			</select>
		</p>
		
		<div id="fieldValues" style="display:<?=(@$field->ftype=='dropdown' or @$field->ftype=='radio')?"block":"none"?>">
		<label><?php _e("Possible values:", 'hostelpro')?></label>
		<textarea name="fvalues" rows="5" cols="40"><?php echo @$field->fvalues?></textarea><br />
		<i><?php _e("Enter one value per line.", 'hostelpro')?></i></div>
		
		<div id="fileUpload" style="display:<?=(@$field->ftype=='file')?"block":"none"?>">
			<p><label><?php _e('Max file size in KB:', 'hostelpro')?></label> <input type="text" name="filesize" value="<?php echo @$filesize?>" size="4"> <?php _e('(Leave blank for no limit)', 'hostelpro')?></p>
			<p><label><?php _e('Accepted file types (ex. "jpg, png, doc"):', 'hostelpro')?></label> <input type="text" name="filetypes" value="<?php echo @$filetypes?>"> <?php _e('(Separate with comma, case insensitive. Leave blank for no restrictions)', 'hostelpro')?></p>
		</div>
		
		<p><label><input type="checkbox" name="is_required" value="1" <?php if(!empty($field->is_required)) echo "checked"?>> <?php _e('This is a required field', 'hostelpro')?></label></p>
		
		<?php do_action ('hostelpro_field_form', @$field);?>		
		
		<div>&nbsp;</div>
		<p><?php if(empty($_GET['id'])):?>
			<input type="submit" name="ok" value="<?php _e('Add Field', 'hostelpro');?>">
		<?php else:?>
			<input type="submit" name="ok" value="<?php _e('Save Field', 'hostelpro');?>">
			<input type="button" value="<?php _e('Delete Field', 'hostelpro');?>" onclick="hostelPROconfirmDeleteField(this.form);">
			<input type="hidden" name="del" value="0">
		<?php endif;?>
		<input type="button" value="<?php _e('Cancel', 'hostelpro');?>" onclick="window.location='admin.php?page=hostelpro_booking_form'"></p>
	</div>
	</form>
</div>	

<script type="text/javascript" >
function validateHostelProField(frm) {
	if(frm.name.value=='') 	{
		alert("<?php _e('Please enter name', 'hostelpro')?>");
		frm.name.focus();
		return false;
	}
	
	if(frm.label.value=='') {
		alert("<?php _e('Please enter label', 'hostelpro')?>");
		frm.label.focus();
		return false;
	}
	
	return true;
}

function HostelProdisplayValues(val) {
	document.getElementById('fieldValues').style.display='none';
	document.getElementById('fileUpload').style.display='none';
	
	if(val=='dropdown' || val=='radio') {
		document.getElementById('fieldValues').style.display='block';
	}
	
	if(val=='file') {
		document.getElementById('fileUpload').style.display='block';
	}
}

function hostelPROconfirmDeleteField(frm) {
	if(confirm("<?php _e('Are you sure?', 'hostelpro')?>")) {
		frm.del.value=1;
		frm.submit();
	}
}
</script>