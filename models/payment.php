<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

class HostelPROPayment {
	static $pdt_mode = false;	
	static $pdt_response = '';	
	
	// handle Paypal IPN request
	static function parse_request($wp) {		
		// only process requests with "hostelpro=paypal"
	   if (array_key_exists('hostelpro', $wp->query_vars) 
	            && $wp->query_vars['hostelpro'] == 'paypal') {
	        self::paypal_ipn($wp);
	   }	
	}
	
	// process paypal IPN
	static function paypal_ipn($wp = null) {
		global $wpdb;
		echo "<!-- HOSTELPRO paypal IPN -->";
		
	   $paypal_email = get_option("hostelpro_paypal");
	   $paypal_sandbox = 0; // this is fixed to false for the moment
	   $test_mode = true;
	   
	   $pdt_mode = false;
	   if(!empty($_GET['tx']) and !empty($_GET['hostelpro_pdt']) and get_option('hostelpro_use_pdt')==1) {
			// PDT			
			$req = 'cmd=_notify-synch';
			$tx_token = strtoupper($_GET['tx']);
			$auth_token = get_option('hostelpro_pdt_token');
			$req .= "&tx=$tx_token&at=$auth_token";
			$pdt_mode = true;
			$success_responce = "SUCCESS";
		}
		else {	
			// IPN		
			$req = 'cmd=_notify-validate';
			foreach ($_POST as $key => $value) { 
			  $value = urlencode(stripslashes($value)); 
			  $req .= "&$key=$value";
			}
			$success_responce = "VERIFIED";
		}		
		
		self :: $pdt_mode = $pdt_mode;	
		
		$paypal_host = "www.paypal.com";
		$paypal_sandbox = get_option('hostelpro_paypal_sandbox');
		if($paypal_sandbox == '1') $paypal_host = 'www.sandbox.paypal.com';
		
		// post back to PayPal system to validate
		// see CURL or fsockopen
		if(function_exists('curl_version')) {
			$ch = curl_init('https://'.$paypal_host.'/cgi-bin/webscr');
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);		
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
			
			if( !($res = curl_exec($ch)) ) {
			   self::log_and_exit("Got " . curl_error($ch) . " when processing IPN data");
			   curl_close($ch);
			   exit;
			}
			curl_close($ch);			
			if (strstr ($res, $success_responce) or $paypal_sandbox == '1' or $test_mode) self :: paypal_ipn_verify($res);
			else return self::log_and_exit("Paypal result is not VERIFIED: $res");
		}
		else {
			$header="";
			$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$header .= "Content-Length: " . strlen($req) . "\r\n";
			$header .="Host: $paypal_host\r\n"; 
			$header .="Connection: close\r\n\r\n";		
			$fp = fsockopen ($paypal_host, 80, $errno, $errstr, 30);
			
			if($fp) {
				fputs ($fp, $header . $req);
				$pp_response = '';
			   while (!feof($fp)) {
			      $res = fgets ($fp, 1024);	
			      $pp_response .= $res;	     
			      if (strstr ($res, $success_responce) or $paypal_sandbox == '1' or $test_mode) {
			      	self :: paypal_ipn_verify($pp_response);
			      	exit;
			     	}			     	
			   }  
			   fclose($fp);
			   return self::log_and_exit("Paypal result is not VERIFIED: $pp_response");  
			} 
			else return self::log_and_exit("Can't connect to Paypal via fsockopen");
		}
		exit;
	}
	
	// get the fee based on whether we have single or multiple booking
	static function get_fee($booking_id) {
		global $wpdb;
		
		if(is_numeric($booking_id)) {
			$booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS." WHERE id=%d", $booking_id));
			$fee =  $original_fee = $booking->amount_due;
			$advance_payment_val = get_option('hostelpro_advance_payment_percentage');
			$unit = get_option('hostelpro_advance_payment_unit');
			if(empty($unit) or $unit == '%') $fee = round($fee * ($advance_payment_val / 100), 2);
			else {
				$fee = $advance_payment_val;
				if($fee > $original_fee) $fee = $original_fee;
			}	
		}
		else {			
			// multiple booking
			$booking_id = str_replace('M', '', $booking_id);
			$session_id = $wpdb->get_var($wpdb->prepare("SELECT session_id FROM ".HOSTELPRO_BOOKINGS." WHERE id=%d", $booking_id));
			$bookings = $wpdb->get_results($wpdb->prepare("SELECT tB.*, tR.title as room_name 
				FROM ".HOSTELPRO_BOOKINGS." tB JOIN ".HOSTELPRO_ROOMS." tR ON tR.id = tB.room_id
				WHERE tB.session_id=%s ORDER BY tB.id", $session_id));
			
			// now select the bookings with this session ID and add to $fee
			$fee = 0;
			foreach($bookings as $booking) $fee += $booking->amount_now;
		}
		
		return $fee;
	}
		
	static function paypal_ipn_verify($pp_response) {
		global $wpdb, $user_ID, $post;
		
		$test_mode = true;
		
		// when we are in PDT mode let's assign all lines as POST variables
		if(self :: $pdt_mode) {
			 $lines = explode("\n", $pp_response);	
				if (strcmp ($lines[0], "SUCCESS") == 0) {
				for ($i=1; $i<count($lines);$i++){
					if(strstr($lines[$i], '=')) list($key,$val) = explode("=", $lines[$i]);
					$_POST[urldecode($key)] = urldecode($val);
				}
			 }
			 
			 $_GET['user_id'] = $user_ID;
			 self :: $pdt_response = $pp_response;
			 
			 $paypal_thankyou = get_option('wphostel_paypal_return');
			 if(empty($paypal_thankyou)) $paypal_thankyou = get_permalink($post->ID);
		} // end PDT mode transfer from lines to $_POST	 				
					
		// check the payment_status is Completed
      // check that txn_id has not been previously processed
      // check that receiver_email is your Primary PayPal email
      // process payment
	   $payment_completed = false;
	   $txn_id_okay = false;
	   $receiver_okay = false;
	   $payment_currency_okay = false;
	   $payment_amount_okay = false;
	   $paypal_email = get_option("hostelpro_paypal");
	   
	   if(@$_POST['payment_status']=="Completed" or $test_mode) {
	   	$payment_completed = true;
	   } 
	   else return self::log_and_exit("Payment status: $_POST[payment_status]");
	   
	   // check txn_id
	   $txn_exists = $wpdb->get_var($wpdb->prepare("SELECT paycode FROM ".HOSTELPRO_PAYMENTS."
		   WHERE paytype='paypal' AND paycode=%s", $_POST['txn_id']));
		if(empty($txn_exists)) $txn_id_okay = true;
		else {
			// in PDT mode just redirect to the post because existing txn_id isn't a problem.
			// but of course we shouldn't insert second payment			
			if( self :: $pdt_mode) hostelpro_redirect($paypal_thankyou);
			return self::log_and_exit("TXN ID exists: $txn_exists");
		}  
		
		// check receiver email
		if($_POST['business']==$paypal_email or $_POST['receiver_id'] == $paypal_email or $test_mode) {
			$receiver_okay = true;
		}
		else return self::log_and_exit("Business email is wrong: $_POST[business]");
		
		// check payment currency
		if($_POST['mc_currency']==get_option("hostelpro_currency") or $test_mode) {
			$payment_currency_okay = true;
		}
		else return self::log_and_exit("Currency is $_POST[mc_currency]"); 
		
		// check amount
		$fee = self :: get_fee($_GET['bid']);

		if($_POST['mc_gross'] >= $fee or $test_mode) {
			$payment_amount_okay = true;
		}
		else self::log_and_exit("Wrong amount: paid $_POST[mc_gross] when price is $fee"); 
		
		// everything OK, insert payment
		// everything OK, insert payment and enroll
		if($payment_completed and $txn_id_okay and $receiver_okay and $payment_currency_okay 
				and $payment_amount_okay) {		
				
			self :: booking_paid($_GET['bid'], $fee, 'paypal', $_POST['txn_id']);
			
			// in PDT mode we may need to redirect
			if( self :: $pdt_mode and !empty($paypal_thankyou)) hostelpro_redirect($paypal_thankyou);
			exit;
		}
	}
	
	// mark booking(s) as paid
	// @param $booking_id - int when single booking or string like M5 when multiple bookings
	static function booking_paid($booking_id, $fee, $paymethod, $paycode) {
		global $wpdb;
		
		$advance_payment_val = get_option('hostelpro_advance_payment_percentage');
		$unit = get_option('hostelpro_advance_payment_unit');
		
		$curdate = date("Y-m-d", current_time('timestamp'));				
								
		$wpdb->query($wpdb->prepare("INSERT INTO ".HOSTELPRO_PAYMENTS." SET 
				booking_id=%d, date=%s, amount=%s, status='completed', paycode=%s, paytype=%s", 
				str_replace('M', '', $booking_id), $curdate, $fee, $paycode, $paymethod));
		
		if(!is_numeric($booking_id)) {
			$bid = str_replace('M', '', $booking_id);
			$session_id = $wpdb->get_var($wpdb->prepare("SELECT session_id FROM ".HOSTELPRO_BOOKINGS." WHERE id=%d", $bid));
			$bookings = $wpdb->get_results($wpdb->prepare("SELECT tB.*, tR.title as room_name 
				FROM ".HOSTELPRO_BOOKINGS." tB JOIN ".HOSTELPRO_ROOMS." tR ON tR.id = tB.room_id
				WHERE tB.session_id=%s ORDER BY tB.id", $session_id));
			// loop to update and sent action
			foreach($bookings as $booking) {
				$wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_BOOKINGS." SET status='active', 
					amount_paid = amount_now, amount_due = amount_arrival WHERE id=%d", $booking->id));		
				
				do_action('hostelpro_booking_paid', $booking->id);				
			}	
		}
		else {			
			if(empty($unit) or $unit == '%') $fee = round($fee * ($advance_payment_val / 100), 2);
			else {
				$fee = $advance_payment_val;
				if($fee > $original_fee) $fee = $original_fee;
			}			
			
			$wpdb->query($wpdb->prepare("UPDATE ".HOSTELPRO_BOOKINGS." SET status='active', 
				amount_paid = $fee, amount_due = amount_due - $fee WHERE id=%d", $booking_id));		
			
			do_action('hostelpro_booking_paid', $booking_id);				
		}
		
		// unset session
		// unset($_SESSION['hostelpro_booking_session']);
		setcookie('hostelpro_booking_session', '', time() - 24*3600, '/');
		
		// send email
		$_booking = new HostelPROBooking();
		$_booking->email($booking_id);
	} // end booking_paid
	
	// log paypal errors
	static function log_and_exit($msg) {
		// log
		$errorlog = get_option("hostelpro_errorlog");
		$errorlog = date_i18n(get_option('date_format').' '.get_option('time_format')).": ".$msg."\n".$errorlog;
		update_option("hostelpro_errorlog",$errorlog);
		
		// throw exception as there's no need to continue
		exit;
	}
	
	// process Stripe Payment
	static function Stripe($is_bundle = false) {
		global $wpdb, $user_ID;
		require_once(HOSTELPRO_PATH.'/lib/stripe-init.php');
 
		$stripe = array(
		  'secret_key'      => get_option('hostelpro_stripe_secret'),
		  'publishable_key' => get_option('hostelpro_stripe_public')
		);
		 
		\Stripe\Stripe::setApiKey($stripe['secret_key']);
		// Uncomment this in case you get Stripe errors		
		// \Stripe\Stripe::$verifySslCerts = false; 
		
		$token  = $_POST['stripeToken'];
		
		// select booking
		$booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".HOSTELPRO_BOOKINGS." WHERE id=%d", $_POST['booking_id']));
		
		// check amount					
		$fee = self :: get_fee($_POST['booking_id']);
		
		// $booking->amount_due		
		
		$user = get_userdata($user_ID);
		$currency = get_option('hostelpro_currency');
			 
		try {
			 $customer = \Stripe\Customer::create(array(
		      'email' => $user->user_email,
		      'card'  => $token
		  ));				
			
		  $charge = \Stripe\Charge::create(array(
		      'customer' => $customer->id,
		      'amount'   => $fee*100,
		      'currency' => $currency
		  ));
		} 
		catch (Exception $e) {
			wp_die($e->getMessage());
		}	  
		
		// Update the booking record
		$curdate = date("Y-m-d", current_time('timestamp'));		
		self :: booking_paid($_POST['booking_id'], $fee, 'stripe', $customer->ID);		
			
		do_action('hostelpro_booking_paid', $_POST['booking_id']);	
		
		// redirect instead of displaying success page?
		$stripe_return = get_option('hostelpro_stripe_return');
		if(!empty($stripe_return)) hostelpro_redirect($stripe_return);								
		
		// show Stripe thankyou text
		$_POST['hostelpro_stripe_success'] = true;
	}	// end Stripe
	
	// returns the Stripe payment success message
	static function stripe_success() {
		global $post;
		
		$message = get_option('hostelpro_stripe_success');
		if(empty($message)) $message = __('Thank you! Your payment is accepted. <a href="{{{back-url}}}">Go back</a>', 'hostelpro');
		$message = stripslashes($message);

		// replace {{{back-url}}}
		$url = get_permalink($post->ID);
		$message = str_replace('{{{back-url}}}', $url, $message); 	
		$message = wpautop($message);
		$message = do_shortcode($message);
		
		return $message;
	}
}