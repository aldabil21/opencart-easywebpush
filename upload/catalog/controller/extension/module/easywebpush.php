<?php
require(str_replace('\\', '/', realpath(DIR_SYSTEM . '../vendor/' . 'autoload.php')));

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class ControllerExtensionModuleEasywebpush extends Controller
{
  private $error = array();

  // Event handle: catalog/controller/common/footer/before
  public function addGlobalScripts()
  {

    $this->document->addScript('catalog/view/javascript/easywebpush/serviceworker.js');
    $this->document->addLink('catalog/view/javascript/easywebpush/manifest.webmanifest', 'manifest');
  }
  // Event handle: catalog/controller/common/home/after
  public function eventHomeAfter(&$route, &$data, &$output)
  {

    $this->load->model('extension/module/easywebpush');
    $this->load->language('extension/module/easywebpush');

    $buttons = $this->load->view('extension/module/easywebpush/test_buttons', $data);

    // Edit template
    $template_buffer = $this->model_extension_module_easywebpush->getTemplateBuffer($route, $output);
    $search = '<footer>';
    $replace = $buttons . '<footer>';
    $output = str_replace($search, $replace, $template_buffer);
  }

  public function subscribe()
  {
    $this->load->model('extension/module/easywebpush');
    $method = $this->request->server['REQUEST_METHOD'];
    $subscription_detail = json_decode(html_entity_decode($this->request->post['subscription']), true);
    $result = array();

    if ($method == 'POST') {
      if (!$subscription_detail['endpoint']) {
        $result['error'] = 'Error: Faild to subscribe. please refresh and try again';
      }
      if ($subscription_detail['endpoint']) {
        $saved = $this->model_extension_module_easywebpush->addSubscription($subscription_detail);
        if ($saved) {
          $pushData = array(
            'title' => "Subscription Success",
            'msg' => "Congratulations, you've got it right!",
          );
          $this->notify($pushData);
          $result['success'] = 'Successfully Subscribed';
        } else {
          $result['error'] = 'Error Saving Subscription: please refresh and try again';
        }
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($result));
  }
  public function unsubscribe()
  {
    $this->load->model('extension/module/easywebpush');
    $method = $this->request->server['REQUEST_METHOD'];
    $subscription_detail = json_decode(html_entity_decode($this->request->post['subscription']), true);
    $result = array();

    if ($method == 'POST') {
      if ($subscription_detail['endpoint']) {
        $deleted = $this->model_extension_module_easywebpush->deleteSubscription($subscription_detail['endpoint']);
        if ($deleted) {
          $result['success'] = 'Successfully unsbscribed. You will not recieve notifications anymore';
        } else {
          $result['error'] = 'Error Deleting Subscription: please refresh and try again';
        }
      } else {
        $result['error'] = 'Error: Faild to unsubscribe. please refresh and try again';
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($result));
  }
  public function isSubscribed()
  {
    $this->load->model('extension/module/easywebpush');
    $method = $this->request->server['REQUEST_METHOD'];
    $subscription_detail = json_decode(html_entity_decode($this->request->post['subscription']), true);
    $result = array();

    if ($method == 'POST') {
      if ($subscription_detail['endpoint']) {
        $exist = $this->model_extension_module_easywebpush->isSubscribed($subscription_detail['endpoint']);
        if (!$exist) {
          $result['error'] = 'Subscription not found';
        } else {
          $result['success'] = 'Subscription found';
        }
      } else {
        $result['error'] = 'Error: Faild to subscribe. please refresh and try again';
      }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($result));
  }
  public function notify($push)
  {
    // echo"<pre>";print_r($push);die;
    //Webpush content
    $close_action = array(
      'action' => "close",
      'title' => "Close",
      // 'icon'=> "images/cancel.png"
    );
    $title = isset($push['title']) ? $push['title'] : $this->config->get('config_name');
    $subject = isset($push['subject']) ? $push['subject'] : $this->config->get('site_ssl');
    $body = $push['msg'];
    $icon = isset($push['icon']) ? $push['icon'] : "https://picsum.photos/300/300"; //change your fallback icon path accordingly
    $badge = isset($push['badge']) ? $push['badge'] : "https://picsum.photos/300/300"; //change your fallback badge accordingly
    $vibrate = isset($push['vibrate']) ? $push['vibrate'] : [100, 50, 100]; //I think this is deprecated?
    $data = isset($push['data']) ? $push['data'] : '';
    $dir = isset($push['dir']) ? $push['dir'] : 'auto';
    $image = isset($push['image']) ? $push['image'] : '';
    $action[] = isset($push['action']) ? $push['action'] : null;
    $action[] = $close_action;
    $final_actions = array_values(array_filter($action));
    $payload = array(
      'title' => $title,
      'body' => $body,
      'icon' => $icon,
      'badge' => $badge,
      'vibrate' => $vibrate,
      'data' => $data,
      'dir' => $dir,
      'image' => $image,
      'actions' => $final_actions,
    );

    //Get user subscriptions
    $this->load->model('extension/module/easywebpush');
    $subs = $this->model_extension_module_easywebpush->getCustomerSubscriptions();
    $notifications = array();
    if ($subs) {
      $pushAuth = array(
        'VAPID' => array(
          'subject' => $subject,
          'publicKey' => "BI5PjOjLjyaQSOsad3tuzM8c5DsxN7GwYn4GeJk-Kig3WVFSfBtOm5E2_l-Y2GaGsvuC0qM7KaalgJse8HmRH78",
          'privateKey' => "dWuwTktY61t64vscDgtl5VYA4pgFp0fnVIdVuf0Lt60",
        ),
      );
      $webPush = new WebPush($pushAuth);
      foreach ($subs as $sub) {
        $subscription = Subscription::create($sub);
        $res = $webPush->queueNotification(
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
          $result['result'] = "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
        }
      }
    }

    return $result;
    // $this->response->addHeader('Content-Type: application/json');
    // $this->response->setOutput(json_encode($result));
  }
  public function notifyAdmin($push)
  {

    //Webpush content
    $close_action = array(
      'action' => "close",
      'title' => "Close",
      // 'icon'=> "images/cancel.png"
    );
    $title = isset($push['title']) ? $push['title'] : $this->config->get('config_name');
    $body = $push['msg'];
    $icon = isset($push['icon']) ? $push['icon'] : "https://picsum.photos/300/300";
    $badge = isset($push['badge']) ? $push['badge'] : "https://picsum.photos/300/300";
    $vibrate = isset($push['vibrate']) ? $push['vibrate'] : [100, 50, 100];
    $data = isset($push['data']) ? $push['data'] : '';
    $dir = isset($push['dir']) ? $push['dir'] : 'auto';
    $image = isset($push['image']) ? $push['image'] : '';
    $action[] = isset($push['action']) ? $push['action'] : null;
    $action[] = $close_action;
    $final_actions = array_values(array_filter($action));
    $payload = array(
      'title' => $title,
      'body' => $body,
      'icon' => $icon,
      'badge' => $badge,
      'vibrate' => $vibrate,
      'data' => $data,
      'dir' => $dir,
      'image' => $image,
      'actions' => $final_actions,
    );

    //Get admins subscriptions
    $this->load->model('extension/module/easywebpush');
    $subs = $this->model_extension_module_easywebpush->getAdminsSubscriptions();
    $result = array();
    // if($subs){
    //     $pushAuth = array(
    //         'VAPID' => array(
    //         'subject' => $this->config->get('site_ssl'),
    //         'publicKey' => PUSH_PUBLIC,
    //         'privateKey' => PUSH_PRIVATE,
    //         ),
    //     );
    //     $webPush = new WebPush($pushAuth);
    //     foreach($subs as $sub){
    //         $subscription = Subscription::create($sub);
    //         $res = $webPush->sendNotification(
    //             $subscription,
    //             json_encode($payload)
    //         );
    //     }
    //     // handle eventual errors here, and remove the subscription from your server if it is expired
    //     foreach ($webPush->flush() as $report) {
    //         $endpoint = $report->getRequest()->getUri()->__toString();

    //         if ($report->isSuccess()) {
    //             $result['result'] = "[v] Message sent successfully for subscription {$endpoint}.";
    //         } else {
    //             $result['result'] = "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
    //         }
    //     }
    // }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($result));
  }

  //Just test
  public function testcustomer()
  {
    $pushData = array(
      'title' => "Hello Customer", //(optional: see fallback in webpush controller)
      'msg' => "Push body for customer push", //(required)
      'icon' => 'https://picsum.photos/300/300', //(optional: see fallback in webpush controller)
      'badge' => 'https://picsum.photos/300/300', //(optional: see fallback in webpush controller) 
      'vibrate' => [100, 50, 100], //(optional: see fallback in webpush controller)
      'data' => 'https://twitter.com/aldabil21', //(optional: see fallback in webpush controller)
      'dir' => 'ltr', //(optional: see fallback in webpush controller)
      'image' => 'https://picsum.photos/300/300', //(optional: see fallback in webpush controller)
      'action' => array('action' => 'action', 'title' => 'My Twitter')
    );
    $result = $this->notify($pushData);
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($result));
  }
  public function testadmin()
  {
    $pushData = array(
      'title' => "Hello Admin", //(optional: see fallback in webpush controller)
      'msg' => "Push body for admin push", //(required)
      'data' => '/admin', //(optional: see fallback in webpush controller)
      'action' => array('action' => 'action', 'title' => 'Admin Panel')
    );
    $this->notifyAdmin($pushData);
  }
}