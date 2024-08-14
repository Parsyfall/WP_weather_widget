<?php

namespace MyWeatherWidget;

class My_Weather_Widget extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'my_weather_widget',
            'My_Weather_Widget',
            array('description' => __('A widget to display weather in a city', 'text-domain'))
        );
    }

    private function get_weather(string $city)
    {
        $endpoint_url = get_site_url() . "/wp-json/MyWeatherWidget/v1/weather";

        $response = wp_remote_get(
            $endpoint_url,

            array('body' => array('MWW_city' => $city))
        );

        if (is_wp_error($response)) {
            return MWW_render_response(null);
        }

        $body = wp_remote_retrieve_body($response);
        $ret_val = json_decode($body, true);

        return MWW_render_response($ret_val);
    }

    public function widget($args, $instance)
    {
        extract($args);
        echo $before_widget;

        $city = isset($instance['MWW_city']) ? $instance['MWW_city'] : '';

        if (!empty($city)) {
            echo $this->get_weather($city);
        }

        echo $after_widget;
    }

    public function form($instance)
    {
        $data = [
            'city' => $instance['MWW_city'] ?? '',
            'field_id' => $this->get_field_id('MWW_city'),
            'field_name' => $this->get_field_name('MWW_city'),
        ];
        $renderer = new TwigRenderer();
        echo $renderer->render('widgetForm.twig', $data);
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['MWW_city'] = !empty($new_instance['MWW_city']) ? strip_tags($new_instance['MWW_city']) : '';
        return $instance;
    }
}
