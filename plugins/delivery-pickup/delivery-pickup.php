<?php
/**
* Plugin name: Delivery pickup at store
* Author: Izabella
*/

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    function delivery_pickup_store_init() {
        if (!class_exists('WC_Delivery_Pickup_Method')) {
            class WC_Delivery_Pickup_Method extends WC_Shipping_Method {
                public function __construct() {
                    $this->id = 'delivery_pickup_method';
                    $this->method_title = __('Pickup at store');
                    $this->method_description = __('Lets the customer choose from which store they want to pickup.');
                    $this->enabled = 'yes';
                    $this->title = "Pickup at ";
                    $this->init();
                }

                function init() {
                    $this->init_form_fields();
                    $this->init_settings();
                    add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
                }

                function init_form_fields() {
                    $this->form_fields = [
                        'min_amount' => [
                            'title' => __('Minimum order amount', 'woocommerce'),
                            'type' => 'text',
                            'description' => __('Minimum order amount', 'woocommerce'),
                            'default' => __('400', 'woocommerce')
                        ],
                        'cost' => [
                            'title' => __('Cost', 'woocommerce'),
                            'type' => 'text',
                            'description' => __('If total puchase amount does not extend minimum order amount, choose which price to pay. (ex VAT)', 'woocommerce'),
                            'default' => __('100', 'woocommerce')
                        ]
                    ];
                }

                public function calculate_shipping($package = []) {
                    global $woocommerce;
                    $amount = $woocommerce->cart->cart_contents_total+$woocommerce->cart->tax_total;
                    $min_amount = $this->get_option('min_amount');
                    $cost = $this->get_option('cost');

                    $stores = get_posts(
                        [
                            'post_type' => 'stores',
                            'numberposts' => -1
                        ]
                    );

                    foreach ($stores as $store) {
                        $rate = [
                            'id' => $this->id.'-'.$store->post_name,
                            'label' => $this->title.' '.$store->post_title,
                            'cost' => ($amount < $min_amount) ? $cost : '0',
                            'calc_tax' => 'per_order'
                        ];
                        $this->add_rate($rate);
                    }
                }
            }
        }
    }
    add_action('woocommerce_shipping_init', 'delivery_pickup_store_init');
    function add_delivery_pickup_method($methods) {
        $methods['delivery_pickup_method'] = 'WC_Delivery_Pickup_Method';
        return $methods;
    }
    add_filter('woocommerce_shipping_methods', 'add_delivery_pickup_method');
}
