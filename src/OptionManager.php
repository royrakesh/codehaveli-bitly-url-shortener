<?php
/**
 * Option manager for Codehaveli Bitly URL Shortener plugin.
 *
 * @package Codehaveli\Wbitly
 */

namespace Codehaveli\Wbitly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin option storage and retrieval.
 */
class OptionManager {

	/**
	 * Option key for storing plugin settings.
	 *
	 * @var string
	 */
	private static $option_key = 'ch_wbitly_url_option';

	/**
	 * Old option key for migration.
	 *
	 * @var string
	 */
	private static $old_option_key = 'wbitly_url_option_name';

	/**
	 * Run the migration process for updating the option name.
	 *
	 * @return void
	 */
	public static function migrate_option() {
		// Check if the old option exists
		$old_options = get_option( self::$old_option_key, false );

		if ( false !== $old_options ) {
			// Get the current options
			$current_options = get_option( self::$option_key, array() );

			// Merge old options with current options
			$new_options = array_merge( $current_options, $old_options );

			// Update the new option with merged data
			update_option( self::$option_key, $new_options );

			// Delete the old option
			delete_option( self::$old_option_key );
		}
	}

	/**
	 * Get a specific option value.
	 *
	 * @param string $key     Option key.
	 * @param mixed  $default_key Default value if not set.
	 * @return mixed
	 */
	public static function get( $key, $default_key = null ) {
		$options = get_option( self::$option_key, array() );
		$key     = sanitize_key( $key );
		return isset( $options[ $key ] ) ? $options[ $key ] : $default_key;
	}

	/**
	 * Set a specific option value.
	 *
	 * @param string $key   Option key.
	 * @param mixed  $value Option value.
	 * @return void
	 */
	public static function set( $key, $value ) {
		$options         = get_option( self::$option_key, array() );
		$key             = sanitize_key( $key );
		$options[ $key ] = is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : sanitize_text_field( $value );
		update_option( self::$option_key, $options );
	}

	/**
	 * Get all plugin options.
	 *
	 * @return array
	 */
	public static function all() {
		return get_option( self::$option_key, array() );
	}
}
