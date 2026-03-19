<div class="wrap">
	<h1><?php _e('Calendar Overview of All Bookings', 'hostelpro');?></h1>
	
	<p><?php _e('The table below shows all rooms in your property and their availability for the given day. The date will be shown green if the room has any availability. For dorm rooms this means that even one available bed will show the date green.', 'hostelpro');?></p>
	
	<?php if(!$in_shortcode):?>
	<p><?php _e('Shortcode to publish this page:', 'hostelpro');?> <input type="text" value="[hostelpro-calendar-overview]" size="20" readonly="readonly" onclick="this.select();"></p>
	<?php endif;?>
	<div style="overflow-x:auto;">
	<table class="widefat hostelpro-calendar-overview" style="border-spacing: 1px;">
		<tr><th width="100"><a href="<?php echo $target_url?>&month=<?php echo $prev_month?>&y=<?php echo $prev_year?>">&lt;&lt;</a> <?php echo date_i18n('M Y', mktime(0, 0, 0, intval($month), 1, $year));?> <a href="<?php echo $target_url?>&month=<?php echo $next_month?>&y=<?php echo $next_year?>">&gt;&gt;</a></th>
		<?php for($i=1; $i <= $num_days; $i++):?>
		<td width="50"><?php echo $i?></td>
		<?php endfor;?></tr>
		<?php foreach($rooms as $room):
			$class = ('alternate' == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>">
				 <td><?php echo stripslashes($room['title'])?></td>
				 <?php for($i=0; $i < $num_days; $i++):?>
				 <td style='background-color:<?php echo $room['days'][$i]['available_beds'] ? '#55FF55' : 'red';?>;text-align:center;'><?php 
				 	echo substr(date('l', mktime(0,0,0, intval($month), $i+1, $year)),0,2)?><br>
				 	<?php echo $room['days'][$i]['available_beds'];?></td>
				 <?php endfor;?>		
			</tr>
		<?php endforeach;?>
	</table>
	</div>
</div>