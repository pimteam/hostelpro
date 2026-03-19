HostelPRO = {};

function hostelproConfirmDelete(frm, msg) {
	if(!confirm(msg)) return false;
	frm.del.value=1;
	frm.submit(); 
}

// disable dates on the room calendar
// unavailableDates is global array
function hostelPROUnavailable(date, unavailableDates) {
	var m = date.getMonth(), d = date.getDate(), y = date.getFullYear();
	m = m+1; // because months start from 0
	
	// add leading zeros
	m = m.toString();
	if(m.length < 2) m = '0' + m;
	d = d.toString();
	if(d.length < 2) d = '0' + d;	
	
	for (i = 0; i < unavailableDates.length; i++) {
		if(jQuery.inArray(m + '-' + d + '-' + y, unavailableDates) != -1) {			
			return [false];
		}
	}
	return [true];
}

// selects a date from the room-calendar shortcode picker
function hostelPROSelectDate(origDate, roomID, shortcodeID) {	
	// instead of using the date as is, read it from the alternate field
	date = jQuery('#hostelpro-alternate'+roomID).val();

	// is this date from or to?
	// the older date is always from	
	var fromDate = jQuery('#hostelPROBookCalendarForm' + roomID + ' input[name=from_date]').val();
	var toDate = jQuery('#hostelPROBookCalendarForm' + roomID + ' input[name=to_date]').val();
	var fromParts = fromDate.split('-');
	fromDate = new Date(fromParts[0], fromParts[1]-1, fromParts[2]);
	var toParts = toDate.split('-');
	toDate = new Date(toParts[0], toParts[1]-1, toParts[2]);
		
	// convert selected to date to compare
	var selParts = date.split('/');	
	var selDate = new Date(selParts[2], selParts[0]-1, selParts[1]);	
	
	// are we setting from or to?
	var currentlySetting = jQuery('#hostelPROBookCalendarForm' + roomID + ' input[name=currently_setting]').val();
	
	// figure out where to set (field) and display (div)	 
	if(currentlySetting == 'from' || selDate < fromDate) {
		// selDate is "from"
		jQuery('#hostelPROBookCalendarForm' + roomID + ' input[name=from_date]').val(selParts[2] + '-' 
			+ selParts[0] + '-' + selParts[1]);
		jQuery('#hostelPROFromDateDisplay' + roomID).html(origDate);		
	}	
	else {
		// selDate is "to"		
		jQuery('#hostelPROBookCalendarForm' + roomID + ' input[name=to_date]').val(selParts[2] + '-' 
			+ selParts[0] + '-' + selParts[1]);
		jQuery('#hostelPROToDateDisplay' + roomID).html(origDate);
	}
	
	// if we are setting from AND this is the last available date in the sequence we need to automatically set the next date as leaving date
	if(currentlySetting == 'from') {
		 nextday = new Date();
		 nextday.setDate(selDate.getDate()+1);
		 
		 // convert nextday into a MySQl string date
		 nextday = nextday.toISOString().slice(0,10);
		 nextParts = nextday.split('-');
		 nextdayUnav = nextParts[1] + '-' + nextParts[2] + '-' + nextParts[0];
		 
		 // is tomorrow available?
		 var unavDates = jQuery('#hostelPROBookCalendarForm' + roomID + ' input[name=unavailable]').val();
		 unavDates = unavDates.split(',');
		 if(jQuery.inArray(nextdayUnav, unavDates) != -1) {
		 	
		 	 // do something to automatically select color nextday as leaving, and set it in the form		 	 
		 	 jQuery('#hostelPROBookCalendarForm' + roomID + ' input[name=to_date]').val(nextday);
			 jQuery('#hostelPROToDateDisplay' + roomID).html(nextday);
			 
			 // now load booking form
			 var promptMsg = jQuery('#hostelPROBookSingleDayMsg'+roomID).val();
			 if(promptMsg == '' || confirm(promptMsg)) hostelPROLoadBooking(jQuery('#hostelPROBookCalendarForm'+roomID), 'hostelproRoomCalendarWrap'+ shortcodeID);
		 }
	}
	
	// when setting in between, let's change currentlySetting
	currentlySetting = (currentlySetting == 'from') ? 'to' : 'from';
	jQuery('#hostelPROBookCalendarForm' + roomID + ' input[name=currently_setting]').val(currentlySetting);
} // end select date

