<style type="text/css">
table.hostelpro-tabs td {
	background-color:#eee;
	cursor:pointer;
	padding:7px;
	font-weight:bold;
}
table.hostelpro-tabs td.active {
	background-color:black;
	color:white;
}
</style>
<div class="wrap">
	<h1><?php _e('Manage Your Booking Form', 'hostelpro')?></h1>
		
	<h2><?php _e('Manage Custom Fields', 'hostelpro')?></h2>
	
	<p><a href="admin.php?page=hostelpro_booking_form&do=add"><?php _e('Create new custom field', 'hostelpro')?></a></p>
	<?php if($count):?>
	<table class="widefat">
	<tr><th scope="col">#</th><th><?php _e("Field Label", 'hostelpro')?></th><th><?php _e("Field Name", 'hostelpro')?></th><th><?php _e("Field Type", 'hostelpro')?></th>
	<th><?php _e("Is Required?", 'hostelpro')?></th><th><?php _e("Edit/Delete", 'hostelpro')?></th></tr>
	<?php foreach($fields as $cnt=>$field):
		$class = ('alternate' == @$class) ? '' : 'alternate';?>
		<tr class="<?php echo $class?>">
		<td><?php if($count > 1):?>
					<a href="admin.php?page=hostelpro_booking_form&move=<?php echo $field->id?>&dir=up"><img src="<?php echo HOSTELPRO_URL."/img/arrow-up.png"?>" alt="<?php _e('Move Up', 'hostelpro')?>" border="0"></a>
				<?php else:?>&nbsp;<?php endif;?>
				<?php if($count > $cnt+1):?>	
					<a href="admin.php?page=hostelpro_booking_form&move=<?php echo $field->id?>&dir=down"><img src="<?php echo HOSTELPRO_URL."/img/arrow-down.png"?>" alt="<?php _e('Move Down', 'hostelpro')?>" border="0"></a>
				<?php else:?>&nbsp;<?php endif;?></td>		
		<td><?php echo $field->label?></td><td><?php echo $field->name?></td><td><?php echo $field->ftype?></td>
		<td><?php echo $field->is_required?__('Yes', 'hostelpro'):__('No', 'hostelpro')?></td>
		<td><a href="admin.php?page=hostelpro_booking_form&do=edit&id=<?php echo $field->id?>"><?php _e("Edit", 'hostelpro')?></a></td></tr>
	<?php endforeach;?>
	</table>
<?php else:?>
	<p><?php _e("There are no custom fields yet.", 'hostelpro')?></p>
<?php endif;?>

