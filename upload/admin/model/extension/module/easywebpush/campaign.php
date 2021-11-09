<?php

class ModelExtensionModuleEasywebpushCampaign extends Model
{
  public function getCampaigns($data = array())
  {
    $sql = 'SELECT p.*';
    $sql .= ', COUNT(r.opened) AS opened';
    $sql .= ' FROM ' . DB_PREFIX . 'easywebpush_campaign p';
    $sql .= ' LEFT JOIN ' . DB_PREFIX . 'easywebpush_report r ON(p.campaign_id = r.campaign_id AND r.opened = 1)';

    if (isset($data['name']) && !is_null($data['name'])) {
      $sql .= " WHERE p.name LIKE '" . $data['name'] . "%'";
    }
    $sql .= ' GROUP BY p.campaign_id';

    $sort_data = array(
      'p.name',
      'p.total',
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
  public function getTotalCampaigns($data = array())
  {
    $sql = 'SELECT COUNT(*) AS total FROM ' . DB_PREFIX . 'easywebpush_campaign p';
    if (isset($data['name']) && !is_null($data['name'])) {
      $sql .= " WHERE p.name LIKE '" . $data['name'] . "%'";
    }
    $query = $this->db->query($sql);
    return $query->row['total'];
  }
  public function getCampaignById($id)
  {
    $camp_sql = "SELECT p.*, COUNT(r.opened) AS opened, DATE_FORMAT(p.date_added, '%Y-%m-%d') as chart_start";
    $camp_sql .= " FROM " . DB_PREFIX . "easywebpush_campaign p";
    $camp_sql .= " LEFT JOIN " . DB_PREFIX . "easywebpush_report r ON(p.campaign_id = r.campaign_id)";
    $camp_sql .= " WHERE p.campaign_id = '" . (int)$id . "'";
    $camp = $this->db->query($camp_sql);

    $rep_sql = "SELECT COUNT(r.id) AS total, DATE_FORMAT(r.date, '%Y-%m-%d') AS date";
    $rep_sql .= " FROM " . DB_PREFIX . "easywebpush_report r";
    // $rep_sql .= " LEFT JOIN " . DB_PREFIX . "customer c ON(c.customer_id = r.subscriber_id)";
    $rep_sql .= " WHERE r.campaign_id = '" . (int)$id . "'";
    $rep_sql .= " GROUP BY DAY(r.date)";
    $report = $this->db->query($rep_sql);

    $campaign = $camp->row;
    $campaign['chart'] = $report->rows;

    return $campaign;
  }
  public function deleteCampaignsByIds($ids = array())
  {
    $sql = "DELETE FROM " . DB_PREFIX . "easywebpush_campaign WHERE ";
    $sql .= " campaign_id IN('" . implode("','", $ids) . "')";
    $this->db->query($sql);
  }
  public function getCampaignReceivers($filters = array())
  {
    $sql = "SELECT DISTINCT s.id FROM " . DB_PREFIX . "easywebpush_subscription s";
    if ($filters['first_filter'] !== 'all') {

      $second = isset($filters['second_filter']) ? $filters['second_filter'] : '';
      $third = isset($filters['third_filter']) ? $filters['third_filter'] : '';

      if ($second == 'registered') {
        $sql .= " LEFT JOIN " . DB_PREFIX . "customer c ON(s.customer_id = c.customer_id)";
        $sql .= " WHERE s.customer_id > 0 AND c.customer_id IS NOT NULL";
      } elseif ($second == 'anonymous') {
        $sql .= " WHERE s.customer_id = 0";
      } elseif ($second == 'device') {
        $sql .= " WHERE s.device = '" . $third . "'";
      } elseif ($second == 'abandoned_cart') {
        $sql .= " LEFT JOIN " . DB_PREFIX . "order o ON(s.customer_id = o.customer_id)";
        $sql .= " WHERE DATE_SUB(o.date_added, INTERVAL 1 DAY) < NOW()";
      } elseif ($second == 'customer_group') {
        $sql .= " LEFT JOIN " . DB_PREFIX . "customer c ON(s.customer_id = c.customer_id)";
        $sql .= " WHERE c.customer_group_id = '" . (int)$third . "'";
      } elseif ($second == 'country') {
        $sql .= " WHERE s.country_code = '" . $third . "'";
      }
      $sql .= " AND s.admin_id  = '0'";
    } else {
      $sql .= " WHERE s.admin_id  = '0'";
    }

    $query = $this->db->query($sql);
    $ids  = array();
    foreach ($query->rows as $id) {
      $ids[] = $id['id'];
    }
    return $ids;
  }
  public function createCampaign($data = array(), $total)
  {
    $sql = "INSERT INTO " . DB_PREFIX . "easywebpush_campaign SET";
    $sql .= " name = '" . $data["name"] . "', title = '" . $data["title"] . "',";
    $sql .= " message = '" . $data["message"] . "', icon = '" . $data["icon"] . "',";
    if ($data["image"]) {
      $sql .= " image = '" . $data["image"] . "',";
    }
    if (isset($data["second_action"]) && $data["second_action"]) {
      $sql .= " action_title = '" . $data["action_title"] . "',";
      $sql .= " action_link = '" . $data["action_link"] . "',";
    }
    $sql .= " total = '" . $total . "'";

    $this->db->query($sql);

    return $this->db->getLastId();
  }
  public function updateCampaign($id, $result = array())
  {
    $sql = "UPDATE " . DB_PREFIX . "easywebpush_campaign SET";
    $sql .= " success = '" . $result["success_total"] . "',";
    $sql .= " failed = '" . $result["fail_total"] . "'";
    $sql .= " WHERE campaign_id = '" . (int)$id . "'";

    $this->db->query($sql);
  }
}