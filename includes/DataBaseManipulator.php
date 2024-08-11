<?php

namespace MyWeatherWidget;

use DateTimeImmutable;

// FIXME: Do something about timezones

class DataBaseManipulator
{
    private static string $table_name;
    private static string $version;
    private static DataBaseManipulator $instance;
    private DateTimeImmutable $lastInsertionTime;

    private function __construct()
    {
        self::$version = '0.1';
        if (get_site_option('MWW_db_version') !== self::$version) {
            $this->updateTableStructure();
        }

        $this->createDbTable();
        // TODO: Move install and unistall hooks here
    }


    public function updateTableStructure(): void
    { // Reserved for future db structural modifications / migrations
        // FIXME: Not entirely implemented, provide a implementation

        // Update version number
        self::$version = '';

        $sql = '';
        global $wpdb;
        $wpdb->query($sql);
        update_option('MWW_db_version', self::$version);
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
        $sql = "CREATE TABLE " . self::$table_name . " (
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

    public function getLatesEntry(): ?object
    { // Return most recent entry in db

        // TODO: Retrieve latest entry by city
        global $wpdb;
        $row = $wpdb->get_row("SELECT * from " . self::$table_name . " order by date_time desc limit 1;", OBJECT);

        return $row;
    }

    public function insert(array $data): void
    { // insert data in db, update lastInsertionTime
        // TODO: check if all necesary fields are provided

        global $wpdb;
        $format = array('%d', '%s', '%s', '%f', '%f', '%s', '%f', '%d');
        $wpdb->insert(self::$table_name, $data, $format);
        $this->lastInsertionTime = new DateTimeImmutable();
    }

    /**
     * Check whether database data is old
     *
     * @return boolean
     * Returns true if there is no data or it's old, otherwise false
     */
    public function isStale(): bool
    { // check if data needs to be updated
        if (!isset($this->lastInsertionTime)) {
            return true;
        }

        return $this->lastInsertionTime->diff(new DateTimeImmutable())->m >= 30;
    }
}
