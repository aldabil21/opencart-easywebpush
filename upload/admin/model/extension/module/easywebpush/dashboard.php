<?php

class ModelExtensionModuleEasywebpushDashboard extends Model
{
  public function isSubscribed($endpoint)
  {
    $sql = "SELECT DISTINCT * FROM " . DB_PREFIX . "easywebpush_subscription WHERE ";
    // $sql .= " admin_id = '" . (int)$this->user->getId() . "' AND";
    $sql .= " endpoint = '" . $endpoint . "' ";

    $query = $this->db->query($sql);
    return sizeof($query->rows) > 0;
  }
  public function addSubscription($data)
  {
    $subscription = $data['subscription'];
    $sql = "INSERT INTO ";
    $sql .=  DB_PREFIX . "easywebpush_subscription SET ";
    $sql .= "admin_id = '" . (int)$data['admin_id'] . "'";
    $sql .= ", session_id = ''";
    $sql .= ", endpoint = '" . $this->db->escape($subscription['endpoint']) . "'";
    $sql .= ", auth = '" . $this->db->escape($subscription['keys']['auth']) . "'";
    $sql .= ", p256dh = '" . $this->db->escape($subscription['keys']['p256dh']) . "'";
    $sql .= ", device = '" . $this->db->escape($data['device']) . "'";
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
  public function getCampaignRates()
  {
    $sql = "SELECT SUM(total) AS total, SUM(success) AS success, SUM(failed) AS failed";
    $sql .= " FROM " . DB_PREFIX . "easywebpush_campaign";

    $rsql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "easywebpush_report";

    $camp_query = $this->db->query($sql);
    $report_query = $this->db->query($rsql);

    $camp = $camp_query->row;
    $report = $report_query->row;

    $rates = array();
    $rates['success'] = number_format(($camp['success'] / $camp['total']) * 100, 0);
    $rates['failed'] = number_format(($camp['failed'] / $camp['total']) * 100, 0);
    $rates['opened'] = number_format(($report['total'] / $camp['total']) * 100, 0);

    return $rates;
  }
  public function getSubscribersDevicesRate()
  {
    $sql = "SELECT COUNT(id) AS total, device";
    $sql .= " FROM " . DB_PREFIX . "easywebpush_subscription";
    $sql .= " WHERE admin_id = 0";
    $sql .= " GROUP BY device";

    $query = $this->db->query($sql);

    return $query->rows;
  }
  public function getTotalAdminSubscribers()
  {
    $sql = "SELECT COUNT(id) AS total";
    $sql .= " FROM " . DB_PREFIX . "easywebpush_subscription";
    $sql .= " WHERE admin_id != 0";

    $query = $this->db->query($sql);

    return $query->row['total'];
  }
}