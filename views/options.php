<div class="wrap">
	<h1><?php _e("Hostel PRO Options", 'hostelpro')?></h1>
	
	<form method="post" class="hostelpro-form">
		<div class="postbox hostelpro-box">
			<p><label><?php _e("Currency:", 'hostelpro');?></label>
				<select name="currency" onchange="hostelPROChangeCurrency(this.value);">
				<?php foreach($currencies as $key=>$val):
	            if($key==$currency) $selected='selected';
	            else $selected='';?>
	        		<option <?php echo $selected?> value='<?php echo $key?>'><?php echo $val?></option>
	         <?php endforeach; ?>
	         <option value="" <?php if(!in_array($currency, $currency_keys)) echo 'selected'?>><?php _e('Custom', 'wphostel')?></option>
				</select> <input type="text" id="customCurrency" name="custom_currency" style='display:<?php echo in_array($currency, $currency_keys) ? 'none' : 'inline';?>' value="<?php echo $currency?>" onkeyup="jQuery('#cashCurrencyVal').html(this.value)">
				
				<input type="checkbox" name="no_decimals" value="1" <?php if(get_option('hostelpro_no_decimals') == 1) echo 'checked'?>> <?php _e('Show no decimals.', 'hostelpro');?></p>
				
			<p><label><?php _e('Booking mode:', 'hostelpro')?></label> <select name="booking_mode" onchange="changeBookingMode(this.value);">
				<option value="none" <?php if($booking_mode == 'none') echo 'selected'?>><?php _e('No booking', 'hostelpro')?></option>		
				<option value="manual" <?php if($booking_mode == 'manual') echo 'selected'?>><?php _e('Manual / Other / No Payment', 'hostelpro')?></option>
				<option value="paypal" <?php if($booking_mode == 'paypal') echo 'selected'?>><?php _e('Paypal', 'hostelpro')?></option>
				<option value="stripe" <?php if($booking_mode == 'stripe') echo 'selected'?>><?php _e('Stripe', 'hostelpro')?></option>
				</select>
				<input type="checkbox" name="multi_booking" value="1" <?php if(get_option('hostelpro_multi_booking') == 1) echo 'checked'?>> <?php _e('Allow multiple bookings within one session.', 'hostelpro');?>
				<div class="hostelpro-help">
					<p><strong><?php _e('No booking', 'hostelpro')?></strong> <?php _e('- In this mode your site will only show the information for the rooms and will not let the visitors book rooms', 'hostelpro')?></p>
					
					<p><strong><?php _e('Manual / No Payment', 'hostelpro')?></strong> <?php _e('- In this mode your visitors will be able to request booking by clicking on button and filling their information in the booking form. You as admin will approve or reject the booking manually in the admin panel. If there is button code or payment instructions entered they will also be shown.', 'hostelpro')?> <b><?php _e('In this mode use the "Other payment instructions box" also to show confirmation or thank-you message.', 'hostelpro');?></b><br>					
					<p><strong><?php _e('Paypal', 'hostelpro')?></strong> <?php _e('- In this mode your visitors will be able to book and get their bookings activated instantly by paying by Paypal', 'hostelpro')?></p>
					
					<p><strong><?php _e('Stripe', 'hostelpro')?></strong> <?php _e('- In this mode your visitors will be able to book and get their bookings activated instantly by paying by Stripe', 'hostelpro')?></p>
				</div>		
			</p>	
			
			<?php if(!empty($payment_errors)):?>
				<p><a href="#" onclick="jQuery('#hostelproErrorlog').toggle();return false;"><?php _e('View payments errorlog', 'hostelpro')?></a></p>
				<div id="hostelproErrorlog" style="display:none;"><?php echo nl2br($payment_errors)?></div>
			<?php endif;?>	
			
			<div id="HostelPROPaypal" style='display:<?php echo ($booking_mode=='paypal')?'block':'none'?>'>
				<p><label><?php _e('Your Paypal Email:', 'hostelpro')?></label> <input type="text" name="paypal" value="<?php echo @$paypal?>"></p>			
				<!--p><input type="checkbox" name="paypal_sandbox" <?php if(get_option('hostelpro_paypal_sandbox') == 1) echo 'checked'?> value="1"> <?php _e('Use Paypal in sandbox mode', 'watupro');?></p-->	
				<p><b><?php _e('Note: Paypal IPN will not work if your site is behind a "htaccess" login box or running on localhost. Your site must be accessible from the internet for the IPN to work. In cases when IPN cannot work you need to use Paypal PDT.', 'hostelpro')?></b></p>
			
				<p><input type="checkbox" name="use_pdt" value="1" <?php if($use_pdt == 1) echo 'checked'?> onclick="this.checked ? jQuery('#paypalPDTToken').show() : jQuery('#paypalPDTToken').hide();"> <?php printf(__('Use Paypal PDT instead of IPN (<a href="%s" target="_blank">Why and how</a>)', 'hostelpro'), 'http://blog.calendarscripts.info/using-paypal-payment-data-transfer-pdt-instead-of-ipn-in-hostel-and-hostelpro-plugins');?></p>
				
				<div id="paypalPDTToken" style='display:<?php echo ($use_pdt == 1) ? 'block' : 'none';?>'>
					<p><label><?php _e('Paypal PDT Token:', 'hostelpro');?></label> <input type="text" name="pdt_token" value="<?php echo get_option('hostelpro_pdt_token');?>" size="60"></p>
				</div>
				
				<p><?php _e('After payment redirect to this URL:', 'hostelpro');?> <input type="text" name="paypal_return" value="<?php echo get_option('wphostel_paypal_return');?>" size="30"></p>
			</div>
			
			<div id="HostelPROStripe" style='display:<?php echo ($booking_mode=='stripe')?'block':'none'?>'>
				<p><label><?php _e('Your Public Stripe Key:', 'hostelpro')?></label> <input type="text" name="stripe_public" value="<?php echo get_option('hostelpro_stripe_public')?>" size="40"></p>
				<p><label><?php _e('Your Secret Stripe Key:', 'hostelpro')?></label> <input type="text" name="stripe_secret" value="<?php echo get_option('hostelpro_stripe_secret')?>" size="40"></p>			
				<p><label><?php _e('Text to show after successful payment (use this if you do not use a redirect URL):', 'hostelpro');?></label> <br>
				 <?php echo wp_editor(stripslashes(get_option('hostelpro_stripe_success')), 'stripe_success');?><br />
				 <?php _e('You can use the variable {{{back-url}}} to generate a link back to the page where the visitor was prior to payment. Please note the variable makes URL and not a clickable link, so you need to make it clickable using the rich text editor above. This lets you put your own clickable text.', 'hostelpro');?></p>	
				<p><?php _e('After payment redirect to this URL:', 'hostelpro');?> <input type="text" name="stripe_return" value="<?php echo get_option('hostelpro_stripe_return');?>" size="30"></p> 
			</div>
			
			<div id="HostelPROPayment" style='display:<?php echo ($booking_mode=='none')?'none':'block'?>'>
				<p><label><?php _e('Guests have to pay', 'hostelpro')?></label> <input type="text" name="advance_payment_percentage" size="4" value="<?php echo $advance_payment_percentage?>"> <select name="advance_payment_unit">
					<option value="%" <?php if(empty($advance_payment_unit) or $advance_payment_unit == '%') echo 'selected'?>>%</option>
					<option id="cashCurrencyVal" value="cash" <?php if(!empty($advance_payment_unit) and $advance_payment_unit == 'cash') echo 'selected'?>><?php echo $currency?></option>
				</select> <?php _e('of the booking cost in advance', 'hostelpro')?></p>

				<p><b><?php _e('Other payment instructions (optional):', 'hostelpro')?></b><br> 
					<?php wp_editor(stripslashes(get_option('hostelpro_payemnt_instructions')), 'instructions')?></p>	
				
				<p><?php _e('You can use this box to provide instructions about advance payment by bank wire or checque or even to include HTML button code from payment systems like CCAvenue, Authorize.net, 2Checkout.com etc. The following variables can be used:', 'hostelpro')?></p>
				
				<ol>
					<li><input type="text" value="{{{booking-id}}}" onclick="this.select();" readonly="readonly"> <?php _e('Unique ID of the booking - might be useful for management purposes or enter as Order ID in the button code etc.', 'hostelpro')?></li>
					<li><input type="text" value="{{{amount-now}}}" onclick="this.select();" readonly="readonly"> <?php _e('The amount that has to be paid in advance (Amount due now).', 'hostelpro')?></li>
					<li><input type="text" value="{{{amount-arrival}}}" onclick="this.select();" readonly="readonly"> <?php _e('The amount due on arrival.', 'hostelpro')?></li>
					<li><input type="text" value="{{{costs-breakdown}}}" onclick="this.select();" readonly="readonly"> <?php _e('A table with breakdown of all costs and discounts.', 'hostelpro')?></li>
				</ol>			
								
				<p><?php _e('Automatically cleanup unconfirmed (unpaid) bookings after', 'hostelpro')?> <input type="text" name="cleanup_hours" value="<?php echo $cleanup_hours?>" size="4"> <?php _e('hours. (Leave blank for no automated cleanup.)', 'hostelpro')?> </p>
			</div>	
			
			<div id="HostelPROMinStay" style='display:<?php echo ($booking_mode!='none')?'block':'none'?>'>
				<p><label><?php _e('Require minimum stay of:', 'hostelpro')?></label> <input type="text" name="min_stay" value="<?php echo $min_stay?>" size="3"> <?php _e('days', 'hostelpro');?> <a href="admin.php?page=hostelpro_minstays" target="_blank"><?php _e('Define different minimum stay requirements for different periods', 'hostelpro');?></a></p>	
				<p><label><?php _e('Allow maximum stay of:', 'hostelpro')?></label> <input type="text" name="max_stay" value="<?php echo $max_stay?>" size="3"> <?php _e('days', 'hostelpro');?> </p>	
				<p><label><?php _e('Guests can book rooms from:', 'hostelpro');?></label> <select name="booking_start">
					<option value="tomorrow" <?php if($booking_start == 'tomorrow') echo 'selected'?>><?php _e('Next day', 'hostelpro');?></option>
					<option value="today" <?php if($booking_start == 'today') echo 'selected'?>><?php _e('Same day', 'hostelpro');?></option>
				</select></p>		
				
				<p><label><?php _e('Limit bookings to', 'hostelpro');?></label> <input type="text" size="4" name="max_date_num" value="<?php echo $max_date_num?>"> 
					<select name="max_date_unit">
						<option value="m" <?php if($max_date_unit == 'm') echo 'selected';?>><?php echo _e('months', 'hostelpro');?></option>
						<option value="y" <?php if($max_date_unit == 'y') echo 'selected';?>><?php echo _e('years', 'hostelpro');?></option>
					</select> <?php _e('in the future', 'hostelpro');?></p>	
			</div>
			
			<hr>
				<h2><?php _e('Notification Settings', 'hostelpro')?></h2>
				<p>&nbsp;</p>
				<p><label><?php _e('Send emails from:', 'hostelpro')?></label> <input type="text" name="sender_name" value="<?php echo get_option('hostelpro_sender_name')?>" placeholder="<?php _e('Your Name', 'hostelpro')?>"> <input type="text" name="sender_email" value="<?php echo get_option('hostelpro_sender_email')?>" placeholder="<?php echo get_option('admin_email');?>"> <?php _e('If you leave this empty, the WordPress sender email will be used.', 'hostelpro')?></p>
				<p><input type="checkbox" name="do_email_admin" value="1" <?php if(!empty($email_options['do_email_admin'])) echo 'checked'?> onclick="jQuery('#emailAdminOptions').toggle();"> <?php _e('Send me email with booking details when someone makes or requests a booking','hostelpro')?> </p>
			
			<div id="emailAdminOptions" style='display:<?php echo empty($email_options['do_email_admin'])? 'none' : 'block'?>;margin-left:100px;'>
					<p><label><?php _e('Email address to receive the notice:', 'hostelpro')?></label> <input type="text" name="admin_email" value="<?php echo empty($email_options['admin_email']) ? get_option('admin_email') : $email_options['admin_email']?>"></p>		
					<p><label><?php _e('Email subject:', 'hostelpro')?></label> <input type="text" name="email_admin_subject" value="<?php echo $email_options['email_admin_subject']?>" size="40"></p>
					<p><label><?php _e('Email message:', 'hostelpro')?></label> <?php echo wp_editor(stripslashes(@$email_options['email_admin_message']), 'email_admin_message')?></p>
					<p><?php _e('You can use the following variables:', 'hostelpro')?> <b>{{from-date}}</b>, <b>{{to-date}}</b>, <b>{{url}}</b> <?php _e('(The URL to see the booking details in admin)','hostelpro')?>, <b>{{amount-paid}}</b>, 
					<b>{{amount-due}}</b>, <b>{{room-name}}</b>, <b>{{room-type}}</b>, <b>{{num-beds}}</b>, <b>{{extra-beds}}</b>, <b>{{contact-name}}</b>, <b>{{contact-email}}</b>, <b>{{contact-phone}}</b>, <b>{{timestamp}}</b> <?php _e('(Date/time when reservation is made)','hostelpro')?>, <b>{{addons}}</b> <?php _e('(The addon services purchased, if any', 'hostelpro')?>, {{invoice-url}}</p>
					<?php if(sizeof($fields)):?>
						<p><?php _e('And the variables from the custom fields:', 'hostelpro')?> 
							<?php foreach($fields as $cnt=>$field):
							if($cnt) echo ', '; 
							echo '<b>{{{custom-field-' . $field->name . '}}}</b>';
							endforeach;?>	
								</p>							
					<?php endif;?>
					<p><?php printf(__('The start / end tags %s and %s are useful if you have enabled multiple bookings mode. It will mark which part of your email contains the list of bookings and will be repeated once for each booking made.', 'hostelpro'), '[bookings]', '[/bookings]');?></p>
			</div>
			
			<p><input type="checkbox" name="do_email_user" value="1" <?php if(!empty($email_options['do_email_user'])) echo 'checked'?> onclick="jQuery('#emailUserOptions').toggle();"> <?php _e('Send confirmation email to user when booking is made','hostelpro')?> </p>
			
				
			<div id="emailUserOptions" style='display:<?php echo empty($email_options['do_email_user'])? 'none' : 'block'?>;margin-left:100px;'>					
					<p><label><?php _e('Email subject:', 'hostelpro')?></label> <input type="text" name="email_user_subject" value="<?php echo $email_options['email_user_subject']?>" size="40"></p>
					<p><label><?php _e('Email message:', 'hostelpro')?></label> <?php echo wp_editor(stripslashes(@$email_options['email_user_message']), 'email_user_message')?></p>
					<p><?php _e('You can use the following variables:', 'hostelpro')?> <b>{{from-date}}</b>, <b>{{to-date}}</b>, <b>{{amount-paid}}</b>, 
					<b>{{amount-due}}</b>, <b>{{room-name}}</b>, <b>{{room-type}}</b>, <b>{{num-beds}}</b>, <b>{{extra-beds}}</b>, <b>{{timestamp}}</b> <?php _e('(Date/time when reservation is made)','hostelpro')?>, <b>{{addons}}</b> <?php _e('(The addon services purchased, if any', 'hostelpro')?>, {{invoice-url}}</p>
					<?php if(sizeof($fields)):?>
						<p><?php _e('And the variables from the custom fields:', 'hostelpro')?> 
							<?php foreach($fields as $cnt=>$field):
							if($cnt) echo ', '; 
							echo '<b>{{{custom-field-' . $field->name . '}}}</b>';
							endforeach;?>	
								</p>							
					<?php endif;?>
			</div>
			
			<hr>
			<h2><?php _e('Anti SPAM Options', 'hostelpro')?></h2>
			
			<p><input type="checkbox" name="honeypot" value="1" <?php if(get_option('hostelpro_honeypot') == 1) echo 'checked'?>> <?php _e('Enable advanced "honeypot" field on the booking form.', 'hostelpro')?> <a href="http://wp-hostel.com/articles/advanced-honeypot-field.php" target="_blank"><?php _e('(Learn more)', 'hostelpro')?></a></p>
			
			<p><input type="checkbox" name="enable_text_captcha" value="1" <?php if($text_captcha_enabled == 1) echo 'checked'?> onclick="this.checked ? jQuery('#questionCaptcha').show() : jQuery('#questionCaptcha').hide();"> <?php _e('Enable question based captcha', 'hostelpro')?></p>
			
			<div id="questionCaptcha" style='display:<?php echo ($text_captcha_enabled == 1) ? 'block' : 'none';?>;'>				
				<p><?php printf(__("You can use a simple text-based captcha to prevent spam bookings. Use the shortcode %s to include it in the booking form. We have loaded 3 basic questions but you can edit them and load your own. Make sure to enter only one question per line and use = to separate question from answer.", 'hostelpro'), '[hostelpro-field-static captcha]')?></p>				
				<p><textarea name="text_captcha" rows="10" cols="70"><?php echo stripslashes($text_captcha);?></textarea></p>
			</div>			
			
			<h2><?php _e('Other Technical Settings', 'hostelpro');?></h2>
			
			<p><input type="checkbox" name="debug_mode" value="1" <?php if(get_option('hostelpro_debug_mode')) echo 'checked'?> /> <?php _e('Enable debug mode to see SQL errors. (Useful in case you have any problems)', 'hostelpro')?></p>
			
			<?php if(!empty($ical_errors)):?>
				<p><a href="#" onclick="jQuery('#hostelproiCalErrorlog').toggle();return false;"><?php _e('View latest iCal import error', 'hostelpro')?></a></p>
				<div id="hostelproiCalErrorlog" style="display:none;"><?php echo nl2br($ical_errors)?></div>
			<?php endif;?>
			
			<p><input type="submit" value="<?php _e('Save Options', 'hostelpro')?>"></p>
			<input type="hidden" name="ok" value="1">
			<?php wp_nonce_field('hostelpro_settings');?>
		</div>
	</form>
	
	<?php if($is_admin):?>
		<h2><?php _e('Role Management','hostelpro')?></h2>
		<form method="post" class="hostelpro-form">
			<div class="postbox hostelpro-box">
				<h2><?php _e('Wordpress roles that can administrate the plugin', 'hostelpro')?></h2>
		
				<p><?php _e('By default this is only the blog administrator. Here you can enable any of the other roles as well', 'hostelpro')?></p>
				
				<p><?php foreach($roles as $key=>$r):
					if($key=='administrator') continue;
					$role = get_role($key);?>
					<input type="checkbox" name="manage_roles[]" value="<?php echo $key?>" <?php if($role->has_cap('hostelpro_manage')) echo 'checked';?>> <?php _e($role->name, 'hostelpro')?> &nbsp;
				<?php endforeach;?></p>
				<p><a href="admin.php?page=hostelpro_roles" target="_blank"><?php _e('Fine-tune these settings', 'bftpro');?></a></p>	
				<p><input type="submit" value="<?php _e('Save Role Management Settings', 'hostelpro')?>" name="role_settings"></p>
			</div>
						<?php wp_nonce_field('role_settings');?>
		</form>
	<?php endif;?>	
	
	<h2><?php _e('Conversion Tracking','hostelpro')?></h2>
	<form method="post" class="hostelpro-form">
			<div class="postbox hostelpro-box">
				<p><?php printf(__('If you are using conversion tracking, for example from Google Adwords, you need to add the "onclick" JavaScript code here. With Google Adwords this code would be "%s" (<a href="%s" target="_blank">more info</a>).<br> If any scripts need to be added to your page HTML you still need to do it in your theme.', 'hostelpro'), 'goog_report_conversion()', 'http://blog.calendarscripts.info/hostel-pro-conversion-tracking-with-google-adwords/')?></p>
				<p><label><?php _e('Code to evaluate on booking:', 'hostelpro')?></label> <textarea rows="1" name="convtrack_code" cols="40"><?php echo stripslashes(get_option('wphostel_convtrack_code'));?></textarea></p>
				<p><?php _e('Note that the function will be called when the booking form is submitted successfully. It will not wait for online payments to be verified.');?></p>
				<input type="submit" value="<?php _e('Save Conversion Tracking Code', 'hostelpro')?>" name="convtrack_settings">
			</div>
		</form>
	
	
	<h2><?php _e('Datepicker Localization and Theming','hostelpro')?></h2>
		<form method="post" class="hostelpro-form">
			<div class="postbox hostelpro-box">
				<p><?php printf(__('Here you can specify localization and theme files for your datepicker. Please do read <a href="%s" target="_blank">this article</a> for more information.', 'hostelpro'), 'http://blog.calendarscripts.info/localization-and-styling-of-the-datepicker-in-hostelpro/')?></p>
				<p><label><?php _e('Localization  file URL:', 'hostelpro')?></label> <input type="text" name="locale_url" value="<?php echo get_option('wphostel_locale_url');?>" size="80"></p>
				<p><label><?php _e('CSS Theme URL:', 'hostelpro')?></label> <input type="text" name="datepicker_css" value="<?php echo get_option('wphostel_datepicker_css');?>" size="80"></p>
				<input type="submit" value="<?php _e('Save Datepicker Settings', 'hostelpro')?>" name="datepicker_settings">
			</div>
		</form>
		
	<?php if($hostel_installed):?>
		<h2><?php _e('Copy Settings from Hostel','hostelpro')?></h2>
		<form method="post" class="hostelpro-form">
			<div class="postbox hostelpro-box">
				<p><?php _e('You have the free Hostel plugin installed. Do you want to copy the global settings from it? (Rooms and booking data does not need to be copied - it is shared between both plugins).', 'hostelpro')?></p>
				<p><input type="checkbox" name="convert_shortcodes" value="1"> <?php _e('When copying settings also convert any Hostel shortcodes used in the site to Hostel PRO shortcodes', 'hostelpro')?></p>
				<input type="submit" value="<?php _e('Copy settings', 'hostelpro')?>" name="copy_settings">
			</div>
		</form>	
	<?php endif;?>
</div>	

<script type="text/javascript" >
function changeBookingMode(val) {
	jQuery('#HostelPROPaypal').hide();
	jQuery('#HostelPROStripe').hide();
	jQuery('#HostelPROPayment').hide();
	jQuery('#HostelPROMinStay').hide();
	if(val=='paypal') jQuery('#HostelPROPaypal').show();
	if(val=='stripe') jQuery('#HostelPROStripe').show();
	if(val=='paypal' || val == 'manual' || val == 'stripe') {
		jQuery('#HostelPROMinStay').show();
		jQuery('#HostelPROPayment').show();
	}
}

function hostelPROChangeCurrency(val) {
	if(val) {
		jQuery('#customCurrency').hide();
		jQuery('#cashCurrencyVal').html(val);
	}
	else {
		jQuery('#customCurrency').show();
		jQuery('#cashCurrencyVal').html(jQuery('#customCurrency').val());
	}
}
</script>