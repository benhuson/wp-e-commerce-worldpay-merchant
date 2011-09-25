<?php

/*
Plugin Name: WPSC RBS WorldPay Merchant (redirect)
Description: RBS WorldPay Integration for WP e-Commerce.
Author: Ben Huson
*/

function wpscm_worldpay_plugin_path() {
	return WP_PLUGIN_DIR. '/' . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) );
}

function wpscm_worldpay_module( $nzshpcrt_gateways ) {
	$num = count( $nzshpcrt_gateways ) + 1;
	$merchant_directory = dirname( __FILE__ ) . '/merchants/';
	$merchant_includes = array( 'rbs-worldpay-redirect.php' );
	foreach ( $merchant_includes as $merchant_include ) {
		include_once( $merchant_directory . $merchant_include );
		$num++;
	}
	return $nzshpcrt_gateways;
}
add_filter( 'wpsc_gateway_modules', 'wpscm_worldpay_module' );

?>