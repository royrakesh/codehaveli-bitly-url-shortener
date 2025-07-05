<?php

namespace Codehaveli\Wbitly;

use WP_Error;
use WP_REST_Request;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WpRest {

	public static function init() {
		add_action( 'rest_api_init', array( self::class, 'register_routes' ) );
	}

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

	public static function handle_generate( WP_REST_Request $request ) {
		$post_id = intval( $request['post_id'] );

		if ( $post_id <= 0 ) {
			return new WP_Error(
				'wbitly_invalid_post_id',
				__( 'Invalid post ID.', 'wbitly' ),
				array( 'status' => 400 )
			);
		}

		if ( get_post_status( $post_id ) !== 'publish' ) {
			return new WP_Error(
				'wbitly_post_not_published',
				__( 'Post must be published to generate Bitly URL.', 'wbitly' ),
				array( 'status' => 400 )
			);
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You cannot edit this post.', 'wbitly' ),
				array( 'status' => 403 )
			);
		}

		$short_url = WbitlyManager::getShortUrl( $post_id );

		if ( ! $short_url ) {
			$permalink = get_permalink( $post_id );
			$api       = new BitlyAPI();
			$short_url = $api->shortenURL( $permalink );
			WbitlyManager::updateShortUrl( $post_id, $short_url );
		}

		return rest_ensure_response( array( 'short_url' => esc_url_raw( $short_url ) ) );
	}

	public static function permission_check( WP_REST_Request $request ) {
		$post_id = intval( $request['post_id'] );
		return $post_id > 0 && current_user_can( 'edit_post', $post_id );
	}
}
