<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles and process orders from asyncronous flows.
 *
 */
class WC_PLEXYPAY_Payments_Order_Handler extends WC_PLEXYPAY_Payments_Gateway {
    public function __construct() {
        add_action( 'woocommerce_order_status_changed', array( $this, 'capture_payment' ), 10, 3 );
        add_action( 'woocommerce_order_status_cancelled', array( $this, 'process_refund' ) );
        add_action( 'woocommerce_order_status_refunded', array( $this, 'process_refund' ) );
        parent::__construct();
    }

    /**
     * Capture payment when the order is changed from on-hold to complete or processing.
     *
     * @param  int $order_id
     */
    public function capture_payment( $order_id, $previous_status, $next_status ) {
        $order = wc_get_order( $order_id );

        if ( ! $this->can_process( $order ) ) {
            return false;
        }

        if (
            $order->get_meta('plexypay_status', true) != 'authorized' ||
            ! in_array($next_status, [ 'processing', 'completed' ]) ||
            $previous_status != 'on-hold'
        ) {
            return false;
        }

        try {
            $order_total = floatval( $order->get_total() );
            $order_total_refunded = floatval( $order->get_total_refunded() );

            if ( 0 < $order_total_refunded ) {
                $order_total = $order_total - $order_total_refunded;
            }

            $result = $this->plexypay->capture_transaction(
                $order->get_transaction_id(),
                $order_total
            );

            if( empty($result) || empty($result['id']) ) {
                return false;
            }

            $order->add_order_note( sprintf(__( 'PlexyPay transaction complete (Transaction ID: %s)', 'woocommerce-gateway-plexypay' ), $result['id']) );
            $order->update_meta_data( 'plexypay_status', 'charged' );
            $order->set_transaction_id( $result['id'] );

            if ( is_callable( array( $order, 'save' ) ) ) {
                $order->save();
            }

            return true;

        } catch (Exception $e) {
            $this->logger->error( json_encode($e) );
            return false;
        }
    }

}

new WC_PLEXYPAY_Payments_Order_Handler();
