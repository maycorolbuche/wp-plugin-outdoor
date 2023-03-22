<?php
/**
 * @package Outdoor
 * @version 0.1.2
 */
/*
Plugin Name: Outdoor
Plugin URI:
Description: Projetar vídeos e imagens em looping de forma dinâmica.
Version: 0.1.2
Requires at least: 5.0
Requires PHP: 7.0
Author: Mayco Rolbuche
Author URI: https://maycorolbuche.com.br/
License: GPLv2 or later
 */

// don't load directly
if (!defined('ABSPATH')) {
    die('-1');
}

define('OUTD_URL', plugins_url('', __FILE__));
define('OUTD_URL_CSS', OUTD_URL . '/assets/css/');
define('OUTD_URL_JS', OUTD_URL . '/assets/js/');
define('OUTD_URL_IMG', OUTD_URL . '/assets/img/');
define('OUTD_DIR', plugin_dir_path(__FILE__));
define('OUTD_DIR_PAGES', OUTD_DIR . 'pages/');
define('OUTD_DIR_CSS', OUTD_DIR . 'assets/css/');
define('OUTD_DIR_JS', OUTD_DIR . 'assets/js/');

register_activation_hook(__FILE__, 'outd_activate');
function outd_activate()
{
    global $wpdb;

    $prefix = $wpdb->prefix;
    $table_name = $prefix . 'outdoor';
    $charset_collate = $wpdb->get_charset_collate();
    $db_version = get_option('outdoor_db_version');

    if (empty($db_version)) {

        $sql = "
            CREATE TABLE $table_name (
                outdoor_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                post_id BIGINT(20) UNSIGNED NOT NULL,
                outdoor_status ENUM('active','inactive') NOT NULL DEFAULT 'active',
                outdoor_order INT(10) NOT NULL DEFAULT '0',
                outdoor_start_date DATE NULL DEFAULT NULL,
                outdoor_end_date DATE NULL DEFAULT NULL,
                outdoor_object_fit ENUM('contain','cover','fill') NOT NULL DEFAULT 'cover',
                outdoor_duration INT(10) NOT NULL DEFAULT '10',
                outdoor_options LONGTEXT NULL,
                PRIMARY KEY (`outdoor_id`),
                INDEX `FK_{$table_name}_{$prefix}posts` (`post_id`),
                CONSTRAINT `FK_{$table_name}_{$prefix}posts` FOREIGN KEY (`post_id`) REFERENCES `{$prefix}posts` (`ID`) ON UPDATE CASCADE ON DELETE CASCADE
            ) $charset_collate;
        ";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        $db_version = '1.0';
        add_option('outdoor_db_version', $db_version);

    }

}

register_uninstall_hook(__FILE__, 'outd_uninstall');
//register_deactivation_hook(__FILE__, 'outd_uninstall');
function outd_uninstall()
{
    global $wpdb;

    delete_option('outdoor_options');
    delete_option('outdoor_db_version');

    $prefix = $wpdb->prefix;
    $table_name = $prefix . 'outdoor';

    $sql = "DROP TABLE IF EXISTS {$table_name}";
    $wpdb->query($sql);

    $check_table = $wpdb->get_results("SHOW TABLES LIKE '{$table_name}';");
    if (!empty($check_table)) {
        die("Não foi possível apagar tabela no banco de dados!");
    }

}

require_once OUTD_DIR . 'functions.php';
