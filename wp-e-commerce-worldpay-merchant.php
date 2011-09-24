<?php



/**
 * RBS WorldPay Redirect
 *
 * This is a WP e-Commerce merchant class for RBS WorldPay Redirect.
 *
 * @link http://www.rbsworldpay.com/support/bg/index.php?page=development
 *
 * @package WP e-Commerce
 * @subpackage Merchants > RBS WorldPay Redirect
 * @since 3.7.6.3
 *
 * @version 1.0
 */



// Hooks
add_action( 'init', array( 'wpsc_merchant_rbsworldpay_redirect', 'reset_basket' ) );



// ----- Define Gateway -----
$nzshpcrt_gateways[$num] = array(

	'name'                   => 'RBS WorldPay (Redirect)',
	'api_version'            => 2.0,
	'class_name'             => 'wpsc_merchant_rbsworldpay_redirect',
	'has_recurring_billing'  => false,
	'wp_admin_cannot_cancel' => true,
	'requirements'           => array(
		'php_version'   => 4.3,    // So that you can restrict merchant modules to PHP 5, if you use PHP 5 features
		'extra_modules' => array() // For modules that may not be present, like curl
	),
	'internalname'           => 'rbsworldpay_redirect', // Not legacy, still required

	// All array members below here are legacy, and use the code in paypal_multiple.php
	'form'                   => 'wpsc_merchant_rbsworldpay_redirect_settings_form',
	'submit_function'        => 'wpsc_merchant_rbsworldpay_redirect_settings_form_submit',
	'payment_type'           => 'rbsworldpay_redirect',
	'supported_currencies'   => array(
		'currency_list'      => array( 'ARS', 'AUD', 'BRL', 'CAD', 'CHF', 'CLP', 'CNY', 'COP', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'IDR', 'JPY', 'KES', 'KRW', 'MXP', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'PTE', 'SEK', 'SGD', 'SKK', 'THB', 'TWD', 'USD', 'VND', 'ZAR' ),
		'option_name'        => 'rbsworldpay_redirect_curcode'
	)
	
);



/**
 * wpsc_merchant_rbsworldpay_redirect_settings_form_submit
 * Calls the method in the wpsc_merchant_rbsworldpay_redirect class.
 * At the moment this is called as a function and cannot be called as a method of a class.
 */
function wpsc_merchant_rbsworldpay_redirect_settings_form_submit() {

	return wpsc_merchant_rbsworldpay_redirect::submit_rbsworldpay_redirect_settings_form();
	
}



/**
 * wpsc_merchant_rbsworldpay_redirect_settings_form
 * Calls the method in the wpsc_merchant_rbsworldpay_redirect class.
 * At the moment this is called as a function and cannot be called as a method of a class.
 */
function wpsc_merchant_rbsworldpay_redirect_settings_form() {

	return wpsc_merchant_rbsworldpay_redirect::rbsworldpay_redirect_settings_form();
	
}



/**
 * wpsc_merchant_rbsworldpay_redirect class
 * Extending the wpsc_merchant class, this provides integration for the
 * RBD WorldPay Redirect payment gateway.
 */
class wpsc_merchant_rbsworldpay_redirect extends wpsc_merchant {
	
	
	
	var $name = 'RBS WorldPay';
	var $worldpay_ipn_values = array();
	
	
	
