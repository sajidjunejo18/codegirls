<?php
/**
 * Course popups: "Active Enrollment" (Enroll Now) and "Upcoming Course" (Notify Me).
 * Native <dialog> elements: focus trap, Escape to close and backdrop come from the browser.
 * Buttons open them via data attributes (see assets/js/main.js):
 *   data-cg-enroll data-course="…" data-slots="Slot A|Slot B"
 *   data-cg-notify data-course="…"
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

global $wp;
$redirect  = trailingslashit( home_url( $wp->request ) ); // This page, so the visitor lands back here.
$education = cg_education_options();
$relations = cg_relation_options();

/**
 * Print a labelled text/number/tel/email input.
 */
$field = static function ( $form, $key, $label, $type = 'text', $req = true, $attrs = '' ) {
	printf(
		'<div class="cg-modal__field"><label for="cg_%1$s_%2$s">%3$s</label><input id="cg_%1$s_%2$s" name="cg_%1$s_%2$s" type="%4$s"%5$s %6$s></div>',
		esc_attr( $form ),
		esc_attr( $key ),
		esc_html( $label ),
		esc_attr( $type ),
		$req ? ' required' : '',
		$attrs // phpcs:ignore WordPress.Security.EscapeOutput -- fixed literals only.
	);
};

/**
 * Print a labelled select.
 */
$select = static function ( $form, $key, $label, $options, $placeholder, $req = true ) {
	printf( '<div class="cg-modal__field"><label for="cg_%1$s_%2$s">%3$s</label><select id="cg_%1$s_%2$s" name="cg_%1$s_%2$s"%4$s><option value="">%5$s</option>', esc_attr( $form ), esc_attr( $key ), esc_html( $label ), $req ? ' required' : '', esc_html( $placeholder ) );
	foreach ( $options as $o ) {
		printf( '<option value="%1$s">%1$s</option>', esc_attr( $o ) );
	}
	echo '</select></div>';
};

$hidden = static function ( $form ) use ( $redirect ) {
	echo '<input type="hidden" name="action" value="cg_form">';
	printf( '<input type="hidden" name="cg_form_id" value="%s">', esc_attr( $form ) );
	printf( '<input type="hidden" name="cg_redirect" value="%s">', esc_url( $redirect ) );
	wp_nonce_field( 'cg_form_' . $form, 'cg_nonce', false );
	echo '<div class="cg-form__hp" aria-hidden="true"><label>Leave empty<input type="text" name="cg_website" tabindex="-1" autocomplete="off"></label></div>';
};
?>
<dialog class="cg-modal" id="cg-enroll" aria-labelledby="cg-enroll-title">
	<form class="cg-modal__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<header class="cg-modal__head">
			<p class="cg-modal__eyebrow"><?php esc_html_e( 'Active Enrollment', 'generatepress-child' ); ?></p>
			<h2 class="cg-modal__title" id="cg-enroll-title" data-cg-title><?php esc_html_e( 'Course Enrollment', 'generatepress-child' ); ?></h2>
			<button class="cg-modal__close" type="button" data-cg-close aria-label="<?php esc_attr_e( 'Close', 'generatepress-child' ); ?>"></button>
		</header>
		<div class="cg-modal__body">
			<?php $hidden( 'enroll' ); ?>
			<input type="hidden" name="cg_enroll_course" data-cg-course>
			<div class="cg-modal__grid">
				<?php
				$field( 'enroll', 'name', 'Your Name:', 'text', true, 'autocomplete="name" autofocus' );
				$field( 'enroll', 'age', 'Age', 'number', true, 'min="10" max="80" inputmode="numeric"' );
				$field( 'enroll', 'city', 'City/Country', 'text', true, 'autocomplete="address-level2"' );
				$field( 'enroll', 'phone', 'Contact No.', 'tel', true, 'autocomplete="tel"' );
				$field( 'enroll', 'email', 'Email', 'email', true, 'autocomplete="email"' );
				$select( 'enroll', 'education', 'Highest Education', $education, 'Select your education' );
				?>
			</div>
			<div class="cg-modal__grid cg-modal__grid--2">
				<div class="cg-modal__field">
					<label for="cg_enroll_slot"><?php esc_html_e( 'Available Slots', 'generatepress-child' ); ?> <span data-cg-phase></span></label>
					<select id="cg_enroll_slot" name="cg_enroll_slot" required data-cg-slots><option value=""><?php esc_html_e( 'Select your Slot', 'generatepress-child' ); ?></option></select>
				</div>
				<fieldset class="cg-modal__field cg-modal__radio">
					<legend><?php esc_html_e( 'Basic computer usage awareness?', 'generatepress-child' ); ?></legend>
					<label><input type="radio" name="cg_enroll_computer" value="Yes" required> <?php esc_html_e( 'Yes', 'generatepress-child' ); ?></label>
					<label><input type="radio" name="cg_enroll_computer" value="No"> <?php esc_html_e( 'No', 'generatepress-child' ); ?></label>
				</fieldset>
			</div>
			<fieldset class="cg-modal__emergency">
				<legend><?php esc_html_e( 'Emergency Contact', 'generatepress-child' ); ?></legend>
				<div class="cg-modal__grid">
					<?php
					$field( 'enroll', 'ec_name', 'Contact Name', 'text', true );
					$select( 'enroll', 'ec_relation', 'Relationship', $relations, 'Select relationship' );
					$field( 'enroll', 'referral', 'Referral Source', 'text', false, 'placeholder="How did you hear about us?"' );
					?>
				</div>
			</fieldset>
			<div class="cg-modal__actions"><button class="cg-btn" type="submit"><?php esc_html_e( 'Submit Application', 'generatepress-child' ); ?></button></div>
		</div>
	</form>
</dialog>

<dialog class="cg-modal" id="cg-notify" aria-labelledby="cg-notify-title">
	<form class="cg-modal__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<header class="cg-modal__head">
			<p class="cg-modal__eyebrow"><?php esc_html_e( 'Upcoming Course', 'generatepress-child' ); ?></p>
			<h2 class="cg-modal__title" id="cg-notify-title" data-cg-title><?php esc_html_e( 'Upcoming Course', 'generatepress-child' ); ?></h2>
			<p class="cg-modal__sub"><?php esc_html_e( 'You’ll be notified once Admissions are open.', 'generatepress-child' ); ?></p>
			<button class="cg-modal__close" type="button" data-cg-close aria-label="<?php esc_attr_e( 'Close', 'generatepress-child' ); ?>"></button>
		</header>
		<div class="cg-modal__body">
			<?php $hidden( 'notify' ); ?>
			<input type="hidden" name="cg_notify_course" data-cg-course>
			<div class="cg-modal__grid">
				<?php
				$field( 'notify', 'name', 'Your Name:', 'text', true, 'autocomplete="name" autofocus' );
				$field( 'notify', 'age', 'Age', 'number', true, 'min="10" max="80" inputmode="numeric"' );
				$field( 'notify', 'city', 'City/Country', 'text', true, 'autocomplete="address-level2"' );
				$field( 'notify', 'phone', 'Contact No.', 'tel', true, 'autocomplete="tel"' );
				$field( 'notify', 'email', 'Email', 'email', true, 'autocomplete="email"' );
				$select( 'notify', 'education', 'Highest Education', $education, 'Select your education' );
				?>
			</div>
			<div class="cg-modal__actions"><button class="cg-btn" type="submit"><?php esc_html_e( 'Get Notified', 'generatepress-child' ); ?></button></div>
		</div>
	</form>
</dialog>
