<?php
class ModelExtensionModuleEasywebpush extends Model
{
  public function installWebpush()
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
    $ROOT_DIR = str_replace('\\', '/', realpath(DIR_APPLICATION . '..'));
    exec("which composer", $output);
    if (!isset($output) || empty($output)) {
      trigger_error($this->language->get('error_composer_not_found'));
      exit;
    }

    if (file_exists($ROOT_DIR . "vendor/minishlink")) {
      return true;
    }

    exec("cd " . $ROOT_DIR . " && composer require minishlink/web-push", $ioutput);

    return !empty($output);
  }
  public function moveSwjsToRoot()
  {
    // Check if already in root
    $ROOT_DIR = str_replace('\\', '/', realpath(DIR_APPLICATION . '..'));
    $ROOT_SW = $ROOT_DIR  . '/sw.js';
    if (file_exists($ROOT_SW) && is_file($ROOT_SW)) {
      return  true;
    }
    $APP_DIR = str_replace('\\', '/', realpath(DIR_CATALOG . 'view/javascript/easywebpush/sw.js'));
    if (file_exists($APP_DIR) && is_file($APP_DIR)) {
      return  rename($APP_DIR, $ROOT_SW);
    }

    return false;
  }
  public function createTables()
  {

    $tables = array(
      "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX .
        "easywebpush` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `customer_id` int(11) unsigned NOT NULL,
                `session_id` VARCHAR(32) NOT NULL,
                `admin_id` int(11) unsigned NOT NULL,
                `endpoint` varchar(255) NOT NULL,
                `auth` varchar(25) NOT NULL,
                `p256dh` varchar(90) NOT NULL,
                `date_added` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
    );

    foreach ($tables as $query) {
      $this->db->query($query);
    }
  }

  public function dropTables()
  {

    $tables = array(
      "DROP TABLE IF EXISTS `" . DB_PREFIX . "easywebpush`",
    );

    foreach ($tables as $query) {
      $this->db->query($query);
    }
  }
}