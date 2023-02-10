<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_order_statuses() {
   $statuses = wc_get_order_statuses();
   $statuses_without_prefix = [];
   foreach ($statuses as $key => $value) {
      $key__without_prefix = str_replace('wc-', '', $key);
      $statuses_without_prefix[$key__without_prefix] = $value;
   }
   return $statuses_without_prefix;
}

function get_foropay_admin_fields() {
   return array(
      'enabled' => array(
         'title'       => __( 'Enable/Disable', 'woocommerce-gateway-foropay' ),
         'label'       => __( 'Enabled Foropay Gateway', 'woocommerce-gateway-foropay' ),
         'type'        => 'checkbox',
         'description' => '',
         'default'     => 'no'
      ),
      'title' => array(
         'title'       => __( 'Title', 'woocommerce-gateway-foropay' ),
         'type'        => 'text',
         'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-foropay' ),
         'default'     => 'ForoPay',
         'desc_tip'    => true,
      ),
      'description' => array(
         'title'       => __( 'Description', 'woocommerce-gateway-foropay' ),
         'type'        => 'text',
         'desc_tip'    => true,
         'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-foropay' ),
         'default'     => 'Card payment method',
      ),
      'private_key' => array(
         'title'       => __( 'LIVE Private Key', 'woocommerce-gateway-foropay' ),
         'type'        => 'text'
      ),
      'public_key' => array(
         'title'       => __( 'LIVE Public Key ', 'woocommerce-gateway-foropay' ),
         'type'        => 'text'
      ),
      'terminal_id' => array(
         'title'       => __( 'LIVE Terminal ID', 'woocommerce-gateway-foropay' ),
         'type'        => 'text'
      ),
      'transaction_type' => array(
         'title'       => __( 'Transaction type', 'woocommerce-gateway-foropay' ),
         'type'        => 'select',
         'class'       => 'wc-enhanced-select',
         'description' => __( 'Choose whether you wish to capture funds immediately or authorize payment only.', 'woocommerce-gateway-foropay' ),
         'default'     => 'auth',
         'desc_tip'    => true,
         'options'     => array(
            'auth' => __( 'Authorisation', 'woocommerce-gateway-foropay' ),
            'sale' => __( 'Sale', 'woocommerce-gateway-foropay' )
         ),
      ),
      'integration_type' => array(
         'title'       => __( 'Payment form integration type', 'woocommerce-gateway-foropay' ),
         'type'        => 'select',
         'class'       => 'wc-enhanced-select',
         'default'     => 'redirect',
         'desc_tip'    => true,
         'options'     => array(
            'redirect' => __( 'Full Redirect', 'woocommerce-gateway-foropay' ),
            'popup' => __( 'iFrame Popup', 'woocommerce-gateway-foropay' )
         ),
       ),
      'is_test_mode' => array(
         'title'       => __( 'Test mode', 'woocommerce-gateway-foropay' ),
         'label'       => 'Enable Test Mode',
         'type'        => 'checkbox',
         'description' => __( 'Place the payment gateway in test mode using test Privte/Public keys.', 'woocommerce-gateway-foropay' ),
         'default'     => 'no',
         'desc_tip'    => true,
      ),
      'test_private_key' => array(
         'title'       => __( 'TEST Private Key', 'woocommerce-gateway-foropay' ),
         'type'        => 'text'
      ),
      'test_public_key' => array(
         'title'       => __( 'TEST Public Key', 'woocommerce-gateway-foropay' ),
         'type'        => 'text'
      ) ,
      'test_terminal_id' => array(
         'title'       => __( 'TEST Terminal ID', 'woocommerce-gateway-foropay' ),
         'type'        => 'text',
      ),
      'success_url' => array(
         'title'       => __( 'Success back link', 'woocommerce-gateway-foropay' ),
         'type'        => 'textarea',
         'description' => __( 'URL for success page.', 'woocommerce-gateway-foropay' ),
         'desc_tip'    => true
      ),
      'failure_url' => array(
         'title'       => __( 'Failure back link', 'woocommerce-gateway-foropay' ),
         'type'        => 'textarea',
         'description' => __( 'URL for failure page.', 'woocommerce-gateway-foropay' ),
         'desc_tip'    => true
      ),
      'gateway_order_description' => array(
         'title'       => __( 'Gateway order description', 'woocommerce-gateway-foropay' ),
         'type'        => 'textarea',
         'default'     => 'Pay with your credit card via our payment gateway',
      )
)  ;
}
