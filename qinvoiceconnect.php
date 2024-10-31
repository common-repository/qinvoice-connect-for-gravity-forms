<?php
/*
Plugin Name: Gravity Forms Qinvoice Connect Add-On
Plugin URI: http://www.q-invoice.com
Description: Fully integrate Gravity Forms with q-invoice for sending invoices
Version: 2.3.2
Author: q-invoice
Author URI: http://www.q-invoice.com
Text Domain: qinvoice-connect-for-gravity-forms
Domain Path: /languages

*/

define( 'GF_QINVOICECONNECT_VERSION', '2.3.2' );

add_action( 'gform_loaded', array( 'GF_QinvoiceConnect_Bootstrap', 'load' ), 5 );

add_action( 'gform_post_payment_action', array ('GF_QinvoiceConnect_Bootstrap', 'after_payment'), 10, 2);

// hook to payments
// specific for Pronamic
add_action( 'gform_ideal_fulfillment', array ('GF_QinvoiceConnect_Bootstrap', 'after_ideal'), 5, 1);


add_action( 'gform_sisow_fulfillment', array ('GF_QinvoiceConnect_Bootstrap', 'after_ideal'), 5, 1);

// paypal -> moved to gform_ideal_fulfillment
// add_action( 'gform_paypal_fulfillment', array ('GF_QinvoiceConnect_Bootstrap', 'after_paypal'), 5, 1);

// all other payments (including paypal)

add_action( 'gform_entry_detail_sidebar_middle', array ('GF_QinvoiceConnect_Bootstrap', 'container'), 1, 2);
add_action( 'wp_ajax_gf_resend_request', array ('GF_QinvoiceConnect_Bootstrap', 'resend'), 1, 2);


function qinvoice_connect_for_gravity_forms_load_textdomain()
{
    load_plugin_textdomain('qinvoice-connect-for-gravity-forms', false, basename(dirname(__FILE__)) . '/languages');
}

add_action('plugins_loaded', 'qinvoice_connect_for_gravity_forms_load_textdomain');

class GF_QinvoiceConnect_Bootstrap {

	public static $_plugin_basename;

	public static function load(){

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		self::$_plugin_basename = plugin_basename(__FILE__);

		require_once( 'class-gf-qinvoice-connect.php' );

		GFAddOn::register( 'GFQinvoiceConnect' );



        //self::get_get();
	}

	public static function after_paypal($entry){

		$gfqc = new GFQinvoiceConnect();
		$gfqc->export_after_payment($entry,'paypal');

	}

	public static function after_ideal($entry){

		$gfqc = new GFQinvoiceConnect();
        $gfqc->export_after_payment($entry,'ideal');

	}

	public static function after_payment($entry, $action){

		$gfqc = new GFQinvoiceConnect();
		$gfqc->export_after_payment($entry, $action);

	}

	public static function container($form,$lead){

		$gfqc = new GFQinvoiceConnect();
		$gfqc->show_entry_options($form, $lead);

	}

	public static function resend(){

		$gfqc = new GFQinvoiceConnect();
		$gfqc->export_resend(GFAPI::get_entry( $_POST['leadId'] ));

	}

}

function gf_qinvoiceconnect(){
	return GFQinvoiceConnect::get_instance();
}