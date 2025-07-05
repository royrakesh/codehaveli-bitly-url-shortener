<?php
/**
 * Assets handler for Codehaveli Bitly URL Shortener plugin.
 *
 * @package Codehaveli\Wbitly
 */

namespace Codehaveli\Wbitly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles plugin assets (JS/CSS) and related scripts.
 */
class Assets {

	/**
	 * Initialize asset hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueue_admin_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( self::class, 'block_sidebar_assets' ) );
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @return void
	 */
	public static function enqueue_admin_assets() {
		wp_enqueue_script(
			'wbitly-js',
			WBITLY_PLUGIN_URL . 'build/admin/admin.min.js',
			array( 'jquery' ),
			WBITLY_PLUGIN_VERSION,
			true
		);

		wp_enqueue_style(
			'wbitly-css',
			WBITLY_PLUGIN_URL . 'build/admin/admin.min.css',
			array(),
			WBITLY_PLUGIN_VERSION,
			'all'
		);

		wp_localize_script(
			'wbitly-js',
			'wbitlyJS',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}


	/**
	 * Enqueue block editor sidebar assets and localize data.
	 *
	 * @return void
	 */
	public static function block_sidebar_assets() {
		wp_enqueue_script(
			'wbitly-sidebar',
			WBITLY_PLUGIN_URL . 'build/admin/sidebar.min.js',
			array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data' ),
			WBITLY_PLUGIN_VERSION,
			true
		);

		global $post;
		$post_id = ( isset( $post ) && is_object( $post ) && isset( $post->ID ) ) ? intval( $post->ID ) : 0;

		$token      = OptionManager::get( 'access_token' );
		$group_guid = OptionManager::get( 'group_guid' );

		wp_localize_script(
			'wbitly-sidebar',
			'wbitlyData',
			array(
				'postId'        => $post_id,
				'accessToken'   => $token ? 1 : '',
				'groupGuid'     => $group_guid ? 1 : '',
				'shortUrl'      => get_wbitly_short_url( $post_id ),
				'socialEnabled' => OptionManager::get( 'wbitly_social_share' ) === 'enable',
				'settingsLink'  => admin_url( 'tools.php?page=wbitly' ),
				'isPublished'   => $post_id ? ( get_post_status( $post_id ) === 'publish' ) : false,
			)
		);
	}
}
