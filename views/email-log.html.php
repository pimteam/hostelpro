<div class="wrap">
	<h1><?php _e('Email Log', 'hostelpro');?></h1>
	
	<p><?php _e('This page shows what booking notification emails have been sent on each date. The last column will show the message status, i.e. response from the mailing server. If emails are not delivered or you see errors there you should contact your hosting support.', 'hostelpro');?> </p>

	<div class="postbox wp-admin" style="padding:20px;">
		<form method="post">
			<p><label><?php _e('Log date:', 'hostelpro')?></label> <input type="text" name="date" class="hostelproDatePicker" value="<?php echo $date?>">
			<input type="submit" value="<?php _e('Show log', 'hostelpro')?>">
			&nbsp;			
			<?php _e('Automatically cleanup old logs after', 'hostelpro')?> <input type="text" size="4" name="cleanup_days" value="<?php echo $cleanup_raw_log?>"> <?php _e('days', 'hostelpro')?> <input type="submit" name="cleanup" value="<?php _e('Set Cleanup', 'hostelpro')?>"> </p>
		</form>		
		
		<?php if(!sizeof($emails)):?>
			<p><?php _e('No emails have been sent on the selected date.', 'hostelpro')?></p>
		<?php else:?>
			<table class="widefat">
				<tr><th><?php _e('Time', 'hostelpro')?></th><th><?php _e('Sender', 'hostelpro')?></th><th><?php _e('Receiver', 'hostelpro')?></th>
<th><?php _e('Subject', 'hostelpro')?></th><th><?php _e('Response from the mailing server', 'hostelpro')?></th></tr>
				<?php foreach($emails as $email):
					$class = ('alternate' == @$class) ? '' : 'alternate';?>
					<tr class="<?php echo $class?>"><td><?php echo date('H:i', strtotime($email->datetime))?></td>
					<td><?php echo htmlentities(stripslashes($email->sender))?></td>
					<td><?php echo stripslashes($email->receiver)?></td>
					<td><?php echo stripslashes($email->subject)?></td>
					<td><?php echo $email->status?></td></tr>
				<?php endforeach;?>
			</table>
		<?php endif;?>
	</div>
</div>	

<script type="text/javascript" >
jQuery(function(){
	jQuery('.hostelproDatePicker').datepicker({dateFormat: "yy-m-d"});
});
</script>