	/**
	 * Construct value array method
	 * Converts the data gathered by the base class code to something acceptable to the gateway
	 */
	function construct_value_array() {
	
		$worldpay_vars = array();
		
		// Required parameters
		$worldpay_vars['instId']   = get_option( 'rbsworldpay_redirect_instId' );    // (int) Your Installation ID
		$worldpay_vars['cartId']   = $this->purchase_id;                             // (255 char) Your own reference number for this purchase. It is returned to you along with the authorisation results by whatever method you have chosen for being informed (email and / or Payment Notifications).
		$worldpay_vars['amount']   = $this->cart_data['total_price'];                // (decimal) A decimal number giving the cost of the purchase in terms of the major currency unit e.g. 12.56 would mean 12 pounds and 56 pence if the currency were GBP (Pounds Sterling). Note that the decimal separator must be a dot (.), regardless of the typical language convention for the chosen currency. The decimal separator does not need to be included if the amount is an integral multiple of the major currency unit. Do not include other separators, for example between thousands.
		$worldpay_vars['currency'] = $this->cart_data['store_currency'];             // (3 char) 3 letter ISO code for the currency of this payment - please refer to the appendix Currency Codes (http://www.rbsworldpay.com/support/kb/bg/htmlredirect/rhtmla002.html).
		
		// Optional Order Details Parameters
		$worldpay_vars['desc']     = '';                                             // (255 char) A textual description of this purchase, up to 255 characters. This is used in web-pages, statements and emails for yourself and the shopper.
		$worldpay_vars['testMode'] = get_option( 'rbsworldpay_redirect_test_mode' ); // A value greater than 0 specifies that this is a test payment. Specify the test result you want by entering REFUSED, AUTHORISED, ERROR, or CAPTURED in the name parameter.
		$worldpay_vars['authMode'] = get_option( 'rbsworldpay_redirect_auth_mode' ); // (char) This specifies the authorisation mode to use. This is only needed if you have merchant codes with different authorisation modes, in order to specify which type of merchant code to use. If there is no merchant code with a matching authMode then the transaction is rejected. The values are:
		                                                                             // - "A" for a full auth
		                                                                             // - "E" for a pre-auth. In the payment result this parameter can also take the value
		                                                                             // - "O" when performing a post-auth.
		//$worldpay_vars['authValidFrom'] = '';                                        // (int) This specifies a time window within which the purchase must (or must not) be completed, eg. if the purchase is a time-limited special offer. Each of these parameters is a time in milliseconds since 1 January 1970 GMT - a Java long date value (as from System.currentTimeMillis() or Date.getTime()), or 1000* a C time_t. If from<to, then the authorisation must complete between those two times. If to<from, then the authorisation must complete either before the to time or after the from time. Either may be zero or omitted to give the effect of a simple "not before" or "not after" constraint. If both are zero or omitted, there are no restrictions on how long a shopper can spend making their purchase (although our server will time-out their session if it is idle for too long).
		//$worldpay_vars['authValidTo'] = '';                                          // (int) See above.
		//$worldpay_vars['resultFile'] = '';                                           // (string) The name of one of your uploaded files, which will be used to format the result. Please refer to Configuring Your Installation. If this is not specified, resultY.html or resultC.html are used as described in Payment Result - to You.
		//$worldpay_vars['accId<n>'] = '';                                             // (string) This specifies which merchant code should receive funds for this payment. By default our server tries accId1.
		
		// Order Details Parameters (required)
		$worldpay_vars['address']  = trim( implode( "&#10;", explode( "\n\r", $this->cart_data['billing_address']['address'] ) ), "&#10;" ); // (255 char) The shopper's address. Encode newlines as "&#10;" (the HTML entity for ASCII 10, the new line character).
		$worldpay_vars['country']  = $this->cart_data['billing_address']['country']; // (255 char) The shopper's country, as 2 character ISO code, uppercase. Please refer to the appendix Country Codes (http://www.rbsworldpay.com/support/kb/bg/htmlredirect/rhtmla003.html).
		
		// Order Details Parameters (optional)
		$worldpay_vars['email']    = $this->cart_data['email_address'];              // (80 char) The shopper's email address.
		$worldpay_vars['name']     = $this->cart_data['billing_address']['first_name'] . ' ' . $this->cart_data['billing_address']['last_name']; // (40 char) The shopper's full name, including any title, personal name and family name. Also note that if you are sending a test submission you can specify the type of response you want from our system by entering REFUSED, AUTHORISED, ERROR or CAPTURED as the value in the name parameter.
		$worldpay_vars['postcode'] = $this->cart_data['billing_address']['post_code']; // (12 char) Note that at your request we can assign mandatory status to this parameter. That is, if it is not supplied in the order details then the shopper must enter it in the payment pages.
		if ( $this->cart_data['shipping_address']['city'] != '' ) {
			$worldpay_vars['address'] .= '&#10;' . $this->cart_data['billing_address']['city'];
		}
		if ( $this->cart_data['shipping_address']['state'] != '' ) {
			$worldpay_vars['address'] .= '&#10;' . $this->cart_data['shipping_address']['state'];
		}
		
		// Payment Notification URL
		$worldpay_vars['MC_callback'] = add_query_arg( 'gateway', 'rbsworldpay_redirect', $this->cart_data['notification_url'] );
		
		// Custom Fields
		$worldpay_vars['MC_invoice']     = $this->cart_data['session_id'];
		$worldpay_vars['MC_ipn_request'] = get_option( 'rbsworldpay_redirect_ipn' );
		
		$this->collected_gateway_data = $worldpay_vars;
		
	}
	
	
	
