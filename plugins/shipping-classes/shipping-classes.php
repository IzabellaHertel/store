<?php
/**
* Plugin name: Shipping classes
* Author: Izabella
*/

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    function custom_shipping_classes_init() {
        if (!class_exists('WC_Custom_Shipping_Classes_Price')) {
            class WC_Custom_Shipping_Classes_Price extends WC_Shipping_Method {
                public function __construct() {
                    $this->id = 'custom_shipping_classes';
                    $this->method_title = __('Courier');
                    $this->method_description = __('Delivery courier');
                    $this->enabled = 'yes';
                    $this->title = "Delivery courier";
                    $this->init();
                }

                function init() {
                    $this->init_form_fields();
                    $this->init_settings();
                    add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
                }


                function init_form_fields() {
                    $wc_shipping = new WC_Shipping;
                    $shipping_classes = $wc_shipping->get_shipping_classes();
                    $array['standard'] = [
                        'title' => __('Standard', 'woocommerce'),
                        'type' => 'number',
                        'description' => __('Standard shipping price', 'woocommerce')
                    ];
                    foreach ($shipping_classes as $shipping_class) {
                        $array[$shipping_class->slug] = [
                            'title' => __($shipping_class->name, 'woocommerce'),
                            'type' => 'number',
                            'description' => __($shipping_class->name . ' shipping price', 'woocommerce')
                        ];
                    }
                    $this->form_fields = $array;
                }


                public function calculate_shipping($package = []) {
                    // Get the weights of the products in cart
                    $product_weights = [];
                    foreach ($package['contents'] as $item_id => $values) {
                        $product_weights[$values['data']->get_shipping_class()] = $values['data']->get_weight();
                    }

                    // Get the shipping class of the heaviest product
                    $shipping_class = array_keys($product_weights, max($product_weights));

                    // If there are different shipping classes with the same weight on products,
                    // choose the heaviest
                    $shipping_costs = [];
                    foreach ($shipping_class as $class) {
                        $cost = $this->get_option($class);
                        $shipping_costs[] = $cost;
                    }
                    $shipping_cost = max($shipping_costs);

                    $rate = [
                        'id' => $this->id,
                        'label' => $this->title,
                        'cost' => $shipping_cost,
                        'calc_tax' => 'per_item'
                    ];
                    $this->add_rate($rate);

                }
            }
        }
    }
    add_action('woocommerce_shipping_init', 'custom_shipping_classes_init');
    function add_custom_shipping_classes($methods) {
        $methods['custom_shipping_classes'] = 'WC_Custom_Shipping_Classes_Price';
        return $methods;
    }
    add_filter('woocommerce_shipping_methods', 'add_custom_shipping_classes');
}
