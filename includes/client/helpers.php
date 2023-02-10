<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_FOROPAY_Payments_Client_Helpers {

    function order_details(WC_Abstract_order $order, $description) {
        return [
            'description' => $description,
            'orderReference' => strval($order->get_order_number()),
            'cartReference' => null,
            'amount' => floatval($order->get_total()),
            'currency' => $order->get_currency(),
            'items' => []
        ];
    }

    function customer_details(WC_Abstract_order $order) {
        return [
            'accountId' => $order->get_customer_id() ? strval($order->get_customer_id()) : '',
            'email' => $order->get_billing_email(),
            'deliveryAddress' => [
                'firstName' => $order->get_shipping_first_name(),
                'lastName' => $order->get_shipping_last_name(),
                'addressLine1' => $order->get_shipping_address_1(),
                'addressLine2' => $order->get_shipping_address_2(),
                'postalCode' => $order->get_shipping_postcode(),
                'city' => $order->get_shipping_city(),
                'phone' => $this->get_shipping_phone($order),
                'country' => $order->get_shipping_country()
            ],
            'billingAddress' => [
                'firstName' => $order->get_billing_first_name(),
                'lastName' => $order->get_billing_last_name(),
                'addressLine1' => $order->get_billing_address_1(),
                'addressLine2' => $order->get_billing_address_2(),
                'postalCode' => $order->get_billing_postcode(),
                'city' => $order->get_billing_city(),
                'phone' => $order->get_billing_phone(),
                'country' => $order->get_billing_country()
            ]
        ];
    }

    function get_shipping_phone(WC_Abstract_order $order) {
        $shipping_phone = '';
        if (version_compare( WC_VERSION, '5.6.0', '<' )) {
            $shipping_phone = $order->get_meta('_shipping_phone');
        } else {
            $shipping_phone = $order->get_shipping_phone();
        }
        return $shipping_phone ? $shipping_phone : $order->get_billing_phone();
    }

    function is_order_completed(WC_Order $order) {
        return !in_array( $order->get_status(), ['pending', 'failed'] );
    }

    function is_foropay_order(WC_Order $order): bool {
        return 'foropay' === $order->get_payment_method();
    }

    function get_link( $link, $default_link ) {
        if ( empty( $link ) ) {
            return $default_link;
        } elseif ( !$this->is_absolute($link) ) {
            return get_site_url(null, $link);
        }
        return $link;
    }

    function is_absolute( $url ) {
        $pattern = "/^(?:ftp|https?|feed):\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
            (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
            (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
            (?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

        return (bool) preg_match($pattern, $url);
    }

    function add_notice($message, $notice_type = 'success', $data = array()) {
        if(function_exists(wc_add_notice)) {
            wc_add_notice($message, $notice_type, $data);
        }
    }
}
