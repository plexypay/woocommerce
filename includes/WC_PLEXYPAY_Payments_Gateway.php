<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( is_readable( WC_PLEXYPAY_PLUGIN_PATH . '/vendor/autoload.php' ) ) {
    require WC_PLEXYPAY_PLUGIN_PATH . '/vendor/autoload.php';
}

require_once WC_PLEXYPAY_PLUGIN_PATH . '/includes/admin/fields.php';
require_once( WC_PLEXYPAY_PLUGIN_PATH . '/includes/client/helpers.php' );

class WC_PLEXYPAY_Payments_Gateway extends WC_Payment_Gateway {

    /**
     * @var bool
     */
    public $is_test_mode;
    /**
     * @var string
     */
    public $terminal_id;
    /**
     * @var string
     */
    public $private_key;
    /**
     * @var string
     */
    public $public_key;
    /**
     * @var string
     */
    public $integration_type;
    /**
     * @var \Plexypay\Plexypay
     */
    public $plexypay;

    public $helpers;

    public $logger;

    public function __construct() {

        $this->id = 'plexypay';
        $this->icon = '';
        $this->method_title = 'PlexyPay Gateway';
        $this->method_description = 'Card payment method';

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->enabled = $this->get_option( 'enabled' );
        $this->is_test_mode = 'yes' === $this->get_option( 'is_test_mode' );
        $this->integration_type = $this->get_option( 'integration_type' );

        $this->private_key = $this->is_test_mode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
        $this->public_key = $this->is_test_mode ? $this->get_option( 'test_public_key' ) : $this->get_option( 'public_key' );
        $this->terminal_id = $this->is_test_mode ? $this->get_option( 'test_terminal_id' ) : $this->get_option('terminal_id');
        
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts') );
        load_plugin_textdomain( 'woocommerce-gateway-plexypay', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        add_action( 'rest_api_init', array( $this, 'register_routes' ));

        $this->logger = wc_get_logger();
        $this->helpers = new WC_PLEXYPAY_Payments_Client_Helpers();
        $this->plexypay = new Plexypay\Plexypay([
            'env' => $this->is_test_mode ? 'test' : 'prod',
            'private_key' => $this->private_key
        ]);
        $this->supports = array( 'products', 'refunds' );
    }

    public function can_process( $order ) {
        return (
            $order &&
            $order->get_transaction_id() &&
            $this->helpers->is_plexypay_order( $order ) &&
            $this->can_refund_order( $order )
        );
    }

    /**
     * Can the order be refunded via PlexyPay
     *
     * @param  WC_Order $order Order object.
     * @return bool
     */
    public function can_refund_order( $order ) {
        return in_array($order->get_meta('plexypay_status', true), array('charged', 'authorized'));
    }

    /**
     * Refund a charge.
     *
     * @param  int $order_id
     * @param  float $amount
     * @return bool
     */
    public function process_refund( $order_id, $amount = null, $reason = '') {

        $order = wc_get_order( $order_id );

        if ( ! $this->can_process( $order ) ) {
            return false;
        }

        try {
            switch ($order->get_meta('plexypay_status', true)) {
                case 'authorized':
                    $result = $this->plexypay->cancel_transaction(
                        $order->get_transaction_id()
                    );
                    $order->update_meta_data('plexypay_status', 'cancelled');
                    break;
                case 'charged':
                    $result = $this->plexypay->refund_transaction(
                        $order->get_transaction_id(),
                        floatval( $amount )
                    );
                    $order->update_meta_data('plexypay_status', 'refunded');
                    break;
            }

            $order->save();

            return true;

        } catch (Exception $e) {
            $this->logger->error( json_encode($e) );
            return false;
        }
    }

    public function register_routes() {
        register_rest_route( 'plexypay', 'callback', array(
            'methods'  => WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'callback_webhook'),
            'permission_callback' => '__return_true'
        ) );

        register_rest_route( 'plexypay', 'failure-page', array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array( $this, 'fail_page_webhook'),
            'permission_callback' => '__return_true'
        ) );
    }

     public function callback_webhook( WP_REST_Request $input ) {
        if (
            empty($input) || 
            empty($input['orderReference']) ||
            !$this->plexypay->is_valid_signature($input, $input->get_header('x-signature'))
        ) {
            return;
        }

        $order = wc_get_order($input['orderReference']);

        if(!$this->helpers->is_plexypay_order($order) || $this->helpers->is_order_completed($order)) {
            return;
        }

        if ($input['id']) {
            $order->set_transaction_id($input['id']);
        }

        if ($input['success']) {
            if($input['settled']) {
                $order->payment_complete();
                $order->add_order_note( sprintf( __( 'PlexyPay transaction complete (Transaction ID: %s)', 'woocommerce-gateway-plexypay' ), $input['id']) );
            } else {
                $order->update_status('on-hold');
                $order->add_order_note( sprintf( __( 'PlexyPay awaiting payment complete (Transaction ID: %s)', 'woocommerce-gateway-plexypay' ), $input['id']) );
            }
    
            $order->update_meta_data('externalReference', $input['externalReference']);
            $order->update_meta_data('payment_method', $input['paymentMethod']);
            $order->update_meta_data('plexypay_status', $input['settled'] ? 'charged' : 'authorized');
    
            $order->reduce_order_stock();
        } else {
            $order->update_status('failed', 'Payment failed');
        }

        $order->save();
    }

    public function fail_page_webhook() {
        $this->helpers->add_notice( __('Oups! Payment failed! Please, try later...', 'woocommerce-gateway-plexypay'), 'error');
        wp_redirect( get_permalink( wc_get_page_id( 'cart' ) ) );
        exit();
    }

    public function init_form_fields(){
        $this->form_fields = get_plexypay_admin_fields();
    }

    public function payment_scripts() {
        if ( ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
            return;
        }

        if ( 'no' === $this->enabled || empty( $this->private_key ) ) {
            return;
        }

        wp_enqueue_script( 'plexypay-api', 'https://checkout.plexypay.com/pay/api.js' , array('jquery'), '1.0', true );
        wp_register_script('woocommerce_plexypay', plugins_url('assets/js/plexypay.js', WC_PLEXYPAY_MAIN_FILE), array('jquery', 'plexypay-api') , WC_PLEXYPAY_VERSION, true);


        $plexypay_params = array(
            'is_test_mode' => $this->is_test_mode,
            'integration_type' => $this->integration_type
        );
        wp_localize_script( 'woocommerce_plexypay', 'wc_plexypay_params', apply_filters( 'wc_plexypay_params', $plexypay_params ) );
        wp_enqueue_script('woocommerce_plexypay');
    }

    public function validate_fields() {
        if( strlen ( $_POST[ 'billing_country' ]) > 2 ) {
            $this->helpers->add_notice(__('Country must be less than 2 symbols', 'woocommerce-gateway-plexypay' ), 'error');
            return false;
        } else if( strlen ( $_POST[ 'billing_city' ]) > 50 ) {
            $this->helpers->add_notice(__ ('City must be less than 50 symbols', 'woocommerce-gateway-plexypay' ), 'error');
            return false;
        } else if( strlen ( $_POST[ 'billing_address_1' ]) > 50 ) {
            $this->helpers->add_notice( __('Address must be less than 50 symbols', 'woocommerce-gateway-plexypay' ), 'error');
            return false;
        }  else if( strlen ( $_POST[ 'billing_email' ]) > 256 ) {
            $this->helpers->add_notice( __('Email must be less than 256 symbols', 'woocommerce-gateway-plexypay' ), 'error');
            return false;
        } else if( strlen ( $_POST[ 'billing_last_name' ]) > 32 ) {
            $this->helpers->add_notice(__('Lastname must be less than 32 symbols', 'woocommerce-gateway-plexypay' ), 'error');
            return false;
        } else if( strlen ( $_POST[ 'billing_first_name' ]) > 32 ) {
            $this->helpers->add_notice(__( 'Firstname must be less than 32 symbols', 'woocommerce-gateway-plexypay' ), 'error');
            return false;
        } else if( strlen ( $_POST[ 'billing_postcode' ]) > 13 ) {
            $this->helpers->add_notice(__('Postcode must be less than 13 symbols', 'woocommerce-gateway-plexypay' ), 'error');
            return false;
        }

        return true;
    }

    public function process_payment( $order_id ) {
        global $woocommerce;
        global $wp;

        try {
            $current_url = home_url( add_query_arg( array(), $wp->request ) );
            $order = wc_get_order( $order_id );

            $success = $this->helpers->get_link( $this->get_option('success_url'), $this->get_return_url($order) );
            $failure = $this->helpers->get_link( $this->get_option('failure_url'), get_rest_url(null, 'plexypay/failure-page') );

            $result = $this->plexypay->create_payment_session([
                'terminalId' => $this->terminal_id,
                'transactionType' => $this->get_option('transaction_type'),
                'locale' => 'en-gb', 
                'urls' => [
                    'cancel' => $current_url,
                    'success' => $success,
                    'failure' => $failure,
                    'callback' => get_rest_url(null, 'plexypay/callback')
                ],
                'customerDetails' => $this->helpers->customer_details($order),
                'orderDetails' => $this->helpers->order_details($order, $this->get_option('gateway_order_description'))
            ]);

            return array(
                'result' => 'success',
                'redirectUrl' => $result['redirectUrl'],
                'urls' => [
                    'success' => $success,
                    'failure' => $failure
                ]
            );
        } catch (Error $e) {
            return array(
                'result' => 'failure',
                'messages' => [
                    __('Could not create payment sesssion', 'woocommerce-gateway-plexypay')
                ],
                'error' => json_encode($e)
            );
        }

    }
}
