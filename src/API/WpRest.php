<?php

/**
 * WP REST API handler for Codehaveli Bitly URL Shortener plugin.
 *
 * @package Codehaveli\Wbitly
 */

namespace Codehaveli\Wbitly\API;

use Codehaveli\Wbitly\Admin\Manager;
use Codehaveli\Wbitly\Admin\OptionManager;
use Codehaveli\Wbitly\Util\Logger;
use WP_Error;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles REST API endpoints for Bitly URL Shortener.
 */
class WpRest {


	/**
	 * Initialize REST API routes.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

	/**
	 * Register REST API routes for Bitly URL Shortener.
	 *
	 * @return void
	 */
	public static function register_routes() {
		register_rest_route(
			'wbitly/v1',
			'/generate/(?P<post_id>\d+)',
			array(
				'methods'             => 'POST',
				'callback'            => array( self::class, 'handle_generate' ),
				'permission_callback' => array( self::class, 'permission_check' ),
				'args'                => array(
					'post_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'validate_callback' => function ( $param, $request, $key ) {
							// Ensure it's numeric and positive.
							if ( ! is_numeric( $param ) ) {
								return false;
							}
							$post_id = absint( $param );
							if ( $post_id <= 0 ) {
								return false;
							}
							// Verify post exists.
							return get_post( $post_id ) !== null;
						},
						'sanitize_callback' => 'absint',
						'description'       => __( 'ID of the post to generate Bitly URL for.', 'wbitly' ),
					),
				),
			)
		);

		register_rest_route(
			'wbitly/v1',
			'/meta/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => function ( $request ) {
					$post_id = absint( $request['id'] );
					
					// Validate post exists.
					if ( $post_id <= 0 || ! get_post( $post_id ) ) {
						return new WP_Error(
							'wbitly_invalid_post_id',
							__( 'Invalid post ID.', 'wbitly' ),
							array( 'status' => 400 )
						);
					}

					$short_url = Manager::get_short_url( $post_id );
					return rest_ensure_response(
						array(
							'short_url' => $short_url,
						)
					);
				},
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
				'args'                => array(
					'id' => array(
						'required'          => true,
						'type'              => 'integer',
						'validate_callback' => function ( $param ) {
							$post_id = absint( $param );
							return $post_id > 0 && get_post( $post_id ) !== null;
						},
						'sanitize_callback' => 'absint',
						'description'       => __( 'ID of the post to get Bitly URL for.', 'wbitly' ),
					),
				),
			)
		);
	}

	/**
	 * Handle the Bitly URL generation request.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return array|WP_Error
	 */
	public static function handle_generate( WP_REST_Request $request ) {
		// Validate and sanitize post_id.
		$post_id = intval( $request['post_id'] );

		if ( $post_id <= 0 ) {
			return new WP_Error(
				'wbitly_invalid_post_id',
				__( 'Invalid post ID.', 'wbitly' ),
				array( 'status' => 400 )
			);
		}

		// Ensure post is published.
		if ( get_post_status( $post_id ) !== 'publish' ) {
			return new WP_Error(
				'wbitly_post_not_published',
				__( 'Post must be published to generate Bitly URL.', 'wbitly' ),
				array( 'status' => 400 )
			);
		}

		// Permission check.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You cannot edit this post.', 'wbitly' ),
				array( 'status' => 403 )
			);
		}

		// Try to get existing short URL.
		$short_url = Manager::get_short_url( $post_id );

		// If not found, generate and save a new one.
		if ( ! $short_url ) {
			$permalink = get_permalink( $post_id );
			
			if ( ! $permalink ) {
				return new WP_Error(
					'wbitly_no_permalink',
					__( 'Could not generate permalink for this post.', 'wbitly' ),
					array( 'status' => 400 )
				);
			}

			$api       = new BitlyAPI();
			$short_url = $api->shorten_url( $permalink );

			// Validate the short URL before saving.
			if ( ! $short_url || ! filter_var( $short_url, FILTER_VALIDATE_URL ) ) {
				Logger::error( sprintf( 'Failed to generate Bitly URL for post ID %d', $post_id ) );
				return new WP_Error(
					'wbitly_generation_failed',
					__( 'Failed to generate Bitly short URL. Please check your API credentials.', 'wbitly' ),
					array( 'status' => 500 )
				);
			}

			// Only update if we have a valid URL.
			Manager::update_short_url( $post_id, $short_url );
		}

		$esc_url     = esc_url( $short_url );
		$encoded_url = rawurlencode( $short_url );
		$title       = rawurlencode( get_the_title( $post_id ) );
		$show_social = OptionManager::get( 'wbitly_social_share' ) === 'enable';

		// Load the share.php template and capture output.
		ob_start();
		self::wbitly_get_template(
			'share.php',
			array(
				'esc_url'     => $esc_url,
				'encoded_url' => $encoded_url,
				'title'       => $title,
				'show_social' => $show_social,
			)
		);
		$share_html = ob_get_clean();

		// Return the short URL in the response.
		return rest_ensure_response(
			array(
				'short_url'   => esc_url_raw( $short_url ),
				'share_block' => $share_html,
			)
		);
	}

	/**
	 * Permission check callback for Bitly URL generation.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return bool
	 */
	public static function permission_check( WP_REST_Request $request ) {
		$post_id = intval( $request['post_id'] );

		if ( $post_id <= 0 ) {
			return false;
		}

		// Validate nonce if sent as custom header or param.
		$nonce = $request->get_header( 'x_wp_nonce' ); // Standard WP header from wp-api-fetch.

		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return false;
		}

		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Get a template file from the plugin's templates directory.
	 *
	 * @param string $filename The name of the template file.
	 * @param array  $args     Optional. Arguments to pass to the template.
	 */
	public static function wbitly_get_template( $filename, $args = array() ) {
		// Validate filename is a string.
		if ( ! is_string( $filename ) || empty( $filename ) ) {
			Logger::error( 'Template filename must be a non-empty string.' );
			return;
		}

		// Only allow .php files from the templates directory.
		$filename = basename( $filename );
		
		// Prevent directory traversal.
		if ( false !== strpos( $filename, '..' ) || false !== strpos( $filename, '/' ) || false !== strpos( $filename, '\\' ) ) {
			Logger::error( 'Invalid template filename: ' . $filename );
			return;
		}
		
		// Validate file extension.
		if ( substr( $filename, -4 ) !== '.php' ) {
			Logger::error( 'Invalid template file extension: ' . $filename );
			return;
		}
		
		// Validate filename contains only safe characters.
		if ( ! preg_match( '/^[a-zA-Z0-9_-]+\.php$/', $filename ) ) {
			Logger::error( 'Template filename contains invalid characters: ' . $filename );
			return;
		}
		
		$filepath = WBITLY_PLUGIN_PATH . 'templates/' . $filename;

		if ( file_exists( $filepath ) ) {
			// Use output buffering to capture template output safely.
			// Pass args as a local variable instead of using extract().
			$template_args = array();
			if ( ! empty( $args ) && is_array( $args ) ) {
				// Validate and sanitize template arguments.
				foreach ( $args as $key => $value ) {
					// Only allow alphanumeric keys starting with letter or underscore.
					if ( preg_match( '/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key ) ) {
						// Ensure values are strings for template variables.
						$template_args[ $key ] = is_string( $value ) ? $value : '';
					}
				}
			}

			// Make template args available in template scope.
			foreach ( $template_args as $key => $value ) {
				$$key = $value; // phpcs:ignore WordPress.Variables.VariableAnalysis.VariableVariableName
			}

			include $filepath;
		} else {
			// translators: %s: The full path to the missing template file
			Logger::error( sprintf( __( 'Template file not found: %s', 'wbitly' ), $filepath ) );
		}
	}
}
