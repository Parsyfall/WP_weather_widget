<?php

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
                <li>Temperature:  $temp_c  &degC <br></li>
                <li>Wind:  $wind_speed_kph  km/h ( $wind_dir )</li>
                <li>Atmospheric pressure:  $pressure_mmhg  mmHg</li>
                <li>Humidity:  $humidity %</li>
            </ul>
            </p>";
}
