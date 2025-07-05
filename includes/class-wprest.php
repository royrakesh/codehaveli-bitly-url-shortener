<?php
/**
 * WP REST API handler for Codehaveli Bitly URL Shortener plugin.
 *
 * @package Codehaveli\Wbitly
 */

namespace Codehaveli\Wbitly;

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
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && intval( $param ) > 0;
						},
						'description'       => __( 'ID of the post to generate Bitly URL for.', 'wbitly' ),
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
			$api       = new BitlyAPI();
			$short_url = $api->shorten_url( $permalink );
			Manager::update_short_url( $post_id, $short_url );
		}

		// Return the short URL in the response.
		return rest_ensure_response( array( 'short_url' => esc_url_raw( $short_url ) ) );
	}

	/**
	 * Permission check callback for Bitly URL generation.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return bool
	 */
	public static function permission_check( WP_REST_Request $request ) {
		// Validate and check permissions for post_id.
		$post_id = intval( $request['post_id'] );
		return $post_id > 0 && current_user_can( 'edit_post', $post_id );
	}
}
