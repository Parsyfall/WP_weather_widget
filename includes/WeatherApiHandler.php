<?php

namespace MyWeatherWidget;

use WP_REST_Request;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

use function MyWeatherWidget\write_log;


// TODO: Move all functionality do underneath class 
class WeatherApiHandler
{
    const NAMESPACE = "MyWeatherWidget";
    const VERSION = "v1";
    private static $API_KEY;

    public function __construct(string $API_KEY)
    {
        self::setApiKey($API_KEY);
    }

    public static function setApiKey(string $API_KEY): void
    {
        self::$API_KEY = $API_KEY;
    }

    public function registerRoute()
    {
        register_rest_route(
            WeatherApiHandler::NAMESPACE . "/" . WeatherApiHandler::VERSION,
            "/weather",
            array(
                'methods'  => WP_REST_Server::READABLE,
                // TODO: Update callback
                'callback' => 'MyWeatherWidget\WeatherApiHandler::getWeather'
            )
        );
    }

    public static function getWeather(WP_REST_Request $request): WP_REST_Response| WP_Error
    { // TODO: Refactor this mess
        $db = DataBaseManipulator::getInstance();
        write_log("In function " . __FUNCTION__);

        if (!$db->isStale()) {
            $last_entry = $db->getLatesEntry();

            $data = is_null($last_entry)
                ? json_decode(json_encode($last_entry), true)
                : array();

            return new WP_REST_Response($data, 200);
        }

        // Data is stale

        $city = $request->get_query_params()['MWW_city'];
        $url = sanitize_url("http://api.weatherapi.com/v1/current.json?key=" . self::$API_KEY . "&q=$city&aqi=no");
        $response = wp_remote_get($url, ['format' => 'JSON']);
        $response_code = wp_remote_retrieve_response_code($response);

        if ($response_code >= 200 && $response_code < 300) {  // All good
            $json = json_decode($response['body'], true);

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

            $db->insert($data);
            return new WP_REST_Response($data);
        }

        // Network error
        if (is_wp_error($response)) {
            AdminNotice::displayError("There is a network problem : " . $response->get_error_message());
            return new WP_Error($response->get_error_code(), $response->get_error_message());
        }

        // Api error
        $body = json_decode($response['body'], true);
        $msg = "There is a Weather service problem: {$body['error']['message']}" . " Code: {$body['error']['code']}";
        write_log($msg);
        AdminNotice::displayError($msg);

        $last_entry = $db->getLatesEntry();

        if (is_null($last_entry)) {
            write_log('Error and no data in db');
            return new WP_REST_Response(null, 204);
        }

        write_log('No response from weather api, lucky me I have some data in db');
        $db_data = json_decode(json_encode($last_entry), true);
        return new WP_REST_Response($db_data, 201);
    }
}

enum ReturnType: string
{
    case OBJECT = "OBJECT";
    case ARRAY_A = "ARRAY_A";
    case ARRAY_N = "ARRAY_N";
}
