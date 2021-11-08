<?php

class ControllerExtensionModuleEasywebpushEvents extends Controller
{
  private $error = array();

  public function index()
  {
    $this->load->language('extension/module/easywebpush');
    $this->load->model('setting/setting');
    $this->document->setTitle($this->language->get('nav_setting_events'));

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      foreach ($this->request->post as $key => $value) {
        $this->model_setting_setting->editSettingValue('module_easywebpush', "module_easywebpush_" . $key, $value);
      }
      $data['success'] = $this->language->get('text_saved_success');
    }

    // Errors
    foreach ($this->error as $key => $value) {
      if (isset($this->error[$key])) {
        $data['errors'][$key] = $value;
      }
    }

    //Breadcrumbs
    $data['breadcrumbs'] = array();
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link('extension/module/easywebpush/dashboard', 'user_token=' . $this->session->data['user_token'], true)
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('nav_setting_events'),
      'href' => $this->url->link('extension/module/easywebpush/events', 'user_token=' . $this->session->data['user_token'], true)
    );

    //action
    $data['action'] = $this->url->link('extension/module/easywebpush/events', 'user_token=' . $this->session->data['user_token'], true);

    // Data
    $admin_events = array('register', 'order', 'review');
    foreach ($admin_events as $ae) {
      $data['admin_events'][] = array('label' => $this->language->get("entry_" . $ae), 'value' => $ae);
    }
    $customer_events = array('order_status');
    foreach ($customer_events as $ce) {
      $data['customer_events'][] = array('label' => $this->language->get("entry_" . $ce), 'value' => $ce);
    }
    $data['events_for_admin'] = $this->model_setting_setting->getSettingValue('module_easywebpush_events_for_admin');
    $data['events_for_customer'] = $this->model_setting_setting->getSettingValue('module_easywebpush_events_for_customer');

    $data['user_token'] = $this->session->data['user_token'];
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');
    $data["nav"] = $this->load->controller('extension/module/easywebpush/general/nav');

    $this->response->setOutput($this->load->view('extension/module/easywebpush/setting/events', $data));
  }

  protected function validate()
  {
    if (!$this->user->hasPermission('modify', 'extension/module/easywebpush/events')) {
      $this->error['error_warning'] = $this->language->get('error_permission');
    }
    if (!isset($this->request->post['events_for_admin'])) {
      $this->request->post['events_for_admin'] = array();
    }
    if (!isset($this->request->post['events_for_customer'])) {
      $this->request->post['events_for_customer'] = array();
    }
    if ($this->error && !isset($this->error['error_warning'])) {
      $this->error['error_warning'] = $this->language->get('error_save_fail');
    }
    return !$this->error;
  }
}