<?php
class ModelExtensionModuleEasywebpush extends Model
{
  public function addSubscription($data)
  {
    $subscription = $data['subscription'];
    // Some cases of session change/manual clear data
    // which causing duplicate subscription of same endpoint
    // So clear endpoint first
    $this->deleteSubscription($this->db->escape($subscription['endpoint']));

    $sql = "INSERT INTO ";
    $sql .=  DB_PREFIX . "easywebpush_subscription SET ";
    $sql .= "customer_id = '" . (int)$data['customer_id'] . "'";
    $sql .= ", session_id = '" . $this->db->escape($data['session_id']) . "'";
    $sql .= ", endpoint = '" . $this->db->escape($subscription['endpoint']) . "'";
    $sql .= ", auth = '" . $this->db->escape($subscription['keys']['auth']) . "'";
    $sql .= ", p256dh = '" . $this->db->escape($subscription['keys']['p256dh']) . "'";
    $sql .= ", device = '" . $this->db->escape($data['device']) . "'";
    $sql .= ", ip = '" . $this->db->escape($data['ip']) . "'";
    $sql .= ", country_code = '" . $this->db->escape($data['country_code']) . "'";
    $sql .= ", country_name = '" . $this->db->escape($data['country_name']) . "'";
    $sql .= ", date_added = NOW()";

    $this->db->query($sql);

    $saved = $this->db->getLastId();

    return $saved;
  }
  public function deleteSubscription($endpoint)
  {
    $sql = "DELETE FROM " . DB_PREFIX . "easywebpush_subscription WHERE";
    $sql .= " endpoint = '" . $endpoint . "'";
    $deleted = $this->db->query($sql);
    return $deleted;
  }
  public function getCustomerSubscriptions($id = 0)
  {
    $sql = "SELECT * FROM " . DB_PREFIX . "easywebpush_subscription WHERE ";
    if ($id) {
      $sql .= " customer_id = '" . (int)$id . "'";
    } elseif ($this->customer->isLogged()) {
      $sql .= " customer_id = '" . (int)$this->customer->getId() . "'";
    } else {
      $sql .= " session_id = '" . $this->session->getId() . "'";
    }
    $sql .= " GROUP BY endpoint";

    $query = $this->db->query($sql);
    $subscriptions = array();
    if ($query->rows) {
      foreach ($query->rows as $sub) {
        if ($sub['endpoint']) {
          $subscriptions[] = array(
            'endpoint' => $sub['endpoint'],
            'keys' => array(
              'auth' =>  $sub['auth'],
              'p256dh' => $sub['p256dh']
            )
          );
        }
      }
    }
    return $subscriptions;
  }
  public function isSubscribed($endpoint)
  {
    $sql = "SELECT DISTINCT * FROM " . DB_PREFIX . "easywebpush_subscription WHERE ";
    if ($this->customer->isLogged()) {
      $sql .= " customer_id = '" . (int)$this->customer->getId() . "' AND";
    } else {
      // $sql .= " session_id = '" . $this->session->getId() . "'";
    }
    $sql .= " endpoint = '" . $endpoint . "' ";

    $query = $this->db->query($sql);
    return sizeof($query->rows) > 0;
  }
  public function getAdminsSubscriptions()
  {
    $sql = "SELECT * FROM " . DB_PREFIX . "easywebpush_subscription WHERE admin_id > 0 ";
    $query = $this->db->query($sql);
    $subscriptions = array();
    if ($query->rows) {
      foreach ($query->rows as $sub) {
        if ($sub['endpoint']) {
          $subscriptions[] = array(
            'endpoint' => $sub['endpoint'],
            'keys' => array(
              'auth' =>  $sub['auth'],
              'p256dh' => $sub['p256dh']
            )
          );
        }
      }
    }
    return $subscriptions;
  }

  public function updateSubscriptionIdsPostAuth($session_id, $customer_id)
  {
    $sql = "UPDATE " . DB_PREFIX . "easywebpush_subscription SET";
    $sql .= " customer_id = '" . (int)$customer_id . "'";
    //TODO: Should clear session?
    $sql .= ", session_id = " . '""' . "";
    $sql .= " WHERE session_id = '" . $session_id . "'";
    $this->db->query($sql);
  }
  public function getTemplateBuffer($route, $template_code = '')
  {
    if ($template_code) {
      return $template_code;
    }

    $theme =  $this->config->get('config_theme') . '/template/';
    $theme_dir =  $theme . $route . '.twig';
    $template_dir = DIR_TEMPLATE . $theme_dir;

    if (file_exists($template_dir) && is_file($template_dir)) {
      $modification_dir = $this->getModifactionIfExist($theme_dir);
      if ($modification_dir) {
        $template_dir = $modification_dir;
      }
      return file_get_contents($template_dir);
    } else {
      trigger_error($this->language->get('error_render_template_not_found'));
    }
  }
  public function reportopen($id)
  {
    $sql = "INSERT INTO " . DB_PREFIX . "easywebpush_report SET";
    $sql .= " campaign_id = '" . $id . "', subscriber_id = '" . (int)$this->customer->getId() . "',";
    $sql .= " opened = '1'";
    $this->db->query($sql);
  }
  public function getModifactionIfExist(String $theme_dir)
  {
    // return a PHP file possibly modified by OpenCart's system/storage/modification
    $modification_dir = DIR_MODIFICATION . 'catalog/view/theme/' . $theme_dir;
    if (file_exists($modification_dir) && is_file($modification_dir)) {
      return  $modification_dir;
    }
    return false;
  }
}