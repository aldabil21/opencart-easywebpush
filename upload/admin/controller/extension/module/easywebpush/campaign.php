<?php

class ControllerExtensionModuleEasywebpushCampaign extends Controller
{
  private $error = array();

  public function index()
  {
    $this->load->language('extension/module/easywebpush');
    $this->load->model('extension/module/easywebpush/campaign');
    $this->document->addStyle('view/javascript/easywebpush/style.css');
    $this->document->setTitle($this->language->get('menu_campaign'));

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateDelete()) {
      $this->model_extension_module_easywebpush_campaign->deleteCampaignsByIds($this->request->post['selected']);
      $this->session->data['success'] = sprintf($this->language->get('text_delete_success'), count($this->request->post['selected']), $this->language->get('text_campaigns_list'));
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
      'text' => $this->language->get('menu_campaign'),
      'href' => $this->url->link('extension/module/easywebpush/campaign', 'user_token=' . $this->session->data['user_token'], true)
    );

    if (isset($this->request->get['sort'])) {
      $data['sort'] = $this->request->get['sort'];
    } else {
      $data['sort'] = 'date_added';
    }
    if (isset($this->request->get['order'])) {
      $data['order'] = $this->request->get['order'];
    } else {
      $data['order'] = 'DESC';
    }
    if (isset($this->request->get['page'])) {
      $data['page'] = (int)$this->request->get['page'];
    } else {
      $data['page'] = 1;
    }
    if (isset($this->request->get['name'])) {
      $data['name'] = $this->request->get['name'];
    } else {
      $data['name'] = null;
    }

    $filter_data = array(
      'name'                     => $data['name'],
      'sort'                     => $data['sort'],
      'order'                    => $data['order'],
      'start'                    => ($data['page'] - 1) * $this->config->get('config_limit_admin'),
      'limit'                    => $this->config->get('config_limit_admin')
    );
    $campaigns = $this->model_extension_module_easywebpush_campaign->getCampaigns($filter_data);
    $total_campaigns = $this->model_extension_module_easywebpush_campaign->getTotalCampaigns($filter_data);
    foreach ($campaigns as $campaign) {
      $campaign['view'] = $this->url->link('extension/module/easywebpush/campaign/view', 'campaign_id=' . $campaign['campaign_id'] . '&user_token=' . $this->session->data['user_token'], true);
      $data['campaigns'][] = $campaign;
    }

    if (isset($this->error['error_warning'])) {
      $data['error_warning'] = $this->error['error_warning'];
    } elseif (isset($this->session->data['error_warning'])) {
      $data['error_warning'] = $this->session->data['error_warning'];
      unset($this->session->data['error_warning']);
    } else {
      $data['error_warning'] = '';
    }
    if (isset($this->session->data['success'])) {
      $data['success'] = $this->session->data['success'];
      unset($this->session->data['success']);
    } else {
      $data['success'] = '';
    }
    if (isset($this->request->post['selected'])) {
      $data['selected'] = (array)$this->request->post['selected'];
    } else {
      $data['selected'] = array();
    }

    $url = '';
    if (isset($this->request->get['sort'])) {
      $url .= '&sort=' . $this->request->get['sort'];
    }
    if (isset($this->request->get['order'])) {
      $url .= '&order=' . $this->request->get['order'];
    }
    if (isset($this->request->get['page'])) {
      //$url .= '&page=' . $this->request->get['page'];
    }
    if (isset($this->request->get['name'])) {
      $url .= '&name=' . urlencode(html_entity_decode($this->request->get['name'], ENT_QUOTES, 'UTF-8'));
    }

