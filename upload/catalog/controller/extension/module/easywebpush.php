<?php
require(str_replace('\\', '/', realpath(DIR_SYSTEM . 'library/easywebpush/autoload.php')));

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use GeoIp2\Database\Reader;

class ControllerExtensionModuleEasywebpush extends Controller
{
  private $error = array();

  // Event handle: catalog/controller/common/header/before
  public function addGlobalScripts()
  {
    $enabled = $this->isEnabled();
    if ($enabled) {
      $this->document->addLink('manifest.json', 'manifest');
    }
  }
  // Event handle: view/common/footer/after
  public function renderWebpush(&$route, &$data, &$output)
  {
    $enabled = $this->isEnabled();
    if ($enabled) {
      $this->load->language("extension/module/easywebpush");
      $this->load->model('tool/image');
      $key = 'module_easywebpush_';

      $data = array();
      $data['vapid_public'] = $this->config->get($key . 'vapid_public');
      $data['bell_status'] = $this->config->get($key . 'subscription_bell_status');
      $data['autoprompt_status'] = $this->config->get($key . 'subscription_autoprompt_status');
      $data['autoprompt_delay'] = $this->config->get($key . 'autoprompt_delay') * 1000;
      $data['autoprompt_reinit'] = $this->config->get($key . 'autoprompt_reinit');
      $data['primary_color'] = $this->config->get($key . 'primary_color');
      $data['custom_css'] = "<style>" . $this->config->get($key . 'custom_css') . "</style>";

      $text = $this->config->get($key . 'prompt_text');
      $lng = $this->config->get('config_language_id');
      $data['prompt_text'] = $text[$lng]['text'];

      $logo = $this->config->get($key . 'prompt_logo');
      $fallback = $this->config->get('config_logo');
      if (isset($logo) && is_file(DIR_IMAGE . $logo)) {
        $data['prompt_logo'] = $this->model_tool_image->resize($logo, 100, 100);
      } elseif ($fallback && is_file(DIR_IMAGE . $fallback)) {
        $data['prompt_logo'] = $this->model_tool_image->resize($fallback, 100, 100);
      } else {
        $data['prompt_logo'] = $this->model_tool_image->resize('no_image.png', 100, 100);
      }

      $buttons = $this->load->view('extension/module/easywebpush/webpush', $data);
      $search = '</body>';
      $replace = $buttons . '</body>';
      $output = str_replace($search, $replace, $output);
    }
  }
  // Event handle: catalog/model/account/customer/deleteLoginAttempts/after
  public function updateSubscriptionIdsPostLogin()
  {
    $session_id = $this->session->getId();
    $customer_id = $this->customer->getId();
    if ($customer_id && $session_id) {
      $this->load->model('extension/module/easywebpush');
      $this->model_extension_module_easywebpush->updateSubscriptionIdsPostAuth($session_id, $customer_id);
    }
  }
  // Event handle: catalog/model/account/customer/addCustomer/after
  public function updateSubscriptionIdsPostRegister(&$route, &$data, &$output)
  {
    $session_id = $this->session->getId();
    $customer_id = $output;
    if ($customer_id && $session_id) {
      $this->load->model('extension/module/easywebpush');
      $this->model_extension_module_easywebpush->updateSubscriptionIdsPostAuth($session_id, $customer_id);

      // Notify admin
      $admin_events = $this->config->get("module_easywebpush_events_for_admin");
      if (in_array("register", $admin_events)) {
        $this->load->language("extension/module/easywebpush");
        $name = $data[0]['firstname'] . " " . $data[0]['lastname'];
        $pushData = array(
          'title'         => $this->language->get("event_registered_title"),
          'message'       => sprintf($this->language->get("event_registered_subtitle"), $name),
          'action_title'  => $this->language->get("event_registered_action"),
          'action_link'   => HTTPS_SERVER . "admin/index.php?route=customer/customer&user_token=",
          'admin'         => true
        );
        $this->notify($pushData);
      }
    }
  }
  public function subscribe()
  {
    $method = $this->request->server['REQUEST_METHOD'];
    $subscription = json_decode(html_entity_decode($this->request->post['subscription']), true);
    if ($method == 'POST' && $subscription['endpoint']) {
      $this->load->language('extension/module/easywebpush');
      $this->load->model('extension/module/easywebpush');
      $customer_id = $this->customer->getId();
      $session_id = $this->customer->isLogged() ? "" : $this->session->getId();

      // Detect Device
      require_once(str_replace('\\', '/', realpath(DIR_SYSTEM . 'library/easywebpush/Mobile_Detect.php')));
      $detect = new Mobile_Detect;
      $device = 'Desktop';
      if ($detect->isTablet()) {
        $device = "Tablet";
      } elseif ($detect->isMobile()) {
        $device = "Mobile";
      }

      // Detect country
      $ip = $this->request->server['REMOTE_ADDR'];
      $geoip = new Reader(str_replace('\\', '/', realpath(DIR_SYSTEM . 'library/easywebpush/GeoLite2-Country.mmdb')));
      $country_code = '';
      $country_name = '';
      try {
        $geo = $geoip->country($ip);
        $country_code = $geo->country->isoCode;
        $country_name = $geo->country->name;
      } catch (Exception $e) {
        $country_code = '';
        $country_name = '';
      }
      $data = array(
        'subscription' => $subscription,
        'session_id'   => $session_id,
        'customer_id'  => $customer_id,
        'device'       => $device,
        'ip'           => $ip,
        'country_code' => $country_code,
        'country_name' => $country_name
      );
      $saved = $this->model_extension_module_easywebpush->addSubscription($data);

      $result = array();
      if ($saved) {
        $pushData = array(
          'title' => $this->language->get("push_subscription_success_title"),
          'message' => $this->language->get("push_subscription_success_msg"),
        );
        $this->notify($pushData);
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
    $this->load->model('extension/module/easywebpush');
    $method = $this->request->server['REQUEST_METHOD'];
    $subscription = json_decode(html_entity_decode($this->request->post['subscription']), true);
    $result = array();

    if ($method == 'POST' && $subscription['endpoint']) {
      $deleted = $this->model_extension_module_easywebpush->deleteSubscription($subscription['endpoint']);
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
  public function isSubscribed()
  {
    $this->load->model('extension/module/easywebpush');
    $method = $this->request->server['REQUEST_METHOD'];
    $subscription_detail = json_decode(html_entity_decode($this->request->post['subscription']), true);
    $subscribed = false;

    if ($method == 'POST' && $subscription_detail['endpoint']) {
      $exist = $this->model_extension_module_easywebpush->isSubscribed($subscription_detail['endpoint']);
      if ($exist) {
        $subscribed = true;
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($subscribed));
  }
  public function notify($push)
  {
    $result = array();
    if ($this->config->get('module_easywebpush_status')) {
      $this->load->language('extension/module/easywebpush');
      $this->load->model('extension/module/easywebpush');
      $this->load->model('tool/image');

      //Settings values
      $publicKey = $this->config->get("module_easywebpush_vapid_public");
      $privateKey = $this->config->get("module_easywebpush_vapid_private");
      $default_badge = $this->model_tool_image->resize($this->config->get("config_icon"), 100, 100);
      $prompt_logo = $this->config->get('module_easywebpush_prompt_logo');
      $default_icon_path = $prompt_logo ? $prompt_logo : $this->config->get("config_logo");
      if (isset($push['icon']) && $push['icon']) {
        $icon = $this->model_tool_image->resize($push['icon'], 256, 256);
      } else {
        $icon = $this->model_tool_image->resize($default_icon_path, 256, 256);
      }
      $badge = $default_badge;
      $subject = HTTPS_SERVER;
      $vibrate = [100, 50, 100];
      $dir = $this->language->get('direction');

      // Msg content
      $title = $push['title'];
      $body  =  $push['message'];
      $image =  '';
      if (isset($push['image']) && $push['image']) {
        list($width) = getimagesize(DIR_IMAGE . $push['image']);
        $image = $this->model_tool_image->resize($push['image'], $width, $width / 2);
      }
      $data =  $push['data'] ?? array();

      // Actions
      $close_action = array(
        'action' => "close",
        'title' => $this->language->get("text_close")
      );
      $action[] = $close_action;
      $action_title = isset($push['action_title']) ? $push['action_title'] : "";
      $action_link = isset($push['action_link']) ? $push['action_link'] : "";
      if ($action_title && $action_link) {
        $second_action = array(
          'action' => "action",
          'title'  => $action_title
        );
        $action[] = $second_action;
        $data['link'] = $action_link;
      }
      $final_actions = array_values(array_filter($action));

      //Get subscriptions
      $subscriptions = array();
      if (isset($push['admin']) && $push['admin']) {
        $subscriptions = $this->model_extension_module_easywebpush->getAdminsSubscriptions();
      } else {
        $cid = isset($push['customer_id']) && $push['customer_id'] ? $push['customer_id'] : 0;
        $subscriptions = $this->model_extension_module_easywebpush->getCustomerSubscriptions($cid);
      }

      if (count($subscriptions) > 0) {
        // Init push
        $pushAuth = array(
          'VAPID' => array(
            'subject' => $subject,
            'publicKey' => $publicKey,
            'privateKey' => $privateKey,
          ),
        );
        $payload = array(
          'title'   => $title,
          'body'    => $body,
          'icon'    => $icon,
          'badge'   => $badge,
          'vibrate' => $vibrate,
          'data'    => $data,
          'dir'     => $dir,
          'image'   => $image,
          'actions' => $final_actions,
        );
        $webPush = new WebPush($pushAuth);
        foreach ($subscriptions as $sub) {
          $subscription = Subscription::create($sub);
          $webPush->queueNotification(
            $subscription,
            json_encode($payload)
          );
        }
        // handle eventual errors here, and remove the subscription from your server if it is expired
        foreach ($webPush->flush() as $report) {
          $endpoint = $report->getEndpoint();
          if ($report->isSuccess()) {
            $result['result'] = "[v] Message sent successfully for subscription {$endpoint}.";
          } else {
            $err = "[x] {$report->getReason()}";
            $result['result'] = $err;
            $this->log->write("[Web Push]: " . $err);
          }
        }
      }
    }

    return $result;
  }
  public function reportopen()
  {
    $campaign_id = isset($this->request->get['campaign_id']) ? $this->request->get['campaign_id'] : null;
    if ($campaign_id) {
      $this->load->model('extension/module/easywebpush');
      $this->model_extension_module_easywebpush->reportopen($campaign_id);
    }
  }
  protected function isEnabled()
  {
    $key = 'module_easywebpush_';
    $enabled = $this->config->get($key . 'status');
    $routes = $this->config->get($key . 'prompt_routes');
    $current_route = isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home';
    $has_wildcard = false;
    foreach ($routes as $route) {
      $curr = explode("/", $current_route);
      $route_split = explode("/", $route);
      $is_wildcard = $route_split[1] === '*';
      $same_root = $curr[0] === $route_split[0];
      if ($same_root && $is_wildcard) {
        $has_wildcard = true;
        break;
      }
    }
    $route_included = in_array($current_route, $routes) || $has_wildcard;

    return $enabled && $route_included;
  }
}
