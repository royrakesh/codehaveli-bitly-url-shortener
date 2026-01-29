<?php
/** @var string $esc_url */
/** @var string $encoded_url */
/** @var string $title */
/** @var bool $show_social */
$show_social = isset( $show_social ) ? (bool) $show_social : false;
?>
<div class="wbitly-url-share">
	<p>
		<a href="<?php echo esc_url( $esc_url ); ?>" target="_blank" rel="noopener noreferrer">
			<?php echo esc_url( $esc_url ); ?>
		</a>
	</p>
	<?php if ( $show_social ) : ?>
	<div class="wbitly-social-icons">
		<a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . $encoded_url ); ?>"
			target="_blank" rel="noopener noreferrer"
			class="wbitly-icon wbitly-icon-facebook"
			title="<?php esc_attr_e( 'Share on Facebook', 'wbitly' ); ?>"></a>

		<a href="<?php echo esc_url( 'mailto:?subject=' . $title . '&body=' . $encoded_url ); ?>"
			class="wbitly-icon wbitly-icon-email"
			title="<?php esc_attr_e( 'Share via Email', 'wbitly' ); ?>"></a>

		<a href="<?php echo esc_url( 'https://twitter.com/intent/tweet?url=' . $encoded_url ); ?>"
			target="_blank" rel="noopener noreferrer"
			class="wbitly-icon wbitly-icon-x"
			title="<?php esc_attr_e( 'Share on X (Twitter)', 'wbitly' ); ?>"></a>

		<button type="button" class="wbitly-icon wbitly-icon-copy"
			title="<?php esc_attr_e( 'Copy URL', 'wbitly' ); ?>"
			aria-label="<?php esc_attr_e( 'Copy URL to clipboard', 'wbitly' ); ?>"
			data-copy-text="<?php echo esc_attr( $esc_url ); ?>"
			style="background: none; border: none; cursor: pointer; padding: 0;"></button>
	</div>
	<?php endif; ?>
</div>
