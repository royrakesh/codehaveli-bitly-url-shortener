<?php
/**
 * Settings page handler for Codehaveli Bitly URL Shortener plugin.
 *
 * @package Codehaveli\Wbitly
 */

namespace Codehaveli\Wbitly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the plugin settings page and related actions.
 */
class Settings {

	private static $instance;

	/**
	 * Initialize settings page hooks.
	 *
	 * @return void
	 */
	public static function init() {
		if ( self::$instance === null ) {
			self::$instance = new self();
			self::$instance->hooks();
		}
	}

	/**
	 * Register admin hooks for settings page.
	 *
	 * @return void
	 */
	private function hooks() {
		add_action( 'admin_menu', array( $this, 'register_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'init', array( $this, 'maybe_update_guid' ) );
		add_action( 'admin_notices', array( $this, 'notice_success' ) );
		add_action( 'admin_notices', array( $this, 'notice_error' ) );
	}

	/**
	 * Register the settings page in the admin menu.
	 *
	 * @return void
	 */
	public function register_page() {
		add_management_page(
			'Codehaveli Bitly Settings',
			'Codehaveli Bitly',
			'manage_options',
			'wbitly',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Render the settings page HTML.
	 *
	 * @return void
	 */
	public function render_page() {
		$options = OptionManager::all();
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Codehaveli Bitly Settings', 'wbitly' ); ?></h2>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wbitly_url_option_group' );
				do_settings_sections( 'wbitly-url-admin' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register plugin settings and fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'wbitly_url_option_group',
			'ch_wbitly_url_option',
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'wbitly_url_setting_section',
			'Settings',
			function () {
				echo '<p>Configure Bitly API credentials</p>';
			},
			'wbitly-url-admin'
		);

		add_settings_field( 'access_token', 'Access Token', array( $this, 'field_access_token' ), 'wbitly-url-admin', 'wbitly_url_setting_section' );
		add_settings_field( 'group_guid', 'Group GUID', array( $this, 'field_group_guid' ), 'wbitly-url-admin', 'wbitly_url_setting_section' );
		add_settings_field( 'bitly_domain', 'Domain (Optional)', array( $this, 'field_bitly_domain' ), 'wbitly-url-admin', 'wbitly_url_setting_section' );
		add_settings_field( 'wbitly_social_share', 'Enable Social Share Button', array( $this, 'field_social_share' ), 'wbitly-url-admin', 'wbitly_url_setting_section' );
		add_settings_field( 'wbitly_custom_post', 'Post Types', array( $this, 'field_custom_post_types' ), 'wbitly-url-admin', 'wbitly_url_setting_section' );
	}

	/**
	 * Sanitize settings input.
	 *
	 * @param array $input Raw input array.
	 * @return array Sanitized input.
	 */
	public function sanitize( $input ) {
		return array(
			'access_token'        => sanitize_text_field( $input['access_token'] ?? '' ),
			'group_guid'          => sanitize_text_field( $input['group_guid'] ?? '' ),
			'bitly_domain'        => sanitize_text_field( $input['bitly_domain'] ?? '' ),
			'wbitly_social_share' => sanitize_text_field( $input['wbitly_social_share'] ?? '' ),
			'wbitly_custom_post'  => array_map( 'sanitize_text_field', $input['wbitly_custom_post'] ?? array() ),
		);
	}

	/**
	 * Render the Access Token field.
	 *
	 * @return void
	 */
	public function field_access_token() {
		printf(
			'<input class="regular-text" type="text" name="ch_wbitly_url_option[access_token]" value="%s" />',
			esc_attr( OptionManager::get( 'access_token', '' ) )
		);
		echo '<p><a href="https://www.codehaveli.com/how-to-generate-bitly-oauth-access-token/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'How to generate Bitly OAuth access token?', 'wbitly' ) . '</a></p>';
	}

	/**
	 * Render the Group GUID field.
	 *
	 * @return void
	 */
	public function field_group_guid() {
		$guid_url = esc_url( admin_url( 'tools.php?page=wbitly&wbitly_guid=update' ) );
		printf(
			'<input class="regular-text" type="text" name="ch_wbitly_url_option[group_guid]" value="%s" /> <a href="%s" class="button button-primary">%s</a>',
			esc_attr( OptionManager::get( 'group_guid', '' ) ),
			$guid_url,
			esc_html__( 'Get GUID', 'wbitly' )
		);
		echo '<p><small>' . esc_html__( 'Save Access Token before getting GUID.', 'wbitly' ) . '</small></p>';
	}

	/**
	 * Render the Bitly Domain field.
	 *
	 * @return void
	 */
	public function field_bitly_domain() {
		printf(
			'<input class="regular-text" type="text" placeholder="%s" name="ch_wbitly_url_option[bitly_domain]" value="%s" />',
			esc_attr__( 'Default: bit.ly', 'wbitly' ),
			esc_attr( OptionManager::get( 'bitly_domain', '' ) )
		);
		echo '<p><small>' . esc_html__( 'Leave blank if you are on a Free Plan.', 'wbitly' ) . '</small></p>';
	}

	/**
	 * Render the Social Share field.
	 *
	 * @return void
	 */
	public function field_social_share() {
		$checked = checked( OptionManager::get( 'wbitly_social_share', '' ), 'enable', false );
		echo '<label><input type="checkbox" name="ch_wbitly_url_option[wbitly_social_share]" value="enable" ' . $checked . '> ' . esc_html__( 'Enable', 'wbitly' ) . '</label>';
		echo '<p><small>' . esc_html__( 'Enable social share button on post edit/list screen.', 'wbitly' ) . '</small></p>';
	}

	/**
	 * Render the Custom Post Types field.
	 *
	 * @return void
	 */
	public function field_custom_post_types() {
		$selected_types = OptionManager::get( 'wbitly_custom_post', array( 'post' ) );
		$post_types     = get_post_types( array( 'public' => true ), 'names' );

		echo '<fieldset>';
		foreach ( $post_types as $type ) {
			$id = 'wbitly_post_' . esc_attr( $type );
			printf(
				'<label for="%1$s"><input id="%1$s" type="checkbox" name="ch_wbitly_url_option[wbitly_custom_post][]" value="%2$s" %3$s> %2$s</label><br>',
				$id,
				esc_html( $type ),
				checked( in_array( $type, $selected_types ), true, false )
			);
		}
		echo '</fieldset>';
	}

	/**
	 * Handle GUID update requests from the settings page.
	 *
	 * @return void
	 */
	public function maybe_update_guid() {
		if (
			current_user_can( 'manage_options' ) &&
			isset( $_GET['page'], $_GET['wbitly_guid'] ) &&
			sanitize_text_field( $_GET['page'] ) === 'wbitly' &&
			sanitize_text_field( $_GET['wbitly_guid'] ) === 'update'
		) {

			$api  = new BitlyAPI();
			$guid = $api->get_group_guid();

			if ( $guid ) {
				OptionManager::set( 'group_guid', $guid );
				set_transient( 'wbitly_guid_success', true, 5 );
			} else {
				set_transient( 'wbitly_guid_error', true, 5 );
			}

			wp_safe_redirect( admin_url( 'tools.php?page=wbitly' ) );
			exit;
		}
	}

	/**
	 * Display a success notice if the GUID was updated.
	 *
	 * @return void
	 */
	public function notice_success() {
		if ( get_transient( 'wbitly_guid_success' ) ) {
			delete_transient( 'wbitly_guid_success' );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Group GUID saved successfully.', 'wbitly' ) . '</p></div>';
		}
	}

	/**
	 * Display an error notice if the GUID update failed.
	 *
	 * @return void
	 */
	public function notice_error() {
		if ( get_transient( 'wbitly_guid_error' ) ) {
			delete_transient( 'wbitly_guid_error' );
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to retrieve Group GUID. Check access token.', 'wbitly' ) . '</p></div>';
		}
	}
}
