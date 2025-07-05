<?php

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
		add_action( 'admin_enqueue_scripts', array( self::class, 'enqueueAdminAssets' ) );
		add_action( 'wp_footer', array( self::class, 'injectCopyScript' ), PHP_INT_MAX );
		add_action( 'enqueue_block_editor_assets', array( self::class, 'blockSidebarAssets' ) );
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @return void
	 */
	public static function enqueueAdminAssets() {
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
	 * Injects a copy-to-clipboard script for Bitly links in the frontend.
	 *
	 * @return void
	 */
	public static function injectCopyScript() {
		$default_roles = array( 'administrator' );
		$allowed_roles = apply_filters( 'wbitly_script_for_allowed_roles', $default_roles );

		foreach ( $allowed_roles as $role ) {
			if ( ! current_user_can( $role ) ) {
				continue;
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "<script>
                (function($) {
                    $('.wbitly-copy-class').on('click', function(e) {
                        e.preventDefault();
                        let link = $(this).find('a').attr('href');
                        let title = $(this).find('a').attr('title');
                        if (link) {
                            let temp = $('<textarea />');
                            temp.val(link).css({ width: '1px', height: '1px' }).appendTo('body');
                            temp.select();
                            if (document.execCommand('copy')) {
                                temp.remove();
                                $(this).find('a').html('Copied: ' + $('<div>').text(link).html());
                                setTimeout(() => {
                                    $(this).find('a').html($('<div>').text(title).html());
                                }, 2100);
                            }
                        }
                    });
                })(jQuery);
            </script>";
		}
	}


	/**
	 * Enqueue block editor sidebar assets and localize data.
	 *
	 * @return void
	 */
	public static function blockSidebarAssets() {
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
				'accessToken'   => $token ? $token : '',
				'groupGuid'     => $group_guid ? $group_guid : '',
				'shortUrl'      => get_wbitly_short_url( $post_id ),
				'socialEnabled' => OptionManager::get( 'wbitly_social_share' ) === 'enable',
				'settingsLink'  => admin_url( 'tools.php?page=wbitly' ),
				'isPublished'   => $post_id ? ( get_post_status( $post_id ) === 'publish' ) : false,
			)
		);
	}
}
