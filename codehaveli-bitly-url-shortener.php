<?php
/**
 * Plugin Name: Bitly URL Shortener
 * Plugin URI: https://github.com/codehaveli/
 * Description: Bitly URL Shortener uses the Bitly API to generate short links without leaving your WordPress site.
 * Version: 1.5.0
 * Author: Codehaveli
 * Author URI: https://www.codehaveli.com/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wbitly
 * Domain Path: /languages
 * Requires PHP: 7.2
 * Requires WP: 5.0
 * Tested WP: 6.3
 */

use Codehaveli\Wbitly\{
	Assets,
	Hooks,
	Manager,
	PostColumn,
	Settings,
	ThirdParty,
	WpRest
};

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

// Plugin constants
$plugin_data    = get_file_data( __FILE__, [ 'Version' => 'Version' ] );
$plugin_version = ( defined( 'WP_ENVIRONMENT_TYPE' ) && WP_ENVIRONMENT_TYPE !== 'production' )
	? time()
	: $plugin_data['Version'];

define( 'WBITLY_PLUGIN_VERSION', $plugin_version );
define( 'WBITLY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WBITLY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WBITLY_API_URL', 'https://api-ssl.bitly.com' );
define( 'WBITLY_BASENAME', plugin_basename( __FILE__ ) );
define( 'WBITLY_SETTINGS_URL', admin_url( 'tools.php?page=wbitly' ) );

// Activation hook for option migration
register_activation_hook( __FILE__, function () {
	\Codehaveli\Wbitly\OptionManager::migrate_option();
});

// Initialize plugin components
add_action( 'plugins_loaded', function () {
	Hooks::init();
	PostColumn::init();
	WpRest::init();
	Settings::init();
	Assets::init();
	ThirdParty::init();
});

// Register WP-CLI command
if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( '\Codehaveli\Wbitly\WbitlyCLI' ) ) {
	WP_CLI::add_command( 'wbitly', '\Codehaveli\Wbitly\WbitlyCLI' );
}





/**
 * Get the Bitly short URL for a post.
 *
 * @param int|null $post_id Post ID.
 * @return string|false Short URL or false on failure.
 */
function get_wbitly_short_url( $post_id = null ) {
	if ( ! $post_id ) {
		global $post;
		$post_id = isset( $post->ID ) ? intval( $post->ID ) : 0;
	} else {
		$post_id = intval( $post_id );
	}

	if ( $post_id <= 0 ) {
		return false;
	}

	$url = Manager::get_short_url( $post_id );
	return $url && filter_var( $url, FILTER_VALIDATE_URL ) ? esc_url_raw( $url ) : false;
}