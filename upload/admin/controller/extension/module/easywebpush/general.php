<?php

class ControllerExtensionModuleEasywebpushGeneral extends Controller
{
  private $error = array();

  public function index()
  {
    $this->load->language('extension/module/easywebpush');
    $this->load->model('setting/setting');
    $this->document->setTitle($this->language->get('menu_setting'));

    $this->getList();
  }

  public function getList()
  {
    $data = array();

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      foreach ($this->request->post as $key => $value) {
        $this->model_setting_setting->editSettingValue('module_easywebpush', "module_easywebpush_" . $key, $value);
      }
      $data['success'] = $this->language->get('text_saved_success');
    }

    // Errors
    foreach ($this->error as $key => $value) {
      if (isset($this->error[$key])) {
        $data[$key] = $value;
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
      'text' => $this->language->get('nav_setting_generals'),
      'href' => $this->url->link('extension/module/easywebpush/general', 'user_token=' . $this->session->data['user_token'], true)
    );

    //action
    $data['action'] = $this->url->link('extension/module/easywebpush/general', 'user_token=' . $this->session->data['user_token'], true);

    //Data
    $settings = $this->model_setting_setting->getSetting('module_easywebpush');
    foreach ($settings as $key => $value) {
      $short_key = str_replace('module_easywebpush_', '', $key);
      if (isset($this->request->post[$short_key])) {
        $data[$short_key] = $this->request->post[$short_key];
      } else {
        $data[$short_key] = $value;
      }
    }

    // Logo
    $this->load->model('tool/image');
    if (isset($data['prompt_logo']) && is_file(DIR_IMAGE . $data['prompt_logo'])) {
      $data['prompt_logo_preview'] = $this->model_tool_image->resize($data['prompt_logo'], 100, 100);
    } elseif ($this->config->get('config_logo') && is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
      $data['prompt_logo_preview'] = $this->model_tool_image->resize($this->config->get('config_logo'), 100, 100);
    } else {
      $data['prompt_logo_preview'] = $this->model_tool_image->resize('no_image.png', 100, 100);
    }

    // Languages
    $this->load->model('localisation/language');
    $data['languages'] = $this->model_localisation_language->getLanguages();

    // Routes
    $data['routes'] = array(
      'common/home',
      'account/*',
      'checkout/cart',
      'checkout/checkout',
      'product/category',
      'product/manufacturer',
      'product/manufacturer/info',
      'product/product',
      'product/special',
      'product/search',
      'information/*',
    );

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');
    $data["nav"] = $this->nav();

    $this->response->setOutput($this->load->view('extension/module/easywebpush/setting/generals', $data));
  }

  public function nav()
  {

    $route = explode("/", $_GET["route"]);
    $controller = end($route);

    $data = array();
    $data["nav_menu"][] = array(
      'text' => $this->language->get('nav_setting_generals'),
      'link' => $this->url->link('extension/module/easywebpush/general', 'user_token=' . $this->session->data['user_token'], true),
      'active' => $controller == 'general'
    );
    if ($this->user->hasPermission('access', 'extension/module/easywebpush/events')) {
      $data["nav_menu"][] = array(
        'text' => $this->language->get('nav_setting_events'),
        'link' => $this->url->link('extension/module/easywebpush/events', 'user_token=' . $this->session->data['user_token'], true),
        'active' => $controller == 'events'
      );
    }
    if ($this->user->hasPermission('access', 'extension/module/easywebpush/pwa')) {
      $data["nav_menu"][] = array(
        'text' => $this->language->get('nav_setting_pwa'),
        'link' => $this->url->link('extension/module/easywebpush/pwa', 'user_token=' . $this->session->data['user_token'], true),
        'active' => $controller == 'pwa'
      );
    }
    if ($this->user->hasPermission('access', 'extension/module/easywebpush/vapid')) {
      $data["nav_menu"][] = array(
        'text' => $this->language->get('nav_setting_vapid'),
        'link' => $this->url->link('extension/module/easywebpush/vapid', 'user_token=' . $this->session->data['user_token'], true),
        'active' => $controller == 'vapid'
      );
    }

    return $this->load->view('extension/module/easywebpush/setting/nav', $data);
  }
  protected function validate()
  {
    if (!$this->user->hasPermission('modify', 'extension/module/easywebpush/general')) {
      $this->error['save_error_warning'] = $this->language->get('error_permission');
    }
    foreach ($this->request->post['prompt_text'] as $language_id => $value) {
      if ((utf8_strlen($value['text']) < 1) || (utf8_strlen($value['text']) > 255)) {
        $this->error['save_error_warning'] = $this->language->get('error_save_fail');
        $this->error['error_prompt_text'][$language_id] = $this->language->get('error_prompt_text');
      }
    }
    if (!isset($this->request->post['prompt_routes'])) {
      $this->request->post['prompt_routes'] = array();
    }

    return !$this->error;
  }
}
