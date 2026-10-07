<?php
/**
 * "Hire a Codegirl" employer enquiry form: [cg_hire_form] + admin-post handler.
 * Emails the site admin; an optional job description file (doc/docx, max 2MB) is attached.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Form fields: name => [ label, type, required, options ].
 */
function cg_hire_fields() {
	return array(
		'name'        => array( 'Your Name:', 'text', true ),
		'company'     => array( 'Company Name:', 'text', true ),
		'website'     => array( 'Company Website:', 'url', true ),
		'email'       => array( 'Work Email Address:', 'email', true ),
		'phone'       => array( 'Work Phone Number:', 'tel', true ),
		'arrangement' => array( 'Work Arrangement:', 'select', true, array( 'On Site', 'Hybrid', 'Remote' ) ),
		'job_title'   => array( 'Job Title:', 'text', true ),
		'skills'      => array( 'Required Skills:', 'text', true ),
		'education'   => array( 'Minimum Education:', 'select', true, array( 'Intermediate', 'Bachelor’s', 'Master’s' ) ),
		'apply_by'    => array( 'Apply By Date:', 'date', true ),
	);
}

function cg_hire_field_html( $key, $f ) {
	$id  = 'cg_hire_' . $key;
	$req = ! empty( $f[2] );
	echo '<label class="cg-form__field' . ( $req ? ' is-required' : '' ) . '" for="' . esc_attr( $id ) . '">' . esc_html( $f[0] );
	if ( 'select' === $f[1] ) {
		echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '"' . ( $req ? ' required' : '' ) . '><option value="">Select…</option>';
		foreach ( $f[3] as $opt ) {
			echo '<option>' . esc_html( $opt ) . '</option>';
		}
		echo '</select>';
	} else {
		echo '<input id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" type="' . esc_attr( $f[1] ) . '"' . ( $req ? ' required' : '' ) . '>';
	}
	echo '</label>';
}

function cg_hire_form_shortcode() {
	$status = isset( $_GET['cg_hire'] ) ? sanitize_key( wp_unslash( $_GET['cg_hire'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$fields = cg_hire_fields();
	$keys   = array_keys( $fields );

	ob_start();

	if ( 'sent' === $status ) {
		echo '<p class="cg-form__notice" role="status">' . esc_html__( 'Thank you! We have received your request and will be in touch soon.', 'generatepress-child' ) . '</p>';
	} elseif ( 'error' === $status ) {
		echo '<p class="cg-form__notice cg-form__notice--error" role="alert">' . esc_html__( 'Sorry, we could not send your request. Please check the fields (and that the file is a .doc/.docx under 2MB) and try again.', 'generatepress-child' ) . '</p>';
	}
	?>
	<form class="cg-form cg-form--hire" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="cg_hire">
		<input type="hidden" name="cg_redirect" value="<?php echo esc_url( get_permalink() ? get_permalink() : home_url( '/' ) ); ?>">
		<?php wp_nonce_field( 'cg_hire', 'cg_nonce' ); ?>
		<div class="cg-form__hp" aria-hidden="true"><label>Leave empty<input type="text" name="cg_website" tabindex="-1" autocomplete="off"></label></div>

		<?php
		// Two-column rows of the ten short fields.
		for ( $i = 0; $i < count( $keys ); $i += 2 ) {
			echo '<div class="cg-form__row">';
			cg_hire_field_html( $keys[ $i ], $fields[ $keys[ $i ] ] );
			if ( isset( $keys[ $i + 1 ] ) ) {
				cg_hire_field_html( $keys[ $i + 1 ], $fields[ $keys[ $i + 1 ] ] );
			}
			echo '</div>';
		}
		?>

		<label class="cg-form__field is-required" for="cg_hire_description">Job Description or Upload Document ( Max 2MB ) ( doc,docx ):
			<textarea id="cg_hire_description" name="cg_hire_description"></textarea>
		</label>

		<div class="cg-form__actions">
			<input class="cg-form__file" id="cg_hire_file" type="file" name="cg_hire_file" accept=".doc,.docx">
			<label class="cg-form__file-btn" for="cg_hire_file"><?php esc_html_e( 'Choose File', 'generatepress-child' ); ?></label>
			<span class="cg-form__file-name"><?php esc_html_e( 'No File Chosen', 'generatepress-child' ); ?></span>
			<button class="cg-btn cg-form__submit" type="submit"><?php esc_html_e( 'Submit', 'generatepress-child' ); ?></button>
		</div>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'cg_hire_form', 'cg_hire_form_shortcode' );

function cg_handle_hire() {
	$redirect = isset( $_POST['cg_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['cg_redirect'] ) ) : home_url( '/' );
	$redirect = wp_validate_redirect( $redirect, home_url( '/' ) );

	$ok = isset( $_POST['cg_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cg_nonce'] ) ), 'cg_hire' );
	$ok = $ok && empty( $_POST['cg_website'] ); // Honeypot.

	$lines = array();
	foreach ( cg_hire_fields() as $key => $f ) {
		$val = isset( $_POST[ 'cg_hire_' . $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'cg_hire_' . $key ] ) ) : '';
		if ( ! empty( $f[2] ) && '' === $val ) {
			$ok = false;
		}
		$lines[] = rtrim( $f[0], ':' ) . ': ' . $val;
	}
	$email = isset( $_POST['cg_hire_email'] ) ? sanitize_email( wp_unslash( $_POST['cg_hire_email'] ) ) : '';
	$name  = isset( $_POST['cg_hire_name'] ) ? sanitize_text_field( wp_unslash( $_POST['cg_hire_name'] ) ) : '';
	$ok    = $ok && is_email( $email );

	$description = isset( $_POST['cg_hire_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cg_hire_description'] ) ) : '';
	$lines[]     = "\nJob Description:\n" . $description;

	$attachments = array();
	$uploaded    = '';
	if ( $ok && ! empty( $_FILES['cg_hire_file']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$file = $_FILES['cg_hire_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( $file['size'] > 2 * MB_IN_BYTES ) {
			$ok = false;
		} else {
			$res = wp_handle_upload(
				$file,
				array(
					'test_form' => false,
					'mimes'     => array(
						'doc'  => 'application/msword',
						'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
					),
				)
			);
			if ( ! empty( $res['file'] ) ) {
				$attachments[] = $res['file'];
				$uploaded      = $res['file'];
			} else {
				$ok = false;
			}
		}
	}

	$sent = false;
	if ( $ok ) {
		$sent = wp_mail(
			get_option( 'admin_email' ),
			sprintf( '[%s] Hire request from %s', wp_specialchars_decode( get_bloginfo( 'name' ) ), $name ),
			implode( "\n", $lines ),
			array( 'Reply-To: ' . $name . ' <' . $email . '>' ),
			$attachments
		);
	}
	if ( $uploaded && file_exists( $uploaded ) ) {
		wp_delete_file( $uploaded ); // Not kept on the server once emailed.
	}

	wp_safe_redirect( add_query_arg( 'cg_hire', $sent ? 'sent' : 'error', $redirect ) . '#hire' );
	exit;
}
add_action( 'admin_post_nopriv_cg_hire', 'cg_handle_hire' );
add_action( 'admin_post_cg_hire', 'cg_handle_hire' );
