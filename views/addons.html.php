<style type="text/css">
<?php hostelpro_resp_table_css(600);?>
</style>

<div class="wrap">
	<h1><?php _e('Manage Addon Services', 'hostelpro')?></h1>
	
	<p><?php _e('Here you can add services like bike rentals and car rentals, laundry, breakfast, extra beds etc. They will appear on the booking form and your guests will be able to add them to their booking. Each addon service can have fixed price, price per person, price per day, or combined.', 'hostelpro')?></p>
	
	<p><a href="admin.php?page=hostelpro_addons&do=add"><?php _e('Create addon service', 'hostelpro')?></a></p>
	
	<?php if(sizeof($addons)):?>
		<table class="widefat hostelpro-table">
			<thead>
				<tr><th><?php _e('Service', 'hostelpro')?></th><th><?php _e('Room', 'hostelpro')?></th><th><?php _e('Price', 'hostelpro')?></th><th><?php _e('Max. available', 'hostelpro')?></th>
				<th><?php _e('Edit / Delete', 'hostelpro')?></th></tr>
			</thead>
			<tbody>			
			<?php foreach($addons as $addon):
			   if(HOSTELPRO_NO_DECIMALS) $addon->price = hostelpro_number_format($addon->price);
				$class = ('alternate' == @$class) ? '' : 'alternate';?>
				<tr class="<?php echo $class?>"><td><?php echo stripslashes($addon->name)?></td>
				<td><?php echo $addon->room_id ? stripslashes($addon->room_title) : __('All rooms', 'hostelpro');?></td>
				<td><?php echo sprintf(__('%s %s', 'hostelpro'), HOSTELPRO_CURRENCY, $addon->price);
				if($addon->per_person) echo ' ' . __('per person');
				if($addon->per_day) echo ' ' . __('per day');?></td>
				<td><?php echo $addon->max_available ? $addon->max_available : __('Unlimited', 'hostelpro')?></td>
				<td><a href="admin.php?page=hostelpro_addons&do=edit&id=<?php echo $addon->id?>"><?php _e('Edit', 'hostelpro')?></a>
			 | <a href="#" onclick="HostelPROConfirmDelAddon(<?php echo $addon->id?>);return false;"><?php _e('Delete', 'hostelpro')?></a></td></tr>
			<?php endforeach;?>
			</tbody>
		</table>
	<?php endif;?>
</div>

<script type="text/javascript" >
function HostelPROConfirmDelAddon(id) {
	if(confirm("<?php _e('Are you sure?', 'hostelpro')?>")) {
		window.location = 'admin.php?page=hostelpro_addons&del=1&id=' + id;
	}
}
<?php hostelpro_resp_table_js();?>
</script>