	/**
	 * submit method
	 * Sends the received data to the payment gateway
	 *
	 * @access public
	 */
	function submit() {
	
		$name_value_pairs = array();
		foreach ( $this->collected_gateway_data as $key=>$value ) {
			$name_value_pairs[] = $key . '=' . urlencode( $value );
		}
		$gateway_values = implode( '&', $name_value_pairs );

		if ( get_option( 'rbsworldpay_redirect_test_mode' ) > 0 ) {
			echo '<a href="' . $this->get_rbsworldpay_redirect_url() . '?' . $gateway_values . '">Test the URL here</a>';
			echo '<pre>' . print_r( $this->collected_gateway_data, true ) . '</pre>';
			echo '<form action="' . add_query_arg( 'gateway', 'rbsworldpay_redirect', $this->cart_data['notification_url'] )  . '" method="POST">
					transStatus: <input name="transStatus" value="Y" type="text" /><br />
					MC_invoice: <input name="MC_invoice" value="' . $this->cart_data['session_id'] . '" type="text" /><br />
					MC_ipn_request: <input type="text" name="MC_ipn_request" value="1" /><br />
					authAmount: <input type="text" name="authAmount" value="' . $this->collected_gateway_data['amount'] . '" /><br />
					authMode: <input type="text" name="authMode" value="' . $this->collected_gateway_data['authMode'] . '" /><br />
					<input type="submit" value="submit" />
				</form>';
			exit();
		}
		
		header( 'Location: ' . $this->get_rbsworldpay_redirect_url() . '?' . $gateway_values );
		exit();
	
	}
	
	
	
