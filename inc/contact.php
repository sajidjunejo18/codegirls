<?php
/**
 * Contact form: [cg_contact_form] shortcode + admin-post handler (no plugin needed).
 * Messages are emailed to the site admin address (Settings → General).
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

function cg_contact_form_shortcode() {
	$status = isset( $_GET['cg_contact'] ) ? sanitize_key( wp_unslash( $_GET['cg_contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	ob_start();

	if ( 'sent' === $status ) {
		echo '<p class="cg-form__notice" role="status">' . esc_html__( 'Thank you! Your message has been sent.', 'generatepress-child' ) . '</p>';
	} elseif ( 'error' === $status ) {
		echo '<p class="cg-form__notice cg-form__notice--error" role="alert">' . esc_html__( 'Sorry, something went wrong. Please check the fields and try again.', 'generatepress-child' ) . '</p>';
	}
	?>
	<form class="cg-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="cg_contact">
		<input type="hidden" name="cg_redirect" value="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php wp_nonce_field( 'cg_contact', 'cg_nonce' ); ?>
		<div class="cg-form__hp" aria-hidden="true"><label>Leave empty<input type="text" name="cg_website" tabindex="-1" autocomplete="off"></label></div>

		<div class="cg-form__row">
			<label class="cg-form__field">Your Name:
				<input type="text" name="cg_name" required autocomplete="name">
			</label>
			<label class="cg-form__field">Phone:
				<input type="tel" name="cg_phone" autocomplete="tel">
			</label>
		</div>
		<label class="cg-form__field">Email Address:
			<input type="email" name="cg_email" required autocomplete="email">
		</label>
		<label class="cg-form__field">Message:
			<textarea name="cg_message" required></textarea>
		</label>
		<button class="cg-btn cg-form__submit" type="submit"><?php esc_html_e( 'Submit', 'generatepress-child' ); ?></button>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'cg_contact_form', 'cg_contact_form_shortcode' );

function cg_handle_contact() {
	$redirect = isset( $_POST['cg_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['cg_redirect'] ) ) : home_url( '/' );
	$redirect = wp_validate_redirect( $redirect, home_url( '/' ) );

	$ok = isset( $_POST['cg_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cg_nonce'] ) ), 'cg_contact' );
	$ok = $ok && empty( $_POST['cg_website'] ); // Honeypot.

	$name    = isset( $_POST['cg_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cg_name'] ) ) : '';
	$phone   = isset( $_POST['cg_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['cg_phone'] ) ) : '';
	$email   = isset( $_POST['cg_email'] ) ? sanitize_email( wp_unslash( $_POST['cg_email'] ) ) : '';
	$message = isset( $_POST['cg_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cg_message'] ) ) : '';

	$sent = false;
	if ( $ok && $name && is_email( $email ) && $message ) {
		$body = "Name: $name\nPhone: $phone\nEmail: $email\n\n$message";
		$sent = wp_mail(
			get_option( 'admin_email' ),
			sprintf( '[%s] New contact message from %s', wp_specialchars_decode( get_bloginfo( 'name' ) ), $name ),
			$body,
			array( 'Reply-To: ' . $name . ' <' . $email . '>' )
		);
	}

	wp_safe_redirect( add_query_arg( 'cg_contact', $sent ? 'sent' : 'error', $redirect ) . '#contact' );
	exit;
}
add_action( 'admin_post_nopriv_cg_contact', 'cg_handle_contact' );
add_action( 'admin_post_cg_contact', 'cg_handle_contact' );
