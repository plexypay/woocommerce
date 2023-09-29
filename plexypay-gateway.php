<?php

/*
 * Plugin Name: WooCommerce PlexyPay Gateway
 * Plugin URI: https://www.plexypay.com
 * Description: Take credit card payments on your store.
 * Author: PlexyPay Integration
 * Author URI: https://www.plexypay.com
 * Version: 1.0.0
*/

define( 'WC_PLEXYPAY_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WC_PLEXYPAY_MAIN_FILE', __FILE__ );
define( 'WC_PLEXYPAY_VERSION', '1.0.0' );
define( 'WC_PLEXYPAY_MIN_PHP_VER', '5.6.0' );
define( 'WC_PLEXYPAY_MIN_WC_VER', '3.0' );

add_filter( 'woocommerce_payment_gateways', 'plexypay_add_gateway_class' );
function plexypay_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_PLEXYPAY_Payments_Gateway';
	return $gateways;
}

add_action( 'plugins_loaded', 'plexypay_init_gateway_class' );

/**
 * WooCommerce fallback notice.
 *
 * @since 1.0.1
 * @return string
 */
function woocommerce_plexypay_missing_wc_notice() {
    echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'PlexyPay require WooCommerce to be installed and active. You can download %s here.', 'woocommerce-gateway-plexypay' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
}

/**
 * WooCommerce not supported fallback notice.
 *
 * @since 1.0.1
 * @return string
 */
function woocommerce_plexypay_wc_not_supported_notice() {
    echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'PlexyPay require WooCommerce %1$s or greater to be installed and active. WooCommerce %2$s is no longer supported.', 'woocommerce-gateway-plexypay' ), WC_PLEXYPAY_MIN_WC_VER, WC_VERSION ) . '</strong></p></div>';
}

function woocommerce_plexypay_php_not_supported_notice()
{
    echo '<div class="error"><p><strong>' . sprintf( esc_html____( 'WooCommerce PlexyPay - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'woocommerce-gateway-plexypay' ), WC_PLEXYPAY_MIN_PHP_VER, phpversion() ) . '</strong></p></div>';
}


function plexypay_init_gateway_class() {

    load_plugin_textdomain( 'woocommerce-gateway-plexypay', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'woocommerce_plexypay_missing_wc_notice' );
        return;
    }

    if ( version_compare( WC_VERSION, WC_PLEXYPAY_MIN_WC_VER, '<' ) ) {
        add_action( 'admin_notices', 'woocommerce_plexypay_wc_not_supported_notice' );
        return;
    }

    if (version_compare(PHP_VERSION, WC_PLEXYPAY_MIN_PHP_VER, '<')) {
        add_action('admin_notices', 'woocommerce_plexypay_php_not_supported_notice');
        return;
    }


	static $gateway;

	if ( ! isset( $gateway ) ) {
        require_once( WC_PLEXYPAY_PLUGIN_PATH . '/includes/WC_PLEXYPAY_Payments_Gateway.php' );
        require_once( WC_PLEXYPAY_PLUGIN_PATH . '/includes/admin/handlers.php' );
		$gateway = new WC_PLEXYPAY_Payments_Gateway();
	}

	return $gateway;
}

