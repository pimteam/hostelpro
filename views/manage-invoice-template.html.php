<div class="wrap">
	<h1><?php _e('Manage Invoice Template', 'hostelpro')?></h1>
	
	<p><?php _e('Here you can edit the design of the template used to generate invoices. The following variables can be used:', 'hostelpro')?> <br>
	{{client-name}}, {{invoice-num}}, {{invoice-date}}, {{currency}}, {{amount}}, {{amount-paid}}, {{amount-due}}, {{num-beds}}, {{room-type}}, {{room-name}},
	{{from-date}}, {{to-date}}, {{addons}}, {{num-days}}</p>
	
	<form method="post">
		<p><textarea rows="30" cols="120" name="template"><?php echo $template;?></textarea></p>
		
		<p><input type="submit" value="<?php _e('Save Design', 'hostelpro');?>"></p>
		<input type="hidden" name="ok" value="1">
	</form>
</div>	