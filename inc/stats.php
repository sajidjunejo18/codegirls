<?php
/**
 * Site Stats: one place to manage the "2000+ Total Trainings", "70% Placement Rate",
 * "55K+ Average Monthly Salary" numbers shown on the homepage hero and the
 * Hire a Codegirl page. Settings → Site Stats.
 *
 * Each stat is a number + a short suffix (stored separately so the number can count up
 * from 0 on the front end) and a label. The salary stat also has an optional small note
 * under it (e.g. "*Reported in 2025").
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/** The three stats, in display order, with their defaults (the current Figma numbers). */
function cg_stats_fields() {
	return array(
		'trainings' => array(
			'number_default' => 2000,
			'suffix_default' => '+',
			'label_default'  => 'Total Trainings',
		),
		'placement' => array(
			'number_default' => 70,
			'suffix_default' => '%',
			'label_default'  => 'Placement Rate',
		),
		'salary'    => array(
			'number_default' => 55,
			'suffix_default' => 'K+',
			'label_default'  => 'Average Monthly Salary',
			'has_note'       => true,
			'note_default'   => '*Reported in 2025',
		),
	);
}

/** One stat's saved values (number, suffix, label, note), falling back to its defaults. */
function cg_stat( $key ) {
	$fields = cg_stats_fields();
	if ( ! isset( $fields[ $key ] ) ) {
		return array( 'number' => 0, 'suffix' => '', 'label' => '', 'note' => '' );
	}
	$f = $fields[ $key ];
	return array(
		'number' => (int) get_option( "cg_stat_{$key}_number", $f['number_default'] ),
		'suffix' => (string) get_option( "cg_stat_{$key}_suffix", $f['suffix_default'] ),
		'label'  => (string) get_option( "cg_stat_{$key}_label", $f['label_default'] ),
		'note'   => ! empty( $f['has_note'] ) ? (string) get_option( "cg_stat_{$key}_note", $f['note_default'] ) : '',
	);
}

/** "2000+" style number+suffix for a stat, ready to print. */
function cg_stat_display( $key ) {
	$s = cg_stat( $key );
	return $s['number'] . $s['suffix'];
}

/**
 * The stat <p>, with data-cg-count-* attributes the front-end script reads to count up
 * from 0. Every place a stat number is shown should go through this function, so a change
 * in Site Stats updates all of them.
 */
function cg_stat_number_html( $key, $class ) {
	$s = cg_stat( $key );
	return sprintf(
		'<p class="%1$s cg-counter" data-cg-count-end="%2$d" data-cg-count-suffix="%3$s">%2$d%3$s</p>',
		esc_attr( $class ),
		(int) $s['number'],
		esc_attr( $s['suffix'] )
	);
}

/**
 * [cg_stat_number key="trainings" class="gb-text hero__stat-num"]
 * The counting number, as a full <p>. Used on both the homepage hero and the
 * Hire a Codegirl page so one Site Stats value feeds both places.
 */
add_shortcode(
	'cg_stat_number',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'key' => '', 'class' => 'cg-stat-num' ), $atts, 'cg_stat_number' );
		return cg_stat_number_html( sanitize_key( $atts['key'] ), $atts['class'] );
	}
);

/** [cg_stat_label key="trainings"]  plain label text, for use inside an existing <p>. */
add_shortcode(
	'cg_stat_label',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'key' => '' ), $atts, 'cg_stat_label' );
		return esc_html( cg_stat( sanitize_key( $atts['key'] ) )['label'] );
	}
);

/** [cg_stat_note key="salary" class="gb-text hire-stats__note"]  the small note, or '' if none set. */
add_shortcode(
	'cg_stat_note',
	function ( $atts ) {
		$atts = shortcode_atts( array( 'key' => '', 'class' => 'cg-stat-note' ), $atts, 'cg_stat_note' );
		$note = cg_stat( sanitize_key( $atts['key'] ) )['note'];
		return $note ? '<p class="' . esc_attr( $atts['class'] ) . '">' . esc_html( $note ) . '</p>' : '';
	}
);

add_action(
	'admin_menu',
	function () {
		add_options_page(
			__( 'Site Stats', 'generatepress-child' ),
			__( 'Site Stats', 'generatepress-child' ),
			'manage_options',
			'cg-site-stats',
			'cg_stats_page'
		);
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( 'settings_page_cg-site-stats' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'cg-voice-editor', CG_URI . '/assets/admin/voice-editor.css', array(), filemtime( CG_DIR . '/assets/admin/voice-editor.css' ) );
		wp_enqueue_style( 'cg-stats-editor', CG_URI . '/assets/admin/stats-editor.css', array( 'cg-voice-editor' ), filemtime( CG_DIR . '/assets/admin/stats-editor.css' ) );
	}
);

