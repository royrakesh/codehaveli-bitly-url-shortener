<?php
/**
 * Option manager for Codehaveli Bitly URL Shortener plugin.
 *
 * @package Codehaveli\Wbitly
 */

namespace Codehaveli\Wbitly\Admin;

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
	 * @return bool True on success, false on failure.
	 */
	public static function set( $key, $value ) {
		// Validate key.
		if ( ! is_string( $key ) || empty( $key ) ) {
			return false;
		}

		$key = sanitize_key( $key );

		// Define allowed option keys for validation.
		$allowed_keys = array(
			'access_token',
			'group_guid',
			'bitly_domain',
			'wbitly_social_share',
			'wbitly_custom_post',
		);

		// Validate key is in allowed list.
		if ( ! in_array( $key, $allowed_keys, true ) ) {
			return false;
		}

		$options = get_option( self::$option_key, array() );

		// Validate and sanitize value based on key type.
		switch ( $key ) {
			case 'access_token':
				// Access token should be a non-empty string.
				if ( ! is_string( $value ) || empty( trim( $value ) ) ) {
					return false;
				}
				// Bitly access tokens are typically alphanumeric with hyphens/underscores, max 255 chars.
				$value = sanitize_text_field( $value );
				if ( strlen( $value ) > 255 ) {
					return false;
				}
				break;

			case 'group_guid':
				// GUID should be a non-empty string, typically alphanumeric with hyphens.
				if ( ! is_string( $value ) || empty( trim( $value ) ) ) {
					return false;
				}
				$value = sanitize_text_field( $value );
				// GUID format validation (alphanumeric, hyphens, underscores).
				if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $value ) ) {
					return false;
				}
				break;

			case 'bitly_domain':
				// Domain is optional, but if provided should be valid domain format.
				if ( ! empty( $value ) ) {
					$value = sanitize_text_field( $value );
					// Remove protocol if present.
					$value = preg_replace( '#^https?://#', '', $value );
					// Remove trailing slash.
					$value = rtrim( $value, '/' );
					// Validate domain format.
					if ( ! preg_match( '/^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/', $value ) ) {
						return false;
					}
				} else {
					$value = '';
				}
				break;

			case 'wbitly_social_share':
				// Should be 'enable' or empty string.
				$value = sanitize_text_field( $value );
				if ( ! in_array( $value, array( 'enable', '' ), true ) ) {
					$value = '';
				}
				break;

			case 'wbitly_custom_post':
				// Should be an array of valid post type names.
				if ( ! is_array( $value ) ) {
					$value = array();
				}
				// Validate each post type exists.
				$validated_post_types = array();
				$registered_types    = get_post_types( array( 'public' => true ), 'names' );
				foreach ( $value as $post_type ) {
					$post_type = sanitize_key( $post_type );
					if ( in_array( $post_type, $registered_types, true ) ) {
						$validated_post_types[] = $post_type;
					}
				}
				// Ensure at least one post type is selected (default to 'post').
				if ( empty( $validated_post_types ) ) {
					$validated_post_types = array( 'post' );
				}
				$value = array_unique( $validated_post_types );
				break;

			default:
				// For any other keys, use basic sanitization.
				$value = is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : sanitize_text_field( $value );
				break;
		}

		$options[ $key ] = $value;
		$result          = update_option( self::$option_key, $options );

		return $result;
	}

	/**
	 * Get all plugin options.
	 *
	 * @return array
	 */
	public static function all() {
		return get_option( self::$option_key, array() );
	}

	/**
	 * Get the access token.
	 *
	 * @return string
	 */
	public static function get_access_token() {
		return self::get( 'access_token' );
	}

	/**
	 * Get the GUID.
	 *
	 * @return string
	 */
	public static function get_guid() {
		return self::get( 'group_guid' );
	}
}