function HostelPROValidateBooking(frm) {
	// beds
	/*if(frm.beds.value == '' || isNaN(frm.beds.value) || frm.beds.value < 1) {
		alert(hostelpro_i18n.beds_required);
		frm.beds.focus();
		return false;
	}*/
	if(frm.room_id.value == 0) {
		alert(hostelpro_i18n.select_room);
		frm.room_id.focus();
		return false;
	}
	
	// from date
	if(frm.from_date.value == '') {
		alert(hostelpro_i18n.from_date_required);
		frm.from_date.focus();
		return false;
	}
	
	// to date
	if(frm.to_date.value == '') {
		alert(hostelpro_i18n.to_date_required);
		frm.to_date.focus();
		return false;
	}
	
	// to date must be > from date
	var curDate = new Date();
	var fromParts = frm.from_date.value.split('-');
	var fromDate = new Date(fromParts[0], fromParts[1]-1, fromParts[2], 23, 59, 59);
	var toParts = frm.to_date.value.split('-');
	var toDate = new Date(toParts[0], toParts[1]-1, toParts[2]);
	
	if(curDate > fromDate) {
		alert(hostelpro_i18n.from_date_atleast_today);
		frm.from_date.focus();
		return false;
	}

	if(fromDate >= toDate) {
		alert(hostelpro_i18n.from_date_before_to);
		frm.to_date.focus();
		return false;
	}
	
	if(frm.contact_name.value == '') {
		alert(hostelpro_i18n.name_required);
		frm.contact_name.focus();
		return false;
	}
	
	var emailStr = frm.contact_email.value; 
	if(emailStr == '' || emailStr.indexOf('@') < 1 || emailStr.indexOf('.') < 1) {
		alert(hostelpro_i18n.email_required);
		frm.contact_email.focus();
		return false;
	}
	
	// check custom fields
	// console.log(frm.elements["required_fields[]"]);
	var req_cnt = frm.elements["required_fields[]"].length; // there's always at least 1
	
	if(req_cnt > 1) {
		for(i = 0; i<req_cnt; i++) {
			var fieldName = frm.elements["required_fields[]"][i].value;
			
			if(fieldName !='') {
				var isFilled = false;
				// ignore radios
				if(frm.elements[fieldName].type == 'radio') continue;
				
				// checkbox
				if(frm.elements[fieldName].type == 'checkbox' && !frm.elements[fieldName].checked) {
					alert(hostelpro_i18n.required_field);
					frm.elements[fieldName].focus();
					return false;
				}		
				
				// all other fields
				if(frm.elements[fieldName].value=="") {
					alert(hostelpro_i18n.required_field);
					frm.elements[fieldName].focus();
					return false;
				}
			}
		}
	}
	
	var divID = '#hostelPROBooking' + frm.shortcode_id.value;
	
	// all fine, submit by ajax
	var form_data = jQuery(frm).serialize();
	// honey
	if(typeof frm.hostelpro_ssid != 'undefined') {		
		form_data += "&hostelpro_ssid="+ '_' + frm.hostelpro_hive_ssid.value;		
	}
	
	// console.log(form_data);
	jQuery.post(hostelpro_i18n.ajax_url, form_data, function(msg) {
		if(msg.indexOf('BOOKERROR') != -1) {
			parts = msg.split('BOOKERROR-->');
			alert(parts[1]);
			return false;
		}		

		// if there is conversion tracking call, eval it
		convTrackCode = false;
		if(msg.indexOf('|CONVTRACK|') != -1) {
			parts = msg.split('|CONVTRACK|');
			msg = parts[0];
			var convTrackCode = parts[1];
		}		
		
		jQuery(divID).html(msg);
		
		if(convTrackCode) eval(convTrackCode);
	}); 
}

// when changing room in the room booking form, default to proper number of beds:
// 1 for dorm rooms and entire room price
// all beds for private rooms
// explanation of the returned result:
// parts[0] = number of room beds
// parts[1] - whether the number of beds can be changed or not 
// parts[2] - price per room or per bed
// parts[3] - extra beds available?
// parts[4] - the price of the extra bed
// parts[5] - private or dorm
// parts[6] - the HTML of the beds section
// parts[7] - room notes
function HostelPROChangeRoom(roomID, frm) {
	data = {'action': 'hostelpro_ajax', 'type': 'change_room', 'room_id' : roomID, 'from_date' : frm.from_date.value, 'to_date': frm.to_date.value}
	jQuery.post(hostelpro_i18n.ajax_url, data, function(msg) {
		// if there are room addons, remove them from msg and put them in var
		if(msg.indexOf('[ADDONS]') > -1) {
			var msgParts = msg.split('[ADDONS]');
			msg = msgParts[0];
			var addonsHTML = msgParts[1];		
		}		
		else addonsHTML = '';
		
		// if there is room description, remove them from msg and put id in var
		// the [DESCRIPTION] should be BEFORE [ADDONS] in the Ajax output
		if(msg.indexOf('[DESCRIPTION]') > -1) {
			var msgParts = msg.split('[DESCRIPTION]');
			msg = msgParts[0];
			var descHTML = msgParts[1];		
		}		
		else descHTML = '';
				
		var parts = msg.split("|");

		var bedsSelector = jQuery(frm).find('.hostelpro-beds-wrapper');
		// console.log(bedsSelector);
		jQuery(bedsSelector).html(parts[6]);
		
		// hide or show beds selector		
		if(parts[1] == 1) jQuery('.select-beds').show();
		else jQuery('.select-beds').hide();
		
		// extra beds available?
		if(parts[3] > 0) {
			// construct drop-down HTML
			var eddHTML = '';
			for(i=0; i <= parts[3]; i++) {				
				eddHTML += '<option value="' + i + '">' + i + '</option>';
			}
			jQuery(frm.extra_beds).html(eddHTML);			
			
			jQuery('.extra-beds').show();
			jQuery('#' + frm.extra_bed_price_id.value).html(parts[4]);
			frm.extra_beds.value = 0;
		}
		else jQuery('.extra-beds').hide(); 
		
		// room notes
		if(frm.elements['notes_id'] != null) {
			jQuery('#' + frm.notes_id.value).html(parts[7]);
		}
		
		// addons
		// figure out the wrap div id
		var idParts = frm.id.split('-');
		var wrapID = 'hostelPROBooking' + idParts[2];
		if(jQuery('#' + wrapID + ' .hostelpro-room-addons').length) {
			jQuery('#' + wrapID + ' .hostelpro-room-addons').html(addonsHTML);
		}
		
		// description
		// figure out the wrap div id
		var idParts = frm.id.split('-');
		var wrapID = 'hostelPROBooking' + idParts[2];
		if(jQuery('#' + wrapID + ' .hostelpro-room-description').length) {
			jQuery('#' + wrapID + ' .hostelpro-room-description').html(descHTML);
		}
		
	});
}

