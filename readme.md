
# My Weather Widget

**Plugin Name:** My Weather Widget  
**Description:** Displays weather in a certain city.  
**Version:** 0.0.1  

## Description

My Weather Widget is a simple WordPress plugin that allows you to display the current weather for a specified city. It uses a weather API to fetch the latest weather data and displays it in a widget on your site.

## Installation

1. **Download** the plugin.
2. **Extract** the plugin files.
3. **Upload** the `my-weather-widget` directory to the `/wp-content/plugins/` directory.
4. **Navigate** to the plugin directory: `cd /path/to/your/wp-content/plugins/my-weather-widget`.
5. **Install dependencies** by running: ``composer install ``
6. **Add the widget** to your sidebar:
    - Go to `Appearance > Widgets`.
    - Drag and drop the "My Weather Widget" to your desired widget area.
7. **Configure the widget**:
    - Enter the name of the city you want to display the weather for.
    - Save your changes.

## Folder Structure
 ```
/my_weather_widget/
├── composer.json
├── composer.lock
├── includes
│   ├── api-functions.php
│   ├── class-admin-notice.php
│   ├── class-my-weather-widget.php
│   ├── db-functions.php
│   └── functions.php
├── my_weather_widget.php
 ```
 
## Files

### `my-weather-widget.php`

This is the main plugin file. It includes all the other files and initializes the plugin.

### `includes/class-my-weather-widget.php`

This file contains the `My_Weather_Widget` class, which defines the widget's functionality, including how it displays the weather data.

### `includes/class-admin-notice.php`

This file contains the `AdminNotice` class, which is responsible for displaying admin notices.

### `includes/functions.php`

This file contains utility functions used across the plugin.

### `includes/db-functions.php`

This file contains functions for database operations, such as creating, updating, and deleting the plugin's database table.

### `includes/api-functions.php`

This file contains functions related to the REST API, including the function that fetches weather data from the weather API.

## Hooks

- **`widgets_init`**: Registers the widget with WordPress.
- **`register_activation_hook`**: Runs when the plugin is activated. It creates the necessary database table.
- **`register_uninstall_hook`**: Runs when the plugin is uninstalled. It cleans up the database.
- **`plugins_loaded`**: Checks for database updates when the plugin is loaded.
- **`rest_api_init`**: Registers the REST API route for fetching weather data.
- **`admin_notices`**: Displays admin notices.

## Frequently Asked Questions

### How do I get a weather API key?

You can obtain an API key by signing up at a weather service provider such as WeatherAPI.com. Once you have signed up, you will be able to generate an API key that you can use in this plugin.

### The widget is not displaying any weather data. What should I do?

- Ensure you have entered a valid API key in the `.env` file.
- Make sure your server is able to connect to the weather API endpoint.
- Check your WordPress debug logs for any errors.

### How often is the weather data updated?

The plugin fetches new weather data every 30 minutes. If the data in the database is older than 30 minutes, the plugin will fetch fresh data from the API.

## Changelog

### 0.0.1

- Initial release.

## Credits

This plugin was developed using the [WeatherAPI.com](https://www.weatherapi.com/) service for fetching weather data.

## License

This plugin is licensed under the [GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html).


