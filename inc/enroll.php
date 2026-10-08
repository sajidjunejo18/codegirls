<?php
/**
 * Course popups (Enroll Now / Notify Me) and the thank-you/error toast.
 * Popup markup: parts/enroll-modals.php. Handler: inc/forms.php (forms "enroll" and "notify").
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pages that carry course cards: the homepage and the Courses page.
 */
function cg_has_course_modals() {
	return is_front_page() || is_page( 'courses' );
}

function cg_render_enroll_modals() {
	if ( ! cg_has_course_modals() ) {
		return;
	}
	get_template_part( 'parts/enroll-modals' );

	// Result toast after a popup form was submitted (the handler redirects back with ?cg_form=…&cg_id=…).
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$status = isset( $_GET['cg_form'] ) ? sanitize_key( wp_unslash( $_GET['cg_form'] ) ) : '';
	$id     = isset( $_GET['cg_id'] ) ? sanitize_key( wp_unslash( $_GET['cg_id'] ) ) : '';
	// phpcs:enable
	if ( ! in_array( $id, array( 'enroll', 'notify' ), true ) || ! in_array( $status, array( 'sent', 'error' ), true ) ) {
		return;
	}
	$cfg = cg_forms_config();
	if ( 'sent' === $status ) {
		$msg = $cfg[ $id ]['thanks'];
	} else {
		$msg = __( 'Sorry, we could not send your details. Please check the fields and try again.', 'generatepress-child' );
	}
	printf(
		'<div class="cg-toast cg-toast--%1$s" role="%2$s" data-cg-toast><span>%3$s</span><button type="button" class="cg-toast__close" aria-label="%4$s" data-cg-toast-close></button></div>',
		esc_attr( $status ),
		'sent' === $status ? 'status' : 'alert',
		esc_html( $msg ),
		esc_attr__( 'Dismiss', 'generatepress-child' )
	);
}
add_action( 'wp_footer', 'cg_render_enroll_modals', 5 );