<p>&nbsp;</p>
	<h2><?php _e('Configure the Auto-Generated Booking Form', 'hostelpro')?></h2>
	
	<p><?php _e('Use the "Raw code for visual mode" from the box on bottom of this page to configure how the auto-generated booking form will look like. If you do not configure this, a default booking form will be used.<br> This auto-generated form is displayed for example after clicking on the "Book" button on the table that lists rooms ([hostelpro-list] shortcode).', 'hostelpro')?></p>
	<form method="post" onsubmit="return hostelPROValidate(this);">
	<p><textarea rows="15" cols="80" name="booking_form_design"><?php echo stripslashes(get_option('hostelpro_booking_form_design'));?></textarea></p>
	<p><?php _e('Note: do not separate the [hostelpro-if-extra-beds] shortcode from its closing part - [/hostelpro-if-extra-beds]. This is a conditional shortcode and the content enclosed by it will appear only if the chosen room offers extra beds.', 'hostelpro')?></p>
	<p><?php _e('You can reorder all fields. You can remove any fields except the required fields. They can only be hidden by adding "hidden=true" attribute in the shortcode. For example: [hostelpro-field-static beds hidden=true]', 'hostelpro')?></p>
	<p><?php printf(__('If you use room-specific addon services you can include the shortcode %s to have them dynamically appear on room selection.', 'hostelpro'), '<input type="text" readonly="readonly" onclick="this.select()" value="[hostelpro-room-addons]">')?></p>
	<p><?php printf(__('You can include the room description on the booking form using the shortcode %s', 'hostelpro'), '<input type="text" size="30" value="[hostelpro-room-description]" onclick="this.select();" readonly="readonly">');?></p>
	<?php if(get_option('hostelpro_text_captcha_enabled') == 1):?>
		<p><?php printf(__('To include a question based captcha, configured on the HostlPRO Settings page include the shortcode %s', 'hostelpro'), '<input type="text" value="[hostelpro-field-static captcha]" readonly="readonly" onclick="this.select();" size="30">')?></p>
	<?php endif;?>	
	<p><input type="submit" value="<?php _e('Save design', 'hostelpro')?>" name="save_design"></p>
	</form>
	
	
	<h2><?php _e('Publish Booking Form', 'hostelpro')?></h2>
	
	<p><?php _e('You can also manually publish a booking form on a chosen post or page.', 'hostelpro')?></p>
	
	<p><?php _e('The default shortcode for publishing the booking form is','hostelpro')?> <input type="text" readonly="readonly" onclick="this.select();" value="[hostelpro-booking]"> </p>
	<p><?php _e('However you may want to change the design and fields order in the form. To do this, you can use the WordPress editor friendly code shown in the table below:', 'hostelpro')?></p>
	
	<table>
		<tr><td width="40%" valign="top"><h3><?php _e('WordPress-friendly form code (to save as default auto-generated form or place in post or page)','hostelpro')?></h3>		
			
				<h3><?php _e('How to use the code:', 'hostelpro')?></h3>
					<div id="explainTextMode" style="display:none;"><p><?php _e('This is the Text mode code. Use it if you are familiar with editing HTML. Otherwise please click on "Raw Code for Visual Mode" and get that code.', 'hostelpro')?></p></div>	
					
					<div id="explainVisualMode"><p><?php _e('This is the Visual mode code. Use it if you are NOT familiar with editing HTML and want to design your form in the WordPress visual editor. Note that unlike the other code, this has nothing pre-designed.', 'hostelpro')?></p></div>				
				
				<ol>
					<li><?php _e('Copy the code by clicking in the box at right.','hostelpro');?></li>
					<li><?php _e('Create a post or page in your blog or edit an existing post or page.', 'hostelpro')?></li>
					<li id="liTextMode"  style="display:none;"><?php _e('Paste the code in <b>Text Mode</b> of visual editor and feel free to edit it any way you wish without changing the contents of the shortcodes.', 'hostelpro')?></li>
					<li id="liVisualMode"><?php _e('Paste the code in <b>Visual Mode</b> of visual editor and feel free to edit it any way you wish without changing the contents of the shortcodes.', 'hostelpro')?></li>
					
				</ol>			</td>
				<td width="60%">
					<table class="hostelpro-tabs"><tr><td onclick="hostelProSetMode('visual');" class="active" id="visualModeTab">Raw Code for Visual Mode</td>
					<td width="50%"  onclick="hostelProSetMode('text');" id="textModeTab">Pre-Designed HTML Code For Text Mode</td></tr></table>	
								
				<div id="bookFormForText" style="display:none;">
					<textarea rows="15" cols="80" readonly="true" onclick="this.select();">[hostelpro-form-start]