	/**
	 * parse_gateway_notification method
	 * Receives data from the payment gateway
	 *
	 * @access private
	 */
	function parse_gateway_notification() {
	
		// If this is an IPN callback...
		if ( ( $_POST['MC_ipn_request'] == '1' ) && ( get_option( 'rbsworldpay_redirect_ipn' ) == 1 ) ) {
			
			$received = array();
			
			// Parameters generated by the Purchase Token
			$received['cartId']           = $_POST['cartId'];           // Your own reference number for the order.
			$received['authMode']     	  = $_POST['authMode'];         // The authMode: A for auth, E for pre-ath.
			$received['testMode']         = $_POST['testMode'];         // 0 = LIVE, 100 (or any number greater than 0) is test mode
			
			// Payment Response parameters returned from WorldPay
			$received['transId']          = $_POST['transId'];          // The ID for the transaction
			$received['transStatus']      = $_POST['transStatus'];      // Result of the transaction - "Y" for a successful payment authorisation, "C" for a cancelled payment
			$received['transTime']        = $_POST['transTime'];        // Time of the transaction in milliseconds since the start of 1970 GMT. This is the standard system date in Java, and is also 1000x the standard C time_t time.
			$received['authAmount']       = $_POST['authAmount'];       // Amount that the transaction was authorised for, in the currency given as authCurrency.
			$received['authCurrency']     = $_POST['authCurrency'];     // The currency used for authorisation.
			$received['authAmountString'] = $_POST['authAmountString']; // HTML string produced from authorisation amount and currency
			$received['rawAuthMessage']   = $_POST['rawAuthMessage'];   // The text received from the bank summarising the different states listed below:
			                                                            // - cardbe.msg.testSuccess - Make Payment (test)
			                                                            // - cardbe.msg.authorised - Make Payment (live)
			                                                            // - trans.cancelled - Cancel Purchase (test or live) Ê
			$received['callbackPW']       = $_POST['callbackPW'];       // The Payment Response password set in the Merchant Interface.
			$received['cardType']         = $_POST['cardType'];         // The type of payment method used by the shopper.
			$received['AVS']              = $_POST['AVS'];              // A 4-character string giving the results of 4 internal fraud-related checks. The characters respectively give the results of the following checks:
			                                                            // - 1st character - Card Verification Value check
			                                                            // - 2nd character - postcode AVS check
			                                                            // - 3rd character - address AVS check
			                                                            // - 4th character - country comparison check (see also countryMatch)
			                                                            // The possible values for each result character are:
			                                                            // - 0 - Not supported
			                                                            // - 1 - Not checked
			                                                            // - 2 - Matched
			                                                            // - 4 - Not match
			                                                            // - 8 - Partially match
			$received['wafMerchMessage']  = $_POST['wafMerchMessage'];  // If you have the Risk Management service enabled, you will receive one of the fraud messages listed below:
			                                                            // - waf.warning - Make Payment (test)
			                                                            // - waf.caution - Make Payment (live)
			                                                            // For more detailed explanation about the fraud message, refer to the: Risk Management service guide.
			$received['authentication']   = $_POST['authentication'];   // If you have enrolled to the Verified By Visa or MasterCard SecureCode authentication schemes you will receive one of the authentication messages listed below:
			                                                            // - ARespH.card.authentication.0 - Cardholder authenticated
			                                                            // - ARespH.card.authentication.1 - Cardholder/Issuing Bank not enrolled for authentication
			                                                            // - ARespH.card.authentication.6 - Cardholder authentication not available
			                                                            // - ARespH.card.authentication.7 - Cardholder did not complete authentication
			                                                            // For more detailed explanation about the authentication messages, refer to the: Card Authentication Guide.
			$received['ipAdress']         = $_POST['ipAdress'];         // The IP address from which the purchase token was submitted
			$received['charenc']          = $_POST['charenc'];          // The character encoding used to display the payment page to the shopper
			
			// Custom parameters returned from WorldPay
			$received['MC_invoice']       = $_POST['MC_invoice'];       // Session ID
			
			// Received information is validated in the process_gateway_notification() method
			
			// Save data
			$this->worldpay_ipn_values = $received;
			$this->session_id = $received['MC_invoice'];
			
		} else {
			exit( 'IPN Not Enabled' );
		}
		
	}
	
	
	
