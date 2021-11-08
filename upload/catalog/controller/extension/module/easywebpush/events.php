<?php
class ControllerExtensionModuleEasywebpushEvents extends Controller
{

  // Event handle: catalog/controller/checkout/success/before
  public function postOrderEvent(&$route, &$data)
  {
    $admin_events = $this->config->get("module_easywebpush_events_for_admin");
    $data = $this->session->data;
    if (in_array("order", $admin_events) && isset($data['order_id'])) {
      $this->load->language("extension/module/easywebpush");
      $name = $data['shipping_address']['firstname'] . " " . $data['shipping_address']['lastname'];
      $country = $data['shipping_address']['country'];
      $pushData = array(
        'title'         =>  $this->language->get("event_order_title"),
        'message'       =>  sprintf($this->language->get("event_order_subtitle"), $name, $country, $data['payment_method']['title']),
        'action_title'  =>  $this->language->get("event_order_action"),
        'action_link'   =>  HTTPS_SERVER . "admin/index.php?route=sale/order/info&user_token=&order_id=" . $data['order_id'],
        'admin'         => true
      );
      $this->load->controller("extension/module/easywebpush/notify/", $pushData);
    }
  }
  // Event handle: catalog/model/catalog/review/addReview/after
  public function postReviewEvent(&$route, &$data, &$output)
  {
    $admin_events = $this->config->get("module_easywebpush_events_for_admin");
    $review = isset($data[1]) ? $data[1] : "";
    if (in_array("review", $admin_events) && $review) {
      $this->load->language("extension/module/easywebpush");
      $name = $review['name'];
      $rating = $review['rating'];
      $pushData = array(
        'title'         =>  $this->language->get("event_review_title"),
        'message'       =>  sprintf($this->language->get("event_review_subtitle"), $rating, $name),
        'action_title'  =>  $this->language->get("event_review_action"),
        'action_link'   =>  HTTPS_SERVER . "admin/index.php?route=catalog/review&user_token=",
        'admin'         => true
      );
      $this->load->controller("extension/module/easywebpush/notify/", $pushData);
    }
  }
  // Event handle: catalog/model/checkout/order/addOrderHistory/after
  public function postOrderStatus(&$route, &$data, &$output)
  {
    $api = isset($this->session->data['api_id']);
    $order_id = isset($data[0]) ? $data[0] : null;
    $status_id = isset($data[1]) ? $data[1] : null;
    $comment = isset($data[2]) ? $data[2] : "";
    $notify = isset($data[3]) ? $data[3] : null;

    $customer_events = $this->config->get("module_easywebpush_events_for_customer");

    if (in_array("order_status", $customer_events) && $api && $order_id && $notify) {
      $this->load->model('checkout/order');
      $order_info = $this->model_checkout_order->getOrder($order_id);
      if ($order_info) {
        $this->load->language("extension/module/easywebpush");
        $status = $order_info['order_status'];
        $message = substr($comment, 0, 10) . "...";
        $pushData = array(
          'title'         =>  $this->language->get("event_order_status_title"),
          'message'       =>  sprintf($this->language->get("event_order_status_subtitle"), $status, $message),
          'action_title'  =>  $this->language->get("event_order_status_action"),
          'action_link'   =>  HTTPS_SERVER . "index.php?route=account/order/info&order_id=" . $order_id,
          'customer_id'   =>  $order_info['customer_id']
        );
        $this->load->controller("extension/module/easywebpush/notify/", $pushData);
      }
    }
  }
}