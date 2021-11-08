<?php
require(str_replace('\\', '/', realpath(DIR_SYSTEM . 'library/easywebpush/autoload.php')));

use Minishlink\WebPush\VAPID;

class ControllerExtensionModuleEasywebpushVapid extends Controller
{
  private $error = array();

  public function index()
  {
    $this->load->language('extension/module/easywebpush');
    $this->load->model('setting/setting');
    $this->document->setTitle($this->language->get('nav_setting_vapid'));

    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
      $data['edit_mode'] = true;
    }

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      foreach ($this->request->post as $key => $value) {
        $this->model_setting_setting->editSettingValue('module_easywebpush', "module_easywebpush_" . $key, $value);
      }
      $data['success'] = $this->language->get('text_saved_success');
      $data['edit_mode'] = false;
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
      'text' => $this->language->get('nav_setting_vapid'),
      'href' => $this->url->link('extension/module/easywebpush/vapid', 'user_token=' . $this->session->data['user_token'], true)
    );

    //action
    $data['action'] = $this->url->link('extension/module/easywebpush/vapid', 'user_token=' . $this->session->data['user_token'], true);

    // Data
    if (isset($this->request->post['vapid_public'])) {
      $data['vapid_public'] = $this->request->post['vapid_public'];
    } else {
      $data['vapid_public'] = $this->model_setting_setting->getSettingValue('module_easywebpush_vapid_public');
    }
    if (isset($this->request->post['vapid_private'])) {
      $data['vapid_private'] = $this->request->post['vapid_private'];
    } else {
      $data['vapid_private'] = $this->model_setting_setting->getSettingValue('module_easywebpush_vapid_private');
    }

    $data['user_token'] = $this->session->data['user_token'];
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');
    $data["nav"] = $this->load->controller('extension/module/easywebpush/general/nav');

    $this->response->setOutput($this->load->view('extension/module/easywebpush/setting/vapid', $data));
  }

  public function generate()
  {
    $vapid_keys = VAPID::createVapidKeys();
    $json['public'] = $vapid_keys["publicKey"];
    $json['private'] = $vapid_keys["privateKey"];

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }
  protected function validate()
  {
    if (!$this->user->hasPermission('modify', 'extension/module/easywebpush/vapid')) {
      $this->error['error_warning'] = $this->language->get('error_permission');
    }
    if ((utf8_strlen($this->request->post['vapid_public']) != 87)) {
      $this->error['vapid_public'] = sprintf($this->language->get('error_vapid'));
    }
    if ((utf8_strlen($this->request->post['vapid_private']) != 43)) {
      $this->error['vapid_private'] = sprintf($this->language->get('error_vapid'));
    }
    if ($this->error && !isset($this->error['error_warning'])) {
      $this->error['error_warning'] = $this->language->get('error_save_fail');
    }
    return !$this->error;
  }
}