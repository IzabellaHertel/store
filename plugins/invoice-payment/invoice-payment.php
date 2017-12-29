<?php
/**
* Plugin name: Invoice payment
* Author: Izabella
*/
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    function init_invoice_gateway_class() {
        if (!class_exists('WC_Gateway_Invoice_Gateway')) {

            class WC_Gateway_Invoice_Gateway extends WC_Payment_Gateway {
                public function __construct() {
                    $this->id = 'invoice-gateway';
                    $this->has_fields = true;
                    $this->method_title = 'Invoice gateway';
                    $this->title = 'Pay by invoice';
                    $this->method_description = 'Pay by invoice';
                    $this->init();
                }

                function init() {
                    $this->init_form_fields();
                    $this->init_settings();
                    add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
                }

                function init_form_fields() {
                    $this->form_fields =[
                        'title' => [
                            'title' => __('Title', 'woocommerce'),
                            'type' => 'text',
                            'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                            'default' => __('Invoice Payment', 'woocommerce')
                        ],
                        'description' => [
                            'title' => __('Customer Message', 'woocommerce'),
                            'type' => 'textarea',
                            'default' => 'Hallå eller'
                        ]
                    ];
                }

                function payment_fields() {
                    echo '<label for="ssn">Enter your social security number YYYYMMDDXXXX</label>';
                    echo '<input type="text" name="ssn" placeholder="YYYYMMDDXXXX"/>';
                }

                function validate_fields() {
                    $ssn = $_POST['ssn'];
                    if (empty($ssn)) {
                        wc_add_notice(__('You must enter your social security number to be able to pay by invoice.', 'woothemes'). $error_message, 'error');
                        return false;
                    }

                    $year = (int)substr($ssn, 0, 4);
                    $month = (int)substr($ssn, 4, 2);
                    $day = (int)substr($ssn, 6, 2);

                    // Check validity of day and month
                    if ($month < 1 || $month > 12) {
                        wc_add_notice(__('Incorrect month given in ssn.', 'woothemes'). $error_message, 'error');
                        return false;
                    }
                    if ($day < 1) {
                        wc_add_notice(__('Incorrect day given in ssn.', 'woothemes'). $error_message, 'error');
                        return false;
                    }
                    switch ($month) {
                        case 1: case 3: case 5: case 7: case 8: case 10: case 12:
                            if ($day > 31) {
                                wc_add_notice(__('Your ssn is invalid.', 'woothemes'). $error_message, 'error');
                                return false;
                            }
                        break;
                        case 4: case 6: case 9: case 11:
                            if ($day > 30) {
                                wc_add_notice(__('Your ssn is invalid.', 'woothemes'). $error_message, 'error');
                                return false;
                            }
                        break;
                        case 2:
                            if ($this->is_leap_year($year)){
                                if ($day > 29) {
                                    wc_add_notice(__('Your ssn is invalid.', 'woothemes'). $error_message, 'error');
                                    return false;
                                }
                            } else {
                                if ($day > 28) {
                                    wc_add_notice(__('Your ssn is invalid.', 'woothemes'). $error_message, 'error');
                                    return false;
                                }
                            }
                        break;
                    }

                    $new_ssn = substr($ssn, 2); // Remove the first two digits from year
                    $control = []; // Will help calculate control number
                    $x = 0; // Every single digit from ssn
                    $change = true; // Used for toggle
                    $sum = 0; // Will eventually hold control digit

                    // Multiply every other digit by two
                    for ($i = 0; $i < strlen($new_ssn); $i++ ) {
                        $x = substr($new_ssn, $i, 1);
                        if ($change){
                            $x *= 2;
                            $change = false;
                        } else {
                            $x *= 1;
                            $change = true;
                        }
                        $control[] = $x;
                    }

                    // If sum is two digits, remove 9 (-10 + 1)
                    for ($i = 0; $i < 9; $i++ ) {
                        if ($control[$i] >= 10 ) {
                            $sum += $control[$i] - 9;
                        } else {
                            $sum += $control[$i];
                        }
                    }

                    // Get control number
                    $new_sum = (string)$sum;
                    if (strlen($new_sum) == 2) {
                        $sum = substr($new_sum, 1);
                    }
                    $sum = 10 - $sum;

                    // Check if given control number matches
                    $control_number = substr($new_ssn, 9);
                    if ($control_number == $sum) {
                        return true;
                    } else {
                        wc_add_notice(__('Control number invalid', 'woothemes'). $error_message, 'error');
                        return false;
                    }

                }

                private function is_leap_year($year) {
                    return (($year % 4 === 0) && ($year % 100 !== 0)) || ($year % 400 === 0);
                }

                function process_payment($order_id) {
                    global $woocommerce;
                    $order = new WC_Order($order_id);

                    $order->update_status('on-hold', __( 'Awaiting invoice payment', 'woocommerce' ));
                    $order->reduce_order_stock();
                    $woocommerce->cart->empty_cart();

                    return [
                        'result' => 'success',
                        'redirect' => $this->get_return_url($order)
                    ];
                }
            }

        }
    }

    function add_invoice_gateway_class($methods) {
        $methods[] = 'WC_Gateway_Invoice_Gateway';
        return $methods;
    }
    add_filter('woocommerce_payment_gateways', 'add_invoice_gateway_class');
    add_action('plugins_loaded', 'init_invoice_gateway_class');
}
