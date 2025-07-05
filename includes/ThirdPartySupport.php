<?php

namespace Codehaveli\Wbitly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ThirdPartySupport {

	private static $instance;

	/**
	 * Meta keys to exclude when duplicating posts.
	 */
	private $meta_blacklist = array(
		'_wbitly_shorturl',
	);

	public static function init() {
		if ( ! self::$instance ) {
			self::$instance = new self();
			self::$instance->registerHooks();
		}
	}

	private function registerHooks() {
		add_filter( 'duplicate_post_excludelist_filter', array( $this, 'excludeMetaOnDuplicate' ), 10, 1 );
	}

	/**
	 * Add meta keys to the exclusion list when duplicating posts.
	 *
	 * @param array $meta_blacklist
	 * @return array
	 */
	public function excludeMetaOnDuplicate( $meta_blacklist ) {
		$sanitized = array_map( 'sanitize_key', $this->meta_blacklist );
		return array_merge( $meta_blacklist, $sanitized );
	}

	/**
	 * Allows dynamically adding more meta keys from other plugins or contexts.
	 *
	 * @param string|array $keys
	 * @return void
	 */
	public function addMetaToExclude( $keys ) {
		$keys = (array) $keys;
		$keys = array_map( 'sanitize_key', $keys );
		$this->meta_blacklist = array_unique( array_merge( $this->meta_blacklist, $keys ) );
	}
}
