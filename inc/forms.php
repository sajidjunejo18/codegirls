<?php
/**
 * Config-driven enquiry forms (no plugin): [cg_hire_form], [cg_trainer_form].
 * Submissions are emailed to the site admin address (Settings → General).
 * Optional file upload: doc/docx, max 2MB, attached to the email and then deleted.
 *
 * Field spec: key => [ label, type (text|email|tel|url|date|select|file), required, options|null, full-width? ]
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/** Dropdown choices shared by the popup forms (markup) and the handler (validation). */
function cg_education_options() {
	return array( 'Matric', 'Intermediate (12th grade)', 'Bachelors in CS', 'Bachelors (other field)', 'Masters', 'Other' );
}

function cg_relation_options() {
	return array( 'Father', 'Mother', 'Brother', 'Sister', 'Husband', 'Other' );
}

function cg_forms_config() {
	return array(
		'hire'    => array(
			'subject'  => 'Hire request',
			'fields'   => array(
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
			),
			'textarea' => 'Job Description or Upload Document ( Max 2MB ) ( doc,docx ):',
			'file'     => true,
			'thanks'   => 'Thank you! We have received your request and will be in touch soon.',
		),
		// Popup forms opened from the course cards (markup lives in parts/enroll-modals.php).
		'enroll'  => array(
			'subject' => 'Enrollment application',
			'anchor'  => 'courses',
			'thanks'  => 'Thank you! Your application has been received. Our team will contact you soon.',
			'fields'  => array(
				'course'      => array( 'Course:', 'text', false ),
				'name'        => array( 'Your Name:', 'text', true ),
				'age'         => array( 'Age:', 'text', true ),
				'city'        => array( 'City/Country:', 'text', true ),
				'phone'       => array( 'Contact No.:', 'tel', true ),
				'email'       => array( 'Email:', 'email', true ),
				'education'   => array( 'Highest Education:', 'select', true, cg_education_options() ),
				'slot'        => array( 'Available Slot:', 'select', true ),
				'computer'    => array( 'Basic computer usage awareness:', 'radio', true ),
				'ec_name'     => array( 'Emergency Contact Name:', 'text', true ),
				'ec_relation' => array( 'Emergency Contact Relationship:', 'select', true, cg_relation_options() ),
				'referral'    => array( 'Referral Source:', 'text', false ),
			),
		),
		'notify'  => array(
			'subject' => 'Course notification request',
			'anchor'  => 'upcoming',
			'thanks'  => 'Thank you! We will notify you as soon as admissions open.',
			'fields'  => array(
				'course'    => array( 'Course:', 'text', false ),
				'name'      => array( 'Your Name:', 'text', true ),
				'age'       => array( 'Age:', 'text', true ),
				'city'      => array( 'City/Country:', 'text', true ),
				'phone'     => array( 'Contact No.:', 'tel', true ),
				'email'     => array( 'Email:', 'email', true ),
				'education' => array( 'Highest Education:', 'select', true, cg_education_options() ),
			),
		),
		'trainer' => array(
			'subject' => 'Trainer application',
			'fields'  => array(
				'name'     => array( 'Your Name:', 'text', true ),
				'email'    => array( 'Email Address:', 'email', true ),
				'org'      => array( 'Organization / Institute:', 'text', true, null, true ),
				'role'     => array( 'Designation:', 'text', true ),
				'city'     => array( 'City:', 'text', true ),
				'position' => array( 'Position Applied For:', 'text', true ),
				'resume'   => array( 'Upload your resume ( Max 2MB ) ( doc,docx ):', 'file', true ),
			),
			'file'    => true,
			'thanks'  => 'Thank you! We have received your application and will be in touch soon.',
		),
	);
}

