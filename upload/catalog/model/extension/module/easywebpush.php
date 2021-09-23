<?php
class ModelExtensionModuleEasywebpush extends Model
{
  public function addSubscription($data)
  {
    $customer_id = $this->customer->getId();
    $session_id = $this->customer->isLogged() ? "" : $this->session->getId();
    $this->db->query("INSERT INTO " . DB_PREFIX . "easywebpush SET customer_id = '" . (int)$customer_id . "', session_id = '" . $this->db->escape($session_id) . "', endpoint = '" . $this->db->escape($data['endpoint']) . "', auth = '" . $this->db->escape($data['keys']['auth']) . "', p256dh = '" . $this->db->escape($data['keys']['p256dh']) . "', date_added = NOW() ");

    $saved = $this->db->getLastId();

    return $saved;
  }
  public function deleteSubscription($endpoint)
  {
    $customer_id = $this->customer->getId();
    $deleted = $this->db->query("DELETE FROM " . DB_PREFIX . "easywebpush WHERE endpoint = '" . $endpoint . "'");
    return $deleted;
  }
  public function getCustomerSubscriptions()
  {
    $sql = "SELECT * from " . DB_PREFIX . "easywebpush WHERE ";
    if ($this->customer->isLogged()) {
      $sql .= " customer_id = '" . (int)$this->customer->getId() . "'";
    } else {
      $sql .= " session_id = '" . $this->session->getId() . "'";
    }
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
    $sql = "SELECT DISTINCT * FROM " . DB_PREFIX . "easywebpush WHERE ";
    if ($this->customer->isLogged()) {
      $sql .= " customer_id = '" . (int)$this->customer->getId() . "'";
    } else {
      $sql .= " session_id = '" . $this->session->getId() . "'";
    }
    $sql .= " AND endpoint = '" . $endpoint . "' ";

    $query = $this->db->query($sql);
    return sizeof($query->rows) > 0;
  }
  public function getAdminsSubscriptions()
  {
    $sql = "SELECT * from " . DB_PREFIX . "easywebpush WHERE admin_id != '0' ";
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