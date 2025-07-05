<?php

namespace Codehaveli\Wbitly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OptionManager {

	private static $option_key = 'wbitly_url_option_name';

	public static function get( $key, $default = null ) {
		$options = get_option( self::$option_key, array() );
		$key = sanitize_key( $key );
		return isset( $options[ $key ] ) ? $options[ $key ] : $default;
	}

	public static function set( $key, $value ) {
		$options         = get_option( self::$option_key, array() );
		$key = sanitize_key( $key );
		$options[ $key ] = is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : sanitize_text_field( $value );
		update_option( self::$option_key, $options );
	}

	public static function all() {
		return get_option( self::$option_key, array() );
	}
}
