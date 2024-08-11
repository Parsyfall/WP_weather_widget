<?php

namespace MyWeatherWidget;

use MyWeatherWidget\TwigRenderer;

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

function MWW_render_response(?array $data): string
{
    write_log('In function ' . __FUNCTION__);

    if (is_null($data)) {
        write_log('Weather service unavailable');
        return "<p>Weather service unavailable</p>";
    }

    $sanitized_values = sanitize($data);
    write_log('End of ' . __FUNCTION__);

    $renderer = new TwigRenderer();
    return $renderer->render('widgetDisplay.twig', $sanitized_values);
}

function sanitize(array $arr): array
{
    foreach ($arr as $key => $value) {
        sanitize_text_field($arr[$key]);
    }
    return $arr;
}
