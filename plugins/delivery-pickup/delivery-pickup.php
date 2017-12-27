<?php
/**
* Plugin name: Delivery pickup at store
* Author: Izabella
*/

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    function your_shipping_method_init() {
        if (!class_exists( 'WC_Your_Shipping_Method')) {
            class WC_Your_Shipping_Method extends WC_Shipping_Method {
                public function __construct() {
                    $this->id = 'your_shipping_method';
                    $this->method_title = __('Local pickup at store');
                    $this->method_description = __('Lets the customer choose which store they want to pickup.');
                    $this->enabled = 'yes';
                    $this->title = "Local pickup at ";
                    $this->init();
                }

                function init() {
                    $this->init_form_fields();
                    $this->init_settings();
                    add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
                }

                function init_form_fields() {
                    $this->form_fields = [
                        'title' => [
                            'title' => __('Title', 'woocommerce'),
                            'type' => 'text',
                            'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                            'default' => __('Local pickup', 'woocommerce')
                        ],
                        'description' => [
                            'title' => __('Description', 'woocommerce'),
                            'type' => 'textarea',
                            'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                            'default' => __('Choose which store to pickup your package', 'woocommerce')
                        ],
                        'min_amount' => [
                            'title' => __('Minimun order amount', 'woocommerce'),
                            'type' => 'text',
                            'description' => __('This controls the description which the user sees during checkout.', 'woocommerce'),
                            'default' => __('400', 'woocommerce')
                        ],
                        'cost' => [
                            'title' => __('Cost', 'woocommerce'),
                            'type' => 'text',
                            'description' => __('If total puchase amount does not extend minimum order amount, choose which price to pay', 'woocommerce'),
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
                            'calc_tax' => 'per_item'
                        ];
                        $this->add_rate($rate);
                    }
                }
            }
        }
    }
    add_action( 'woocommerce_shipping_init', 'your_shipping_method_init' );
    function add_your_shipping_method($methods) {
        $methods['your_shipping_method'] = 'WC_Your_Shipping_Method';
        return $methods;
    }
    add_filter( 'woocommerce_shipping_methods', 'add_your_shipping_method' );
}