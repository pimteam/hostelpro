<style type="text/css">
<?php hostelpro_resp_table_css(800);?>
</style>

<div class="wrap">
	<h1><?php _e('Manage Discounts &amp; Surcharges', 'hostelpro')?></h1>
	
	<p><a href="admin.php?page=hostelpro_discounts&action=add"><?php _e('Create a discount', 'hostelpro')?></a>
	| <a href="admin.php?page=hostelpro_discounts&action=add&type=surcharge"><?php _e('Create a surcharge', 'hostelpro')?></a></p>
	
	<?php if(empty($discounts)):?>
		<p><?php _e("There are no discounts or surcharges yet.", 'hsotelpro')?></p></div>
	<?php return true;
	endif;?>	
	
	<table class="widefat hostelpro-table">
		<thead>
			<tr><th><?php _e('Name', 'discount')?></th><th><?php _e('Type', 'hostelpro')?></th><th><?php _e('Date condition', 'hostelpro')?></th>
				<th><?php _e('Weekdays', 'hostelpro')?></th><th><?php _e('Total price condition', 'hostelpro')?></th><th><?php _e('Coupon code', 'hostelpro')?></th>
				<th><?php _e('Discount / Surcharge', 'hostelpro')?></th><th><?php _e('Room', 'hostelpro')?></th>
				<th><?php _e('Edit/Delete', 'hostelpro')?></th></tr>
		</thead>
		<tbody>		
			<?php foreach($discounts as $discount):
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>"><td><?php echo stripslashes($discount->name);?></td>
				<td><?php echo $discount->disc_type == 'surcharge' ? __('Surcharge', 'hostelpro') : __('Discount', 'hostelpro')?></td>
				<td><?php echo $discount->date_condition ? sprintf(__('From %s to %s', 'hostelpro'), date_i18n($date_format, strtotime($discount->date_from)), date_i18n($date_format, strtotime($discount->date_to))) : __('Any dates', 'hostelpro');?></td>
				<td><?php echo $_discount->prettify($discount, 'weekdays');?></td>
				<td><?php echo empty($discount->min_price) ? __('None', 'hostelpro') : sprintf(__('%1$s %2$s', 'hostelpro'), HOSTELPRO_CURRENCY, $discount->min_price);?></td>
				<td><?php echo $discount->coupon_condition ? $discount->coupon : __('None', 'hostelpro')?></td>
				<td><?php echo ($discount->discount_type == 'amount') ? sprintf(__('%1$s %2$s', 'hostelpro'), HOSTELPRO_CURRENCY, $discount->discount_value) : $discount->discount_value.'%'?></td>
				<td><?php echo $discount->room ? stripslashes($discount->room) : __('All rooms', 'hostelpro')?></td>
				<td><a href="admin.php?page=hostelpro_discounts&action=edit&id=<?php echo $discount->id?>"><?php _e('Edit', 'hostelpro')?></a>
				|
				<a href="#" onclick="hostelPRODelDiscount(<?php echo $discount->id?>);return false;"><?php _e('Delete', 'hostelpro')?></a></td></tr>
			<?php endforeach;?>
		</tbody>		
	</table>
</div>

<script type="text/javascript" >
function hostelPRODelDiscount(id) {
	if(confirm("<?php _e('Are you sure?', 'hostelpro')?>")) {
		window.location = 'admin.php?page=hostelpro_discounts&del=1&id=' + id;
	}
}

<?php hostelpro_resp_table_js();?>
</script>