<div class="wrap">
	
	<h1><?php printf(__('Booking calendar for room "%s"', 'hostelpro'), stripslashes($room->title));?></h1>
	
	<br>
	
	<!-- This is admin-view calendar that shows the bookings made for a given room -->
	<div id="hostelproRoomCalendarWrap">
		<div id="hostelproRoomCalendar" align="center"></div>	
	</div>	
	
	<?php foreach($month_divs as $key => $div):?>
		<div id="monDiv_<?php echo $key?>" style='display:<?php echo (date('Y') .'-'. intval(date('m')) == $key) ? 'block' : 'none';?>' class="hostelpro-month-div">
			<ol>
			<?php foreach($div['bookings'] as $booking):?>
				<li><?php if($booking->is_static): printf(__('The room is unavailable from %s to %s', 'hostelpro'), date_i18n($dateformat, strtotime($booking->from_date)), date_i18n($dateformat, strtotime($booking->to_date)));
				   else: printf(__('Booking ID: %d from %s (%s) - from %s to %s (<a href="admin.php?page=hostelpro_bookings&do=edit&id=%d&type=upcoming" target="_blank">view/edit</a>)', 'hostelpro'), 
					$booking->id, stripslashes($booking->contact_name), $booking->contact_email, date_i18n($dateformat, strtotime($booking->from_date)), date_i18n($dateformat, strtotime($booking->to_date)), $booking->id);
					endif;?></li>
			<?php endforeach;?>
			</ol>
		</div>
	<?php endforeach;?>
	
	<p align="center"><a href="#" onclick="window.close();"><?php _e('Close popup', 'hostelpro');?></a></p>

</div>

<script type="text/javascript">	
  jQuery(function() {  	 	
    jQuery( "#hostelproRoomCalendar" ).datepicker({
    	numberOfMonths: 1,	
    	yearRange: "<?php echo $yearfrom?>:<?php echo $yearto?>",
    	minDate: "-1y",   
    	maxDate: "+<?php echo HOSTELPRO_MAX_DATE?>",
    	dateFormat : '<?php echo dateformat_PHP_to_jQueryUI(get_option('date_format'));?>',        
      altFormat : "mm/dd/yy",	
      altField: "#hostelpro-alternate",
    	beforeShowDay: function(date) {
    		var bookedDates = [<?php foreach($udates as $cnt=>$udate):
			if($cnt>0) echo ", ";
				echo '"'.$udate.'"';	
			endforeach;?>];
			var selDate = date;
			result = hostelPROUnavailable(date, bookedDates);
			if(!result[0]) return result; // don't check further the unavailable dates			
			// else just return true
			return [true];
    	},	
		
		onSelect: function(date) {
			// hostelPROSelectDate(date, <?php echo $room->id?>)
		},
		
		onChangeMonthYear(year, month, inst) {
			jQuery('.hostelpro-month-div').hide();			
			jQuery('#monDiv_' + year + '-' + month).show();
		}  
		
    });
});
</script>