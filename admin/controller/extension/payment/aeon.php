<?php
class ControllerExtensionPaymentAeon extends Controller {
    private $error = array();
    private $settings = array();
    public function index() {
        $this->load->language('extension/payment/aeon');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_aeon', $this->request->post);
            $this->session->data['success'] = "Success! Welcome to aeon!";
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }
        
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['aeon_address_text'] = $this->language->get('wallet_address');
        $data['button_save'] = "save";
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['help_total'] = $this->language->get('help_total');
        $data['settings'] = $this->config->get('aeon');
        //Errors
        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';     

       // Values for Settings
        $data['payment_aeon_status'] = isset($this->request->post['payment_aeon_status']) ? $this->request->post['payment_aeon_status'] : $this->config->get('payment_aeon_status');
        $data['payment_aeon_address'] = isset($this->request->post['payment_aeon_address']) ? $this->request->post['payment_aeon_address'] : $this->config->get('payment_aeon_address');
        $data['payment_aeon_wallet_rpc_host'] = isset($this->request->post['payment_aeon_wallet_rpc_host']) ? $this->request->post['payment_aeon_wallet_rpc_host'] : $this->config->get('payment_aeon_wallet_rpc_host');
        $data['payment_aeon_wallet_rpc_port'] = isset($this->request->post['payment_aeon_wallet_rpc_port']) ? $this->request->post['payment_aeon_wallet_rpc_port'] : $this->config->get('payment_aeon_wallet_rpc_port');     
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/aeon', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['action'] = $this->url->link('extension/payment/aeon', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/payment/aeon', $data));
    }
   
    private function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/aeon')) {
            $this->error['warning'] = $this->language->get('error_permission');
            return false;
        }
        return true;
    }
   
    public function uninstall() {
        $this->load->model('extension/payment/aeon');
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('aeon');
        $this->model_extension_payment_aeon->dropDatabaseTables();
    }
}
