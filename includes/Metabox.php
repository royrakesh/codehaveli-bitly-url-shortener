<?php

namespace Codehaveli\Wbitly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Metabox {

	public static function init() {
		add_action( 'add_meta_boxes', array( self::class, 'registerMetabox' ) );
		add_action( 'admin_bar_menu', array( self::class, 'addFrontendShortlink' ), 999 );
	}

	public static function registerMetabox() {
		$capability = apply_filters( 'wbitly_metabox_capability', 'manage_options' );
		if ( ! current_user_can( $capability ) ) {
			return;
		}

		$settings          = new SettingsPage();
		$active_post_types = OptionManager::get( 'wbitly_custom_post', array( 'post' ) );

		foreach ( $active_post_types as $post_type ) {
			add_meta_box(
				'wbitly-bitly-url-metabox',
				__( 'Bitly Short URL', 'wbitly' ),
				array( self::class, 'renderMetabox' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	public static function renderMetabox( $post ) {
		if ( ! $post instanceof \WP_Post ) {
			return;
		}
		$post_id = intval( $post->ID );

		if ( get_post_status( $post_id ) !== 'publish' ) {
			echo '<h4>' . esc_html__( 'Publish to generate Bitly URL', 'wbitly' ) . '</h4>';
			return;
		}

		$access_token = OptionManager::get( 'access_token' );
		$guid         = OptionManager::get( 'group_guid' );

		if ( ! $access_token || ! $guid ) {
			echo '<a class="wbitly_settings" href="' . esc_url( admin_url( 'tools.php?page=wbitly' ) ) . '">' . esc_html__( 'Get Started', 'wbitly' ) . '</a>';
			return;
		}

		echo '<div class="wbitly_metabox_container wbitly-mt-5">';

		$bitly_url = get_wbitly_short_url( $post_id );

		if ( $bitly_url ) {
			echo '<div class="wbitly_tooltip wbitly copy_bitly">';
			echo '<p><span class="copy_bitly_link wbitly-meta-bg-link">' . esc_html( $bitly_url ) . '</span></p>';
			echo '</div>';

			if ( OptionManager::get( 'wbitly_social_share' ) === 'enable' ) {
				wbitly_get_template(
					'share.php',
					array(
						'post_id'   => $post_id,
						'bitly_url' => $bitly_url,
					)
				);
			}
		} else {
			echo '<div class="wbitly_tooltip">';
			echo '<button class="wbitly generate_bitly button button-primary" data-post_id="' . esc_attr( $post_id ) . '">' . esc_html__( 'Generate URL', 'wbitly' ) . '</button>';
			echo '</div>';
		}

		echo '</div>';
	}

	public static function addFrontendShortlink( $wp_admin_bar ) {
		$allowed_roles = apply_filters( 'wbitly_script_for_allowed_roles', array( 'administrator' ) );

		foreach ( $allowed_roles as $role ) {
			if ( ! current_user_can( $role ) ) {
				continue;
			}

			$active_post_types = OptionManager::get( 'wbitly_custom_post', array( 'post' ) );

			foreach ( $active_post_types as $post_type ) {
				if ( is_singular( $post_type ) ) {
					global $post;
					if ( ! $post instanceof \WP_Post ) {
						continue;
					}
					$bitly_url = get_wbitly_short_url( $post->ID );

					if ( $bitly_url ) {
						$wp_admin_bar->add_node(
							array(
								'id'    => 'wbitly_link_' . intval( $post->ID ),
								'title' => esc_html__( 'Click to Copy Bitly Link', 'wbitly' ),
								'href'  => esc_url( $bitly_url ),
								'meta'  => array(
									'class' => 'wbitly-copy-class',
									'title' => esc_attr__( 'Click to Copy Bitly Link', 'wbitly' ),
								),
							)
						);
					}
				}
			}
		}
	}
}
