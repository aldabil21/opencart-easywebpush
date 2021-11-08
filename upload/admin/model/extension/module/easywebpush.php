<?php
require(str_replace('\\', '/', realpath(DIR_SYSTEM . 'library/easywebpush/autoload.php')));

use Minishlink\WebPush\VAPID;

class ModelExtensionModuleEasywebpush extends Model
{

  public function preInstall()
  {
    // Check PHP version
    if (phpversion() < '7.2') {
      trigger_error(sprintf($this->language->get('error_php_version'), phpversion()));
      exit;
    }

    // Check gmp extension
    if (!extension_loaded("gmp")) {
      trigger_error($this->language->get('error_gmp_not_loaded'));
      exit;
    }

    // Make sure webpush lib folder exists
    $LIB_DIR = str_replace('\\', '/', realpath(DIR_SYSTEM . 'library/easywebpush/minishlink'));
    return file_exists($LIB_DIR);
  }
  public function moveSwjsToRoot()
  {
    // Moving sw.js & serviceworker.js & manifest.json to root
    $ROOT_DIR = str_replace('\\', '/', realpath(DIR_APPLICATION . '..'));
    $tomove[] = array(
      'from' => str_replace('\\', '/', realpath(DIR_CATALOG . 'view/javascript/easywebpush/sw.js')),
      'to' => $ROOT_DIR  . '/sw.js'
    );
    $tomove[] = array(
      'from' => str_replace('\\', '/', realpath(DIR_CATALOG . 'view/javascript/easywebpush/manifest.json')),
      'to' => $ROOT_DIR  . '/manifest.json'
    );
    foreach ($tomove as $file) {
      $moved = copy($file["from"], $file["to"]);
      if (!$moved) {
        return false;
      }
    }
    return true;
  }
  public function setInitialSettings()
  {
    $languages = $this->model_localisation_language->getLanguages();
    $prompt_text = array();
    foreach ($languages as $language) {
      $prompt_text[$language['language_id']] = array('text' => 'Follow us to be the first informed about our offers. Don\'t worry we won\'t be annoying!');
    }
    $routes = array(
      'common/home',
    );
    $admin_events = array('register', 'order', 'review');
    $customer_events = array('order_status');
    $initials = array(
      'module_easywebpush_status' => 0,
      'module_easywebpush_subscription_bell_status' => 1,
      'module_easywebpush_subscription_autoprompt_status' => 0,
      'module_easywebpush_autoprompt_delay' => 8,
      'module_easywebpush_autoprompt_reinit' => 24,
      'module_easywebpush_prompt_text' => $prompt_text,
      'module_easywebpush_primary_color' => '#3b9ac8',
      'module_easywebpush_prompt_logo' => '',
      'module_easywebpush_prompt_html' => '',
      'module_easywebpush_prompt_routes' => $routes,
      'module_easywebpush_events_for_admin' => $admin_events,
      'module_easywebpush_events_for_customer' => $customer_events,
    );

    // Check if DB has old keys - else create them
    $old_keys = $this->model_setting_setting->getSetting('module_easywebpush_uninstalled');
    if (strlen($old_keys["module_easywebpush_uninstalled_vapid_public"]) > 1 && strlen($old_keys["module_easywebpush_uninstalled_vapid_private"]) > 1) {
      $initials['module_easywebpush_vapid_public'] = $old_keys["module_easywebpush_uninstalled_vapid_public"];
      $initials['module_easywebpush_vapid_private'] = $old_keys["module_easywebpush_uninstalled_vapid_private"];
    } else {
      $vapid_keys = VAPID::createVapidKeys();
      $initials['module_easywebpush_vapid_public'] = $vapid_keys["publicKey"];
      $initials['module_easywebpush_vapid_private'] = $vapid_keys["privateKey"];
    }

    // Delete preserved keys if exists
    $this->model_setting_setting->deleteSetting('module_easywebpush_uninstalled');

    $this->model_setting_setting->editSetting('module_easywebpush', $initials);
  }
  public function registerEvents()
  {
    // Catalog Events
    $this->model_setting_event->addEvent(
      'easywebpush',
      'catalog/controller/common/header/before',
      'extension/module/easywebpush/addGlobalScripts'
    );
    $this->model_setting_event->addEvent(
      'easywebpush',
      'catalog/view/common/footer/after',
      'extension/module/easywebpush/renderWebpush'
    );
    /**
     * This is to update customer_id after login/register in easywebpush_subscription table
     * Could not find a better event hook than deleteLoginAttempts
     */
    $this->model_setting_event->addEvent(
      'easywebpush',
      'catalog/model/account/customer/deleteLoginAttempts/after',
      'extension/module/easywebpush/updateSubscriptionIdsPostLogin'
    );
    $this->model_setting_event->addEvent(
      'easywebpush',
      'catalog/model/account/customer/addCustomer/after',
      'extension/module/easywebpush/updateSubscriptionIdsPostRegister'
    );
    /**
     * Client triggered events
     */
    $this->model_setting_event->addEvent(
      'easywebpush',
      'catalog/controller/checkout/success/before',
      'extension/module/easywebpush/events/postOrderEvent'
    );
    $this->model_setting_event->addEvent(
      'easywebpush',
      'catalog/model/catalog/review/addReview/after',
      'extension/module/easywebpush/events/postReviewEvent'
    );
    // 
    // 
    // 
    // Admin events
    $this->model_setting_event->addEvent(
      'easywebpush',
      'admin/controller/extension/extension/module/uninstall/before',
      'extension/module/easywebpush/preserveVapidsOnUninstall'
    );
    $this->model_setting_event->addEvent(
      'easywebpush',
      'admin/view/common/column_left/before',
      'extension/module/easywebpush/menu'
    );
    /**
     * Admin triggered events
     */
    $this->model_setting_event->addEvent(
      'easywebpush',
      'catalog/model/checkout/order/addOrderHistory/after',
      'extension/module/easywebpush/events/postOrderStatus'
    );
  }
  public function createTables()
  {
    $this->db->query(" CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "easywebpush_campaign` (
      `campaign_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `title` varchar(255) NOT NULL,
      `message` varchar(255) NOT NULL,
      `icon` varchar(255) NOT NULL,
      `image` varchar(255) DEFAULT NULL,
      `action_title` varchar(255) DEFAULT NULL,
      `action_link` varchar(255) DEFAULT NULL,
      `total` int(11) NOT NULL,
      `success` int(11) NOT NULL DEFAULT 0,
      `failed` int(11) NOT NULL DEFAULT 0,
      `date_added` datetime NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`campaign_id`)
    ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

    $this->db->query(" CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "easywebpush_report` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `campaign_id` int(11) unsigned NOT NULL,
      `subscriber_id` int(11) unsigned NOT NULL DEFAULT 0,
      `opened` tinyint(1) NOT NULL DEFAULT 0,
      `date` date NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      KEY `campaign_id` (`campaign_id`),
      CONSTRAINT `oc_easywebpush_report_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `oc_easywebpush_campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

    $this->db->query(" CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "easywebpush_subscription` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `session_id` varchar(32) NOT NULL,
      `customer_id` int(11) unsigned NOT NULL DEFAULT 0,
      `admin_id` int(11) unsigned NOT NULL DEFAULT 0,
      `endpoint` varchar(255) NOT NULL,
      `auth` varchar(25) NOT NULL,
      `p256dh` varchar(90) NOT NULL,
      `device` varchar(32) NOT NULL,
      `ip` varchar(40) NOT NULL,
      `country_code` varchar(3) DEFAULT NULL,
      `country_name` varchar(255) DEFAULT NULL,
      `date_added` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");
  }

  public function dropTables()
  {

    $tables = array(
      "DROP TABLE IF EXISTS `" . DB_PREFIX . "easywebpush_campaign`",
      "DROP TABLE IF EXISTS `" . DB_PREFIX . "easywebpush_report`",
      "DROP TABLE IF EXISTS `" . DB_PREFIX . "easywebpush_subscription`",
    );

    foreach ($tables as $query) {
      $this->db->query($query);
    }
  }
}