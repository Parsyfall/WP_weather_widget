<?php

namespace MyWeatherWidget;

function write_log($data)
{
    if (true !== WP_DEBUG) {
        return;
    }

    if (is_array($data) || is_object($data)) {
        error_log(print_r($data, true));
    } else {
        error_log($data);
    }
}

function MWW_render_response(array|null $json)
{
    write_log('In function ' . __FUNCTION__);

    if (empty($json) || is_null($json)) {
        write_log('Weather service unavailable');
        return "<p>Weather service unavailable</p>";
    }

    $values = [
        'city'           => sanitize_text_field($json['city']),
        'weather_status' => sanitize_text_field($json['weather_status']),
        'temp_c'         => sanitize_text_field($json['temp_c']),
        'wind_speed_kph' => sanitize_text_field($json['wind_speed']),
        'wind_dir'       => sanitize_text_field($json['wind_dir']),
        'pressure_mmhg'  => sanitize_text_field($json['atm_press']),
        'humidity'       => sanitize_text_field($json['humidity']),
    ];
    write_log('End of ' . __FUNCTION__);
    
    $loader = new \Twig\Loader\FilesystemLoader(plugin_dir_path(__FILE__) . '../view');
    $twig = new \Twig\Environment($loader);
    return $twig->render('widget.html', $values);
}
