<?php

namespace Codehaveli\Wbitly;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SettingsPage {

	private static $instance;

	public static function init() {
		if ( self::$instance === null ) {
			self::$instance = new self();
			self::$instance->hooks();
		}
	}

	private function hooks() {
		add_action( 'admin_menu', array( $this, 'registerPage' ) );
		add_action( 'admin_init', array( $this, 'registerSettings' ) );
		add_action( 'init', array( $this, 'maybeUpdateGuid' ) );
		add_action( 'admin_notices', array( $this, 'noticeSuccess' ) );
		add_action( 'admin_notices', array( $this, 'noticeError' ) );
	}

	public function registerPage() {
		add_management_page(
			'Codehaveli Bitly Settings',
			'Codehaveli Bitly',
			'manage_options',
			'wbitly',
			array( $this, 'renderPage' )
		);
	}

	public function renderPage() {
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

	public function registerSettings() {
		register_setting(
			'wbitly_url_option_group',
			'wbitly_url_option_name',
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'wbitly_url_setting_section',
			'Settings',
			fn() => print( '<p>Configure Bitly API credentials</p>' ),
			'wbitly-url-admin'
		);

		add_settings_field( 'access_token', 'Access Token', array( $this, 'fieldAccessToken' ), 'wbitly-url-admin', 'wbitly_url_setting_section' );
		add_settings_field( 'group_guid', 'Group GUID', array( $this, 'fieldGroupGuid' ), 'wbitly-url-admin', 'wbitly_url_setting_section' );
		add_settings_field( 'bitly_domain', 'Domain (Optional)', array( $this, 'fieldBitlyDomain' ), 'wbitly-url-admin', 'wbitly_url_setting_section' );
		add_settings_field( 'wbitly_social_share', 'Enable Social Share Button', array( $this, 'fieldSocialShare' ), 'wbitly-url-admin', 'wbitly_url_setting_section' );
		add_settings_field( 'wbitly_custom_post', 'Post Types', array( $this, 'fieldCustomPostTypes' ), 'wbitly-url-admin', 'wbitly_url_setting_section' );
	}

	public function sanitize( $input ) {
		return array(
			'access_token'        => sanitize_text_field( $input['access_token'] ?? '' ),
			'group_guid'          => sanitize_text_field( $input['group_guid'] ?? '' ),
			'bitly_domain'        => sanitize_text_field( $input['bitly_domain'] ?? '' ),
			'wbitly_social_share' => sanitize_text_field( $input['wbitly_social_share'] ?? '' ),
			'wbitly_custom_post'  => array_map( 'sanitize_text_field', $input['wbitly_custom_post'] ?? array() ),
		);
	}

	public function fieldAccessToken() {
		printf(
			'<input class="regular-text" type="text" name="wbitly_url_option_name[access_token]" value="%s" />',
			esc_attr( OptionManager::get( 'access_token', '' ) )
		);
		echo '<p><a href="https://www.codehaveli.com/how-to-generate-bitly-oauth-access-token/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'How to generate Bitly OAuth access token?', 'wbitly' ) . '</a></p>';
	}

	public function fieldGroupGuid() {
		$guid_url = esc_url( admin_url( 'tools.php?page=wbitly&wbitly_guid=update' ) );
		printf(
			'<input class="regular-text" type="text" name="wbitly_url_option_name[group_guid]" value="%s" /> <a href="%s" class="button button-primary">%s</a>',
			esc_attr( OptionManager::get( 'group_guid', '' ) ),
			$guid_url,
			esc_html__( 'Get GUID', 'wbitly' )
		);
		echo '<p><small>' . esc_html__( 'Save Access Token before getting GUID.', 'wbitly' ) . '</small></p>';
	}

	public function fieldBitlyDomain() {
		printf(
			'<input class="regular-text" type="text" placeholder="%s" name="wbitly_url_option_name[bitly_domain]" value="%s" />',
			esc_attr__( 'Default: bit.ly', 'wbitly' ),
			esc_attr( OptionManager::get( 'bitly_domain', '' ) )
		);
		echo '<p><small>' . esc_html__( 'Leave blank if you are on a Free Plan.', 'wbitly' ) . '</small></p>';
	}

	public function fieldSocialShare() {
		$checked = checked( OptionManager::get( 'wbitly_social_share', '' ), 'enable', false );
		echo '<label><input type="checkbox" name="wbitly_url_option_name[wbitly_social_share]" value="enable" ' . $checked . '> ' . esc_html__( 'Enable', 'wbitly' ) . '</label>';
		echo '<p><small>' . esc_html__( 'Enable social share button on post edit/list screen.', 'wbitly' ) . '</small></p>';
	}

	public function fieldCustomPostTypes() {
		$selected_types = OptionManager::get( 'wbitly_custom_post', array( 'post' ) );
		$post_types     = get_post_types( array( 'public' => true ), 'names' );

		echo '<fieldset>';
		foreach ( $post_types as $type ) {
			$id = 'wbitly_post_' . esc_attr( $type );
			printf(
				'<label for="%1$s"><input id="%1$s" type="checkbox" name="wbitly_url_option_name[wbitly_custom_post][]" value="%2$s" %3$s> %2$s</label><br>',
				$id,
				esc_html( $type ),
				checked( in_array( $type, $selected_types ), true, false )
			);
		}
		echo '</fieldset>';
	}

	public function maybeUpdateGuid() {
		if (
			current_user_can( 'manage_options' ) &&
			isset( $_GET['page'], $_GET['wbitly_guid'] ) &&
			sanitize_text_field( $_GET['page'] ) === 'wbitly' &&
			sanitize_text_field( $_GET['wbitly_guid'] ) === 'update'
		) {

			$api  = new BitlyAPI();
			$guid = $api->getGroupGuid();

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

	public function noticeSuccess() {
		if ( get_transient( 'wbitly_guid_success' ) ) {
			delete_transient( 'wbitly_guid_success' );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Group GUID saved successfully.', 'wbitly' ) . '</p></div>';
		}
	}

	public function noticeError() {
		if ( get_transient( 'wbitly_guid_error' ) ) {
			delete_transient( 'wbitly_guid_error' );
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to retrieve Group GUID. Check access token.', 'wbitly' ) . '</p></div>';
		}
	}
}
