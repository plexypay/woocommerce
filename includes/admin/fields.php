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

function get_plexypay_admin_fields() {
   return array(
      'enabled' => array(
         'title'       => __( 'Enable/Disable', 'woocommerce-gateway-plexypay' ),
         'label'       => __( 'Enabled PlexyPay Gateway', 'woocommerce-gateway-plexypay' ),
         'type'        => 'checkbox',
         'description' => '',
         'default'     => 'no'
      ),
      'title' => array(
         'title'       => __( 'Title', 'woocommerce-gateway-plexypay' ),
         'type'        => 'text',
         'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-gateway-plexypay' ),
         'default'     => 'PlexyPay',
         'desc_tip'    => true,
      ),
      'description' => array(
         'title'       => __( 'Description', 'woocommerce-gateway-plexypay' ),
         'type'        => 'text',
         'desc_tip'    => true,
         'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-gateway-plexypay' ),
         'default'     => 'Card payment method',
      ),
      'private_key' => array(
         'title'       => __( 'LIVE Private Key', 'woocommerce-gateway-plexypay' ),
         'type'        => 'text'
      ),
      'public_key' => array(
         'title'       => __( 'LIVE Public Key ', 'woocommerce-gateway-plexypay' ),
         'type'        => 'text'
      ),
      'terminal_id' => array(
         'title'       => __( 'LIVE Terminal ID', 'woocommerce-gateway-plexypay' ),
         'type'        => 'text'
      ),
      'transaction_type' => array(
         'title'       => __( 'Transaction type', 'woocommerce-gateway-plexypay' ),
         'type'        => 'select',
         'class'       => 'wc-enhanced-select',
         'description' => __( 'Choose whether you wish to capture funds immediately or authorize payment only.', 'woocommerce-gateway-plexypay' ),
         'default'     => 'auth',
         'desc_tip'    => true,
         'options'     => array(
            'auth' => __( 'Authorisation', 'woocommerce-gateway-plexypay' ),
            'sale' => __( 'Sale', 'woocommerce-gateway-plexypay' )
         ),
      ),
      'integration_type' => array(
         'title'       => __( 'Payment form integration type', 'woocommerce-gateway-plexypay' ),
         'type'        => 'select',
         'class'       => 'wc-enhanced-select',
         'default'     => 'redirect',
         'desc_tip'    => true,
         'options'     => array(
            'redirect' => __( 'Full Redirect', 'woocommerce-gateway-plexypay' ),
            'popup' => __( 'iFrame Popup', 'woocommerce-gateway-plexypay' )
         ),
       ),
      'is_test_mode' => array(
         'title'       => __( 'Test mode', 'woocommerce-gateway-plexypay' ),
         'label'       => 'Enable Test Mode',
         'type'        => 'checkbox',
         'description' => __( 'Place the payment gateway in test mode using test Privte/Public keys.', 'woocommerce-gateway-plexypay' ),
         'default'     => 'no',
         'desc_tip'    => true,
      ),
      'test_private_key' => array(
         'title'       => __( 'TEST Private Key', 'woocommerce-gateway-plexypay' ),
         'type'        => 'text'
      ),
      'test_public_key' => array(
         'title'       => __( 'TEST Public Key', 'woocommerce-gateway-plexypay' ),
         'type'        => 'text'
      ) ,
      'test_terminal_id' => array(
         'title'       => __( 'TEST Terminal ID', 'woocommerce-gateway-plexypay' ),
         'type'        => 'text',
      ),
      'success_url' => array(
         'title'       => __( 'Success back link', 'woocommerce-gateway-plexypay' ),
         'type'        => 'textarea',
         'description' => __( 'URL for success page.', 'woocommerce-gateway-plexypay' ),
         'desc_tip'    => true
      ),
      'failure_url' => array(
         'title'       => __( 'Failure back link', 'woocommerce-gateway-plexypay' ),
         'type'        => 'textarea',
         'description' => __( 'URL for failure page.', 'woocommerce-gateway-plexypay' ),
         'desc_tip'    => true
      ),
      'gateway_order_description' => array(
         'title'       => __( 'Gateway order description', 'woocommerce-gateway-plexypay' ),
         'type'        => 'textarea',
         'default'     => 'Pay with your credit card via our payment gateway',
      )
)  ;
}
