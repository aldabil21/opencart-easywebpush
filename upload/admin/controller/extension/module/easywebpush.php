<?php
class ControllerExtensionModuleEasywebpush extends Controller
{
  private $error = array();

  public function index()
  {
    $this->load->language('extension/module/easywebpush');
    $this->document->setTitle($this->language->get('heading_title'));

    $this->load->model('setting/setting');

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

      $this->model_setting_setting->editSetting('module_easywebpush', $this->request->post);

      $this->session->data['success'] = $this->language->get('text_success');

      $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
    }

    if (isset($this->error['warning'])) {
      $data['error_warning'] = $this->error['warning'];
    } else {
      $data['error_warning'] = '';
    }

    //Breadcrumbs
    $data['breadcrumbs'] = array();
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_extension'),
      'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link('extension/module/easywebpush', 'user_token=' . $this->session->data['user_token'], true)
    );

    //text
    $data['text_edit'] = $this->language->get('text_edit');


    $data['action'] = $this->url->link('extension/module/easywebpush', 'user_token=' . $this->session->data['user_token'], true);
    $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

    //Status
    if (isset($this->request->post['module_easywebpush_status'])) {
      $data['module_easywebpush_status'] = $this->request->post['module_easywebpush_status'];
    } else {
      $data['module_easywebpush_status'] = $this->config->get('module_easywebpush_status');
    }

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/module/easywebpush/adminview', $data));
  }
  public function install()
  {
    $this->load->model('setting/event');
    $this->load->model('extension/module/easywebpush');
    $this->load->language('extension/module/easywebpush');

    // install Webpush
    $installed = $this->model_extension_module_easywebpush->installWebpush();
    if (!$installed) {
      trigger_error($this->language->get('error_fail_install_webpush_lib'));
      exit;
    }
    // Move sw.js to root
    $moved = $this->model_extension_module_easywebpush->moveSwjsToRoot();
    if (!$moved) {
      trigger_error($this->language->get('error_swjs_not_found'));
      exit;
    }

    // Create events
    $this->clearEvents(); //In case some left overs
    $this->model_setting_event->addEvent('easywebpush', 'catalog/controller/common/header/before', 'extension/module/easywebpush/addGlobalScripts');
    $this->model_setting_event->addEvent('easywebpush', 'catalog/view/common/home/after', 'extension/module/easywebpush/eventHomeAfter');

    //install tables
    $this->model_extension_module_easywebpush->createTables();

    //add extension 
    $this->load->model('setting/extension');
    $this->model_setting_extension->install('module', 'easywebpush');
  }

  public function uninstall()
  {
    if ($this->validate()) {

      $this->clearEvents();

      $this->load->model('extension/module/easywebpush');
      // $this->model_extension_module_easywebpush->dropTables();

      $this->load->model('setting/extension');
      $this->model_setting_extension->uninstall('module', 'easywebpush');
    }
  }
  protected function validate()
  {
    if (!$this->user->hasPermission('modify', 'extension/module/easywebpush')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    return !$this->error;
  }
  protected function clearEvents()
  {
    $this->model_setting_event->deleteEventByCode('easywebpush');
  }
}