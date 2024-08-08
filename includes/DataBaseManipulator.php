<?php

namespace MyWeatherWidget;

class DataBaseManipulator
{
    private static $table_name;
    private static $version;
    private static $instance;

    private function __construct()
    {
        self::$version = '0.1';
        if (get_site_option('MWW_db_version') !== self::$version) {
            $this->updateTable();
        }

        $this->createDbTable();
        // TODO: Move install and unistall hooks here
    }

    public static function getInstance(): DataBaseManipulator
    {
        if (!isset(self::$instance)) {
            self::$instance = new DataBaseManipulator();
        }

        return self::$instance;
    }

    public function createDbTable(): void
    {
        global $wpdb;

        self::$table_name = $wpdb->prefix . 'mww_weather_records';
        $charset_collate = $wpdb->get_charset_collate();

        // TODO: Test if table is created correctly
        $sql = "CREATE TABLE if not exists " . self::$table_name . " (
            date_time int(11) PRIMARY KEY not NULL,
            city varchar(20) NOT NULL,
            weather_status text default '',
            temp_c decimal not NULL,
            wind_speed decimal not NULL,
            wind_dir char(3) NOT null,
            atm_press decimal not NULL,
            humidity int not null 
        ) $charset_collate;";

        $wpdb->query($sql);

        add_option('MWW_db_version', self::$version);
    }

    public static function dropTable(): void
    {
        global $wpdb;
        $sql = "DROP TABLE " . self::$table_name . ";";
        $wpdb->query($sql);

        delete_option('MWW_db_version');
    }

    public function updateTable(): void
    { // Reserved for future db structural modifications / migrations
        // FIXME: Not entirely implemented, provide a implementation

        // Update version number
        self::$version = '';

        $sql = '';
        global $wpdb;
        $wpdb->query($sql);
        update_option('MWW_db_version', self::$version);
    }
}
