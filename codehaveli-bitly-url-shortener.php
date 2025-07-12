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
 * Requires PHP: 7.4
 * Requires WP: 5.6
 * Tested WP: 6.3
 */

use Codehaveli\Wbitly\{
	Assets,
	Hooks,
	Manager,
	OptionManager,
	PostColumn,
	Settings,
	ThirdParty,
	WpRest
};

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Check minimum requirements before loading the plugin
add_action( 'plugins_loaded', 'wbitly_check_requirements', 1 );
function wbitly_check_requirements() {
	$required_php = '7.4';
	$required_wp  = '5.6';

	$errors = array();

	if ( version_compare( PHP_VERSION, $required_php, '<' ) ) {
		$errors[] = sprintf(
			__( 'Bitly URL Shortener requires PHP version %1$s or higher. You are running version %2$s.', 'wbitly' ),
			$required_php,
			PHP_VERSION
		);
	}

	global $wp_version;
	if ( version_compare( $wp_version, $required_wp, '<' ) ) {
		$errors[] = sprintf(
			__( 'Bitly URL Shortener requires WordPress version %1$s or higher. You are running version %2$s.', 'wbitly' ),
			$required_wp,
			$wp_version
		);
	}

	if ( ! file_exists( __DIR__ . '/lib/autoload.php' ) ) {
		$errors[] = __( 'Bitly URL Shortener plugin requires the Composer autoloader. Please run `composer install` in the plugin directory.', 'wbitly' );
	}

	if ( ! empty( $errors ) ) {
		if ( is_admin() ) {
			add_action(
				'admin_notices',
				function () use ( $errors ) {
					foreach ( $errors as $error ) {
						echo '<div class="notice notice-error"><p>' . esc_html( $error ) . '</p></div>';
					}
				}
			);
		}
		return;
	}

	// Load the plugin if all requirements are met
	require_once __DIR__ . '/lib/autoload.php';
	wbitly_init_plugin();
}

// Initialize plugin components
function wbitly_init_plugin() {
	// Plugin constants
	$plugin_data    = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
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
	register_activation_hook(
		__FILE__,
		function () {
			OptionManager::migrate_option();
		}
	);

	// Initialize plugin components
	add_action(
		'plugins_loaded',
		function () {
			Hooks::init();
			PostColumn::init();
			WpRest::init();
			Settings::init();
			Assets::init();
			ThirdParty::init();
		}
	);

	// Register WP-CLI command
	if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( '\Codehaveli\Wbitly\WbitlyCLI' ) ) {
		WP_CLI::add_command( 'wbitly', '\Codehaveli\Wbitly\WbitlyCLI' );
	}
}

/**
 * Get the Bitly short URL for a post.
 *
 * @param int|null $post_id Post ID. Defaults to current post if null.
 * @return string|false Short URL on success, false on failure.
 * @throws InvalidArgumentException If post ID is invalid.
 */
function get_wbitly_short_url( $post_id = null ) {
	try {
		if ( null === $post_id ) {
			global $post;
			$post_id = isset( $post->ID ) ? (int) $post->ID : 0;
		} else {
			$post_id = (int) $post_id;
		}

		if ( $post_id <= 0 ) {
			throw new InvalidArgumentException( __( 'Invalid post ID', 'wbitly' ) );
		}

		$url = Manager::get_short_url( $post_id );
		return $url && filter_var( $url, FILTER_VALIDATE_URL ) ? esc_url_raw( $url ) : false;
	} catch ( Exception $e ) {
		Codehaveli\Wbitly\Logger::error( sprintf( 'Bitly URL Shortener Error: %s', $e->getMessage() ) );
		return false;
	}
}