    $data['delete'] = $this->url->link('extension/module/easywebpush/campaign', 'user_token=' . $this->session->data['user_token'] . $url, true);
    $data['new_campaign'] = $this->url->link('extension/module/easywebpush/campaign/new', 'user_token=' . $this->session->data['user_token'], true);
    $pagination = new Pagination();
    $pagination->total = $total_campaigns;
    $pagination->page = $data['page'];
    $pagination->limit = $this->config->get('config_limit_admin');
    $pagination->url = $this->url->link('extension/module/easywebpush/campaign', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
    $data['pagination'] = $pagination->render();
    $data['results'] = sprintf($this->language->get('text_pagination'), ($total_campaigns) ? (($data['page'] - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($data['page'] - 1) * $this->config->get('config_limit_admin')) > ($total_campaigns - $this->config->get('config_limit_admin'))) ? $total_campaigns : ((($data['page'] - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $total_campaigns, ceil($total_campaigns / $this->config->get('config_limit_admin')));

    $data['user_token'] = $this->session->data['user_token'];
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/module/easywebpush/campaign/list', $data));
  }
  public function new()
  {
    $this->load->language('extension/module/easywebpush');
    $this->load->model('extension/module/easywebpush/campaign');
    $this->load->model('extension/module/easywebpush/subscribers');
    $this->load->model('tool/image');
    $this->load->model('customer/customer_group');
    $this->document->addStyle('view/javascript/easywebpush/style.css');
    $this->document->setTitle($this->language->get('text_new_campaign'));

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateSend()) {
      $ids = $this->model_extension_module_easywebpush_campaign->getCampaignReceivers($this->request->post);
      $total = count($ids);
      if ($total > 0) {
        $subscriptions = $this->model_extension_module_easywebpush_subscribers->getSubscriptionByIds($ids);

        $campaign_id = $this->model_extension_module_easywebpush_campaign->createCampaign($this->request->post, $total);

        $second_action = isset($this->request->post['second_action']) && $this->request->post['second_action'];
        $push = array(
          'subscriptions'   => $subscriptions,
          'icon'            => $this->request->post['icon'],
          'title'           => $this->request->post['title'],
          'message'         => $this->request->post['message'],
          'image'           => $this->request->post['image'],
          'action_title'    => $second_action ? $this->request->post['action_title'] : "",
          'action_link'     => $second_action ? $this->request->post['action_link'] : "",
          'data'            => array(
            'campaign_id' => $campaign_id,
            'url'        => HTTPS_CATALOG
          )
        );
        $result = $this->load->controller('extension/module/easywebpush/subscribers/notify', $push);

        $this->model_extension_module_easywebpush_campaign->updateCampaign($campaign_id, $result);

        $success_msg = $this->request->post['name'] . ". " . $result['success'];
        $this->session->data['success'] = sprintf($this->language->get('text_campaign_sent'), $success_msg);
        $this->session->data['error_warning'] = $result['error'];

        $this->response->redirect($this->url->link('extension/module/easywebpush/campaign', 'user_token=' . $this->session->data['user_token'], true));
      } else {
        $this->error["error_warning"] = $this->language->get("error_no_campaign_receivers");
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
      'text' => $this->language->get('menu_campaign'),
      'href' => $this->url->link('extension/module/easywebpush/campaign', 'user_token=' . $this->session->data['user_token'], true)
    );

    if ($this->error) {
      $data['errors'] = $this->error;
      $data['error_warning'] = $this->error['error_warning'] ?? $this->language->get('error_send_fail');
    }

    $data['first_filters'] = array(
      'all'         => $this->language->get('filter_all'),
      'subscribers' => $this->language->get('filter_subscribers')
    );
    $data['second_filters'] = array(
      'registered'     => $this->language->get('filter_registered'),
      'anonymous'      => $this->language->get('filter_anonymous'),
      'device'         => $this->language->get('filter_device'),
      'abandoned_cart' => $this->language->get('filter_abandoned_cart'),
      'customer_group' => $this->language->get('filter_customer_group'),
      'country'        => $this->language->get('filter_country'),
    );
    $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();
    $data['countries'] = $this->model_extension_module_easywebpush_subscribers->getSubscriberCountries();

    if (isset($this->request->post['name'])) {
      $data['name'] = $this->request->post['name'];
    } else {
      $data['name'] = '';
    }
    if (isset($this->request->post['first_filter'])) {
      $data['first_filter'] = $this->request->post['first_filter'];
    } else {
      $data['first_filter'] = '';
    }
    if (isset($this->request->post['second_filter'])) {
      $data['second_filter'] = $this->request->post['second_filter'];
    } else {
      $data['second_filter'] = '';
    }
    if (isset($this->request->post['third_filter'])) {
      $data['third_filter'] = $this->request->post['third_filter'];
    } else {
      $data['third_filter'] = '';
    }
    if (isset($this->request->post['title'])) {
      $data['title'] = $this->request->post['title'];
    } else {
      $data['title'] = '';
    }
    if (isset($this->request->post['message'])) {
      $data['message'] = $this->request->post['message'];
    } else {
      $data['message'] = '';
    }
    if (isset($this->request->post['second_action'])) {
      $data['second_action'] = $this->request->post['second_action'];
    } else {
      $data['second_action'] = '';
    }

    if (isset($this->request->post['action_title'])) {
      $data['action_title'] = $this->request->post['action_title'];
    } else {
      $data['action_title'] = '';
    }
    if (isset($this->request->post['action_link'])) {
      $data['action_link'] = $this->request->post['action_link'];
    } else {
      $data['action_link'] = '';
    }
    if (isset($this->request->post['icon']) && $this->request->post['icon']) {
      $data['icon_preview'] = $this->model_tool_image->resize($this->request->post['icon'], 100, 100);
      $data['icon'] = $this->request->post['icon'];
    } elseif ($this->config->get('module_easywebpush_prompt_logo') && is_file(DIR_IMAGE . $this->config->get('module_easywebpush_prompt_logo'))) {
      $data['icon_preview'] = $this->model_tool_image->resize($this->config->get('module_easywebpush_prompt_logo'), 100, 100);
      $data['icon'] = $this->config->get('module_easywebpush_prompt_logo');
    } elseif ($this->config->get('config_logo') && is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
      $data['icon_preview'] = $this->model_tool_image->resize($this->config->get('module_easywebpush_prompt_logo'), 100, 100);
      $data['icon'] = $this->config->get('config_logo');
    } else {
      $data['icon_preview'] = $this->model_tool_image->resize('no_image.png', 100, 100);
    }
    if (isset($this->request->post['image']) && $this->request->post['image']) {
      $data['image_preview'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
      $data['image'] = $this->request->post['image'];
    } else {
      $data['image'] = '';
      $data['image_preview'] = $this->model_tool_image->resize('no_image.png', 100, 100);
    }
    $data['badge'] = $this->model_tool_image->resize($this->config->get("config_icon"), 35, 35);
    $data['subject'] = HTTPS_CATALOG;

    $data['emulator'] = $this->load->view('extension/module/easywebpush/emulator', $data);
    $data['action'] = $this->url->link('extension/module/easywebpush/campaign/new', 'user_token=' . $this->session->data['user_token'], true);
    $data['user_token'] = $this->session->data['user_token'];

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');


    $this->response->setOutput($this->load->view('extension/module/easywebpush/campaign/new', $data));
  }
  public function view()
  {

    $this->load->language('extension/module/easywebpush');
    $this->load->model('extension/module/easywebpush/campaign');
    $this->load->model('tool/image');
    $this->document->addStyle('view/javascript/easywebpush/style.css');

    $campaign = $this->model_extension_module_easywebpush_campaign->getCampaignById($this->request->get['campaign_id']);
    $this->document->setTitle($this->language->get($campaign['name']));
    $data['campaign'] = $campaign;

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
      'text' => $this->language->get('menu_campaign'),
      'href' => $this->url->link('extension/module/easywebpush/campaign', 'user_token=' . $this->session->data['user_token'], true)
    );

    if (is_file(DIR_IMAGE . $campaign['icon'])) {
      $data['campaign']['icon_preview'] = $this->model_tool_image->resize($campaign['icon'], 100, 100);
    } elseif ($this->config->get('module_easywebpush_prompt_logo') && is_file(DIR_IMAGE . $this->config->get('module_easywebpush_prompt_logo'))) {
      $data['campaign']['icon_preview'] = $this->model_tool_image->resize($this->config->get('module_easywebpush_prompt_logo'), 100, 100);
      $data['campaign']['icon'] = $this->config->get('module_easywebpush_prompt_logo');
    } elseif ($this->config->get('config_logo') && is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
      $data['campaign']['icon_preview'] = $this->model_tool_image->resize($this->config->get('module_easywebpush_prompt_logo'), 100, 100);
      $data['campaign']['icon'] = $this->config->get('config_logo');
    } else {
      $data['campaign']['icon_preview'] = $this->model_tool_image->resize('no_image.png', 100, 100);
    }
    if ($campaign['image'] && is_file(DIR_IMAGE . $campaign['image'])) {
      $data['campaign']['image_preview'] = $this->model_tool_image->resize($campaign['image'], 150, 150);
    } else {
      $data['campaign']['image'] = '';
      $data['campaign']['image_preview'] = $this->model_tool_image->resize('no_image.png', 150, 150);
    }
    $data['campaign']['badge'] = $this->model_tool_image->resize($this->config->get("config_icon"), 35, 35);
    $data['campaign']['subject'] = HTTPS_CATALOG;

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');
    $this->response->setOutput($this->load->view('extension/module/easywebpush/campaign/view', $data));
  }
  public function total()
  {
    $this->load->model('extension/module/easywebpush/campaign');
    $receiver_ids = $this->model_extension_module_easywebpush_campaign->getCampaignReceivers($this->request->post);
    $total = count($receiver_ids);
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput($total);
  }
  protected function validateDelete()
  {
    if (!isset($this->request->post['selected'])) {
      $this->error["error_warning"] = $this->language->get("error_empty_selected");
    }
    if (!$this->user->hasPermission('modify', 'extension/module/easywebpush/campaign')) {
      $this->error['error_warning'] = $this->language->get('error_permission');
    }

    return !$this->error;
  }
  protected function validateSend()
  {
    if (!$this->user->hasPermission('modify', 'extension/module/easywebpush/campaign')) {
      $this->error['error_warning'] = $this->language->get('error_permission');
    }
    if (utf8_strlen($this->request->post['name']) < 3 || utf8_strlen($this->request->post['name']) > 100) {
      $this->error["name"] = sprintf($this->language->get("error_string_length"), 3, 100);
    }
    if (utf8_strlen($this->request->post['title']) < 3 || utf8_strlen($this->request->post['title']) > 64) {
      $this->error["title"] = sprintf($this->language->get("error_string_length"), 3, 64);
    }
    if (utf8_strlen($this->request->post['message']) < 3 || utf8_strlen($this->request->post['message']) > 160) {
      $this->error["message"] = sprintf($this->language->get("error_string_length"), 3, 160);
    }
    if (isset($this->request->post['second_action']) && (utf8_strlen($this->request->post['action_title']) < 1 || utf8_strlen($this->request->post['action_title']) > 20)) {
      $this->error["action_title"] = sprintf($this->language->get("error_string_length"), 3, 20);
    }
    if (isset($this->request->post['second_action']) && filter_var($this->request->post['action_link'], FILTER_VALIDATE_URL) == false) {
      $this->error["action_link"] = $this->language->get("error_action_link");
    }

    return !$this->error;
  }
}