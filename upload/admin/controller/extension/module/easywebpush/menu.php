<?php

class ControllerExtensionModuleEasywebpushMenu extends Controller
{

  public function index(&$route, &$data, &$output)
  {
    $this->load->language('extension/module/easywebpush');
    $submenu = array();
    if ($this->user->hasPermission('access', 'extension/module/easywebpush/dashboard')) {
      $submenu[] = array(
        'id'       => 'module-easywebpush-dashboard',
        'icon'     => 'fa-bar-chart',
        'name'     => $this->language->get('menu_dashboard'),
        'href'     => $this->url->link('extension/module/easywebpush/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        'children' => array()
      );
    }
    if ($this->user->hasPermission('access', 'extension/module/easywebpush/campaign')) {
      $submenu[] = array(
        'id'       => 'module-easywebpush-compaign',
        'icon'     => 'fa-pie-chart',
        'name'     => $this->language->get('menu_campaign'),
        'href'     => $this->url->link('extension/module/easywebpush/campaign', 'user_token=' . $this->session->data['user_token'], true),
        'children' => array()
      );
    }
    if ($this->user->hasPermission('access', 'extension/module/easywebpush/subscribers')) {
      $submenu[] = array(
        'id'       => 'module-easywebpush-compaign',
        'icon'     => 'fa-pie-chart',
        'name'     => $this->language->get('menu_subscribers'),
        'href'     => $this->url->link('extension/module/easywebpush/subscribers', 'user_token=' . $this->session->data['user_token'], true),
        'children' => array()
      );
    }
    if ($this->user->hasPermission('access', 'extension/module/easywebpush/general')) {
      $submenu[] = array(
        'id'       => 'module-easywebpush-setting',
        'icon'     => 'fa-cog',
        'name'     => $this->language->get('menu_setting'),
        'href'     => $this->url->link('extension/module/easywebpush/general', 'user_token=' . $this->session->data['user_token'], true),
        'children' => array()
      );
    }
    $menu[] = array(
      'id'       => 'module-easywebpush',
      'icon'     => 'fa-paper-plane',
      'name'     => $this->language->get('menu_title'),
      'href'     => '',
      'children' => $submenu
    );

    array_splice($data["menus"], 7, 0, $menu);
  }
}