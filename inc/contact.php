<?php
/**
 * Contact form: [cg_contact_form] shortcode + admin-post handler (no plugin needed).
 * Messages are emailed to the site admin address (Settings → General).
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

function cg_contact_form_shortcode() {
	global $wp;
	$status = isset( $_GET['cg_contact'] ) ? sanitize_key( wp_unslash( $_GET['cg_contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$token  = isset( $_GET['cg_ctok'] ) ? sanitize_key( wp_unslash( $_GET['cg_ctok'] ) ) : '';

	$flash = array();
	if ( $token ) {
		$data = get_transient( 'cg_contact_err_' . $token );
		if ( is_array( $data ) ) {
			$flash = $data;
			delete_transient( 'cg_contact_err_' . $token );
		}
	}

	ob_start();

	if ( 'sent' === $status ) {
		echo '<p class="cg-form__notice" role="status">' . esc_html__( 'Thank you! Your message has been sent.', 'generatepress-child' ) . '</p>';
	} elseif ( 'error' === $status ) {
		$err_msg = ! empty( $flash['error'] ) ? $flash['error'] : __( 'Sorry, something went wrong. Please check the fields and try again.', 'generatepress-child' );
		echo '<p class="cg-form__notice cg-form__notice--error" role="alert">' . esc_html( $err_msg ) . '</p>';
	}
	?>
	<form class="cg-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="cg_contact">
		<input type="hidden" name="cg_redirect" value="<?php echo esc_url( trailingslashit( home_url( $wp->request ) ) ); // Back to this page. ?>">
		<?php wp_nonce_field( 'cg_contact', 'cg_nonce' ); ?>
		<div class="cg-form__hp" aria-hidden="true"><label>Leave empty<input type="text" name="cg_website" tabindex="-1" autocomplete="off"></label></div>

		<div class="cg-form__row">
			<label class="cg-form__field">Your Name<span class="cg-req" aria-hidden="true">*</span>:
				<input type="text" name="cg_name" value="<?php echo esc_attr( $flash['name'] ?? '' ); ?>" required autocomplete="name" <?php echo cg_text_attrs( 'name' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped. ?>>
			</label>
			<label class="cg-form__field">Phone:
				<input type="tel" name="cg_phone" value="<?php echo esc_attr( $flash['phone'] ?? '' ); ?>" autocomplete="tel" <?php echo cg_phone_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped. ?>>
			</label>
		</div>
		<label class="cg-form__field">Email Address<span class="cg-req" aria-hidden="true">*</span>:
			<input type="email" name="cg_email" value="<?php echo esc_attr( $flash['email'] ?? '' ); ?>" required autocomplete="email">
		</label>
		<label class="cg-form__field">Message<span class="cg-req" aria-hidden="true">*</span>:
			<textarea name="cg_message" required><?php echo esc_textarea( $flash['message'] ?? '' ); ?></textarea>
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

	$saved = 0;
	$raw_name = isset( $_POST['cg_name'] ) ? (string) wp_unslash( $_POST['cg_name'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- only checked for < >.
	if ( $ok && $name && cg_is_valid_text( 'name', $raw_name, $name ) && is_email( $email ) && $message && ( '' === $phone || cg_is_phone( $phone ) ) ) {
		$saved = cg_save_entry( 'contact', array( 'Name' => $name, 'Phone' => $phone, 'Email' => $email, 'Message' => $message ), $name, $email, $phone );
		$sent  = cg_mail_admin(
			sprintf( '[%s] New contact message from %s', wp_specialchars_decode( get_bloginfo( 'name' ) ), $name ),
			__( 'New contact message', 'generatepress-child' ),
			array( 'Name' => $name, 'Phone' => $phone, 'Email' => $email, 'Message' => $message ),
			$name,
			$email,
			$saved
		);
		if ( $saved && $sent ) {
			update_post_meta( $saved, '_cg_mailed', 1 );
		}
	}

	$token = '';
	if ( ! $saved ) {
		$token = wp_generate_password( 16, false );
		set_transient(
			'cg_contact_err_' . $token,
			array(
				'name'    => $name,
				'phone'   => $phone,
				'email'   => $email,
				'message' => $message,
				'error'   => ! is_email( $email ) ? __( 'Please enter a valid email address.', 'generatepress-child' ) : __( 'Please check your information and try again.', 'generatepress-child' ),
			),
			300
		);
	}

	$args = array( 'cg_contact' => $saved ? 'sent' : 'error' );
	if ( $token ) {
		$args['cg_ctok'] = $token;
	}

	wp_safe_redirect( add_query_arg( $args, $redirect ) . '#contact' );
	exit;
}
add_action( 'admin_post_nopriv_cg_contact', 'cg_handle_contact' );
add_action( 'admin_post_cg_contact', 'cg_handle_contact' );