function cg_form_field_html( $form, $key, $f ) {
	$id  = "cg_{$form}_{$key}";
	$req = ! empty( $f[2] );
	echo '<div class="cg-form__field' . ( $req ? ' is-required' : '' ) . ( ! empty( $f[4] ) ? ' cg-form__field--full' : '' ) . '"><label for="' . esc_attr( $id ) . '">' . esc_html( $f[0] ) . '</label>';
	if ( 'select' === $f[1] ) {
		echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '"' . ( $req ? ' required' : '' ) . '><option value="">Select…</option>';
		foreach ( $f[3] as $opt ) {
			echo '<option>' . esc_html( $opt ) . '</option>';
		}
		echo '</select>';
	} elseif ( 'file' === $f[1] ) {
		echo '<div class="cg-form__filebox"><input class="cg-form__file" id="' . esc_attr( $id ) . '" type="file" name="cg_file" accept=".doc,.docx"' . ( $req ? ' required' : '' ) . '><label class="cg-form__file-btn" for="' . esc_attr( $id ) . '">' . esc_html__( 'Choose File', 'generatepress-child' ) . '</label><span class="cg-form__file-name">' . esc_html__( 'No File Chosen', 'generatepress-child' ) . '</span></div>';
	} else {
		echo '<input id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" type="' . esc_attr( $f[1] ) . '"' . ( $req ? ' required' : '' ) . '>';
	}
	echo '</div>';
}

