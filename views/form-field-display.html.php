<?php foreach($fields as $field):
   if(empty($field)) continue;   
	// $nolabel is set as true by the "visual form" shortcode
	if(!empty($autop)) echo "<p>"; // this is when called in admin
	if($field->ftype!='checkbox' and empty($nolabel)) echo "<div><label>".($field->is_required?'*':'')."{$field->label}:</label> ";
	switch($field->ftype):
		case 'textfield':	?>
			<input type="text" name="field_<?php echo $field->id?>" value="<?php echo @$booking->fields["field_".$field->id]?>">
		<?php	break;		
		case 'textarea':?>
			<textarea name="field_<?php echo $field->id?>"><?php echo stripslashes(@$booking->fields["field_".$field->id])?></textarea>
		<?php break;		
		case 'dropdown':
			$vals=explode("\n",$field->fvalues);	?>
			<select name="field_<?php echo $field->id?>">
			<?php foreach($vals as $val):			
				$val=trim($val);			
				if($val==@$booking->fields["field_".$field->id]) $selected='selected';
				else $selected='';	
				echo "<option value=\"$val\" $selected>$val</option>";
			endforeach; ?>
			</select>
		<?php	break;		
		case 'radio':
			$vals=explode("\n",$field->fvalues);			
			foreach($vals as $vct=>$val):			
				$val=trim($val);				
				if(trim($val)==@$booking->fields["field_".$field->id] or (empty($booking->fields["field_".$field->id]) and $vct==0)) $checked='checked';
				else $checked='';
				echo " <input type='radio' name='field_{$field->id}' value='$val' $checked> $val ";
			endforeach;			
		break;	
		case 'date':
			echo '<input type="text" value="'.@$booking->fields['field_'.$field->id].'" class="hostelproDatePicker" id="hostelproCustomDateField'.$field->id.'_'.$shortcode_id.'">';
			echo '<input type="hidden" name="field_'.$field->id.'" id="alt_hostelproCustomDateField'.$field->id.'_'.$shortcode_id.'">';
		break;	
		case 'checkbox':?>
			<?php if(empty($nolabel)):?><div><label><?php endif;?><input type="checkbox" name="field_<?=$field->id?>" <?php if(@$booking->fields["field_".$field->id]) echo "checked"?> value='1'> <?php if(empty($nolabel)): echo ($field->is_required?'*':'').$field->label.'</label></div>'; endif;?> 
		<?php break;
	endswitch;
	if($field->ftype!='checkbox' and empty($nolabel)) echo "</div>";
	if($field->is_required) echo "<input type='hidden' name='required_fields[]' value='field_{$field->id}'>";
	if(!empty($autop)) echo "</p>"; // this is when called in admin
endforeach;?>