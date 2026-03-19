<div class="wrap">
	<h1><?php _e('Hostel PRO for WordPress', 'hostelpro')?></h1>
	
	<p><?php _e('This is a Premium plugin for managing hostels, BNB sites, and small hotel sites. You get an area where to manage your available rooms and prices, and the bookings made by visitors. Start with the main settings page to set up your booking mode, currency etc.', 'hostelpro')?></p>
	
	<h2><?php _e('Getting Started', 'hostelpro')?></h2>
	
	<ol>
		<li><?php printf(__('Visit the <a href="%s">settings page</a> to set up booking methods, booking form URL etc.', 'hostelpro'),'admin.php?page=hostelpro_options')?></li>
		<li><?php _e('Create your rooms.', 'hostelpro')?></li>		
		<li><?php _e('If there are dates when your hostel does not work add them in the "Unavailable dates" page.', 'hostelpro')?></li>
		<li><?php _e('Publish some of the shortcodes below, or the room calendar shortcodes from the "Manage Rooms" page so your guests can see your room availability.', 'hostelpro')?></li>
		<li><?php _e('You are ready to get accept guests!', 'hostelpro')?></li>
	</ol>
	
	<h2><?php _e('Shortcodes', 'hostelpro')?></h2>
	
	<ol>
		<li><input type="text" value="[hostelpro-list]" readonly onclick="this.select();"> <?php _e('will display a table with your available rooms. A date selector on the top lets the user choose dates of their visit and then the rooms list is updated. If you have enabled booking in your Hostel settings page, the table will also show "Book" button when appropriate. The button will automaically load the booking form.', 'hostelpro')?><br>
		<?php _e('This shorcode accepts arguments which allow you to specify whether room title, description, type or bathroom information should be shown in the table. Each argument can be set to 0 (means "do not show") and 1 (means "show"):', 'hostelpro')?>
			<ol>
				<li>show_table - <?php _e('Show / hide the table initially when loading the page (it will always be shown after clicking the availability button). Defaults to 1.', 'hostelpro')?></li>
				<li>form_horizontal - <?php _e('Set to 1 if you want the availability checking form to be displayed horizontally. Defaults to 0 (vertical form).', 'hostelpro')?></li>
				<li>show_titles - <?php _e('Show / hide room titles. Defaults to 0.', 'hostelpro')?></li>
				<li>show_descriptions - <?php _e('Show / hide room descriptions. Defaults to 0.', 'hostelpro')?></li>
				<li>show_types - <?php _e('Show / hide room types. Defaults to 1.', 'hostelpro')?></li>
				<li>show_bathrooms - <?php _e('Show / hide bahtroom information. Defaults to 1.', 'hostelpro')?></li>
				<li>group_rooms - <?php _e('This will automatically group rooms of same type / gender / number of beds / price. For example if you have 3 mixed dorm rooms with 8 beds and same price, only one will be shown. In cases when there is different number of available rooms, our smart logic engine will choose the one with the best availability. Learn more below. This parameter defaults to 0.', 'hostelpro')?></li>
				<li>max_days - <?php _e('The maximum number of days that can be selected. Defaults to 5.', 'hostelpro');?></li>
				<li>vertical_after - <?php _e('Whether to switch the "availability" table cells to vertical display to avoid making the table too wide. By default this setting is off. Pass number of days after which the cells should turn vertical.', 'hostelpro');?></li>
				<li>hide_dates - <?php _e('By default this shortcode shows each of the selected dates along with the availability for it. You can hide this by passing "hide_dates=1".', 'hostelpro');?></li>
				<li>orderby - <?php _e('Defines how rooms are sorted. Possible values: title or price. Defaults to "price".', 'hostelpro');?></li>
				<li>orderdir - <?php _e('Defines the direction of sorting - asceding or descending. Possible values: asc or desc. Defaults to "asc".', 'hostelpro');?></li>
			</ol>
			<p><?php printf(__('For example using the shortcode like this: <b>%s</b> will generate a table that shows room titles and descriptions withouth "type" information.', 'hostelpro'),'[hostelpro-list show_titles=1 show_descriptions=1 show_types=0]')?></p>
		</li>
		<li><input type="text" value="[hostelpro-booking]" readonly onclick="this.select();"> <?php _e('displays a generic booking form with a drop-down selector for choosing room, and a date selector. If you use the [hostelpro-list] shortcode you most probably do not need this one because the booking form is automatically generated.', 'hostelpro');?></li>
	</ol>
	
	<h3><?php _e('How the smart logic chooses which room to show when rooms are grouped?', 'hostelpro')?></h3>
	
	<p><?php _e('When you use the [hostelpro-list] code with group_rooms=1 (like this: [hostelpro_list group_rooms=1]), the following logic will be used:', 'hostelpro')?></p>
	<ol>
		<li><?php _e('The room that has available beds for more of the user-selected days will be preferred.', 'hostelpro')?></li>
		<li><?php _e('In case of a tie the room that has more available beds in the least available day will be preferred.', 'hostelpro')?></li>
		<li><?php _e('In case of a tie the room with the highest sum of available beds in the user-selected days will be preferred.', 'hostelpro')?></li>
	</ol>
	
	<p><?php _e('This algo is used by the most popular hostel listing sites so we decided to follow it.', 'hostelpro')?></p>
	
	<h2><?php _e('Translating', 'hostelpro')?></h2>
	
	<p><?php printf(__('If you want to translate this plugin check out <a href="%s" target="_blank">this guide</a>. The language template file is available in the plugin folder and called hostelpro.pot. Our plugin textdomain is "hostelpro" and you have to place your .po and .mo files in folder languages/', 'hostelpro'), 'http://blog.calendarscripts.info/how-to-translate-a-wordpress-plugin/');?></p>
	
	<h2><?php _e('Modifying the views/templates', 'hostelpro');?></h2>
	
	<p style="color:red;"><b><?php _e('Only for advanced users!', 'hostelpro')?></b></p>
	
	<p><?php _e('You can safely customize all files from the "views" folders by placing their copies in your theme folder. Simply create folder "hostelpro" <b>in your theme root folder</b> and copy the files you want to custom from "views" folder directly there.', 'hostelpro')?></p>

	<p><?php _e('For example:', 'hostelpro')?></p>
	
	<ol>
		<li><?php _e('If you are using the Twenty Fourteen theme, you should create folder "hostelpro" under it so the structure will now be something like <b>wp-content/themes/twentyfourteen/hostelpro</b>. (The files that are above the new "hostelpro" folder should remain where they are).', 'hostelpro')?></li>
		<li><?php _e('Then if you want to modify the "Manage Rooms" page copy the file rooms.php from the plugin "views" folder and place it in the new "hostelpro" folder so you will have  <b>wp-content/themes/twentyfourteen/hostelpro/rooms.php</b>', 'hostelpro')?></li>	
	</ol>	
	
	<p><?php _e("Don't worry if you use modified WordPress directory structure and don't have 'wp-content' folder. The trick will work with any structure as long as you follow the same logic.", 'hostelpro')?></p>
	
	<p><?php _e('Then feel free to modify the code, but of course be careful not to mess with the PHP or Javascript inside. This will let you change the design and even part of the functionality and not lose these changes when the plugin is upgraded. Be careful: we can not provide support for your custom versions of our views.', 'hostelpro')?></p>
	
	<h2><?php _e('Extending', 'hostelpro');?></h2>
	
	<p><?php printf(__('If you want to extend the plugin with custom functions our <a href="%s" target="_blank">Developers API</a> may come handy. Please let us know if you need a hook or filter to be added.', 'hostelpro'), 'http://blog.calendarscripts.info/hostelpro-developers-api/');?></p>
	
	<h2><?php _e('Questions and Support', 'hostelpro')?></h2>
	
	<p>If you need help please email us at <a href="mailto:info@calendarscripts.info">info@calendarscripts.info</a></p>
</div>