<style type="text/css">
<?php hostelpro_resp_table_css(800);?>
</style>

<h1><?php _e('Manage Rooms', 'hostelpro')?></h1>

<div class="wrap">
	<p><a href="admin.php?page=hostelpro_rooms&action=add"><?php _e('Click here to add room', 'hostelpro')?></a></p>
	
	<?php if(!sizeof($rooms)):?>
		<p><?php _e('You have not added any rooms yet.', 'hostelpro')?></p>		
	<?php echo "</div>"; 
	return false;
	endif?>
	
	<table class="widefat hostelpro-table">
		<thead>
			<tr><th><?php _e('Room title', 'hostelpro')?></th><th><?php _e('Room type', 'hostelpro')?></th><th><?php _e('Num beds', 'hostelpro')?></th>
			<th><?php _e('Bathroom', 'hostelpro')?></th><th><?php _e('Price', 'hostelpro')?></th><th><?php _e('Book button shortcode', 'hostelpro')?></th><th><?php _e('Action', 'hostelpro')?></th></tr>
		</thead>	
		<tbody>
		<?php foreach($rooms as $room):
		 if(HOSTELPRO_NO_DECIMALS) $room->price = hostelpro_number_format($room->price);	
		 $class = ('alternate' == @$class) ? '' : 'alternate';?>
			<tr class="<?php echo $class?>"><td><?php echo stripslashes($room->title)?></td> <td><?php echo $_room->prettify('rtype', $room->rtype, $room);?></td>
			<td><?php echo $room->beds?></td><td><?php echo $_room->prettify('bathroom', $room->bathroom)?></td> <td><?php echo HOSTELPRO_CURRENCY.' '.$room->price?><br><?php echo $_room->prettify('price_type', $room->price_type);?><br>
			<a href="#" onclick="HostelPROloadRoomBookingCalendar(<?php echo $room->id?>);return false;"><?php _e('View bookings calendar', 'hostelpro');?></a></td>
			<td><input type="text" value="[hostelpro-book <?php echo $room->id?>]" size="15" onclick="this.select();" readonly><br>
			<a href="admin.php?page=hostelpro_room_calendar&id=<?php echo $room->id?>"><?php _e('Create calendar', 'hostelpro')?></a></td>
			<td><a href="admin.php?page=hostelpro_rooms&action=edit&id=<?php echo $room->id?>"><?php _e('Edit', 'hostelpro')?></a> | <a href="#" onclick="HostelPRODeleteRoom(<?php echo $room->id?>);return false;"><?php _e('Delete', 'hostelpro')?></a>
			| <a href='#' onclick="jQuery('#iCal<?php echo $room->id?>').toggle();return false;"><?php _e('iCalendar', 'hostelpro');?></a>
				<div id="iCal<?php echo $room->id?>" style="display:none;">
					<?php _e('Get link:', 'hostelpro');?><br> <input type="text" value="<?php echo site_url("?hostelpro_ical=1&room_id=".$room->id);?>" onclick="this.select();" readonly="readonly" size="30">
					<br>
					<a href="<?php echo site_url("?hostelpro_ical=1&room_id=".$room->id."&download=1");?>"><?php _e('Download file', 'hostelpro');?></a>
				</div>			
			</td></tr>
		<?php endforeach;?>
		</tbody>
	</table>
	
	<p align="center"><?php if($offset > 0):?>
		<a href="admin.php?page=hostelpro_rooms&action=list&offset=<?php echo $offset - $page_limit?>"><?php _e('[previous page]', 'hostelpro')?></a>
		<?php endif;?> 
		<?php if($count > ($page_limit + $offset)):?>
			<a href="admin.php?page=hostelpro_rooms&action=list&offset=<?php echo $offset + $page_limit?>"><?php _e('[next page]', 'hostelpro')?></a>
		<?php endif;?>	
	</p>		
	
	<p><?php _e('When you place the booking shortcode a "Book" button will be automatically generated. Use it on a page where you have manually described your room with pictures etc. You can pass custom button text as second argument to the shortcode - like this:', 'hostelpro')?> [hostelpro-book 1 "Reserve room!"].</p>
</div>

<script type="text/javascript" >
function HostelPRODeleteRoom(id) {
	if(confirm("<?php _e('Are you sure?', 'hostelpro')?>")) {
		window.location='admin.php?page=hostelpro_rooms&action=delete&id='+id;
	}
}

function HostelPROloadRoomBookingCalendar(id) {
	// tb_show("<?php _e('Room bookings calendar', 'hostelpro')?>", "admin-ajax.php?action=hostelpro_ajax&type=load_room_bookings&width=800&height=600&id="+id, "admin-ajax.php");
    var width = 500;
    var height = 500;
    var toppx = (jQuery(window).height() / 2) - (height / 2);
    var leftpx = (jQuery(window).width() / 2) - (width / 2);
    window.open("admin.php?page=hostelpro_room_bookings&id=" + id, "popupWindow", "width=" + width + ",height=" + height + ",scrollbars=yes,left=" + leftpx + "top="+toppx);
}
<?php hostelpro_resp_table_js();?>

</script>