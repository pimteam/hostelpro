<div class="wrap">
	<h1><?php _e('Room Availability Calendar', 'hostelpro')?></h1>
	
	<p><?php printf(__('Here you can configure and get shortcode for room availability calendar. You can then publish the calendar on a page describing your room. Feel free to also add the <a href="%s">booking button shortcode</a> or even embed a <a href="%s">booking form</a> on the same page', 'hostelpro'), 'admin.php?page=hostelpro_rooms', 'admin.php?page=hostelpro_booking_form&room_id='.$room->id)?></p>
	
	<p><?php _e('Selected room:', 'hostelpro')?> <b><?php echo $room->title?></b></p>
	
	<p><a href="admin.php?page=hostelpro_rooms"><?php _e('Back to manage rooms', 'hostelpro')?></a></p>

	<h2><?php _e('Configure Calendar', 'hostelpro')?></h2>	
	<form method="post">
		<p><?php _e('Show calendar for:', 'hostelpro')?> &nbsp; <input type="radio" name="months" value="1" <?php if($months == 1):?>checked="true"<?php endif;?>> <?php _e('Current month', 'hostelpro')?>
		<input type="radio" name="months" value="3" <?php if($months == 3):?>checked="true"<?php endif;?>> <?php _e('Three months', 'hostelpro')?></p>
		<p><input type="checkbox" name="bookable" value="1" <?php if(!empty($_POST['bookable'])) echo 'checked';?> onclick="this.checked ? jQuery('#oneDayMessage').show() : jQuery('#oneDayMessage').hide();"> <?php _e('Bookable - if selected, will also display a booking button. Otherwise only shows which dates are available.', 'hostelpro')?></p>
		<p id="oneDayMessage" style='display:<?php echo empty($_POST['bookable']) ? 'none' : 'block';?>'>
			<?php _e('If the user starts clicking on a date right before a booked date the booking form will automatically load for one-day stay. You can choose to prompt the user about this and enter a confirmation message below. If you enter no message, the guest will not be asked.', 'hostelpro');?> <br />
			<input type="text" name="confirm_single_day" size="100" value="<?php echo empty($_POST['confirm_single_day']) ? '' : stripslashes($_POST['confirm_single_day']); ?>">
		</p>
		<p><input type="submit" value="<?php _e('Update shortcode', 'hostelpro')?>"</p>
	</form>
	
	<p><?php _e('Get shortcode:', 'hostelpro')?> <input type="text" size="100" readonly="readonly" onclick="this.select()" value='[hostelpro-calendar room_id="<?php echo $room->id?>" months="<?php echo $months?>"<?php if(!empty($_POST['bookable'])) echo ' bookable="true"'?><?php if(!empty($_POST['confirm_single_day'])) echo ' confirm_single_day="'.$_POST['confirm_single_day'].'"'?>]'></p>
</div>