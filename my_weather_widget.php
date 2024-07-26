<?php

/**
 * Plugin Name: My Weather Widget
 * Description: Displays weather in a certain city
 * Version: 0.0.1
 */

if (!defined('ABSPATH')) {
    exit;
}

// TODO: Separate code in files

// Load API key
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(plugin_dir_path(__FILE__));
$dotenv->load();
define('WEATHER_API_KEY', $_ENV['WEATHER_API_KEY']);

global $MWW_db_version;
$MWW_db_version = '1.0';

class My_Weather_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'my_weather_widget',
            'My_Weather_Widget',
            array(
                'description' => __('A widget to display weather in a city', 'text-domain')
            )
        );

        add_action('widgets_init', function () {
            register_widget('My_Weather_Widget');
        });
        register_activation_hook(__FILE__, 'MWW_create_db');
        register_uninstall_hook(__FILE__, 'MWW_uninstall');
        add_action('plugins_loaded', 'MWW_update_db_check');
        add_action('rest_api_init', 'add_weather_api_route');
        add_action('admin_notices', [new AdminNotice(), 'displayAdminNotice']);
    }

    private function get_weather(string $city)
    {

        $response = wp_remote_get(
            'http://localhost:10008/wp-json/myplugin/v1/weather',
            array(
                'body' => array(
                    'city' => $city
                )
            )
        );
        $code = wp_remote_retrieve_response_code($response);

        $body = wp_remote_retrieve_body($response);
        write_log('In function ' . __FUNCTION__ . ' response code: ' . $code);
        // write_log($body);
        // write_log(gettype($body));
        $ret_val = json_decode($body, true);
        write_log('End of ' . __FUNCTION__);
        return MWW_render_response($ret_val);
    }

    public function widget($args, $instance)
    {
        extract($args);
        echo $before_widget;
        $city = $instance['city'];

        echo $this->get_weather($city);
        write_log("In function " . __FUNCTION__ . ' done rendering');
        // echo MWW_get_weather_data($city);

        echo $after_widget;
    }



    public function form($instance)
    {
        if (isset($instance['city'])) {
            $city = $instance['city'];
        } else {
            $city = __('City', 'text-domain');
        }

        $field_id = $this->get_field_id('city');
        $field_name = $this->get_field_name('city');
?>
        <p>
            <label for="<?php echo $field_id ?>" id="<?php echo $field_name . 'label' ?>">
                City
            </label>
            <input id="<?php echo $field_id ?>" name="<?php echo $field_name ?>" type="text" class="widefat" placeholder="eg. London" value="<?php echo empty($instance['city']) ? '' : $instance['city']; ?>">
        </p>
<?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['city'] = (!empty($new_instance['city'])) ? strip_tags($new_instance['city']) : '';
        return $instance;
    }
}

$widget = new My_Weather_Widget();

/////////////////////////////////////// MANAGE DATABASE TABLE /////////////////////////////////////////////////
function MWW_create_db()
{ // Read more: https://codex.wordpress.org/Creating_Tables_with_Plugins

    global $wpdb, $MWW_db_version;

    // TODO: Run this script on instalation
    $table_name = $wpdb->prefix . 'mww_weather_records';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
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
    write_log($log);   //quirky

    add_option('MWW_db_version', $MWW_db_version);
}

// Migration

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

    // Alter table
    $sql = '';

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Might not be correct
    update_option('MWW_db_version', $MWW_db_version);
}



function MWW_uninstall()
{ // Do things when uninstalled
    // Drop table
    global $wpdb;
    $table_name = $wpdb->prefix . 'mww_weather_records';
    $sql = "DROP TABLE $table_name;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Remove any options
    delete_option('MWW_db_version');
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function write_log($data)
{
    if (true === WP_DEBUG) {
        if (is_array($data) || is_object($data)) {
            error_log(print_r($data, true));
        } else {
            error_log($data);
        }
    }
}


function add_weather_api_route()
{ // full address is https//:my-site.com/wp-json/myplugin/v1/weather
    register_rest_route(
        'myplugin/v1',
        '/weather',
        array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => 'MWW_get_weather_data'
        )
    );
}



