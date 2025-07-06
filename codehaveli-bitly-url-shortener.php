<?php

/*
Plugin Name: Bitly URL Shortener
Plugin URI: https://github.com/codehaveli/
Description: Bitly URL Shortener uses the functionality of Bitly API to generate Bitly short links without leaving your WordPress site.
Version: 1.5.1
Author: Codehaveli
Author URI: https://www.codehaveli.com/
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wbitly
Domain Path: /languages
Requires PHP: 7.2
Requires WP: 5.0
Tested WP: 6.3
*/


use Codehaveli\Wbitly\Assets;
use Codehaveli\Wbitly\Hooks;
use Codehaveli\Wbitly\Manager;
use Codehaveli\Wbitly\Settings;
use Codehaveli\Wbitly\ThirdParty;
use Codehaveli\Wbitly\WpRest;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

require_once 'vendor/autoload.php';

$plugin_data = get_file_data(
	__FILE__,
	array(
		'Version' => 'Version',
	)
);
$plugin_version = time(); //$plugin_data['Version'];

if (! defined('WBITLY_PLUGIN_VERSION')) {
	define('WBITLY_PLUGIN_VERSION',  $plugin_version);
}
define('WBITLY_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WBITLY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WBITLY_API_URL', 'https://api-ssl.bitly.com');
define('WBITLY_BASENAME', plugin_basename(__FILE__));
define('WBITLY_SETTINGS_URL', admin_url('tools.php?page=wbitly'));


register_activation_hook(__FILE__, 'migrate_wbitly_options');

function migrate_wbitly_options()
{
	\Codehaveli\Wbitly\OptionManager::migrate_option();
}


/**
 * Initialize the plugin components.
 *
 * This function is called when the plugins_loaded action is triggered.
 */
add_action('plugins_loaded','wbitly_init_plugin_components');

function wbitly_init_plugin_components()
{
	Hooks::init();
	WpRest::init();
	Settings::init();
	Assets::init();
	ThirdParty::init();

}



/**
 * Get a template file from the plugin's templates directory.
 *
 * @param string $filename The name of the template file.
 * @param array  $args     Optional. Arguments to pass to the template.
 */
if (! function_exists('wbitly_get_template')) {
	function wbitly_get_template($filename, $args = array())
	{
		// Only allow .php files from the templates directory.
		$filename = basename($filename);
		if (substr($filename, -4) !== '.php') {
			error_log("Invalid template file extension: $filename");
			return;
		}
		$filepath = plugin_dir_path(__FILE__) . 'templates/' . $filename;

		if (file_exists($filepath)) {
			if (! empty($args) && is_array($args)) {
				// Prevent variable injection.
				$safe_args = array();
				foreach ($args as $key => $value) {
					if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
						$safe_args[$key] = $value;
					}
				}
				extract($safe_args, EXTR_SKIP); // safer extract.
			}

			include $filepath;
		} else {
			error_log("Template file not found: $filepath");
		}
	}
}



/**
 * Get the Bitly short URL for a specific post.
 *
 * @param int|null $post_id The post ID.
 * @return string|false The Bitly short URL or false if not found.
 */
function get_wbitly_short_url($post_id = null)
{
	// Validate and sanitize post ID.
	if (! $post_id) {
		global $post;
		$post_id = isset($post->ID) ? intval($post->ID) : 0;
	} else {
		$post_id = intval($post_id);
	}

	if (! $post_id || $post_id <= 0) {
		return false;
	}

	$wbitly_url =  Manager::get_short_url($post_id);

	// Validate URL before returning.
	if ($wbitly_url && filter_var($wbitly_url, FILTER_VALIDATE_URL)) {
		return esc_url_raw($wbitly_url);
	}

	return false;
}
