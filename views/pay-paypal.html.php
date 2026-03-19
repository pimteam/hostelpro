<p align="center"><?php _e('Booking ID:', 'hostelpro')?> <?php echo $bid?></p>
<p align="center"><?php _e('Amount due now:', 'hostelpro')?> <?php echo HOSTELPRO_CURRENCY.' '. $amount_now;?></p>
<?php if($full_cost > $cost):?>
	<p align="center"><?php _e('Amount due on arrival:', 'hostelpro')?> <?php echo HOSTELPRO_CURRENCY.' '. $amount_arrival;?></p>
<?php endif;
if(!empty($_POST['coupon']) and empty($discount)):?>
<p align="center"><b><?php _e('Invalid discount code entered. Discount not applied.', 'hostelpro');?></b></p>
<?php endif;
if($discount>0):?>
	<p align="center"><b><?php printf(__('%s discount applies!', 'hostelpro'), HOSTELPRO_CURRENCY.' '. number_format($discount,2,".",""))?></b></p>
<?php endif;?>

<form action="https://<?php echo $paypal_host;?>/cgi-bin/webscr" method="post">
	<p align="center">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="<?php echo get_option('hostelpro_paypal');?>">
		<input type="hidden" name="item_name" value="<?php printf(__('Booking for %s Room / %d', 'hostelpro'), $_room->prettify('rtype', $room->rtype, $room), $bid)?>">
		<input type="hidden" name="item_number" value="<?php echo $bid?>">
		<input type="hidden" name="amount" value="<?php echo number_format($cost,2,".","")?>">
		<input type="hidden" name="return" value="<?php echo $paypal_return;?>">
		<?php if(get_option('hostelpro_use_pdt') != 1):?><input type="hidden" name="notify_url" value="<?php echo site_url('?hostelpro=paypal&bid='.$bid)?>"><?php endif;?>
		<input type="hidden" name="no_shipping" value="1">
		<input type="hidden" name="no_note" value="1">
		<input type="hidden" name="currency_code" value="<?php echo HOSTELPRO_PAYMENT_CURRENCY;?>">
		<input type="hidden" name="lc" value="US">
		<input type="hidden" name="bn" value="PP-BuyNowBF">
		<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" onclick="this.form.action = 'https://www.paypal.com/cgi-bin/webscr'">
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
	</p>
	</form> 