<style type="text/css">
.hostelpro-charts {
	clear:both;
}
.hostelpro-chart {	
	max-width: 500px;
	float:left;
	padding: 20px;
}
</style>
<div class="wrap">
	<h1><?php _e('Reports and Charts', 'hostelpro');?></h1>
	
	<form method="get" action="admin.php" onsubmit="return validateHostelPROReports(this);">
		<input type="hidden" name="page" value="hostelpro_reports">
		<p><?php _e('From:', 'hostelpro')?> <input type="text" value="<?php echo date_i18n($dateformat, strtotime($start_date))?>" class="hostelproDatePicker" id="hostelPROFromDate">
		<input type="hidden" name="start_date" value="<?php echo $start_date?>" id="alt_hostelPROFromDate">
		<?php _e('To:', 'hostelpro')?> <input type="text" name="end_date" value="<?php echo date_i18n($dateformat, strtotime($end_date))?>" class="hostelproDatePicker" id="hostelPROToDate">
		<input type="hidden" name="end_date" value="<?php echo $end_date?>" id="alt_hostelPROToDate">
		<input type="submit" value="<?php _e('Reload Reports', 'hostelpro')?>"></p>
	</form>
	
	<div class="hostelpro-charts">
		<div class="hostelpro-chart">
			<h3><?php _e('Money earned per room', 'hostelpro')?></h3>		
			<span class="rooms-pie"><?php if(sizeof($bookings)):
			foreach($rooms as $cnt=>$room):
			if($cnt >0) echo ",";
				echo $room->money;
			endforeach;?></span>
			<p>&nbsp;</p>
			<table class="hostelpro-table">
				<?php foreach($rooms as $room):?>
					<tr><td width="20" bgcolor="<?php echo $room->color?>">&nbsp;</td><td><?php echo stripslashes($room->title)?></td><td><?php echo HOSTELPRO_CURRENCY.' '.$room->money?></td></tr>
				<?php endforeach;?>
			</table>		
			<?php else: _e('No data', 'hostelpro');
			endif;?>
		</div>
		
		<div class="hostelpro-chart">
			<h3><?php _e('Money earned per room type', 'hostelpro')?></h3>		
			<span class="types-pie"><?php if(sizeof($bookings)): 
			$i=0;
			foreach($types as $key=>$money):				
				if($i >0) echo ",";
				$i++;
				echo $money;
			endforeach;?></span>
			<p>&nbsp;</p>
			<table class="hostelpro-table">
				<?php $i=0; 
				foreach($types as $key=>$money):
					$color = $i ? "yellow" : "blue";
					$type = ($key == 'dorm') ? __('Dorm', 'hostelpro') : __('Private', 'hostelpro');
					$i++;?>
					<tr><td width="20" bgcolor="<?php echo $color?>">&nbsp;</td><td><?php echo $type?></td><td><?php echo HOSTELPRO_CURRENCY.' '.$money?></td></tr>
				<?php endforeach;?>
			</table>		
			<?php else: _e('No data', 'hostelpro');
			endif;?>
		</div>
		
		<div class="hostelpro-chart">
			<h3><?php _e('Occupancy rate per room', 'hostelpro')?></h3>		
			<span class="rooms-bar"><?php if(sizeof($bookings)):  
			foreach($orooms as $cnt=>$room):
			if($cnt >0) echo ",";
				echo $room->occupancy;
			endforeach;?></span>
			<p>&nbsp;</p>
			<table class="hostelpro-table">
				<?php foreach($orooms as $room):?>
					<tr><td width="20" bgcolor="<?php echo $room->color?>">&nbsp;</td><td><?php echo stripslashes($room->title)?></td><td><?php echo $room->occupancy.'%'?></td></tr>
				<?php endforeach;?>
			</table>		
			<?php else: _e('No data', 'hostelpro');
			endif;?>
		</div>
		
		<div class="hostelpro-chart">
			<h3><?php _e('Occupancy rate per room type', 'hostelpro')?></h3>		
			<span class="types-bar"><?php if(sizeof($bookings)):  
			$i=0;
			foreach($otypes as $key=>$occupancy):				
				if($i >0) echo ",";
				$i++;
				echo $occupancy;
			endforeach;?></span>
			<p>&nbsp;</p>
			<table class="hostelpro-table">
				<?php $i=0; 
				foreach($otypes as $key=>$occupancy):
					$color = $i ? "yellow" : "blue";
					$type = ($key == 'dorm') ? __('Dorm', 'hostelpro') : __('Private', 'hostelpro');
					$i++;?>
					<tr><td width="20" bgcolor="<?php echo $color?>">&nbsp;</td><td><?php echo $type?></td><td><?php echo $occupancy.'%';?></td></tr>
				<?php endforeach;?>
			</table>		
			<?php else: _e('No data', 'hostelpro');
			endif;?>
		</div>
	</div>
	
	<div class="hostelpro-charts">
		<div class="hostelpro-chart">
			<h2><?php _e('Money earned per day', 'hostelpro');?></h2>
			<?php if(sizeof($bookings)):?>
			<table class="widefat">
				<tr><th><?php _e('Date', 'hostelpro');?></th><th><?php _e('Income', 'hostelpro')?></th></tr>
				<?php foreach($report_dates as $date => $money):
					$class = ('alternate' == @$class) ? '' : 'alternate';?>
					<tr class="<?php echo $class?>"><td><?php echo date_i18n($dateformat, strtotime($date))?></td>
					<td><?php echo HOSTELPRO_CURRENCY.' '.$money;?></td></tr>
				<?php endforeach;?>
			</table>
			<?php else: _e('No data', 'hostelpro');
			endif;?>
		</div>
		
		<div class="hostelpro-chart">
			<h2><?php _e('Other global stats', 'hostelpro')?></h2>
			
			<table class="widefat">
				<tr><td class="alternate"><?php _e('Total amount of bookings:', 'hostelpro')?></td><td><?php echo HOSTELPRO_CURRENCY.' '.$total_booked?></td></tr>
				<tr><td class="alternate"><?php _e('New bookings made in this period:', 'hostelpro')?></td><td><?php echo $no_bookings?></td></tr>
				<?php do_action('hostelpro_other_global_stats', $total_booked, $start_date, $end_date);?>
			</table>
		</div>
	</div>	
