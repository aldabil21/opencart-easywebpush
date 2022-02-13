<?php

class ControllerExtensionModuleEasywebpushPwa extends Controller
{
  private $error = array();

  public function index()
  {
    $this->load->language('extension/module/easywebpush');
    $this->load->model('setting/setting');
    $this->load->model('tool/image');
    $this->document->setTitle($this->language->get('nav_setting_pwa'));
    $ROOT_DIR = str_replace('\\', '/', realpath(DIR_APPLICATION . '..'));

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $tosave = array();
      foreach ($this->request->post as $key => $value) {
        if ($key === "icons") {
          $tosave_icons = array();
          foreach ($value as $size => $src) {
            $img = getimagesize(DIR_IMAGE . $src);
            $tosave_icons[] = array(
              'src' => 'image/' . $src,
              'sizes' => $size,
              'type' => $img['mime'],
            );
          }
          $tosave[$key] = $tosave_icons;
        } else {
          $tosave[$key] = $value;
        }
      }
      $success = file_put_contents($ROOT_DIR . "/manifest.json", json_encode($tosave));
      if ($success > 0) {
        $data['success'] = $this->language->get('text_saved_success');
      } else {
        $this->error['error_warning'] = "Failed to re-write manifest.json";
      }
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
      'text' => $this->language->get('nav_setting_pwa'),
      'href' => $this->url->link('extension/module/easywebpush/pwa', 'user_token=' . $this->session->data['user_token'], true)
    );

    //action
    $data['action'] = $this->url->link('extension/module/easywebpush/pwa', 'user_token=' . $this->session->data['user_token'], true);


    $manifest = json_decode(file_get_contents($ROOT_DIR . "/manifest.json"));
    $data['manifest'] = array();

    foreach ($manifest as $key => $value) {
      if ($key === 'icons') {
        $icons = array();
        foreach ($value as $icon) {
          $src =  str_replace('image/', '', $icon->src);
          if (isset($this->request->post['icons'][$icon->sizes])) {
            $src = $this->request->post['icons'][$icon->sizes];
          }
          if (isset($src) && is_file(DIR_IMAGE . $src)) {
            $preview = $this->model_tool_image->resize($src, 100, 100);
          } elseif ($this->config->get('config_logo') && is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
            $preview = $this->model_tool_image->resize($this->config->get('config_logo'), 100, 100);
          } else {
            $preview = $this->model_tool_image->resize('no_image.png', 100, 100);
          }
          $icons[] = array(
            'src'       => $src,
            'sizes'     => $icon->sizes,
            'preview'   =>  $preview,
          );
        }
        $data['manifest'][] = array(
          'key' => $key,
          'text' => $this->language->get('entry_pwa_' . $key),
          'helper' => $this->language->get('entry_pwa_' . $key . '_helper'),
          'value' => $icons
        );
      } else {
        $val = $value;
        if (isset($this->request->post[$key])) {
          $val = $this->request->post[$key];
        }
        $data['manifest'][] = array(
          'key' => $key,
          'text' => $this->language->get('entry_pwa_' . $key),
          'helper' => $this->language->get('entry_pwa_' . $key . '_helper'),
          'value' => $val
        );
      }
    }

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');
    $data["nav"] = $this->load->controller('extension/module/easywebpush/general/nav');

    $this->response->setOutput($this->load->view('extension/module/easywebpush/setting/pwa', $data));
  }

  protected function validate()
  {
    if (!$this->user->hasPermission('modify', 'extension/module/easywebpush/pwa')) {
      $this->error['error_warning'] = $this->language->get('error_permission');
    }
    if ((utf8_strlen($this->request->post['short_name']) < 3) || (utf8_strlen($this->request->post['short_name']) > 64)) {
      $this->error['short_name'] = sprintf($this->language->get('error_string_length'), 3, 64);
    }
    if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
      $this->error['name'] = sprintf($this->language->get('error_string_length'), 3, 64);
    }
    if ((utf8_strlen($this->request->post['description']) < 3) || (utf8_strlen($this->request->post['description']) > 255)) {
      $this->error['description'] = sprintf($this->language->get('error_string_length'), 3, 255);
    }
    if ($this->error && !isset($this->error['error_warning'])) {
      $this->error['error_warning'] = $this->language->get('error_save_fail');
    }
    return !$this->error;
  }
}
