<?php

global $MWW_db_version;
$MWW_db_version = '1.0';

function MWW_create_db()
{
    global $wpdb, $MWW_db_version;

    $table_name = $wpdb->prefix . 'mww_weather_records';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        date_time int(11) PRIMARY KEY not NULL,
        city varchar(20) NOT NULL,
        weather_status text default '',
        temp_c decimal not NULL,
        wind_speed decimal not NULL,
        wind_dir char(3) NOT null,
        atm_press decimal not NULL,
        humidity int not null 
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $log = dbDelta($sql);
    write_log($log);

    add_option('MWW_db_version', $MWW_db_version);
}

function MWW_update_db_check()
{
    global $MWW_db_version;
    if (get_site_option('MWW_db_version') != $MWW_db_version) {
        MWW_updates_db();
    }
}

function MWW_updates_db()
{
    global $wpdb, $MWW_db_version;

    $table_name = $wpdb->prefix . 'mww_weather_records';

    $sql = '';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    update_option('MWW_db_version', $MWW_db_version);
}

function MWW_uninstall()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'mww_weather_records';
    $sql = "DROP TABLE $table_name;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    delete_option('MWW_db_version');
}
