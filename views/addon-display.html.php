<?php 
if(HOSTELPRO_NO_DECIMALS) $addon->price = hostelpro_number_format($addon->price); 
if($show_label):?>
<div><?php printf(__('%s - %s%s %s', 'hostelpro'), stripslashes($addon->name), HOSTELPRO_CURRENCY, $addon->price, $priceinfo);?>
<?php endif;?>

<?php if($addon->per_person):?>
	<input type="checkbox" name="addon-<?php echo $addon->id?>" value="1" <?php if(!empty($current_addons[$addon->id])) echo 'checked'?>>
<?php else:
	// not per person so user can choose the number. If there is no max, display checkbox. Otherwise display dropd-down
	if($addon->max_available):?>
		<select name="addon-<?php echo $addon->id?>">
			<option value="0">0</option>
			<?php for($i=1; $i <= $addon->max_available; $i++):
				if(!empty($current_addons[$addon->id]) and $current_addons[$addon->id]==$i) $selected = 'selected';
				else $selected='';?>
				<option value="<?php echo $i?>" <?php echo $selected?>><?php echo $i?></option>
			<?php endfor;?>
		</select>
	<?php else:?>
		<input type="text" size="4" name="addon-<?php echo $addon->id?>" value="<?php echo @$current_addons[$addon->id]?>">		
<?php endif; 
endif;

if(!empty($addon->description)) echo "<div class='hostelpro-addon-description'>".stripslashes(nl2br($addon->description))."</div>";?>	
<?php if($show_label):?>
</div>
<?php endif;?>