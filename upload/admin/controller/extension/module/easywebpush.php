<?php
class ControllerExtensionModuleEasywebpush extends Controller
{
  private $error = array();

  public function index()
  {
    $this->response->redirect($this->url->link('extension/module/easywebpush/general', 'user_token=' . $this->session->data['user_token'], true));
  }

  public function install()
  {
    $this->load->model('setting/setting');
    $this->load->model('setting/event');
    $this->load->model('localisation/language');
    $this->load->model('extension/module/easywebpush');
    $this->load->language('extension/module/easywebpush');

    // Pre-install Checks
    $checked = $this->model_extension_module_easywebpush->preInstall();
    if (!$checked) {
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
    $this->unregisterEvents(); //In case some left overs
    $this->model_extension_module_easywebpush->registerEvents();

    //install tables
    $this->model_extension_module_easywebpush->createTables();

    // Set Initial Settings
    $this->model_extension_module_easywebpush->setInitialSettings();

    //add extension 
    $this->load->model('setting/extension');
    $this->model_setting_extension->install('module', 'easywebpush');
  }

  public function uninstall()
  {
    if ($this->validate()) {

      $this->unregisterEvents();

      $this->load->model('extension/module/easywebpush');
      // $this->model_extension_module_easywebpush->dropTables();

      $ROOT_DIR = str_replace('\\', '/', realpath(DIR_APPLICATION . '..'));
      unlink($ROOT_DIR . "/sw.js");
      unlink($ROOT_DIR . "/manifest.json");

      $this->load->model('setting/extension');
      $this->model_setting_extension->uninstall('module', 'easywebpush');
    }
  }
  public function preserveVapidsOnUninstall()
  {
    $this->load->model('setting/setting');
    $settings = $this->model_setting_setting->getSetting('module_easywebpush');
    $preserve = array(
      'module_easywebpush_uninstalled_vapid_public' => $settings["module_easywebpush_vapid_public"],
      'module_easywebpush_uninstalled_vapid_private' => $settings["module_easywebpush_vapid_private"],
    );
    $this->model_setting_setting->editSetting('module_easywebpush_uninstalled', $preserve);
  }
  protected function validate()
  {
    if (!$this->user->hasPermission('modify', 'extension/module/easywebpush')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    return !$this->error;
  }
  protected function unregisterEvents()
  {
    $this->model_setting_event->deleteEventByCode('easywebpush');
  }
  protected function deleteDirectory($dir)
  {
    if (!file_exists($dir)) {
      return true;
    }

    if (!is_dir($dir)) {
      return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
      if ($item == '.' || $item == '..') {
        continue;
      }

      if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
        return false;
      }
    }

    return rmdir($dir);
  }
}
