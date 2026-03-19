<p align="center"><?php _e('Booking ID:', 'hostelpro')?> <?php echo $bid?></p>
<p align="center"><?php _e('Amount due now:', 'hostelpro')?> <?php echo HOSTELPRO_CURRENCY.' '. $amount_now;?></p>
<?php if($full_cost > $cost):?>
	<p align="center"><?php _e('Amount due on arrival:', 'hostelpro')?> <?php echo HOSTELPRO_CURRENCY.' '. $amount_arrival;?></p>
<?php endif;
if(!empty($_POST['coupon']) and empty($discount)):?>
<p align="center"><b><?php _e('Invalid discount code entered. Discount not applied.', 'hostelpro');?></b></p>
<?php endif;
if($discount>0):?>
	<p align="center"><b><?php printf(__('%s discount applies!', 'hostelpro'), HOSTELPRO_CURRENCY.' '. (HOSTELPRO_NO_DECIMALS ? number_format($discount) : number_format($discount,2,".","")))?></b></p>
<?php endif;?>

<form method="post">
		<p align="center">
	  <script src="https://checkout.stripe.com/v2/checkout.js" class="stripe-button"
	          data-key="<?php echo $stripe_public; ?>"
	          data-amount="<?php echo $amount_now * 100;?>" data-description="<?php printf(__('Booking for %s Room / %d', 'hostelpro'), $_room->prettify('rtype', $room->rtype, $room), $bid)?>" data-currency="<?php echo HOSTELPRO_PAYMENT_CURRENCY;?>"></script>
	<input type="hidden" name="hostelpro_stripe_pay" value="1">
	<input type="hidden" name="booking_id" value="<?php echo $bid?>">
	</p>
</form>