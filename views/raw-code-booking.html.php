[hostelpro-form-start]

<?php _e("Select room:", 'hostelpro')?> 
[hostelpro-field-static room_id][hostelpro-if-beds]*<?php _e("No. beds to book", 'hostelpro')?> [hostelpro-field-static beds][/hostelpro-if-beds]<?php if($any_extra_beds):?>[hostelpro-if-extra-beds]<?php _e("Extra beds:", 'hostelpro')?> [hostelpro-field-static extra_beds] <?php printf(__('%s{{price}} per bed', 'hostelpro'), HOSTELPRO_CURRENCY);?>[/hostelpro-if-extra-beds]
<?php endif;?>[hostelpro-field-static notes]*<?php _e("From date", 'hostelpro')?> 
[hostelpro-field-static from_date]
*<?php _e("To date", 'hostelpro')?> 
[hostelpro-field-static to_date]
*<?php _e("Contact Name:", 'hostelpro')?> 
[hostelpro-field-static contact_name]
*<?php _e("Contact Email:", 'hostelpro')?> 
[hostelpro-field-static contact_email]		
<?php _e("Contact Phone:", 'hostelpro')?> 
[hostelpro-field-static contact_phone]
<?php foreach($fields as $field):
	if($field->ftype!='checkbox') echo ($field->is_required?'*':'').$field->label."\n ";
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
	echo "\n";	
endforeach;
if(sizeof($addons)):
	foreach($addons as $addon):
		$priceinfo = '';
		if($addon->per_person) $priceinfo .= ' '.__('per person', 'hostelpro').' ';
		if($addon->per_day) $priceinfo .= ' '.__('per day', 'hostelpro').' ';
printf(__('%s - %s%s %s', 'hostelpro'), stripslashes($addon->name), HOSTELPRO_CURRENCY, $addon->price, $priceinfo);?>
[hostelpro-addon id="<?php echo $addon->id?>"]
<?php endforeach;
endif;?>*<?php _e("You are:", 'hostelpro')?> 
[hostelpro-field-static contact_type]
<?php _e("Discount coupon:", 'hostelpro')?> 
[hostelpro-field-static coupon]

[hostelpro-submit-button "<?php _e('Make Reservation', 'hostelpro')?>"]	

[hostelpro-form-end]