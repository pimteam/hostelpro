<div id="hostelproRoomCalendarWrap<?php echo $shortcode_id?>">
	<div id="hostelproRoomCalendar<?php echo $room->id?>" style="overflow-x: auto; overflow-y: hidden;"></div>
	<form method="post" action="<?php echo $permalink;?>" id="hostelPROBookCalendarForm<?php echo $room->id?>">
		<input type="hidden" name="room_id" value="<?php echo $room->id?>">
		<input type="hidden" name="in_booking_mode" value="1">
		<input type="hidden" name="from_date" value="<?php echo date('Y-m-d', strtotime('tomorrow'));?>">
		<input type="hidden" name="to_date" value="<?php echo date('Y-m-d', $default_dateto_diff);?>">
		<input type="hidden" name="currently_setting" value="from">		
		<input type="hidden" name="action" value="hostelpro_ajax">
		<input type="hidden" name="type" value="load_booking_form">
		<input type="hidden" id="hostelpro-alternate<?php echo $room->id?>">
		<input type="hidden" name="unavailable" value="<?php foreach($udates as $cnt=>$udate):
			if($cnt>0) echo ',';
				echo $udate;	
			endforeach;?>">
	<?php if($bookable):?>			
		<p><?php _e('From:', 'hostelpro')?> <span id="hostelPROFromDateDisplay<?php echo $room->id?>"><?php echo date_i18n($dateformat, strtotime('tomorrow'))?></span><br>
		<?php _e('To:', 'hostelpro')?> <span id="hostelPROToDateDisplay<?php echo $room->id?>"><?php echo date_i18n($dateformat, $default_dateto_diff)?></span><br></p>
		<p class="hostelpro-book-buttton-wrap"><input type="button" value="<?php echo $text?>" onclick="hostelPROLoadBooking(this.form, 'hostelproRoomCalendarWrap<?php echo $shortcode_id?>');"></p>
		<input type="hidden" id="hostelPROBookSingleDayMsg<?php echo $room->id?>" value="<?php echo empty($atts['confirm_single_day']) ? '' : $atts['confirm_single_day'];?>">
	<?php endif;?>	
	</form>
</div>	

<script type="text/javascript">	
	function setNoMonths(months) {	
	   let elem = document.getElementById('hostelproRoomCalendarWrap<?php echo $shortcode_id?>')
		let w = elem.clientWidth;
		
 		if(months == 1) return months;
 		
 		if(months == 3) {
 			return (w < 760)  ? [3,1] : 3;
 		}
 		
 		// although we don't offer it as option, it could be useful to allow typing 12 in the shortcode
 		// other settings are ignored and default to 12 		
 		if(w < 480)  return [12,1];
	  	if(w < 760)  return [6,2];	  
	  	return [4,3];
	}

  jQuery(function() {
    jQuery( "#hostelproRoomCalendar<?php echo $room->id?>" ).datepicker({
    	numberOfMonths: setNoMonths(<?php echo $months;?>),	
    	yearRange: "<?php echo $yearfrom?>:<?php echo $yearto?>",
    	maxDate: "+<?php echo HOSTELPRO_MAX_DATE?>",
    	minDate: "0",   
    	dateFormat : '<?php echo dateformat_PHP_to_jQueryUI(get_option('date_format'));?>',        
      altFormat : "mm/dd/yy",	
      altField: "#hostelpro-alternate<?php echo $room->id?>",
    	beforeShowDay: function(date) {
    		var unavailableDates = [<?php foreach($udates as $cnt=>$udate):
			if($cnt>0) echo ", ";
				echo '"'.$udate.'"';	
			endforeach;?>];
			var selDate = date;
			result = hostelPROUnavailable(date, unavailableDates);
			if(!result[0]) return result; // don't check further the unavailable dates			
			// if the date is not unavailable, let's see do we need to color it
			var fromDate = jQuery('#hostelPROBookCalendarForm<?php echo $room->id?> input[name=from_date]').val();
			var toDate = jQuery('#hostelPROBookCalendarForm<?php echo $room->id?> input[name=to_date]').val();
			var fromParts = fromDate.split('-');
			fromDate = new Date(fromParts[0], fromParts[1]-1, fromParts[2]);
			var toParts = toDate.split('-');
			toDate = new Date(toParts[0], toParts[1]-1, toParts[2]);			
			if(date.valueOf() >= fromDate.valueOf() && date.valueOf() <= toDate.valueOf()) {
				// return true with highlighted class
				 return [true, 'hostelpro-highlight', null];
			}
			
			// else just return true
			return [true, '', null];
    	},	
		
		onSelect: function(date) {
			hostelPROSelectDate(date, <?php echo $room->id?>, '<?php echo $shortcode_id?>');
		}
		
    });
    
    
});
</script>

<style type="text/css">
.ui-datepicker td span,
.ui-datepicker td a {
  padding-bottom: 1em;
}

.ui-datepicker td[title]::after {
  content: attr(title);
  display: block;
  position: relative;
  font-size: .8em;
  height: 1.25em;
  margin-top: -1.25em;
  text-align: right;
  padding-right: .25em;
}
</style>