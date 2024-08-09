<?php

/**
 * Plugin Name: My Weather Widget
 * Description: Displays current weather for a specified city using data from WeatherAPI.com. Easily add this widget to your sidebar or other widget areas to show live weather updates. The widget can be customized to display various weather details such as temperature, wind speed, humidity, and atmospheric pressure. Updates weather data every 30 minutes to ensure it provides the latest information.
 * Version: 0.0.1
 * Author: Parsyfall
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: my-weather-widget
 */

use MyWeatherWidget\DataBaseManipulator;
use MyWeatherWidget\WeatherApiHandler;
use MyWeatherWidget\AdminNotice;
use Dotenv\Dotenv;

if (!defined('ABSPATH')) {
    exit;
}


// Load dependencies
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

// Load API key
$dotenv = Dotenv::createImmutable(plugin_dir_path(__FILE__));
$dotenv->load();
define('WEATHER_API_KEY', $_ENV['WEATHER_API_KEY']);

$dbManipulator = DataBaseManipulator::getInstance();
$apiHandler = new WeatherApiHandler(WEATHER_API_KEY);

// Register hooks
register_activation_hook(__FILE__, [$dbManipulator, 'createDbTable']);
register_uninstall_hook(__FILE__, ['MyWeatherWidget\DataBaseManipulator::dropTable']);
add_action('plugins_loaded', [$dbManipulator, 'updateTableStructure']);
add_action('rest_api_init', [$apiHandler, 'registerRoute']);
add_action('widgets_init', function () {
    register_widget('MyWeatherWidget\\My_Weather_Widget');
});
add_action('admin_notices', [new AdminNotice(), 'displayAdminNotice']);


// Initialize widget
$widget = new MyWeatherWidget\My_Weather_Widget();
