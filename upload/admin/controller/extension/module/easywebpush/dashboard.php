<?php

class ControllerExtensionModuleEasywebpushDashboard extends Controller
{
  private $error = array();

  public function index()
  {
    $this->load->language('extension/module/easywebpush');
    $this->load->model('extension/module/easywebpush/dashboard');
    $this->load->model('extension/module/easywebpush/subscribers');
    $this->load->model('extension/module/easywebpush/campaign');
    $this->document->addStyle('view/javascript/easywebpush/style.css');
    $this->document->setTitle($this->language->get('menu_dashboard') . " - " . $this->language->get('heading_title'));


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

    // Data
    $key = 'module_easywebpush_';
    $data['vapid_public'] = $this->config->get($key . 'vapid_public');

    $data['subscribers'] = $this->model_extension_module_easywebpush_subscribers->getTotalSubscribers();
    $data['admin_subscribers'] = $this->model_extension_module_easywebpush_dashboard->getTotalAdminSubscribers();
    $data['campaigns'] = $this->model_extension_module_easywebpush_campaign->getTotalCampaigns();
    $data['rates'] = $this->model_extension_module_easywebpush_dashboard->getCampaignRates();
    $data['countries'] = $this->model_extension_module_easywebpush_subscribers->getSubscriberCountries();
    $data['devices'] = $this->model_extension_module_easywebpush_dashboard->getSubscribersDevicesRate();

    // actions
    $data['user_token'] = $this->session->data['user_token'];
    $data['subscribe'] = $this->load->view('extension/module/easywebpush/subscribe', $data);

    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/module/easywebpush/dashboard', $data));
  }

  public function isSubscribed()
  {
    $this->load->model('extension/module/easywebpush/dashboard');
    $method = $this->request->server['REQUEST_METHOD'];
    $subscription_detail = json_decode(html_entity_decode($this->request->post['subscription']), true);
    $subscribed = false;

    if ($method == 'POST' && $subscription_detail['endpoint']) {
      $exist = $this->model_extension_module_easywebpush_dashboard->isSubscribed($subscription_detail['endpoint']);
      if ($exist) {
        $subscribed = true;
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($subscribed));
  }
  public function subscribe()
  {
    require_once(str_replace('\\', '/', realpath(DIR_SYSTEM . 'library/easywebpush/Mobile_Detect.php')));
    $detect = new Mobile_Detect;
    $this->load->language('extension/module/easywebpush');
    $this->load->model('extension/module/easywebpush/dashboard');
    $method = $this->request->server['REQUEST_METHOD'];
    $subscription = json_decode(html_entity_decode($this->request->post['subscription']), true);
    $admin_id = $this->user->getId();
    $device = 'Desktop';
    if ($detect->isTablet()) {
      $device = "Tablet";
    } elseif ($detect->isMobile()) {
      $device = "Mobile";
    }

    $result = array();

    if ($method == 'POST' && $subscription['endpoint']) {
      $data = array(
        'subscription' => $subscription,
        'admin_id'  => $admin_id,
        'device'      => $device,
      );
      $saved = $this->model_extension_module_easywebpush_dashboard->addSubscription($data);
      if ($saved) {
        $result['success'] = $this->language->get("text_subscription_success");
      } else {
        $result['error'] = $this->language->get("error_subscription_failed");
      }
    } else {
      $result['error'] = $this->language->get("error_subscription_failed");
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($result));
  }
  public function unsubscribe()
  {
    $this->load->language('extension/module/easywebpush');
    $this->load->model('extension/module/easywebpush/dashboard');
    $method = $this->request->server['REQUEST_METHOD'];
    $subscription = json_decode(html_entity_decode($this->request->post['subscription']), true);
    $result = array();

    if ($method == 'POST' && $subscription['endpoint']) {
      $deleted = $this->model_extension_module_easywebpush_dashboard->deleteSubscription($subscription['endpoint']);
      if ($deleted) {
        $result['success'] = $this->language->get('text_unsubscription_success');
      } else {
        $result['error'] = $this->language->get('text_unsubscription_error');
      }
    } else {
      $result['error'] = $this->language->get('text_unsubscription_error');
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($result));
  }
}