<?php

/**
 * Hooks handler for Codehaveli Bitly URL Shortener plugin.
 *
 * @package Codehaveli\Wbitly
 */

namespace Codehaveli\Wbitly;

if (! defined('ABSPATH')) {
	exit; // Prevent direct access.
}

/**
 * Handles WordPress hooks for Bitly URL Shortener.
 */
class Hooks
{

	/**
	 * Initialize plugin hooks.
	 *
	 * @return void
	 */
	public static function init()
	{
		add_filter('pre_get_shortlink', array(self::class, 'change_core_short_link_with_wbitly_link'), 10, 2);
		add_action('transition_post_status', array(self::class, 'wbitly_update_shorturl'), 10, 3);
		add_action('plugin_action_links_' . WBITLY_BASENAME, array(self::class, 'add_settings_link'));
		add_action('init', array(self::class, 'create_ch_bitly_block_init'));
	}


	/**
	 * Replace core shortlink with Bitly short URL if available.
	 *
	 * @param string|false $shortlink   The original shortlink URL.
	 * @param int          $id          Post ID.
	 * @return string|false             Bitly short URL or original shortlink.
	 */
	public static function change_core_short_link_with_wbitly_link($shortlink, $id)
	{
		$id = intval($id);
		if (! is_int($id) || $id <= 0) {
			return $shortlink; // Invalid post ID, return default.
		}

		// Get the stored short URL safely.
		$bitly_url = Manager::get_short_url($id);

		// Validate URL format before returning.
		if ($bitly_url && filter_var($bitly_url, FILTER_VALIDATE_URL)) {
			return esc_url_raw($bitly_url);
		}

		return $shortlink;
	}

	/**
	 * Generate and save Bitly short URL when a post is published.
	 *
	 * @param string   $new_status New post status.
	 * @param string   $old_status Old post status.
	 * @param \WP_Post $post       Post object.
	 * @return void
	 */
	public static function wbitly_update_shorturl($new_status, $old_status, $post)
	{
		if (! $post instanceof \WP_Post) {
			return; // Security: ensure $post is WP_Post object.
		}

		if ('publish' === $new_status && 'publish' !== $old_status) {

			$active_post_types = OptionManager::get('wbitly_custom_post', array('post'));

			if (is_array($active_post_types) && in_array($post->post_type, $active_post_types, true)) {
				$post_id = absint($post->ID);

				if (0 === $post_id) {
					return; // Invalid post ID, bail early.
				}

				// Check if short URL already exists.
				$shorten_url = Manager::get_short_url($post_id);

				if (empty($shorten_url) && ! wp_is_post_revision($post_id)) {
					$permalink = get_permalink($post_id);

					// Sanity check permalink.
					if ($permalink && filter_var($permalink, FILTER_VALIDATE_URL)) {

						$api       = new BitlyAPI();
						$short_url = $api->shorten_url($permalink);

						// Validate returned short URL before saving.
						if ($short_url && filter_var($short_url, FILTER_VALIDATE_URL)) {
							Manager::update_short_url($post_id, $short_url);
						} else {
							// Log or handle invalid short URL.
							Logger::error(sprintf('Invalid Bitly URL for post ID %d: %s', $post_id, $short_url));
						}
					}
				}
			}
		}
	}


	/**
	 * Add settings link to the plugin action links.
	 *
	 * @param array $links Existing action links.
	 * @return array Modified action links with settings link.
	 */

	public static function add_settings_link($links)
	{
		$settings_link = '<a href="' . esc_url(admin_url('tools.php?page=wbitly')) . '">' . esc_html__('Settings', 'wbitly') . '</a>';
		array_unshift($links, $settings_link);
		return $links;
	}


	/**
	 * Initialize the Bitly block for Gutenberg.
	 *
	 * This function registers the Bitly block with WordPress.
	 */
	public static function create_ch_bitly_block_init()
	{

		if (function_exists('wp_register_block_types_from_metadata_collection')) {
			wp_register_block_types_from_metadata_collection(__DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php');
			return;
		}

		if (function_exists('wp_register_block_metadata_collection')) {
			wp_register_block_metadata_collection(WBITLY_PLUGIN_PATH . '/build', WBITLY_PLUGIN_PATH . '/build/blocks-manifest.php');
		}

		$manifest_data = require WBITLY_PLUGIN_PATH . '/build/blocks-manifest.php';
		foreach (array_keys($manifest_data) as $block_type) {
			register_block_type(WBITLY_PLUGIN_PATH . "/build/{$block_type}");
		}
	}
}