function cg_form_render( $form ) {
	$cfg    = cg_forms_config()[ $form ];
	$status = isset( $_GET['cg_form'] ) ? sanitize_key( wp_unslash( $_GET['cg_form'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$only   = isset( $_GET['cg_id'] ) ? sanitize_key( wp_unslash( $_GET['cg_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	ob_start();

	if ( $only === $form && 'sent' === $status ) {
		echo '<p class="cg-form__notice" role="status">' . esc_html( $cfg['thanks'] ) . '</p>';
	} elseif ( $only === $form && 'error' === $status ) {
		echo '<p class="cg-form__notice cg-form__notice--error" role="alert">' . esc_html__( 'Sorry, we could not send your message. Please check the fields (and that any file is a .doc/.docx under 2MB) and try again.', 'generatepress-child' ) . '</p>';
	}
	?>
	<form class="cg-form cg-form--hire" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="cg_form">
		<input type="hidden" name="cg_form_id" value="<?php echo esc_attr( $form ); ?>">
		<input type="hidden" name="cg_redirect" value="<?php echo esc_url( get_permalink() ? get_permalink() : home_url( '/' ) ); ?>">
		<?php wp_nonce_field( 'cg_form_' . $form, 'cg_nonce' ); ?>
		<div class="cg-form__hp" aria-hidden="true"><label>Leave empty<input type="text" name="cg_website" tabindex="-1" autocomplete="off"></label></div>
		<?php
		$row = array();
		foreach ( $cfg['fields'] as $key => $f ) {
			if ( ! empty( $f[4] ) ) { // Full-width field: flush the open row first.
				if ( $row ) {
					echo '<div class="cg-form__row">' . implode( '', $row ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
					$row = array();
				}
				echo '<div class="cg-form__row">';
				cg_form_field_html( $form, $key, $f );
				echo '</div>';
				continue;
			}
			ob_start();
			cg_form_field_html( $form, $key, $f );
			$row[] = ob_get_clean();
			if ( 2 === count( $row ) ) {
				echo '<div class="cg-form__row">' . implode( '', $row ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
				$row = array();
			}
		}
		if ( $row ) {
			echo '<div class="cg-form__row">' . implode( '', $row ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}

		if ( ! empty( $cfg['textarea'] ) ) {
			echo '<div class="cg-form__field is-required"><label for="cg_' . esc_attr( $form ) . '_description">' . esc_html( $cfg['textarea'] ) . '</label><textarea id="cg_' . esc_attr( $form ) . '_description" name="cg_' . esc_attr( $form ) . '_description"></textarea></div>';
			echo '<div class="cg-form__actions"><input class="cg-form__file" id="cg_' . esc_attr( $form ) . '_file" type="file" name="cg_file" accept=".doc,.docx"><label class="cg-form__file-btn" for="cg_' . esc_attr( $form ) . '_file">' . esc_html__( 'Choose File', 'generatepress-child' ) . '</label><span class="cg-form__file-name">' . esc_html__( 'No File Chosen', 'generatepress-child' ) . '</span><button class="cg-btn cg-form__submit" type="submit">' . esc_html__( 'Submit', 'generatepress-child' ) . '</button></div>';
		} else {
			echo '<div class="cg-form__actions cg-form__actions--end"><button class="cg-btn cg-form__submit" type="submit">' . esc_html__( 'Submit', 'generatepress-child' ) . '</button></div>';
		}
		?>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode(
	'cg_hire_form',
	function () {
		return cg_form_render( 'hire' );
	}
);
add_shortcode(
	'cg_trainer_form',
	function () {
		return cg_form_render( 'trainer' );
	}
);

function cg_handle_form() {
	$form = isset( $_POST['cg_form_id'] ) ? sanitize_key( wp_unslash( $_POST['cg_form_id'] ) ) : '';
	$all  = cg_forms_config();
	$redirect = isset( $_POST['cg_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['cg_redirect'] ) ) : home_url( '/' );
	$redirect = wp_validate_redirect( $redirect, home_url( '/' ) );

	if ( ! isset( $all[ $form ] ) ) {
		wp_safe_redirect( $redirect );
		exit;
	}
	$cfg = $all[ $form ];

	$ok = isset( $_POST['cg_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cg_nonce'] ) ), 'cg_form_' . $form );
	$ok = $ok && empty( $_POST['cg_website'] ); // Honeypot.

	$lines = array();
	$name  = '';
	$email = '';
	foreach ( $cfg['fields'] as $key => $f ) {
		if ( 'file' === $f[1] ) {
			continue;
		}
		$val = isset( $_POST[ "cg_{$form}_{$key}" ] ) ? sanitize_text_field( wp_unslash( $_POST[ "cg_{$form}_{$key}" ] ) ) : '';
		if ( ! empty( $f[2] ) && '' === $val ) {
			$ok = false;
		}
		if ( 'select' === $f[1] && ! empty( $f[3] ) && '' !== $val && ! in_array( $val, $f[3], true ) ) {
			$ok = false; // Value is not one of the offered choices.
		}
		if ( 'radio' === $f[1] && '' !== $val && ! in_array( $val, array( 'Yes', 'No' ), true ) ) {
			$ok = false;
		}
		if ( 'name' === $key ) {
			$name = $val;
		}
		if ( 'email' === $key ) {
			$email = sanitize_email( $val );
		}
		$lines[] = rtrim( $f[0], ':' ) . ': ' . $val;
	}
	$ok = $ok && is_email( $email );

	if ( ! empty( $cfg['textarea'] ) ) {
		$desc    = isset( $_POST[ "cg_{$form}_description" ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ "cg_{$form}_description" ] ) ) : '';
		$lines[] = "\nDescription:\n" . $desc;
	}

	// A required file field (e.g. the trainer resume) must actually be uploaded.
	foreach ( $cfg['fields'] as $f ) {
		if ( 'file' === $f[1] && ! empty( $f[2] ) && empty( $_FILES['cg_file']['name'] ) ) {
			$ok = false;
		}
	}

	$attachments = array();
	$uploaded    = '';
	if ( $ok && ! empty( $cfg['file'] ) && ! empty( $_FILES['cg_file']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$file = $_FILES['cg_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
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
			sprintf( '[%s] %s from %s', wp_specialchars_decode( get_bloginfo( 'name' ) ), $cfg['subject'], $name ),
			implode( "\n", $lines ),
			array( 'Reply-To: ' . $name . ' <' . $email . '>' ),
			$attachments
		);
	}
	if ( $uploaded && file_exists( $uploaded ) ) {
		wp_delete_file( $uploaded ); // Not kept on the server once emailed.
	}

	wp_safe_redirect( add_query_arg( array( 'cg_form' => $sent ? 'sent' : 'error', 'cg_id' => $form ), $redirect ) . '#' . ( isset( $cfg['anchor'] ) ? $cfg['anchor'] : 'form' ) );
	exit;
}
add_action( 'admin_post_nopriv_cg_form', 'cg_handle_form' );
add_action( 'admin_post_cg_form', 'cg_handle_form' );