	/**
	 * process_gateway_notification method
	 * Receives data from the payment gateway
	 * @access public
	 */
	function process_gateway_notification() {
	
		global $wpdb;
		
		// Check there is an IPN response...
		if ( isset( $this->worldpay_ipn_values['MC_invoice'] ) && is_numeric( $this->worldpay_ipn_values['MC_invoice'] ) ) {
			switch ( $this->worldpay_ipn_values['transStatus'] ) {
				
				// If successful transaction...
				case 'Y':
				
					if ( (float)$this->worldpay_ipn_values['authAmount'] == (float)$this->cart_data['total_price'] ) {
						$this->set_transaction_details( $this->worldpay_ipn_values['transId'], 2 );
						transaction_results( $this->cart_data['session_id'], false );
					}
					break;
				
				// If transaction cancelled...
				case 'C':
					
					$log_id = $wpdb->get_var( "SELECT `id` FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `sessionid`='" . $this->worldpay_ipn_values['MC_invoice'] . "' LIMIT 1" );
					$delete_log_form_sql = "SELECT * FROM `" . WPSC_TABLE_CART_CONTENTS . "` WHERE `purchaseid`='$log_id'";
					$cart_content = $wpdb->get_results( $delete_log_form_sql, ARRAY_A );
					foreach( (array)$cart_content as $cart_item ) {
						$cart_item_variations = $wpdb->query( "DELETE FROM `" . WPSC_TABLE_CART_ITEM_VARIATIONS . "` WHERE `cart_id` = '" . $cart_item['id'] . "'", ARRAY_A );
					}
					$wpdb->query( "DELETE FROM `" . WPSC_TABLE_CART_CONTENTS . "` WHERE `purchaseid`='$log_id'" );
					$wpdb->query( "DELETE FROM `" . WPSC_TABLE_SUBMITED_FORM_DATA . "submited_form_data` WHERE `log_id` IN ('$log_id')" );
					$wpdb->query( "DELETE FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `id`='$log_id' LIMIT 1" );
					break;
				
				// Otherwise, do nothing...
				default:
					break;
				
			}
		}

		$message = "
			{$this->worldpay_ipn_values['receiver_email']} => " . get_option( 'paypal_multiple_business' ) . "
			{$this->worldpay_ipn_values['txn_type']}
			{$this->worldpay_ipn_values['mc_gross']} => {$this->cart_data['total_price']}
			{$this->worldpay_ipn_values['txn_id']}
			" . print_r( $this->cart_items, true ) . "
			{$altered_count}
			";
		
		// Send debug emails in test mode
		if ( is_numeric( $this->worldpay_ipn_values['testMode'] ) && $this->worldpay_ipn_values['testMode'] > 0 ) {
			$message = "\nThis is a debugging message sent because it appears that you are using test mode.\nIt is only sent if you are using WorldPay in test mode.\n\n";
			switch ( $this->worldpay_ipn_values['transStatus'] ) {
				
				case 'Y':
					$message .= "Transaction Successful (transStatus = Y)\n\n";
					break;
				
				case 'C':
					$message .= "Transaction Cancelled (transStatus = C)\n\n";
					break;
				
				default:
					$message .= "Transaction Status Unknown\n(please check you WorldPay admin)\n\n";
					break;
					
			}
			foreach ( $this->worldpay_ipn_values as $key => $val ) {
				$message .= $key . " = " . $val . "\n";
				if ( $key == 'collected_data' ) {
					foreach ( $val as $k => $v ) {
						$message .= $k . " = " . $v . "\n";
					}
					$message .= "\n";
				}
			}
			$subject = '[' . get_bloginfo( 'name' ) . '] WorldPay Test Mode IPN Data';
			mail( get_option( 'purch_log_email' ), $subject, $message );
		}
		
	}
	
	
	
	/**
	 * format_price
	 *
	 * @param $price (number) Price
	 * @return string Formatted price
	 */
	function format_price( $price ) {
		
		$paypal_currency_code = get_option( 'rbsworldpay_redirect_curcode' );
		
		switch ( $paypal_currency_code ) {
			
			case "JPY":
				$decimal_places = 0;
				break;

			case "HUF":
				$decimal_places = 0;

			default:
				$decimal_places = 2;
				break;
			
		}
		
		$price = number_format( sprintf( "%01.2f", $price ), $decimal_places, '.', '' );
		return $price;
		
	}
	
	
	
	/**
	 * get_rbsworldpay_redirect_url
	 * Gets the test or live WorldPay payment reponse URL depending on settings.
	 * Pass the true parameter to force the live URL or false to force the test URL to be returned.
	 *
	 * @return string Payment Response URL
	 */
	function get_rbsworldpay_redirect_url( $live_url = null ) {
		
		$rbsworldpay_redirect_url      = get_option( 'rbsworldpay_redirect_url' );
		$rbsworldpay_redirect_test_url = get_option( 'rbsworldpay_redirect_test_url' );
		
		if ( empty( $rbsworldpay_redirect_url ) ) {
			$rbsworldpay_redirect_url = 'https://secure.wp3.rbsworldpay.com/wcc/purchase';
		}
		if ( empty( $rbsworldpay_redirect_test_url ) ) {
			$rbsworldpay_redirect_test_url = 'https://secure-test.wp3.rbsworldpay.com/wcc/purchase';
		}
		
		if ( $live_url === true ) {
			return $rbsworldpay_redirect_url;
		} elseif ( $live_url === false ) {
			return $rbsworldpay_redirect_test_url;
		} else {
			if ( get_option( 'rbsworldpay_redirect_test_mode' ) > 0 ) {
				return $rbsworldpay_redirect_test_url;
			}
		}
		
		return $rbsworldpay_redirect_url;
		
	}
	
	
	
