<h2><?php _e('Your Booking Summary', 'hostelpro');?></h2>

<table class="hostelpro hostelpro-front-bookings">
	<thead>
		<tr><th><?php _e('Room &amp; Details', 'hostelpro');?></th><th><?php _e('Cost', 'hostelpro');?></th></tr>
		<tbody>	
		<?php $grand_total = 0; 
		foreach($bookings as $booking):
			$grand_total += ($booking->amount_paid + $booking->amount_due); ?>
			<tr id="hostelPROFrontBooking<?php echo $booking->id?>">
				<td><?php echo stripslashes($booking->room_name);?> <br /> 
				<strong><?php echo date_i18n($dateformat, strtotime($booking->from_date)) . ' - '.date_i18n($dateformat, strtotime($booking->to_date));?></strong>
				<br />
				<?php 
					if($booking->extra_beds) printf(__('%d + %d beds', 'hostelpro'), $booking->beds, $booking->extra_beds);
					else printf(__('%d beds', 'hostelpro'), $booking->beds);
					if($booking->child_beds) echo ' ' . sprintf(__('(%d children)', 'hostelpro'), $booking->child_beds);
					if(!empty($booking->addon_text)) {
						echo '<p>'.apply_filters('hostelpro_content', $booking->addon_text).'</p>';
					}
				?>
				<br /><?php printf(__('%1$s / %2$s, phone: %3$s', 'hostelpro'), $booking->contact_name, $booking->contact_email, $booking->contact_phone);?></td>
				<td><?php echo HOSTELPRO_CURRENCY . ($booking->amount_paid + $booking->amount_due)?><?php if(empty($no_delete)):?>&nbsp;<a href="#" style="color:red;" onclick="HostelPRORemoveBooking(<?php echo $booking->id?>);return false;">&#127335;</a><?php endif;?> 
				<?php if($booking->discount > 0): echo '<br>'.sprintf(__('(%1$s%2$s discounts)', 'hostelpro'), HOSTELPRO_CURRENCY, $booking->discount); endif;?>
				<input type="hidden" class="hostelpro-booking-cost" value="<?php echo $booking->amount_paid + $booking->amount_due?>"></td>
			</tr>
		<?php endforeach;?>
		</tbody>	
	</thead>
</table>