function MWW_get_weather_data(WP_REST_Request $request)
{ // TODO: Chop into smaller functions
    // TODO: What if city was changed and data is not old enougth for an update
    global $wpdb;
    $table_name = $wpdb->prefix . 'mww_weather_records';
    $last_entry = $wpdb->get_results("SELECT * from $table_name order by date_time desc limit 1;", OBJECT)[0];
    $city = $request->get_query_params()['city'];

    write_log("In function " . __FUNCTION__);
    // write_log($request->get_query_params());

    $url = 'http://api.weatherapi.com/v1/current.json?key=' . WEATHER_API_KEY . '&q=' . $city . '&aqi=no';

    $response = wp_remote_get($url, array(
        'format' => 'JSON'
    ));

    write_log(wp_remote_retrieve_response_code($response));
    $json = json_decode($response['body'], true);

    // There are some problems with API key, notice admin
    if (wp_remote_retrieve_response_code($response) >= 400) {
        write_log('Notify admin, there are problems');
        $error_code = $json['error']['code'];
        $error_msg = $json['error']['message'];
        AdminNotice::displayError("An error occurred in '". plugin_basename(__FILE__) . "': $error_msg Error code: $error_code");

        return new WP_REST_Response(array(), wp_remote_retrieve_response_code($response));
    }

    // We got an error
    if (is_wp_error($response)) {

        //  No entries in db
        if (is_null($last_entry)) {
            // Request timeout, curl error 28

            // TODO: Return correct response code
            write_log('Error and no db data');
            return new WP_Error(array(), 5004);
        }

        write_log('No response from weather api, lucky me I have some data in db');
        $db_data = json_decode(json_encode($last_entry), true);
        return new WP_REST_Response($db_data, 2001);
    }

    if (
        is_null($last_entry) ||
        time() - $last_entry->date_time >= 1800
        /** 30 minutes */
    ) { // Old or inexisting data

        // TODO: Store last request time in class or somewhere else


        write_log('Update data in db');

        $data = array(
            'date_time'      => time(),
            'city'           => $json['location']['name'],
            'weather_status' => $json['current']['condition']['text'],
            'temp_c'         => $json['current']['temp_c'],
            'wind_speed'     => $json['current']['wind_kph'],
            'wind_dir'       => $json['current']['wind_dir'],
            'atm_press'      => round($json['current']['pressure_mb'] * 0.750062, 1),
            'humidity'       => $json['current']['humidity'],
        );

        $format = array('%d', '%s', '%s', '%f', '%f', '%s', '%f', '%d');

        $wpdb->insert($table_name, $data, $format);
    }

    $data = json_decode(json_encode($last_entry), true);
    write_log('Render data');

    return new WP_REST_Response($data, 200);
}

function MWW_render_response(array|null $json)
{
    write_log('In function ' . __FUNCTION__);

    if (empty($json) || is_null($json)) {
        write_log('Weather service unavailable');
        return "<p>Weather service unavailable</p>";
    }

    $city = $json['city'];
    $weather_status = $json['weather_status'];
    $temp_c = $json['temp_c'];
    $wind_speed_kph = $json['wind_speed'];
    $wind_dir = $json['wind_dir'];
    $pressure_mmhg = $json['atm_press'];
    $humidity = $json['humidity'];

    write_log('End of ' . __FUNCTION__);
    return "<p>
                Weather in  $city: $weather_status<br>
            <ul>
                <li>
                    Temperature:  $temp_c  &degC <br>
                </li>
                <li>
                    Wind:  $wind_speed_kph  km/h ( $wind_dir )
                </li>
                <li>
                    Atmospheric pressure:  $pressure_mmhg  mmHg
                </li>
                <li>
                    Humidity:  $humidity %
                </li>
            </ul> </p>";
}


class AdminNotice
{ // Thanks to this answear https://wordpress.stackexchange.com/a/222027
    const NOTICE_FIELD = 'my_admin_notice_message';

    public function displayAdminNotice()
    {
        $option      = get_option(self::NOTICE_FIELD);
        $message     = isset($option['message']) ? $option['message'] : false;
        $noticeLevel = !empty($option['notice-level']) ? $option['notice-level'] : 'notice-error';

        if ($message) {
            echo "<div class='notice {$noticeLevel} is-dismissible'><p>{$message}</p></div>";
            delete_option(self::NOTICE_FIELD);
        }
    }

    public static function displayError($message)
    {
        self::updateOption($message, 'notice-error');
    }

    public static function displayWarning($message)
    {
        self::updateOption($message, 'notice-warning');
    }

    public static function displayInfo($message)
    {
        self::updateOption($message, 'notice-info');
    }

    public static function displaySuccess($message)
    {
        self::updateOption($message, 'notice-success');
    }

    protected static function updateOption($message, $noticeLevel)
    {
        update_option(self::NOTICE_FIELD, [
            'message' => $message,
            'notice-level' => $noticeLevel
        ]);
    }
}
