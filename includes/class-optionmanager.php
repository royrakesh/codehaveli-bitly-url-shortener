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

	private static $option_key = 'wbitly_url_option_name';

	/**
	 * Get a specific option value.
	 *
	 * @param string $key     Option key.
	 * @param mixed  $default Default value if not set.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$options = get_option( self::$option_key, array() );
		$key = sanitize_key( $key );
		return isset( $options[ $key ] ) ? $options[ $key ] : $default;
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
		$key = sanitize_key( $key );
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
