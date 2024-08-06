
# My Weather Widget

## Description

My Weather Widget is a simple WordPress plugin that allows you to display the current weather for a specified city. It uses a weather API to fetch the latest weather data and displays it in a widget on your site.

## Installation

1. **Download** the plugin.
2. **Extract** the plugin files.
3. **Upload** the `my-weather-widget` directory to the `/wp-content/plugins/` directory.
4. **Navigate** to the plugin directory: `cd /path/to/your/wp-content/plugins/my-weather-widget`.
5. **Install dependencies** by running: `composer install`
6. **Create a `.env` file** in the root of the plugin directory:\
    Add your weather API key in the `.env` file as follows:
   ```
   WEATHER_API_KEY=your_api_key_here
   ```
7. **Add the widget** to your sidebar:
    - Go to `Appearance > Widgets`.
    - Drag and drop the "My Weather Widget" to your desired widget area.
8. **Configure the widget**:
    - Enter the name of the city you want to display the weather for.
    - Save your changes.
