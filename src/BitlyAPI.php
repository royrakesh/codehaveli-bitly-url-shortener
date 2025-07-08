<?php
/**
 * Bitly API handler for Codehaveli Bitly URL Shortener plugin.
 *
 * @package Codehaveli\Wbitly
 */

namespace Codehaveli\Wbitly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles Bitly API requests and responses.
 */
class BitlyAPI {

	const API_URL = 'https://api-ssl.bitly.com/v4';
	const TIMEOUT = 5;

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

		$response = $this->send_request( '/groups', 'GET', $access_token );

		if ( ! $response || ! isset( $response['groups'][0]['guid'] ) ) {
			$this->log_error( 'Invalid group GUID response: ' . wp_json_encode( $response ) );
			return false;
		}

		return sanitize_text_field( $response['groups'][0]['guid'] );
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
		$domain       = sanitize_text_field( OptionManager::get( 'bitly_domain', 'bit.ly' ) );

		if ( ! $access_token || ! $group_guid ) {
			$this->log_error( 'Missing access token or group GUID.' );
			return false;
		}

		$payload = array(
			'group_guid' => $group_guid,
			'long_url'   => $long_url,
		);

		if ( ! empty( $domain ) ) {
			$payload['domain'] = $domain;
		}

		$response = $this->send_request( '/shorten', 'POST', $access_token, $payload );

		if ( ! $response || empty( $response['link'] ) ) {
			$this->log_error( 'Shorten URL failed: ' . wp_json_encode( $response ) );
			return false;
		}

		return esc_url_raw( $response['link'] );
	}


	public function get_rate_limit() {
		$access_token = sanitize_text_field( OptionManager::get_access_token() );
		if ( empty( $access_token ) ) {
			$this->log_error( 'Access token is missing.' );
			return false;
		}

		$endpoint = '/user/platform_limits';

		$response = $this->send_request( $endpoint, 'GET', $access_token );

		if ( ! $response || ! isset( $response['platform_limits'] ) ) {
			$this->log_error( 'Invalid rate limit response: ' . wp_json_encode( $response ) );
			return false;
		}
		$platform_limits = $response['platform_limits'];

		return $platform_limits;
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
	private function send_request( string $endpoint, string $method, string $access_token, array $payload = null ) {
		$args = array(
			'method'  => strtoupper( $method ),
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type'  => 'application/json',
			),
			'timeout' => self::TIMEOUT,
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