<div><label><?php _e("Select room", 'hostelpro')?></label> [hostelpro-field-static room_id]</div>
<div class="select-beds"><label>*<?php _e("No. beds to book:", 'hostelpro')?></label> [hostelpro-field-static beds]</div><?php 
if($any_extra_beds): echo "\n";?>
[hostelpro-if-extra-beds]
<div><label><?php _e("Extra beds:", 'hostelpro')?></label> [hostelpro-field-static extra_beds] <?php printf(__('%s{{price}} per bed', 'hostelpro'), HOSTELPRO_CURRENCY)?></div>
[/hostelpro-if-extra-beds]
<?php endif;?><div><label>*<?php _e("From date", 'hostelpro')?></label> [hostelpro-field-static from_date]</div>
<div><label>*<?php _e("To date", 'hostelpro')?></label> [hostelpro-field-static to_date]</div>
<div><label>*<?php _e("Contact Name", 'hostelpro')?></label> [hostelpro-field-static contact_name]</div>
<div><label>*<?php _e("Contact Email", 'hostelpro')?></label> [hostelpro-field-static contact_email]</div>		
<div><label><?php _e("Contact Phone", 'hostelpro')?></label> [hostelpro-field-static contact_phone]</div>
<?php foreach($fields as $field):
	if($field->ftype!='checkbox') echo "<div><label>".($field->is_required?'*':'').$field->label."</label> ";
	switch($field->ftype):
		case 'textfield':	
		case 'textarea': 
		case 'dropdown':	
		case 'radio':
		case 'date':
			?>[hostelpro-field <?php echo $field->id?>]<?php 		
		break;		
		case 'checkbox':?>[hostelpro-field <?php echo $field->id?>] <?php echo ($field->is_required?'*':'').$field->label?><?php break;
	endswitch;
	echo "</div>\n";	
endforeach;
if(sizeof($addons)):
	foreach($addons as $addon):
		$priceinfo = '';
		if($addon->per_person) $priceinfo .= ' '.__('per person', 'hostelpro').' ';
		if($addon->per_day) $priceinfo .= ' '.__('per day', 'hostelpro').' ';
		echo "<div><label>".sprintf(__('%s - %s%s %s', 'hostelpro'), stripslashes($addon->name), HOSTELPRO_CURRENCY, $addon->price, $priceinfo)."</label> ";?>[hostelpro-addon id="<?php echo $addon->id?>"]<?php echo "</div>";
	endforeach;
endif;?>
<div><label>*<?php _e("You are", 'hostelpro')?></label> [hostelpro-field-static contact_type]</div>
<div><label><?php _e("Discount coupon:", 'hostelpro')?></label> [hostelpro-field-static coupon]</div>

<div align="center">[hostelpro-submit-button "<?php _e('Make Reservation', 'hostelpro')?>"]</div>	

[hostelpro-form-end]</textarea>
				</div>
				<div id="bookFormForVisual" ><textarea rows="15" cols="80" readonly="true" onclick="this.select();"><?php include(HOSTELPRO_PATH."/views/raw-code-booking.html.php");?></textarea></div></td></tr>
	</table>
</div>

<script type="text/javascript" >
function hostelProSetMode(mode) {
	if(mode=='text') {
		jQuery('#explainTextMode').show();
		jQuery('#explainVisualMode').hide();
		jQuery('#liTextMode').show();
		jQuery('#liVisualMode').hide();
		jQuery('#textModeTab').addClass('active');
		jQuery('#visualModeTab').removeClass('active');
		jQuery('#bookFormForVisual').hide();
		jQuery('#bookFormForText').show();
	}
	else {
		jQuery('#explainTextMode').hide();
		jQuery('#explainVisualMode').show();
		jQuery('#liTextMode').hide();
		jQuery('#liVisualMode').show();
		jQuery('#textModeTab').removeClass('active');
		jQuery('#visualModeTab').addClass('active');
		jQuery('#bookFormForVisual').show();
		jQuery('#bookFormForText').hide();
	}
}

function hostelPROValidate(frm) {
	var content = frm.booking_form_design.value;
	
	if(content == '') return true;
	
	if(content.indexOf('[hostelpro-field-static room_id') < 0
		|| content.indexOf('[hostelpro-field-static beds') < 0
		|| content.indexOf('[hostelpro-field-static from_date') < 0
		|| content.indexOf('[hostelpro-field-static to_date') < 0 
		|| content.indexOf('[hostelpro-field-static contact_name') < 0
		|| content.indexOf('[hostelpro-field-static contact_email') < 0) {
			alert("<?php _e('You cannot remove the required fields. They can only be hidden.', 'hostelpro')?>");
			return false;
	}
	
	return true;	
}
</script>