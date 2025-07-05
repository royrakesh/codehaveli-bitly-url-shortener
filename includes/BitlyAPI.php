<?php

namespace Codehaveli\Wbitly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BitlyAPI {

	const API_URL  = 'https://api-ssl.bitly.com/v4';
	const TIMEOUT  = 5;
	const LOG_FILE = 'error.log';

	/**
	 * Get the group GUID using the access token.
	 *
	 * @param string $accessToken
	 * @return string|false
	 */
	public function getGroupGuid() {
		$accessToken = sanitize_text_field( OptionManager::get( 'access_token' ) );

		if ( empty( $accessToken ) ) {
			$this->logError( 'Access token is missing.' );
			return false;
		}

		$response = $this->sendRequest( '/groups', 'GET', $accessToken );

		if ( ! $response || ! isset( $response['groups'][0]['guid'] ) ) {
			$this->logError( 'Invalid group GUID response: ' . print_r( $response, true ) );
			return false;
		}

		return sanitize_text_field( $response['groups'][0]['guid'] );
	}

	/**
	 * Shorten a given long URL using Bitly API.
	 *
	 * @param string $longUrl
	 * @return string|false
	 */
	public function shortenURL( string $longUrl ) {
		$longUrl = esc_url_raw( apply_filters( 'wbitly_url_before_process', $longUrl ) );
		if ( empty( $longUrl ) || ! filter_var( $longUrl, FILTER_VALIDATE_URL ) ) {
			$this->logError( 'Empty or invalid long URL.' );
			return false;
		}

		$accessToken = sanitize_text_field( OptionManager::get( 'access_token' ) );
		$groupGuid   = sanitize_text_field( OptionManager::get( 'group_guid' ) );
		$domain      = sanitize_text_field( OptionManager::get( 'bitly_domain', 'bit.ly' ) );

		if ( ! $accessToken || ! $groupGuid ) {
			$this->logError( 'Missing access token or group GUID.' );
			return false;
		}

		$payload = array(
			'group_guid' => $groupGuid,
			'long_url'   => $longUrl,
		);

		if ( ! empty( $domain ) ) {
			$payload['domain'] = $domain;
		}

		$response = $this->sendRequest( '/shorten', 'POST', $accessToken, $payload );

		if ( ! $response || empty( $response['link'] ) ) {
			$this->logError( 'Shorten URL failed: ' . print_r( $response, true ) );
			return false;
		}

		return esc_url_raw( $response['link'] );
	}

	/**
	 * Perform a Bitly API request.
	 *
	 * @param string     $endpoint Relative API endpoint (e.g. "/shorten")
	 * @param string     $method HTTP method ("GET" or "POST")
	 * @param string     $accessToken OAuth token
	 * @param array|null $payload POST body, optional
	 * @return array|false
	 */
	private function sendRequest( string $endpoint, string $method, string $accessToken, array $payload = null ) {
		$args = array(
			'method'  => strtoupper( $method ),
			'headers' => array(
				'Authorization' => 'Bearer ' . $accessToken,
				'Content-Type'  => 'application/json',
			),
			'timeout' => self::TIMEOUT,
		);

		if ( $payload && $method === 'POST' ) {
			$args['body'] = wp_json_encode( $payload );
		}

		$url      = rtrim( self::API_URL, '/' ) . $endpoint;
		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			$this->logError( 'API request error: ' . $response->get_error_message() );
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return is_array( $body ) ? $body : false;
	}

	/**
	 * Log errors to file if WP_DEBUG is on.
	 *
	 * @param string $message
	 */
	private function logError( string $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$logPath = plugin_dir_path( __FILE__ ) . self::LOG_FILE;
			error_log( '[WBitly] ' . $message . PHP_EOL, 3, $logPath );
		}
	}
}
