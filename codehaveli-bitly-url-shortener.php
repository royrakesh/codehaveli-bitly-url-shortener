<?php

/*
Plugin Name: Bitly URL Shortener
Plugin URI: https://github.com/codehaveli/
Description: Bitly URL Shortener uses the functionality of Bitly API to generate Bitly short link without leaving your WordPress site.
Version: 1.5.0
Author: Codehaveli
Author URI: https://www.codehaveli.com/
License: GPLv2 or later
Text Domain: wbitly
*/

use Codehaveli\Wbitly\Assets;
use Codehaveli\Wbitly\Hooks;
use Codehaveli\Wbitly\Metabox;
use Codehaveli\Wbitly\SettingsPage;
use Codehaveli\Wbitly\ThirdPartySupport;
use Codehaveli\Wbitly\WpRest;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once 'vendor/autoload.php';

define( 'WBITLY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); 
define( 'WBITLY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );	
define( 'WBITLY_PLUGIN_VERSION', '1.4.1' );
define( 'WBITLY_API_URL', 'https://api-ssl.bitly.com' );
define( 'WBITLY_BASENAME', plugin_basename( __FILE__ ) );
define( 'WBITLY_SETTINGS_URL', admin_url( 'tools.php?page=wbitly' ) );



add_action('plugins_loaded', function () {
    WpRest::init();
	SettingsPage::init();
	Assets::init();
	// Metabox::init();
	ThirdPartySupport::init();
	Hooks::init();
});




add_action( 'plugin_action_links_' . WBITLY_BASENAME, function( $links ) {
	$links[] = '<a href="' . esc_url( admin_url( 'tools.php?page=wbitly' ) ) . '">' . esc_html__( 'Settings', 'wbitly' ) . '</a>';
	return $links;
} );


function create_ch_bitly_block_init() {

	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		return;
	}

	
	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	}
	
	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( __DIR__ . "/build/{$block_type}" );
	}
}
add_action( 'init', 'create_ch_bitly_block_init' );




if (!function_exists('wbitly_get_template')) {
    function wbitly_get_template($filename, $args = []) {
        $filepath = plugin_dir_path(__FILE__) . 'templates/' . $filename;

        if (file_exists($filepath)) {
            if (!empty($args) && is_array($args)) {
                extract($args); // extract array to variables
            }

            include $filepath;
        } else {
            error_log("Template file not found: $filepath");
        }
    }
}




function get_wbitly_short_url($post_id = null){

	if(!$post_id){
		global $post;
		$post_id = isset($post->ID) ? $post->ID : 0;
	}

    if(!$post_id){
        return false;
    }

	$wbitly_url = get_post_meta($post_id, '_wbitly_shorturl', true);

	return $wbitly_url ? $wbitly_url : false;

}