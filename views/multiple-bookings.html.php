<?php include(HOSTELPRO_PATH . '/views/partial/multiple-table.html.php');?>
<form method="post">
<p align="center"><?php printf(__('Grand total: %1$s%2$s', 'hostelpro'), HOSTELPRO_CURRENCY, '<span id="hostelPROGrandTotal">'.$grand_total.'</span>');?></p>
<p align="center"><input type="button" value="<?php _e('Confirm Reservation', 'watupro');?>" onclick="HostelPROMultiBook(this.form);"> <input type="button" value="<?php _e('Book More Rooms', 'hostelpro');?>" onclick="window.location.reload(true);"></p>
<input type="hidden" name="shortcode_id" value="<?php echo $_POST['shortcode_id']?>">
</form>