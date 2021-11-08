<?php
require(str_replace('\\', '/', realpath(DIR_SYSTEM . 'library/easywebpush/autoload.php')));

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class ControllerExtensionModuleEasywebpushSubscribers extends Controller
{
  private $error = array();

  public function index()
  {
    $this->load->language('extension/module/easywebpush');
    $this->load->model('extension/module/easywebpush/subscribers');
    $this->load->model('tool/image');
    $this->document->addStyle('view/javascript/easywebpush/style.css');
    $this->document->setTitle($this->language->get('menu_subscribers'));

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateDelete()) {
      $this->model_extension_module_easywebpush_subscribers->deleteSubscriptionByIds($this->request->post['selected']);
      $this->session->data['success'] = sprintf($this->language->get('text_delete_success'), count($this->request->post['selected']), $this->language->get('text_subscribers_list'));
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
      'text' => $this->language->get('menu_subscribers'),
      'href' => $this->url->link('extension/module/easywebpush/subscriber', 'user_token=' . $this->session->data['user_token'], true)
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
    if (isset($this->request->get['email'])) {
      $data['email'] = $this->request->get['email'];
    } else {
      $data['email'] = null;
    }
    if (isset($this->request->get['device'])) {
      $data['device'] = $this->request->get['device'];
    } else {
      $data['device'] = null;
    }

    $filter_data = array(
      'name'                     => $data['name'],
      'email'                    => $data['email'],
      'device'                   => $data['device'],
      'sort'                     => $data['sort'],
      'order'                    => $data['order'],
      'start'                    => ($data['page'] - 1) * $this->config->get('config_limit_admin'),
      'limit'                    => $this->config->get('config_limit_admin')
    );

    $subscribers = $this->model_extension_module_easywebpush_subscribers->getSubscribers($filter_data);
    $subscribers_total = $this->model_extension_module_easywebpush_subscribers->getTotalSubscribers($filter_data);
    foreach ($subscribers as $subscriber) {
      $data["subscribers"][] = array(
        'id'          => $subscriber["id"],
        'device'      => $subscriber["device"],
        'name'        => $subscriber["name"] ?? $this->language->get("text_anonymous"),
        'email'       => $subscriber["email"] ?? $this->language->get("text_anonymous"),
        'telephone'   => $subscriber["telephone"] ?? $this->language->get("text_anonymous"),
        'date_added'  => $subscriber["date_added"],
      );
    }
    if (isset($this->error['error_warning'])) {
      $data['error_warning'] = $this->error['error_warning'];
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

    if ($this->config->get('module_easywebpush_prompt_logo') && is_file(DIR_IMAGE . $this->config->get('module_easywebpush_prompt_logo'))) {
      $data['icon_preview'] = $this->model_tool_image->resize($this->config->get('module_easywebpush_prompt_logo'), 100, 100);
      $data['icon'] = $this->config->get('module_easywebpush_prompt_logo');
    } elseif ($this->config->get('config_logo') && is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
      $data['icon_preview'] = $this->model_tool_image->resize($this->config->get('module_easywebpush_prompt_logo'), 100, 100);
      $data['icon'] = $this->config->get('config_logo');
    } else {
      $data['icon_preview'] = $this->model_tool_image->resize('no_image.png', 100, 100);
    }
    $data['image'] = '';
    $data['image_preview'] = $this->model_tool_image->resize('no_image.png', 100, 100);
    $data['badge'] = $this->model_tool_image->resize($this->config->get("config_icon"), 35, 35);
    $data['subject'] = HTTPS_CATALOG;

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
    if (isset($this->request->get['email'])) {
      $url .= '&email=' . urlencode(html_entity_decode($this->request->get['email'], ENT_QUOTES, 'UTF-8'));
    }
    if (isset($this->request->get['device'])) {
      $url .= '&device=' . urlencode(html_entity_decode($this->request->get['device'], ENT_QUOTES, 'UTF-8'));
    }
    $data['delete'] = $this->url->link('extension/module/easywebpush/subscribers', 'user_token=' . $this->session->data['user_token'] . $url, true);

    $pagination = new Pagination();
    $pagination->total = $subscribers_total;
    $pagination->page = $data['page'];
    $pagination->limit = $this->config->get('config_limit_admin');
    $pagination->url = $this->url->link('extension/module/easywebpush/subscribers', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
    $data['pagination'] = $pagination->render();
    $data['results'] = sprintf($this->language->get('text_pagination'), ($subscribers_total) ? (($data['page'] - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($data['page'] - 1) * $this->config->get('config_limit_admin')) > ($subscribers_total - $this->config->get('config_limit_admin'))) ? $subscribers_total : ((($data['page'] - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $subscribers_total, ceil($subscribers_total / $this->config->get('config_limit_admin')));

    $data['emulator'] = $this->load->view('extension/module/easywebpush/emulator', $data);

    $data['user_token'] = $this->session->data['user_token'];
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/module/easywebpush/subscribers', $data));
  }
  public function send()
  {
    $this->load->language('extension/module/easywebpush');
    $this->load->model('extension/module/easywebpush/subscribers');
    $result = array();
    if ($this->validateSend()) {
      $subscriptions = $this->model_extension_module_easywebpush_subscribers->getSubscriptionByIds($this->request->post['selected']);
      $icon = '';
      if ($this->request->post['icon']) {
        $icon = $this->request->post['icon'];
      }
      $image =  '';
      if ($this->request->post['image']) {
        $image = $this->request->post['image'];
      }
      $second_action = isset($this->request->post['second_action']) && $this->request->post['second_action'];
      $push = array(
        'subscriptions'   => $subscriptions,
        'icon'            => $icon,
        'title'           => $this->request->post['title'],
        'message'         => $this->request->post['message'],
        'image'           => $image,
        'action_title'    => $second_action ? $this->request->post['action_title'] : "",
        'action_link'     => $second_action ? $this->request->post['action_link'] : ""
      );
      $result = $this->notify($push);
    } else {
      $result = $this->error;
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($result));
  }
  public function notify($push)
  {
    $result = array();
    $this->load->model('tool/image');

    //Settings values
    $publicKey = $this->config->get("module_easywebpush_vapid_public");
    $privateKey = $this->config->get("module_easywebpush_vapid_private");
    $default_badge = $this->model_tool_image->resize($this->config->get("config_icon"), 100, 100);
    $prompt_logo = $this->config->get('module_easywebpush_prompt_logo');
    $default_icon_path = $prompt_logo ? $prompt_logo : $this->config->get("config_logo");
    if ($push['icon']) {
      $icon = $this->model_tool_image->resize($push['icon'], 256, 256);
    } else {
      $icon = $this->model_tool_image->resize($default_icon_path, 256, 256);
    }
    $badge = $default_badge;
    $subject = HTTPS_CATALOG;
    $vibrate = [100, 50, 100];
    $dir = $this->language->get('direction');
    // Msg content
    $title = $push['title'];
    $body  =  $push['message'];
    $image =  '';
    if ($push['image']) {
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
    if ($push['action_title'] && $push['action_link']) {
      $second_action = array(
        'action' => "action",
        'title'  => $push['action_title']
      );
      $action[] = $second_action;
      $data['link'] = $push['action_link'];
    }
    $final_actions = array_values(array_filter($action));

    $pushAuth = array(
      'VAPID' => array(
        'subject' => $subject,
        'publicKey' => $publicKey,
        'privateKey' => $privateKey,
      ),
    );
    $webPush = new WebPush($pushAuth);
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

    foreach ($push['subscriptions'] as $sub) {
      $subscription = Subscription::create($sub);
      $webPush->queueNotification(
        $subscription,
        json_encode($payload)
      );
    }
    // handle eventual errors here, and remove the subscription from your server if it is expired
    $result['success_total'] = 0;
    $result['fail_total'] = 0;
    $result['deleted_total'] = 0;
    foreach ($webPush->flush() as $report) {
      $endpoint = $report->getEndpoint();
      if ($report->isSuccess()) {
        $result['success_total'] += 1;
      } elseif ($report->isSubscriptionExpired()) {
        $this->model_extension_module_easywebpush_subscribers->deleteSubscriptionByEndpoint($endpoint);
        $result['deleted_total'] += 1;
        $result['fail_total'] += 1;
      } else {
        $result['fail_total'] += 1;
      }
    }
    if ($result['success_total']) {
      $result['success'] = sprintf($this->language->get('report_send_push_success'), $result['success_total']);
    }
    if ($result['fail_total']) {
      if ($result['deleted_total']) {
        $deleted_notice = sprintf($this->language->get('report_send_push_unsub'), $result['deleted_total']);
      }
      $errMsg = sprintf($this->language->get('report_send_push_fail'), $result['fail_total'], $deleted_notice);
      $result['error'] = $errMsg;
      $this->log->write("[Web Push]: " . $errMsg);
    }

    return $result;
  }

  protected function validateSend()
  {
    if (!isset($this->request->post['selected'])) {
      $this->error["error_warning"] = $this->language->get("error_empty_selected");
    }
    if (utf8_strlen($this->request->post['title']) < 3 || utf8_strlen($this->request->post['title']) > 64) {
      $this->error['validation']["title"] = sprintf($this->language->get("error_string_length"), 3, 64);
    }
    if (utf8_strlen($this->request->post['message']) < 3 || utf8_strlen($this->request->post['message']) > 160) {
      $this->error['validation']["message"] = sprintf($this->language->get("error_string_length"), 3, 160);
    }
    if (isset($this->request->post['second_action']) && (utf8_strlen($this->request->post['action_title']) < 1 || utf8_strlen($this->request->post['action_title']) > 20)) {
      $this->error['validation']["action_title"] = sprintf($this->language->get("error_string_length"), 3, 20);
    }
    if (isset($this->request->post['second_action']) && filter_var($this->request->post['action_link'], FILTER_VALIDATE_URL) == false) {
      $this->error['validation']["action_link"] = $this->language->get("error_action_link");
    }

    return !$this->error;
  }
  protected function validateDelete()
  {
    if (!isset($this->request->post['selected'])) {
      $this->error["error_warning"] = $this->language->get("error_empty_selected");
    }
    if (!$this->user->hasPermission('modify', 'extension/module/easywebpush/subscribers')) {
      $this->error['error_warning'] = $this->language->get('error_permission');
    }

    return !$this->error;
  }
}