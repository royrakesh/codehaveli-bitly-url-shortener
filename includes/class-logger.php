<?php
/**
 * Logger for Codehaveli Bitly URL Shortener plugin.
 *
 * @package Codehaveli\Wbitly
 */

namespace Codehaveli\Wbitly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Basic logger utility.
 */
class Logger {

	/**
	 * Log an error message if WP_DEBUG is enabled.
	 *
	 * @param string $message Error message to log.
	 * @return void
	 */
	public static function error( $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Wbitly Error] ' . $message );
		}
	}

	/**
	 * Log a notice-level message if WP_DEBUG is enabled.
	 *
	 * @param string $message Notice message to log.
	 * @return void
	 */
	public static function notice( $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[Wbitly Notice] ' . $message );
		}
	}

	/**
	 * Log debug-level info if WP_DEBUG and WP_DEBUG_LOG are enabled.
	 *
	 * @param string $message Debug message to log.
	 * @return void
	 */
	public static function debug( $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( '[Wbitly Debug] ' . $message );
		}
	}
}
