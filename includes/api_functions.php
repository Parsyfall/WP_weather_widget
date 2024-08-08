<?php

namespace MyWeatherWidget;
use function MyWeatherWidget\write_log;

function MWW_add_weather_api_route()
{
    register_rest_route(
        'myplugin/v1',
        '/weather',
        array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => 'MyWeatherWidget\MWW_get_weather_data'
        )
    );
}

function MWW_get_weather_data(\WP_REST_Request $request)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'mww_weather_records';
    $last_entry = $wpdb->get_row("SELECT * from $table_name order by date_time desc limit 1;", OBJECT);

    $city = sanitize_text_field($request->get_query_params()['MWW_city']);

    write_log("In function " . __FUNCTION__);

    $url = 'http://api.weatherapi.com/v1/current.json?key=' . WEATHER_API_KEY . '&q=' . $city . '&aqi=no';

    $response = wp_remote_get($url, array('format' => 'JSON'));

    write_log(wp_remote_retrieve_response_code($response));
    $json = json_decode($response['body'], true);

    if (wp_remote_retrieve_response_code($response) >= 400) {
        write_log('Notify admin, there are problems');
        $error_code = $json['error']['code'];
        $error_msg = $json['error']['message'];
        AdminNotice::displayError("An error occurred in '" . plugin_basename(__FILE__) . "': $error_msg Error code: $error_code");

        return new \WP_REST_Response(array(), wp_remote_retrieve_response_code($response));
    }

    if (is_wp_error($response)) {
        if (is_null($last_entry)) {
            write_log('Error and no db data');
            return new \WP_Error(array(), 5004);
        }

        write_log('No response from weather api, lucky me I have some data in db');
        $db_data = json_decode(json_encode($last_entry), true);
        return new \WP_REST_Response($db_data, 2001);
    }

    if (is_null($last_entry) || time() - $last_entry->date_time >= 1800) {
        write_log('Update data in db');

        $data = array(
            'date_time'      => time(),
            'city'           => sanitize_text_field($json['location']['name']),
            'weather_status' => sanitize_text_field($json['current']['condition']['text']),
            'temp_c'         => sanitize_text_field($json['current']['temp_c']),
            'wind_speed'     => sanitize_text_field($json['current']['wind_kph']),
            'wind_dir'       => sanitize_text_field($json['current']['wind_dir']),
            'atm_press'      => round($json['current']['pressure_mb'] * 0.750062, 1),
            'humidity'       => sanitize_text_field($json['current']['humidity']),
        );

        $format = array('%d', '%s', '%s', '%f', '%f', '%s', '%f', '%d');

        $wpdb->insert($table_name, $data, $format);
    }

    $data = json_decode(json_encode($last_entry), true);
    write_log('Render data');

    return new \WP_REST_Response($data, 200);
}
