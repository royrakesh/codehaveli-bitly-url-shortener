<?php
namespace Codehaveli\Wbitly;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

use Codehaveli\Wbitly\Admin\Manager;
use Codehaveli\Wbitly\Admin\OptionManager;
use Codehaveli\Wbitly\API\BitlyAPI;
use WP_CLI;


class WbitlyCLI {

	/**
	 * Generate Bitly shortlinks for posts.
	 *
	 * ## OPTIONS
	 *
	 * [--all]
	 * : Generate shortlinks for all published posts of supported types.
	 *
	 * [--ids=<ids>]
	 * : Comma-separated list of post IDs.
	 *
	 * [--first=<number>]
	 * : Generate for the first X published posts of supported types.
	 *
	 * [--post_type=<type>]
	 * : Specify a post type to process (default: post).
	 *
	 * ## EXAMPLES
	 *
	 *     wp wbitly generate --all
	 *     wp wbitly generate --ids=1,2,3
	 *     wp wbitly generate --first=10
	 *     wp wbitly generate --all --post_type=page
	 *     wp wbitly generate           # runs for first 10 posts of type 'post'
	 *
	 * @when after_wp_load
	 */
	public function generate( $args, $assoc_args ) {
		$post_ids = array();

		// Determine post type to use (default: post)
		if ( isset( $assoc_args['post_type'] ) ) {
			$post_type = sanitize_key( $assoc_args['post_type'] );
		} else {
			$post_type = 'post';
		}

		// Validate post type against enabled types
		$supported_types = OptionManager::get( 'wbitly_custom_post', array( 'post' ) );
		if ( ! in_array( $post_type, $supported_types, true ) ) {
			WP_CLI::error( "Post type '{$post_type}' is not enabled for Bitly shortlinks." );
			return;
		}
		$supported_types = array( $post_type );

		// Check for Bitly token and guid
		$token = OptionManager::get_access_token();
		$guid  = OptionManager::get_guid();
		if ( empty( $token ) || empty( $guid ) ) {
			WP_CLI::error( 'Please configure the plugin first (set Bitly Access Token and Group GUID) before running this command.' );
			return;
		}

		// Default: if no --all, --ids, --first, run for first 10 posts
		if ( ! isset( $assoc_args['all'] ) && ! isset( $assoc_args['ids'] ) && ! isset( $assoc_args['first'] ) ) {
			$assoc_args['first'] = 10;
		}

		if ( isset( $assoc_args['all'] ) ) {
			$query    = new \WP_Query(
				array(
					'post_type'      => $supported_types,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => array(
						array(
							'key'     => '_wbitly_shorturl',
							'compare' => 'NOT EXISTS',
						),
					),
				)
			);
			$post_ids = $query->posts;
		} elseif ( isset( $assoc_args['ids'] ) ) {
			$post_ids = array_filter( array_map( 'absint', explode( ',', $assoc_args['ids'] ) ) );
			// Only keep posts where meta does not exist
			$post_ids = array_filter(
				$post_ids,
				function ( $pid ) use ( $post_type ) {
					return get_post_status( $pid ) === 'publish'
					&& get_post_type( $pid ) === $post_type
					&& get_post_meta( $pid, '_wbitly_shorturl', true ) === '';
				}
			);
		} elseif ( isset( $assoc_args['first'] ) ) {
			$first    = absint( $assoc_args['first'] );
			$query    = new \WP_Query(
				array(
					'post_type'      => $supported_types,
					'post_status'    => 'publish',
					'posts_per_page' => $first,
					'orderby'        => 'ID',
					'order'          => 'ASC',
					'fields'         => 'ids',
					'meta_query'     => array(
						array(
							'key'     => '_wbitly_shorturl',
							'compare' => 'NOT EXISTS',
						),
					),
				)
			);
			$post_ids = $query->posts;
		} else {
			WP_CLI::error( 'Please specify --all, --ids, or --first.' );
			return;
		}

		if ( empty( $post_ids ) ) {
			WP_CLI::warning( 'No posts found to process.' );
			return;
		}

		$api   = new BitlyAPI();
		$count = 0;
		foreach ( $post_ids as $post_id ) {
			// Only process if meta does not exist (extra check for --ids)
			if ( get_post_meta( $post_id, '_wbitly_shorturl', true ) !== '' ) {
				WP_CLI::log( "Post ID {$post_id}: Already has shortlink." );
				continue;
			}
			$permalink = get_permalink( $post_id );
			if ( ! $permalink ) {
				WP_CLI::warning( "Post ID {$post_id}: No permalink found." );
				continue;
			}
			$new_url = $api->shorten_url( $permalink );
			if ( $new_url ) {
				Manager::update_short_url( $post_id, $new_url );
				WP_CLI::success( "Post ID {$post_id}: Shortlink generated: {$new_url}" );
				++$count;
			} else {
				WP_CLI::error( "Post ID {$post_id}: Failed to generate shortlink." );
			}
		}
		WP_CLI::success( "Done. {$count} shortlinks generated." );
	}
}
