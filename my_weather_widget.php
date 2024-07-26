<?php
/**
 * Plugin Name: My Weather Widget
 * Description: Displays weather in a certain city
 * Version: 0.0.1
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-my-weather-widget.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-admin-notice.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/db-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/api-functions.php';

// Load API key
$dotenv = Dotenv\Dotenv::createImmutable(plugin_dir_path(__FILE__));
$dotenv->load();
define('WEATHER_API_KEY', $_ENV['WEATHER_API_KEY']);

// Initialize widget
$widget = new My_Weather_Widget();
