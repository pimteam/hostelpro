<div class="wrap">
	<h1><?php printf(__('Add/Edit %s', 'hostelpro'), $display_type)?></h1>
	
	<div class="wrap hostelpro-box postbox">
		<form class='hostelpro-form' method="post" onsubmit="return HostelPROValidateDiscount(this);">
			<p><label><?php printf(__('%s name:', 'hostelpro'), $display_type)?></label>
			<input type="text" name="name" value="<?php echo stripslashes(@$discount->name)?>"> <?php _e('(For management purposes)', 'hostelpro')?></p>
			
			<p><input type="checkbox" name="date_condition" value="1" <?php if(!empty($discount->date_condition)) echo 'checked'?> onclick="HostelPROapplyCondition('dateCondition', this.checked);"> <?php _e('Apply date condition', 'hostelpro')?></p>
			<div id="dateCondition" style="display:<?php echo empty($discount->date_condition) ? 'none' : 'block'?>;">
				<p><label><?php _e('From:', 'hostelpro')?></label> <input type="text" value="<?php echo empty($discount->date_from) ? '' : date_i18n($dateformat, strtotime($discount->date_from))?>" class="hostelproDatePicker" id="hostelPRODiscountDateFrom"></p>
				<input type="hidden" name="date_from" value="<?php echo @$discount->date_from?>" id="alt_hostelPRODiscountDateFrom">
				<p><label><?php _e('To:', 'hostelpro')?></label> <input type="text" value="<?php echo empty($discount->date_to) ? '' : date_i18n($dateformat, strtotime($discount->date_to))?>" class="hostelproDatePicker" id="hostelPRODiscountDateTo"></p>
				<input type="hidden" name="date_to" value="<?php echo @$discount->date_to?>" id="alt_hostelPRODiscountDateTo">
			</div>
			
			<p><input type="checkbox" name="weekdays_condition" value="1" <?php if(!empty($discount->weekdays_condition)) echo 'checked'?> onclick="HostelPROapplyCondition('weekdaysCondition', this.checked);"> <?php _e('Apply to days of the week', 'hostelpro')?></p>
			<div id="weekdaysCondition" style="display:<?php echo empty($discount->weekdays_condition) ? 'none' : 'block'?>">
				<p><label><?php _e('Select days:', 'hostelpro')?></label> <?php foreach($_discount->weekdays as $key => $weekday):?>
					<input type="checkbox" name="weekdays[]" value="<?php echo $key?>" <?php if(strstr(@$discount->weekdays, '|'.$key.'|')) echo 'checked'?>>
					<?php echo $weekday?> &nbsp;
				<?php endforeach;?></p>
			</div>
			
			<?php if($type == 'discount'): // obviously surcharges don't need coupon codes?>
				<p><input type="checkbox" name="coupon_condition" value="1" <?php if(!empty($discount->coupon_condition)) echo 'checked'?> onclick="HostelPROapplyCondition('couponCondition', this.checked);"> <?php _e('Apply coupon code condition', 'hostelpro')?></p>
				<div id="couponCondition" style="display:<?php echo empty($discount->coupon_condition) ? 'none' : 'block'?>">
					<p><label><?php _e('Coupon code:', 'hostelpro')?></label> <input type="text" name="coupon" value="<?php echo @$discount->coupon?>"></p>
				</div>
				
				<p><input type="checkbox" name="days_condition" value="1" <?php if(!empty($discount->days_condition)) echo 'checked'?> onclick="HostelPROapplyCondition('daysCondition', this.checked);"> <?php _e('Apply minimum stay condition', 'hostelpro')?></p>
				<div id="daysCondition" style="display:<?php echo empty($discount->days_condition) ? 'none' : 'block'?>">
					<p><label><?php _e('Min nights booked:', 'hostelpro')?></label> <input size="5" type="text" name="days" value="<?php echo @$discount->days?>"></p>
				</div>
			<?php endif;?>	
			
			<p><input type="checkbox" name="min_price_condition" value="1" <?php if(!empty($discount->min_price)) echo 'checked'?> onclick="HostelPROapplyCondition('priceCondition', this.checked);"> <?php _e('Apply minimum total price condition', 'hostelpro')?></p>
				<div id="priceCondition" style="display:<?php echo empty($discount->min_price) ? 'none' : 'block'?>">
					<p><label><?php _e('Min total is:', 'hostelpro')?></label> <input size="5" type="text" name="min_price" value="<?php echo @$discount->min_price?>"></p>
				</div>
			
			<p><label><?php echo $display_type;?></label> 
			<input type="text" name="discount_value" value="<?php echo @$discount->discount_value?>" size="6"> 
				<select name="discount_type">
					<option value="percent" <?php if(!empty($discount->discount_type) and $discount->discount_type == 'percent') echo 'selected'?>>%</option>
					<option value="amount" <?php if(!empty($discount->discount_type) and $discount->discount_type == 'amount') echo 'selected'?>><?php echo HOSTELPRO_CURRENCY?></option>
				</select></p>
				
			<p><label><?php _e('Apply to room:', 'hostelpro')?></label>
			<select name="room_id">
				<option value="0"><?php _e('All rooms', 'hostelpro')?></option>
				<?php foreach($rooms as $room):?>
					<option value="<?php echo $room->id?>" <?php if(!empty($discount->room_id) and $discount->room_id == $room->id) echo 'selected'?>><?php echo stripslashes($room->title)?></option>
				<?php endforeach;?>
			</select></p>	
			
			<?php do_action ('hostelpro_discount_form', @$discount);?>
				
			<p><input type="submit" value="<?php printf(__('Save %s', 'hostelpro'), $display_type)?>"></p>
			
			<input type="hidden" name="ok" value="1">	
		</form>
	</div>	
</div>

<script type="text/javascript" >
function HostelPROValidateDiscount(frm) {
	if(frm.name.value == '') {
		alert("<?php _e('Please enter discount name', 'hostelpro')?>");
		frm.name.focus();
		return false;
	}
	
	if(frm.discount_value.value == '' || isNaN(frm.discount_value.value)) {
		alert("<?php _e('Please enter discount value, numbers only', 'hostelpro')?>");
		frm.discount_value.focus();
		return false;
	}
	
	return true;
}

function HostelPROapplyCondition(conDiv, state) {
	if(state) jQuery('#'+conDiv).show();
	else jQuery('#'+conDiv).hide();
}

jQuery(document).ready(function() {
    jQuery('.hostelproDatePicker').datepicker({
        dateFormat : '<?php echo dateformat_PHP_to_jQueryUI($dateformat);?>',        
        altFormat : "yy-mm-dd"        
    });
    
    jQuery(".hostelproDatePicker").each(function (idx, el) { 
	    jQuery(this).datepicker("option", "altField", "#alt_" + jQuery(this).attr("id"));
	});
});	
</script>