function cg_stats_nonce_ok() {
	return ! empty( $_POST['cg_stats_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cg_stats_nonce'] ) ), 'cg_stats_save' );
}

function cg_stats_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$saved = false;
	if ( cg_stats_nonce_ok() ) {
		foreach ( cg_stats_fields() as $key => $f ) {
			update_option( "cg_stat_{$key}_number", isset( $_POST[ "cg_{$key}_number" ] ) ? absint( wp_unslash( $_POST[ "cg_{$key}_number" ] ) ) : 0 );
			update_option( "cg_stat_{$key}_suffix", isset( $_POST[ "cg_{$key}_suffix" ] ) ? sanitize_text_field( wp_unslash( $_POST[ "cg_{$key}_suffix" ] ) ) : '' );
			update_option( "cg_stat_{$key}_label", isset( $_POST[ "cg_{$key}_label" ] ) ? sanitize_text_field( wp_unslash( $_POST[ "cg_{$key}_label" ] ) ) : '' );
			if ( ! empty( $f['has_note'] ) ) {
				update_option( "cg_stat_{$key}_note", isset( $_POST[ "cg_{$key}_note" ] ) ? sanitize_text_field( wp_unslash( $_POST[ "cg_{$key}_note" ] ) ) : '' );
			}
		}
		$saved = true;
	}
	?>
	<div class="wrap cgv-screen">
		<div class="cgv-titlebar">
			<div>
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Site Stats', 'generatepress-child' ); ?></h1>
				<p class="cgv-head__sub"><?php esc_html_e( 'These three numbers show on the homepage hero and the Hire a Codegirl page. Change a number here and it updates in both places.', 'generatepress-child' ); ?></p>
			</div>
		</div>
		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Saved.', 'generatepress-child' ); ?></p></div>
		<?php endif; ?>
		<form method="post">
			<?php wp_nonce_field( 'cg_stats_save', 'cg_stats_nonce' ); ?>
			<div class="cgv">
				<?php foreach ( cg_stats_fields() as $key => $f ) : $s = cg_stat( $key ); ?>
					<section class="cgv-card">
						<h2 class="cgv-step"><em><?php echo esc_html( $s['number'] . $s['suffix'] ); ?></em> <?php echo esc_html( $s['label'] ); ?></h2>
						<div class="cgs-row">
							<div>
								<label class="cgv-label" for="cg_<?php echo esc_attr( $key ); ?>_number"><?php esc_html_e( 'Number', 'generatepress-child' ); ?></label>
								<input type="number" min="0" step="1" id="cg_<?php echo esc_attr( $key ); ?>_number" name="cg_<?php echo esc_attr( $key ); ?>_number" value="<?php echo esc_attr( $s['number'] ); ?>">
							</div>
							<div>
								<label class="cgv-label" for="cg_<?php echo esc_attr( $key ); ?>_suffix"><?php esc_html_e( 'Suffix', 'generatepress-child' ); ?></label>
								<input type="text" id="cg_<?php echo esc_attr( $key ); ?>_suffix" name="cg_<?php echo esc_attr( $key ); ?>_suffix" value="<?php echo esc_attr( $s['suffix'] ); ?>" placeholder="e.g. + or % or K+" maxlength="6">
							</div>
							<div>
								<label class="cgv-label" for="cg_<?php echo esc_attr( $key ); ?>_label"><?php esc_html_e( 'Label', 'generatepress-child' ); ?></label>
								<input type="text" id="cg_<?php echo esc_attr( $key ); ?>_label" name="cg_<?php echo esc_attr( $key ); ?>_label" value="<?php echo esc_attr( $s['label'] ); ?>">
							</div>
						</div>
						<?php if ( ! empty( $f['has_note'] ) ) : ?>
							<div class="cgs-row cgs-row--note">
								<div>
									<label class="cgv-label" for="cg_<?php echo esc_attr( $key ); ?>_note"><?php esc_html_e( 'Small note', 'generatepress-child' ); ?> <span class="cgv-opt"><?php esc_html_e( 'optional', 'generatepress-child' ); ?></span></label>
									<input type="text" id="cg_<?php echo esc_attr( $key ); ?>_note" name="cg_<?php echo esc_attr( $key ); ?>_note" value="<?php echo esc_attr( $s['note'] ); ?>" placeholder="*Reported in 2025">
									<p class="cgv-hint"><?php esc_html_e( 'Shown under this number on the Hire a Codegirl page only.', 'generatepress-child' ); ?></p>
								</div>
							</div>
						<?php endif; ?>
					</section>
				<?php endforeach; ?>
				<button type="submit" class="cgv-btn cgv-btn--primary"><?php esc_html_e( 'Save changes', 'generatepress-child' ); ?></button>
			</div>
		</form>
	</div>
	<?php
}
