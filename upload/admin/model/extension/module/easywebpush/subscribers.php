<?php

class ModelExtensionModuleEasywebpushSubscribers extends Model
{
  public function getSubscribers($data = array())
  {
    $sql = 'SELECT s.id, s.device, s.date_added, CONCAT(c.firstname, " ", c.lastname) AS name';
    $sql .= ', c.email, c.telephone';
    $sql .= ' FROM ' . DB_PREFIX . 'easywebpush_subscription s';
    $sql .= ' LEFT JOIN ' . DB_PREFIX . 'customer c ON(s.customer_id = c.customer_id)';
    $sql .= ' WHERE s.admin_id = 0';

    if (isset($data['name']) && !is_null($data['name'])) {
      $sql .= " AND CONCAT(c.firstname, ' ', c.lastname) LIKE '" . $data['name'] . "%'";
    }
    if (isset($data['email']) && !is_null($data['email'])) {
      $sql .= " AND c.email = '" . $data['email'] . "'";
    }
    if (isset($data['device']) && !is_null($data['device'])) {
      $sql .= " AND s.device = '" . $data['device'] . "'";
    }

    $sort_data = array(
      'name',
      's.device',
      's.date_added',
    );
    if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
      $sql .= " ORDER BY " . $data['sort'];
    } else {
      $sql .= " ORDER BY date_added";
    }

    if (isset($data['order']) && ($data['order'] == 'DESC')) {
      $sql .= " DESC";
    } else {
      $sql .= " ASC";
    }

    if (isset($data['start']) || isset($data['limit'])) {
      if ($data['start'] < 0) {
        $data['start'] = 0;
      }

      if ($data['limit'] < 1) {
        $data['limit'] = 20;
      }

      $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
    }

    $query = $this->db->query($sql);

    return $query->rows;
  }
  public function getTotalSubscribers($data = array())
  {
    $sql = 'SELECT COUNT(*)  AS total FROM ' . DB_PREFIX . 'easywebpush_subscription s';
    $sql .= ' LEFT JOIN ' . DB_PREFIX . 'customer c ON(s.customer_id = c.customer_id)';
    $sql .= ' WHERE s.admin_id = 0';

    if (isset($data['name']) && !is_null($data['name'])) {
      $sql .= " AND CONCAT(c.firstname, ' ', c.lastname) LIKE '" . $data['name'] . "%'";
    }
    if (isset($data['email']) && !is_null($data['email'])) {
      $sql .= " AND c.email = '" . $data['email'] . "'";
    }
    if (isset($data['device']) && !is_null($data['device'])) {
      $sql .= " AND s.device = '" . $data['device'] . "'";
    }

    $query = $this->db->query($sql);

    return $query->row['total'];
  }
  public function getSubscriptionByIds($ids = array())
  {
    $sql = "SELECT * FROM " . DB_PREFIX . "easywebpush_subscription WHERE ";
    $sql .= " id IN('" . implode("','", $ids) . "')";

    $query = $this->db->query($sql);
    $subscriptions = array();
    if ($query->rows) {
      foreach ($query->rows as $sub) {
        if ($sub['endpoint']) {
          $subscriptions[] = array(
            'id'        => $sub['id'],
            'endpoint'  => $sub['endpoint'],
            'keys'      => array(
              'auth' =>  $sub['auth'],
              'p256dh' => $sub['p256dh']
            )
          );
        }
      }
    }
    return $subscriptions;
  }
  public function getSubscriberCountries()
  {
    $sql  = "SELECT COUNT(id) AS total, country_code, country_name";
    $sql .= " FROM " . DB_PREFIX . "easywebpush_subscription";
    $sql .= " WHERE admin_id = 0 AND country_code != '' AND country_code IS NOT NULL";
    $sql .= " GROUP BY country_code";

    $query = $this->db->query($sql);
    return $query->rows;
  }
  public function deleteSubscriptionByEndpoint($endpoint)
  {
    $sql = "DELETE FROM " . DB_PREFIX . "easywebpush_subscription WHERE ";
    $sql .= " endpoint = '" . $endpoint . "'";
    $query = $this->db->query($sql);
  }
  public function deleteSubscriptionByIds($ids = array())
  {
    $sql = "DELETE FROM " . DB_PREFIX . "easywebpush_subscription WHERE ";
    $sql .= " id IN('" . implode("','", $ids) . "')";
    $this->db->query($sql);
  }
}