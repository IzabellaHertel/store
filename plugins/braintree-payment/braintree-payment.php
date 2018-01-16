<?php
/**
* Plugin name: Braintree payment
* Author: Izabella
*/
require('braintree-php-3.26.1/lib/Braintree.php');

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    function init_braintree_gateway_class() {
        if (!class_exists('WC_Gateway_Braintree_Gateway')) {

            class WC_Gateway_Braintree_Gateway extends WC_Payment_Gateway {

                public function __construct() {
                    $this->id = 'braintree-gateway';
                    $this->has_fields = true;
                    $this->method_title = 'Braintree gateway';
                    $this->title = 'Pay with Braintree';
                    $this->method_description = 'Pay with Braintree';
                    $this->init();
                }

                function init() {
                    $this->init_form_fields();
                    $this->init_settings();
                    add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
                }

                private function get_api() {
                    Braintree_Configuration::environment('sandbox');
                    Braintree_Configuration::merchantId($this->get_option('merchant_id'));
                    Braintree_Configuration::publicKey($this->get_option('public_key'));
                    Braintree_Configuration::privateKey($this->get_option('private_key'));
                }

                function init_form_fields() {
                    $this->form_fields = [
                        'enabled' => [
                            'title' => 'Enable',
                            'type' => 'checkbox'
                        ],
                        'title' => [
                            'title' => __('Title', 'woocommerce'),
                            'type' => 'text',
                            'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                            'default' => __('Braintree Payment', 'woocommerce')
                        ],
                        'merchant_id' => [
                            'title' => __('Merchant ID', 'woocommerce'),
                            'type' => 'text'
                        ],
                        'merchant_account_id' => [
                            'title' => __('Merchant account ID', 'woocommerce'),
                            'type' => 'text'
                        ],
                        'public_key' => [
                            'title' => __('Public key', 'woocommerce'),
                            'type' => 'text'
                        ],
                        'private_key' => [
                            'title' => __('Private key', 'woocommerce'),
                            'type' => 'text'
                        ]
                    ];
                }


                function payment_fields() {
                    $this->get_api();
                    $clientToken = Braintree_ClientToken::generate(); ?>
                    <script src="https://js.braintreegateway.com/web/dropin/1.9.2/js/dropin.min.js"></script>
                    <div id="dropin-container"></div>
                        <input type="hidden" id="my-nonce" name="my-nonce" />
                        <button id="submit-button">Ok</button>
                        <script>
                            var button = document.querySelector("#submit-button");
                            braintree.dropin.create({
                                authorization: "<?php echo $clientToken; ?>",
                                container: "#dropin-container"
                            }, function (createErr, instance) {
                                button.addEventListener("click", function (e) {
                                    e.preventDefault();
                                    console.log(createErr);
                                    instance.requestPaymentMethod(function (err, payload) {
                                        document.querySelector('#my-nonce').value = payload.nonce;
                                    });
                                });
                            });
                      </script>

                <?php }

                function process_payment($order_id) {
                    global $woocommerce;
                    $order = new WC_Order($order_id);


                    if (empty($_POST['my-nonce'])) {
                        wc_add_notice('Fill in your card information', 'error');
                    }

                    $this->get_api();

                    $result = Braintree_Transaction::sale([
                        'amount' => $order->total,
                        'paymentMethodNonce' => $_POST['my-nonce'],
                        'options' => [
                            'submitForSettlement' => True
                        ]
                    ]);

                    if ($result->success) {
                        $order->update_status('completed', __('Completed', 'woocommerce'));
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
    }

    function add_braintree_gateway_class($methods) {
        $methods[] = 'WC_Gateway_Braintree_Gateway';
        return $methods;
    }
    add_filter('woocommerce_payment_gateways', 'add_braintree_gateway_class');
    add_action('plugins_loaded', 'init_braintree_gateway_class');
}