	/**
	 * rbsworldpay_redirect_settings_form
	 * Gets the output for the settings form for the RBS WorldPay settings.
	 *
	 * @return string Form output
	 */
	function rbsworldpay_redirect_settings_form() {
	
		$select_currency[get_option( 'rbsworldpay_redirect_curcode' )]   = "selected='true'";
		$select_status[get_option( 'rbsworldpay_redirect_test_mode' )]   = "selected='selected'";
		$select_authmode[get_option( 'rbsworldpay_redirect_auth_mode' )] = "selected='selected'";
		
		$rbsworldpay_redirect_url      = wpsc_merchant_rbsworldpay_redirect::get_rbsworldpay_redirect_url( true );
		$rbsworldpay_redirect_test_url = wpsc_merchant_rbsworldpay_redirect::get_rbsworldpay_redirect_url( false );
		
		$output = "
			<tr>
				<td>Installation ID</td>
				<td><input type='text' size='15' value='" . get_option( 'rbsworldpay_redirect_instId' ) . "' name='rbsworldpay_redirect_instId' /></td>
			</tr>
			<tr>
				<td>WorldPay LIVE URL</td>
				<td><input type='text' size='30' value='" . $rbsworldpay_redirect_url . "' name='rbsworldpay_redirect_url' /></td>
			</tr>
			<tr>
				<td>WorldPay Test URL</td>
				<td><input type='text' size='30' value='" . $rbsworldpay_redirect_test_url . "' name='rbsworldpay_redirect_test_url' /></td>
			</tr>
			<tr>
				<td>Test Mode?</td>
				<td>
					<select name='rbsworldpay_redirect_test_mode'>
						<option " . $select_status['100'] . " value='100'>Test mode</option>
						<option " . $select_status['0'] . " value='0'>LIVE Transactions</option>
					</select> 
				</td>
			</tr>
			<tr>
				<td>Auth Mode</td>
				<td>
					<select name='rbsworldpay_redirect_auth_mode'>
						<option " . $select_authmode['A'] . " value='A'>A - Full auth</option>
						<option " . $select_authmode['E'] . " value='E'>E - Pre-auth</option>
					</select><br />
					<small><strong>Important Note:</strong> If you select 'E' pre-auth the card will be authorised but <strong><u>no payment will be taken and your purchase log will still display Accepted Payment!</u></strong> You must then log into your WorldPay account to take the payment.</small>
				</td>
			</tr>
			";
		
		$rbsworldpay_redirect_ipn = get_option( 'rbsworldpay_redirect_ipn' );
		$rbsworldpay_redirect_ipn1 = "";
		$rbsworldpay_redirect_ipn2 = "";
		switch ( $rbsworldpay_redirect_ipn ) {
			case 0:
				$rbsworldpay_redirect_ipn2 = "checked ='true'";
				break;
			case 1:
				$rbsworldpay_redirect_ipn1 = "checked ='true'";
				break;
		}
		
		$output .= "
			<tr>
				<td>WorldPay IPN</td>
				<td>
					<input type='radio' value='1' name='rbsworldpay_redirect_ipn' id='rbsworldpay_redirect_ipn1' " . $rbsworldpay_redirect_ipn1 . " /> <label for='rbsworldpay_redirect_ipn1'>Yes</label> &nbsp;
					<input type='radio' value='0' name='rbsworldpay_redirect_ipn' id='rbsworldpay_redirect_ipn2' " . $rbsworldpay_redirect_ipn2 . " /> <label for='rbsworldpay_redirect_ipn2'>No</label><br />
					<small>If IPN is disabled then the purchase log status will not be updated when a payment is made.</small>
				</td>
			</tr>
			<tr>
				<td>Purchase Currency</td>
				<td>
					<select name='rbsworldpay_redirect_curcode'>
						<option " . $select_currency['AUD'] . " value='AUD'>AUD - Australian Dollar</option>
						<option " . $select_currency['CAD'] . " value='CAD'>CAD - Canadian Dollar</option>
						<option " . $select_currency['DKK'] . " value='DKK'>DKK - Danish Krone</option>
						<option " . $select_currency['EUR'] . " value='EUR'>EUR - Euro</option>
						<option " . $select_currency['HKD'] . " value='HKD'>HKD - Hong Kong Dollar</option>
						<option " . $select_currency['NZD'] . " value='NZD'>NZD - New Zealand Dollar</option>
						<option " . $select_currency['NOK'] . " value='NOK'>NOK - Norwegian Krone</option>
						<option " . $select_currency['GBP'] . " value='GBP'>GBP - Pound Sterling</option>
						<option " . $select_currency['SGD'] . " value='SGD'>SGD - Singapore Dollar</option>
						<option " . $select_currency['SEK'] . " value='SEK'>SEK - Swedish Krona</option>
						<option " . $select_currency['CHF'] . " value='CHF'>CHF - Swiss Franc</option>
						<option " . $select_currency['USD'] . " value='USD'>USD - U.S. Dollar</option>
					</select> 
				</td>
			</tr>";
		
		$payment_logos = get_option( 'rbsworldpay_redirect_payment_logos' );
		$payment_logos_val['no'] = '';
		$payment_logos_val['js'] = '';
		$payment_logos_val['wpsc'] = '';
		switch ( $payment_logos ) {
			case 'js':
				$payment_logos_val['js'] = "checked ='true'";
				break;
			case 'wpsc':
				$payment_logos_val['wpsc'] = "checked ='true'";
				break;
			case 'no':
			default:
				$payment_logos_val['no'] = "checked ='true'";
				break;
		}
		
		$output .= "
			<tr>
				<td>Show payment logos:</td>
				<td>
					<input type='radio' value='no' name='rbsworldpay_redirect_payment_logos' id='payment_logos_1' " . $payment_logos_val['no'] . " /> <label for='payment_logos_1'>No</label><br />
					<input type='radio' value='js' name='rbsworldpay_redirect_payment_logos' id='payment_logos_2' " . $payment_logos_val['js'] . " /> <label for='payment_logos_2'>Using WorldPay Javascript</label><br />
					<input type='radio' value='wpsc' name='rbsworldpay_redirect_payment_logos' id='payment_logos_2' " . $payment_logos_val['wpsc'] . " /> <label for='payment_logos_2'>Custom logos</label><br />
					<small>(custom logos can be added using the 'wpsc_display_rbsworldpay_redirect_logos' <a href='http://codex.wordpress.org/Function_Reference/add_action' target='_blank'>action hook</a>)</small>
				</td>
			</tr>
			<tr class='update_gateway' >
				<td colspan='2'><div class='submit'><input type='submit' value='Update &raquo;' name='updateoption' /></div></td>
			</tr>
			<tr class='firstrowth'>
				<td style='border-bottom: medium none;' colspan='2'><strong class='form_group'>Forms Sent to Gateway</strong></td>
			</tr>
			<tr>
				<td>First Name Field</td>
				<td><select name='rbsworldpay_redirect_form[first_name]'>" . nzshpcrt_form_field_list( get_option( 'rbsworldpay_redirect_form_first_name' ) ) . "</select></td>
			</tr>
			<tr>
				<td>Last Name Field</td>
				<td><select name='rbsworldpay_redirect_form[last_name]'>" . nzshpcrt_form_field_list( get_option( 'rbsworldpay_redirect_form_last_name' ) ) . "</select></td>
			</tr>
			<tr>
				<td>Email Field</td>
				<td><select name='rbsworldpay_redirect_form[email]'>" . nzshpcrt_form_field_list( get_option( 'rbsworldpay_redirect_form_email' ) ) . "</select></td>
			</tr>
			<tr>
				<td>Address Field</td>
				<td><select name='rbsworldpay_redirect_form[address]'>".nzshpcrt_form_field_list( get_option( 'rbsworldpay_redirect_form_address' ) ) . "</select></td>
			</tr>
			<tr>
				<td>City Field</td>
				<td><select name='rbsworldpay_redirect_form[city]'>" . nzshpcrt_form_field_list( get_option( 'rbsworldpay_redirect_form_city' ) ) . "</select></td>
			</tr>
			<tr>
				<td>Postal code/Zip code Field</td>
				<td><select name='rbsworldpay_redirect_form[post_code]'>" . nzshpcrt_form_field_list( get_option( 'rbsworldpay_redirect_form_post_code' ) ) . "</select></td>
			</tr>
			<tr>
				<td>Country Field</td>
				<td><select name='rbsworldpay_redirect_form[country]'>" . nzshpcrt_form_field_list( get_option( 'rbsworldpay_redirect_form_country' ) ) . "</select></td>
			</tr>";
		
		return $output;
		
	}
	
	
	
