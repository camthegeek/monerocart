<?php

class ControllerExtensionPaymentMonero extends Controller {
    private $payment_module_name = 'monero';
    public function index() {
        $this->load->model('checkout/order');
        $order_id = $this->session->data['order_id'];
        $order = $this->model_checkout_order->getOrder($order_id);
        $current_default_currency = $this->config->get('config_currency');
        $order_total = $order['total'];
        $order_currency = $this->session->data['currency'];
        $amount_monero = round($this->changeto($order_total, $order_currency),6);
        $payment_id = $this->set_paymentid_cookie();
        $data['coincode'] = "monero";
        $data['amount_monero'] = $amount_monero;
        $data['integrated_address'] = $this->make_integrated_address($payment_id);
        $address = $this->config->get("payment_monero_address");
        $data['url_prefix'] = "monero:";
        $json = array();
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/monero')) {
            $this->template = $this->config->get('config_template') . '/template/payment/monero';
        } else {
            $this->template = 'default/template/payment/monero';
        }              
        return $this->load->view('extension/payment/monero', $data);
    }

    public function confirm() {
        $json = array();
        $this->load->model('checkout/order');
        $order_id = $this->session->data['order_id'];
        $order = $this->model_checkout_order->getOrder($order_id);
        $current_default_currency = $this->config->get('config_currency');
        $order_total = $order['total'];
        $order_currency = $this->session->data['currency'];
        $amount_monero = $this->changeto($order_total, $order_currency);
        $payment_id = $this->set_paymentid_cookie();
        $data['amount_monero'] = $amount_monero;
        $data['integrated_address'] = $this->make_integrated_address($payment_id);
        $address = $this->config->get("payment_monero_address");
        $data['url'] = "monero:".$address."";
        if ($this->session->data['payment_method']['code'] == 'monero') {
            $this->load->model('checkout/order');
            if (isset($_COOKIE['payment_id_timeout'])) {
                $json['timeout'] = $_COOKIE['payment_id_timeout'];
            }
            if ($this->verify_payment($payment_id, $data['amount_monero'])) {

                $this->model_checkout_order->addOrderHistory($order_id, 2, 'Monero payment received', true);
                $json['redirect'] = $this->url->link('checkout/success');
                $json['paymentID'] = $payment_id;
                $json['success'] = true;
            } else {
                $json['success'] = false;
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));     
    }

    public function changeto($order_total, $currency) {
        $monero_live_price = $this->moneroliveprice($currency);
        $amount_in_monero = $order_total / $monero_live_price ;
        return $amount_in_monero;
    }

    public function moneroliveprice($currency){
        $url = "https://min-api.cryptocompare.com/data/price?fsym=XMR&tsyms=BTC,USD,EUR,CAD,INR,GBP&extraParams=monero_opencart";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $data = curl_exec($curl);
        curl_close($curl);
        $price = json_decode($data, TRUE);
        switch ($currency) {
            case 'USD':
            return $price['USD'];
            case 'EUR':
            return $price['EUR'];
            case 'CAD':
            return $price['CAD'];
            case 'GBP':
            return $price['GBP'];
            case 'INR':
            return $price['INR'];
            case 'XMR':
            $price = '1';
            return $price;
        }
    }

    private function set_paymentid_cookie() {
        if (!isset($_COOKIE['payment_id'])) {
            $cookieTimeout = time()+1800;
            $payment_id = bin2hex(openssl_random_pseudo_bytes(8));
            setcookie('payment_id', $payment_id, $cookieTimeout);
            setcookie('payment_id_timeout', $cookieTimeout, $cookieTimeout);
        } else {
            $payment_id = $_COOKIE['payment_id'];
            $cookieTimeout = $_COOKIE['payment_id_timeout'];
        }
        return $payment_id;
        return $cookieTimeout;
    }

    private function make_integrated_address($payment_id) {
        $host = $this->config->get("payment_monero_wallet_rpc_host");
        $port = $this->config->get("payment_monero_wallet_rpc_port");
        $monero = new monero($host, $port);
        $integrated_address = $monero->make_integrated_address($payment_id);
        return $integrated_address["integrated_address"];
    }

    private function verify_payment($payment_id, $amount) {
        /*
        * function for verifying payments
        * Check if a payment has been made with this payment id then notify the merchant
        */
        $cookieTimeout = time()-1800;
        $host = $this->config->get("payment_monero_wallet_rpc_host");
        $port = $this->config->get("payment_monero_wallet_rpc_port");
        $monero_daemon = new monero($host, $port);           
        $amount_atomic_units = $amount * 1000000000000;
        $get_payments_method = $monero_daemon->get_payments($payment_id);
        if(isset($get_payments_method["payments"][0]["amount"])) {
            if($get_payments_method["payments"][0]["amount"] >= $amount_atomic_units) {
                $confirmed = true;
                setcookie('payment_id', '', $cookieTimeout);
                setcookie('payment_id_timeout', '', $cookieTimeout);
            }
        } else {
            $confirmed = false;
        }
        return $confirmed;
    }
}