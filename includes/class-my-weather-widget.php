<?php

class My_Weather_Widget extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'my_weather_widget',
            'My_Weather_Widget',
            array('description' => __('A widget to display weather in a city', 'text-domain'))
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
        $endpoint_url = get_site_url() . "/wp-json/myplugin/v1/weather";

        $response = wp_remote_get(
            $endpoint_url,

            array('body' => array('city' => $city))
        );
        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        write_log('In function ' . __FUNCTION__ . ' response code: ' . $code);
        $ret_val = json_decode($body, true);
        write_log('End of ' . __FUNCTION__);
        return MWW_render_response($ret_val);
    }

    public function widget($args, $instance)
    {
        extract($args);
        echo $before_widget;

        $city = empty($instance['city']) ? '' : $instance['city'];

        echo $this->get_weather($city);
        write_log("In function " . __FUNCTION__ . ' done rendering');
        echo $after_widget;
    }

    public function form($instance)
    {
        $city = isset($instance['city']) ? $instance['city'] : __('City', 'text-domain');
        $field_id = $this->get_field_id('city');
        $field_name = $this->get_field_name('city');
        ?>
        <p>
            <label for="<?php echo $field_id ?>" id="<?php echo $field_name . 'label' ?>">City</label>
            <input id="<?php echo $field_id ?>" name="<?php echo $field_name ?>" type="text" class="widefat" placeholder="eg. London" value="<?php echo esc_attr($city); ?>">
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