// load the booking form when called in the room calendar shortcode
function hostelPROLoadBooking(frm, divID) {	
	jQuery('#'+divID).html(hostelpro_i18n.please_wait);
	var form_data = jQuery(frm).serialize();
	
	jQuery.post(hostelpro_i18n.ajax_url, form_data, function(msg) {
		if(msg.indexOf('BOOKERROR') != -1) {
			parts = msg.split('BOOKERROR-->');
			alert(parts[1]);
			return false;
		}
		jQuery('#'+divID).html(msg);
		
		// hide the date search form in case it's still visible		
		if(jQuery('#form-' + divID).length) {
			jQuery('#form-' + divID).hide();
		}
	}); 
}

// check minimum stay for selected period
function hostelPROCheckMinStay(daydiff, fromDate, toDate) {
	data = {'action': 'hostelpro_ajax', 'type': 'min_stay', 'from_date': fromDate, 'to_date': toDate};
	jQuery.post(hostelpro_i18n.ajax_url, data, function(msg) {
		if(msg > 0 && daydiff < msg) {
			alert(hostelpro_i18n.min_stay_required.replace('%d', msg));
			return false;
		}		
	});
	
	return true;
}

// compare dates. If from is before to, return true. If from is same or greater than to, return false (invalid case)
// dates are in MySQL format
function hostelPROCompareDates(fromDate, toDate) {
	// to date must be > from date
	var fromParts = fromDate.split('-');
	var fromDate = new Date(fromParts[0], fromParts[1]-1, fromParts[2], 23, 59, 59);
	var toParts = toDate.split('-');
	var toDate = new Date(toParts[0], toParts[1]-1, toParts[2]);
	
	if(fromDate >= toDate) {
		return false;
	}
	
	return true;
}

// removes booking from the front-end in Multiple bookings mode
function HostelPRORemoveBooking(bid) {
	if(confirm(hostelpro_i18n.are_you_sure)) {
		data = {'action': 'hostelpro_ajax', 'type': 'del_front_booking', 'id': bid};
		jQuery.post(hostelpro_i18n.ajax_url, data, function(msg) {
			jQuery('#hostelPROFrontBooking' + bid).remove();
			
			// recalculate grand total
			var total = 0;
			jQuery('.hostelpro-booking-cost').each(function(elt, i){
				total += parseFloat(jQuery(this).val());
			});
			jQuery('#hostelPROGrandTotal').html(total);
		});
	}
}

function HostelPROMultiBook(frm) {
	// all fine, submit by ajax
	data = {'action': 'hostelpro_ajax', 'type': 'multiple_book'};
	var divID = '#hostelPROBooking' + frm.shortcode_id.value;
		
	// console.log(form_data);
	jQuery.post(hostelpro_i18n.ajax_url, data, function(msg) {
		if(msg.indexOf('BOOKERROR') != -1) {
			parts = msg.split('BOOKERROR-->');
			alert(parts[1]);
			return false;
		}		

		// if there is conversion tracking call, eval it
		convTrackCode = false;
		if(msg.indexOf('|CONVTRACK|') != -1) {
			parts = msg.split('|CONVTRACK|');
			msg = parts[0];
			var convTrackCode = parts[1];
		}		
		
		jQuery(divID).html(msg);
		
		if(convTrackCode) eval(convTrackCode);
	}); 
}
