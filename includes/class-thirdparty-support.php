<?php
/**
 * Third-party support handler for Codehaveli Bitly URL Shortener plugin.
 *
 * @package Codehaveli\Wbitly
 */

namespace Codehaveli\Wbitly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles integration with third-party plugins (e.g., post duplication).
 */
class ThirdPartySupport {

	private static $instance;

	/**
	 * Meta keys to exclude when duplicating posts.
	 *
	 * @var array
	 */
	private $meta_blacklist = array(
		'_wbitly_shorturl',
	);

	/**
	 * Initialize third-party support hooks.
	 *
	 * @return void
	 */
	public static function init() {
		if ( ! self::$instance ) {
			self::$instance = new self();
			self::$instance->registerHooks();
		}
	}

	/**
	 * Register hooks for third-party plugin compatibility.
	 *
	 * @return void
	 */
	private function registerHooks() {
		add_filter( 'duplicate_post_excludelist_filter', array( $this, 'excludeMetaOnDuplicate' ), 10, 1 );
	}

	/**
	 * Add meta keys to the exclusion list when duplicating posts.
	 *
	 * @param array $meta_blacklist Existing meta blacklist.
	 * @return array Updated meta blacklist.
	 */
	public function excludeMetaOnDuplicate( $meta_blacklist ) {
		$sanitized = array_map( 'sanitize_key', $this->meta_blacklist );
		return array_merge( $meta_blacklist, $sanitized );
	}

	/**
	 * Allows dynamically adding more meta keys from other plugins or contexts.
	 *
	 * @param string|array $keys Meta keys to exclude.
	 * @return void
	 */
	public function addMetaToExclude( $keys ) {
		$keys = (array) $keys;
		$keys = array_map( 'sanitize_key', $keys );
		$this->meta_blacklist = array_unique( array_merge( $this->meta_blacklist, $keys ) );
	}
}