	/**
	 * submit_rbsworldpay_redirect_settings_form
	 * Processes the settings form for the RBS WorldPay settings.
	 *
	 * @return bool
	 */
	function submit_rbsworldpay_redirect_settings_form() {
	
		if ( isset( $_POST['rbsworldpay_redirect_instId'] ) ) {
			update_option( 'rbsworldpay_redirect_instId', $_POST['rbsworldpay_redirect_instId'] );
		}
		if ( isset( $_POST['rbsworldpay_redirect_url'] ) ) {
			update_option( 'rbsworldpay_redirect_url', $_POST['rbsworldpay_redirect_url'] );
		}
		if ( isset( $_POST['rbsworldpay_redirect_test_url'] ) ) {
			update_option( 'rbsworldpay_redirect_test_url', $_POST['rbsworldpay_redirect_test_url'] );
		}
		if ( isset( $_POST['rbsworldpay_redirect_test_mode'] ) ) {
			update_option( 'rbsworldpay_redirect_test_mode', $_POST['rbsworldpay_redirect_test_mode'] );
		}
		if ( isset( $_POST['rbsworldpay_redirect_auth_mode'] ) ) {
			update_option( 'rbsworldpay_redirect_auth_mode', $_POST['rbsworldpay_redirect_auth_mode'] );
		}
		if ( isset( $_POST['rbsworldpay_redirect_curcode'] ) ) {
			update_option( 'rbsworldpay_redirect_curcode', $_POST['rbsworldpay_redirect_curcode'] );
		}
		if ( isset( $_POST['rbsworldpay_redirect_ipn'] ) ) {
			update_option( 'rbsworldpay_redirect_ipn', (int)$_POST['rbsworldpay_redirect_ipn'] );
		}
		if ( isset( $_POST['rbsworldpay_redirect_payment_logos'] ) ) {
			update_option( 'rbsworldpay_redirect_payment_logos', $_POST['rbsworldpay_redirect_payment_logos'] );
		}
		
		foreach ( (array)$_POST['rbsworldpay_redirect_form'] as $form => $value ) {
			update_option( ( 'rbsworldpay_redirect_form_' . $form ), $value );
		}
		
		return true;
		
	}
	
	
	
	/**
	 * reset_basket
	 * Clear the basket session.
	 * Unfortunately WorldPay does not allow use to specifiy a return URL dynamically so
	 * In your WorldPay account you need to set the return URL to pass a paramater to
	 * clear the session cart. It should look a bit like:
	 * http://www.example.com/?rbsworldpay_redirect_reset=true
	 */
	function reset_basket() {
		
		if ( isset( $_GET['rbsworldpay_redirect_reset'] ) ) {
			$_SESSION['wpsc_cart'] = null;
			$GLOBALS['wpsc_cart']  = new wpsc_cart;
		}
		
	}

	
	
}



?>