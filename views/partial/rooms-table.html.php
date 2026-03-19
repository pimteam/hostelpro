<table>
		<tr><?php if(!empty($show_titles)): $numcols++;?> <th><?php _e('Room', 'hostelpro');?></th><?php endif;?>
		<?php if(!empty($show_types)): $numcols++;?><th><?php _e('Room type', 'hostelpro')?></th><?php endif;?>
		<?php if(!empty($show_bathrooms)): $numcols++;?><th><?php _e('Bathroom', 'hostelpro')?></th><?php endif;?>
		<?php if(empty($hide_dates)):  
		if(empty($vertical_after) or $vertical_after >= $numdays):
		for($i=0; $i < $numdays; $i++):
			$curday_time = $datefrom_time + $i*24*3600;?>
			<th><?php echo date_i18n($dateformat, $curday_time);?></th>
		<?php endfor;
		else : // vertical display?>
			<th><?php _e('Room Availability', 'hostelpro')?></th>
		<?php endif; // end if vertical display	
		endif; // end if not hide dates?>
		<th><?php _e('Price', 'hostelpro')?></th><?php if($booking_mode != 'none'):?><th><?php _e('Book', 'hostelpro')?></th><?php endif;?></tr>
		
		<?php foreach($rooms as $room):			
			$can_book = true; ?>
			<tr>
			<?php if(!empty($show_titles)):?><td><?php echo stripslashes($room['title']);?></td><?php endif;?>		
			<?php if(!empty($show_types)):?><td><?php echo $_room->prettify('rtype', $room['rtype'], (object)$room)?></td><?php endif;?>
			<?php if(!empty($show_bathrooms)):?><td><?php echo $_room->prettify('bathroom', $room['bathroom'])?></td><?php endif;?>
			<?php if(empty($hide_dates)): 
			if(!empty($vertical_after) and $vertical_after < $numdays) echo "<td><table>"; 
			for($i=0; $i < $numdays; $i++):				
				$curday_time = $datefrom_time + $i*24*3600;
				if(empty($vertical_after) or $vertical_after >= $numdays) echo "<td>";
				else echo "<tr><td><b>".date_i18n($dateformat, $curday_time)."</b></td><td>";
				if(!$room['days'][$i]['available_beds']) $can_book = false;				
				 if($room['days'][$i]['available_beds']): 
					printf(__('%d beds', 'hostelpro'), $room['days'][$i]['available_beds']);					
					$discount = HostelPRODiscounts :: apply_discount($curday_time, $room['id'], $room['price'], $numdays);
					if(!empty($discount)): 
						if($discount > 0) echo "<br>".sprintf(__('%s discount!', 'hostelpro'), HOSTELPRO_CURRENCY.' '.$discount); 
						else echo "<br>".sprintf(__('+%s', 'hostelpro'), HOSTELPRO_CURRENCY.' '.abs($discount));
					endif;
				else: echo 'X'; endif;	
				if(empty($vertical_after) or $vertical_after >= $numdays) echo "</td>";
				else echo "</td></tr>";
			endfor;
			if(!empty($vertical_after) and $vertical_after < $numdays) echo "</table></td>";
			endif; // end if empty hide dates?>	
			<td><?php if(HOSTELPRO_NO_DECIMALS) $room['price'] = hostelpro_number_format($room['price']); 
			if($room['price_type'] == 'per-bed' and !empty($room['whole_dorm_price']) and $room['whole_dorm_price'] > 0) echo HOSTELPRO_CURRENCY.' '.$room['whole_dorm_price'].' <br>('.$_room->prettify('price_type', 'per-room').')<br>';
			echo HOSTELPRO_CURRENCY.' '.$room['price'].' <br>('.$_room->prettify('price_type', $room['price_type']).')';?></td>
			<?php if($booking_mode != 'none'):?><td align="center"><?php if($can_book):?>
				<form method="post">
				<input type="hidden" name="from_date" value="<?php echo $datefrom?>">
				<input type="hidden" name="to_date" value="<?php echo $dateto?>">
				<input type="hidden" name="room_id" value="<?php echo $room['id']?>">
				<input type="hidden" name="currently_setting" value="from">		
				<input type="hidden" name="action" value="hostelpro_ajax">
				<input type="hidden" name="type" value="load_booking_form">
				<input type="hidden" name="in_booking_mode" value="1">
				<input type="button" value="<?php _e('Book', 'hostelpro');?>" onclick="hostelPROLoadBooking(this.form, 'hostelPRORoomsTable<?php echo $shortcode_id?>');">
				</form>
			<?php else: _e('Not available', 'hostelpro');
			endif;?></td><?php endif;?></tr>
			<?php if(!empty($room['notes'])):?>
				<tr><td colspan="<?php echo $numdays + $numcols;?>"><?php echo apply_filters('hostelpro_content', stripslashes($room['notes']));?></td></tr>
			<?php endif;?>
			<?php if(!empty($show_descriptions) and !empty($room['description'])):?>
				<tr><td colspan="<?php echo $numdays + $numcols;?>"><?php echo apply_filters('hostelpro_content', wpautop(stripslashes($room['description'])));?></td></tr>
			<?php endif;?>
		<?php endforeach;?>
	</table>