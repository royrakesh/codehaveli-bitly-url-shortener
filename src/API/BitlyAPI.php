<?php
/**
 * Bitly API handler for Codehaveli Bitly URL Shortener plugin.
 *
 * @package Codehaveli\Wbitly
 */

namespace Codehaveli\Wbitly\API;

use Codehaveli\Wbitly\Admin\OptionManager;
use Codehaveli\Wbitly\Util\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles Bitly API requests and responses.
 */
class BitlyAPI {

	const API_URL = 'https://api-ssl.bitly.com/v4';
	const TIMEOUT = 10; // Increased from 5 to 10 seconds for better reliability.

	/**
	 * Get the group GUID using the access token.
	 *
	 * @return string|false Group GUID or false on failure.
	 */
	public function get_group_guid() {
		$access_token = sanitize_text_field( OptionManager::get( 'access_token' ) );

		if ( empty( $access_token ) ) {
			$this->log_error( 'Access token is missing.' );
			return false;
		}

		// Check cache first (cache for 1 hour).
		$cache_key = 'wbitly_group_guid_' . md5( $access_token );
		$cached_guid = get_transient( $cache_key );
		
		if ( false !== $cached_guid ) {
			return $cached_guid;
		}

		$response = $this->send_request( '/groups', 'GET', $access_token );

		if ( ! $response || ! isset( $response['groups'][0]['guid'] ) ) {
			$this->log_error( 'Invalid group GUID response: ' . wp_json_encode( $response ) );
			return false;
		}

		$guid = sanitize_text_field( $response['groups'][0]['guid'] );
		
		// Cache the GUID for 1 hour.
		set_transient( $cache_key, $guid, HOUR_IN_SECONDS );

		return $guid;
	}

	/**
	 * Shorten a given long URL using Bitly API.
	 *
	 * @param string $long_url The long URL to shorten.
	 * @return string|false Shortened URL or false on failure.
	 */
	public function shorten_url( string $long_url ) {
		$long_url = esc_url_raw( apply_filters( 'wbitly_url_before_process', $long_url ) );

		if ( empty( $long_url ) || ! filter_var( $long_url, FILTER_VALIDATE_URL ) ) {
			$this->log_error( 'Empty or invalid long URL.' );
			return false;
		}

		$access_token = sanitize_text_field( OptionManager::get( 'access_token' ) );
		$group_guid   = sanitize_text_field( OptionManager::get( 'group_guid' ) );
		$domain       = sanitize_text_field( OptionManager::get( 'bitly_domain', '' ) );

		// Validate required credentials.
		if ( empty( $access_token ) || empty( $group_guid ) ) {
			$this->log_error( 'Missing access token or group GUID.' );
			return false;
		}

		// Validate GUID format.
		if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $group_guid ) ) {
			$this->log_error( 'Invalid group GUID format.' );
			return false;
		}

		$payload = array(
			'group_guid' => $group_guid,
			'long_url'   => $long_url,
		);

		// Validate and add domain if provided.
		if ( ! empty( $domain ) ) {
			// Remove protocol if present.
			$domain = preg_replace( '#^https?://#', '', $domain );
			// Remove trailing slash.
			$domain = rtrim( $domain, '/' );
			// Validate domain format.
			if ( preg_match( '/^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/', $domain ) ) {
				$payload['domain'] = $domain;
			} else {
				$this->log_error( 'Invalid domain format: ' . $domain );
				// Continue without domain if invalid.
			}
		}

		$response = $this->send_request( '/shorten', 'POST', $access_token, $payload );

		if ( ! $response || empty( $response['link'] ) ) {
			$this->log_error( 'Shorten URL failed: ' . wp_json_encode( $response ) );
			return false;
		}

		return esc_url_raw( $response['link'] );
	}

	/**
	 * Perform a Bitly API request.
	 *
	 * @param string     $endpoint     Relative API endpoint (e.g. "/shorten").
	 * @param string     $method       HTTP method ("GET" or "POST").
	 * @param string     $access_token OAuth token.
	 * @param array|null $payload      POST body, optional.
	 * @return array|false             Decoded response array or false on failure.
	 */
	private function send_request( string $endpoint, string $method, string $access_token, array $payload = [] ) {
		// Allow developers to filter the timeout.
		$timeout = apply_filters( 'wbitly_api_timeout', self::TIMEOUT );
		
		$args = array(
			'method'  => strtoupper( $method ),
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
			),
			'timeout' => absint( $timeout ),
		);

		if ( $payload && 'POST' === $method ) { // Yoda condition.
			$args['body'] = wp_json_encode( $payload );
		}

		$url      = rtrim( self::API_URL, '/' ) . $endpoint;
		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			$this->log_error( 'API request error: ' . $response->get_error_message() );
			return false;
		}

		// Check HTTP response code.
		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code < 200 || $response_code >= 300 ) {
			$response_body = wp_remote_retrieve_body( $response );
			$this->log_error( sprintf( 'API request failed with status %d: %s', $response_code, $response_body ) );
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return is_array( $body ) ? $body : false;
	}

	/**
	 * Log errors to file if WP_DEBUG is on.
	 *
	 * @param string $message Error message to log.
	 * @return void
	 */
	private function log_error( string $message ) {
		Logger::error( sprintf( $message ) );
	}
}