</div>

<script type="text/javascript" >
jQuery(document).ready(function() {
    jQuery('.hostelproDatePicker').datepicker({
    		dateFormat : '<?php echo dateformat_PHP_to_jQueryUI($dateformat);?>',
         altFormat : 'yy-mm-dd'
    });
    
    jQuery(".hostelproDatePicker").each(function (idx, el) { 
	    jQuery(this).datepicker("option", "altField", "#alt_" + jQuery(this).attr("id"));
    });
	
    <?php if(sizeof($bookings)):?>
    jQuery("span.rooms-pie").peity("pie", {
		fill: [<?php foreach($rooms as $cnt=>$room):
				if($cnt >0) echo ',';
				echo '"'.$room->color.'"';
				endforeach;?>],
		diameter: 160
	});
	jQuery("span.types-pie").peity("pie", {
		fill: ['blue', 'yellow'],
		diameter: 160
	});
	
	 jQuery("span.rooms-bar").peity("bar", {
		fill: [<?php foreach($rooms as $cnt=>$room):
				if($cnt >0) echo ',';
				echo '"'.$room->color.'"';
				endforeach;?>],
		diameter: 160,
		max: 100,
		height: 160,
		width: 150,
		gap: 5
	});
	
	jQuery("span.types-bar").peity("bar", {
		fill: ['blue', 'yellow'],
		diameter: 160,
		max: 100,
		height: 160,
		width: 150,
		gap: 5
	});
	<?php endif;?>
});

// do not allow more than 90 days
function validateHostelPROReports(frm) {
	var fromDate = new Date(frm.start_date.value);
	var toDate = new Date(frm.end_date.value);
	var oneDay = 24*60*60*1000;
	var diffDays = Math.round(Math.abs((fromDate.getTime() - toDate.getTime())/(oneDay)));
	
	if(diffDays > 90) {
		alert("<?php _e('You can run reports for maximum 90 days.','hostelpro')?>");		
		return false;
	}
	
	return true;
}
</script>