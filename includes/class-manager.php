<?php
/**
 * Manager for Bitly short URL meta operations.
 *
 * @package Codehaveli\Wbitly
 */

namespace Codehaveli\Wbitly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles Bitly short URL meta operations for posts.
 */
class Manager {

	/**
	 * Get the Bitly short URL for a post.
	 *
	 * @param int|\WP_Post $post Post ID or object.
	 * @return string|null The short URL or null if not found.
	 */
	public static function getShortUrl( $post ) {
		$post_id = is_a( $post, '\WP_Post' ) ? $post->ID : absint( $post );

		if ( ! $post_id ) {
			return null;
		}

		$short_url = get_post_meta( $post_id, '_wbitly_shorturl', true );

		return ( $short_url && filter_var( $short_url, FILTER_VALIDATE_URL ) ) ? esc_url_raw( $short_url ) : null;
	}

	/**
	 * Update or add the Bitly short URL for a post.
	 *
	 * @param int|\WP_Post $post Post ID or object.
	 * @param string       $short_url The short URL to save.
	 * @return bool True on success, false on failure.
	 */
	public static function updateShortUrl( $post, string $short_url ) {
		$post_id = is_a( $post, '\WP_Post' ) ? $post->ID : absint( $post );

		if ( ! $post_id ) {
			return false;
		}

		$short_url = filter_var( $short_url, FILTER_VALIDATE_URL ) ? esc_url_raw( $short_url ) : '';
		return update_post_meta( $post_id, '_wbitly_shorturl', $short_url );
	}

	/**
	 * Delete the Bitly short URL meta from a post.
	 *
	 * @param int|\WP_Post $post Post ID or object.
	 * @return bool True if deleted, false if not found.
	 */
	public static function deleteShortUrl( $post ) {
		$post_id = is_a( $post, '\WP_Post' ) ? $post->ID : absint( $post );

		if ( ! $post_id ) {
			return false;
		}

		return delete_post_meta( $post_id, '_wbitly_shorturl' );
	}
}
