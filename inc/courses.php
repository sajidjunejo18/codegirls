<?php
/**
 * Courses managed from the WordPress admin ("Courses" menu).
 * The cards on the homepage and the Courses page are rendered from these entries by the
 * [cg_courses] shortcode, so editing a course in the dashboard updates the site.
 *
 *   [cg_courses status="open" limit="3"]        Admission Open cards (Enroll Now popup)
 *   [cg_courses status="upcoming"]               Upcoming cards (Notify Me popup)
 *   [cg_courses status="completed"]              Completed cards (I'm Interested)
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

function cg_course_statuses() {
	return array(
		'open'      => __( 'Admission open', 'generatepress-child' ),
		'upcoming'  => __( 'Upcoming', 'generatepress-child' ),
		'completed' => __( 'Completed', 'generatepress-child' ),
	);
}

/** Allowed values for the phase tag (stored and shown exactly as written). */
function cg_course_phases() {
	return array( 'Phase 1', 'Phase 2', 'Phase 3' );
}

/** Allowed values for "Mode of teaching" (stored and shown exactly as written). */
function cg_course_modes() {
	return array( 'On Site', 'Online' );
}

function cg_register_courses() {
	register_post_type(
		'cg_course',
		array(
			'labels'          => array(
				'name'               => __( 'Courses', 'generatepress-child' ),
				'singular_name'      => __( 'Course', 'generatepress-child' ),
				'add_new_item'       => __( 'Add New Course', 'generatepress-child' ),
				'edit_item'          => __( 'Edit Course', 'generatepress-child' ),
				'featured_image'     => __( 'Course illustration', 'generatepress-child' ),
				'set_featured_image' => __( 'Set course illustration', 'generatepress-child' ),
				'not_found'          => __( 'No courses yet.', 'generatepress-child' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'menu_icon'       => 'dashicons-welcome-learn-more',
			'menu_position'   => 25,
			'supports'        => array( 'title', 'excerpt', 'thumbnail', 'page-attributes' ),
			'capability_type' => 'post',
		)
	);
}
add_action( 'init', 'cg_register_courses' );

/**
 * "Course details" box: every value the cards show comes from these fields.
 */
add_action(
	'add_meta_boxes_cg_course',
	function () {
		add_meta_box( 'cg_course_details', __( 'Course details', 'generatepress-child' ), 'cg_course_box', 'cg_course', 'normal', 'high' );
	}
);

function cg_course_box( $post ) {
	wp_nonce_field( 'cg_course_save', 'cg_course_nonce' );
	$v = static function ( $k, $d = '' ) use ( $post ) {
		$val = get_post_meta( $post->ID, '_cg_' . $k, true );
		return '' === $val ? $d : $val;
	};
	?>
	<style>.cg-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 22px}.cg-fields .full{grid-column:1/-1}.cg-fields label{display:block;font-weight:600;margin-bottom:4px}.cg-fields input[type=text],.cg-fields input[type=date],.cg-fields select,.cg-fields textarea{width:100%}.cg-fields p.description{margin:4px 0 0}</style>
	<style>.cg-weeks{display:flex;align-items:center;gap:8px}.cg-fields .cg-weeks input{width:90px}.cg-weeks span{font-weight:600}</style>
	<style>.cg-point{display:flex;gap:8px;margin-bottom:8px}.cg-point input{flex:1}.cg-point__remove{font-size:18px;line-height:1;padding:0 12px}</style>
	<div class="cg-fields">
		<div>
			<label for="cg_status"><?php esc_html_e( 'Status', 'generatepress-child' ); ?></label>
			<select id="cg_status" name="cg_status">
				<?php foreach ( cg_course_statuses() as $k => $label ) : ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $v( 'status', 'open' ), $k ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Decides where the course appears: Admission Open, Upcoming or Completed.', 'generatepress-child' ); ?></p>
		</div>
		<div>
			<label for="cg_phase"><?php esc_html_e( 'Phase', 'generatepress-child' ); ?></label>
			<select id="cg_phase" name="cg_phase">
				<?php foreach ( cg_course_phases() as $p ) : ?>
					<option value="<?php echo esc_attr( $p ); ?>" <?php selected( $v( 'phase', 'Phase 1' ), $p ); ?>><?php echo esc_html( $p ); ?></option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php esc_html_e( 'Yellow tag on the card.', 'generatepress-child' ); ?></p>
		</div>
		<div>
			<label for="cg_start"><?php esc_html_e( 'Starting from', 'generatepress-child' ); ?></label>
			<input type="date" id="cg_start" name="cg_start" value="<?php echo esc_attr( $v( 'start' ) ); ?>">
			<p class="description"><?php esc_html_e( 'An Upcoming course moves to Admission Open automatically on this date.', 'generatepress-child' ); ?></p>
		</div>
		<div>
			<label for="cg_duration"><?php esc_html_e( 'Duration', 'generatepress-child' ); ?></label>
			<div class="cg-weeks">
				<input type="text" id="cg_duration" name="cg_duration" inputmode="numeric" pattern="[0-9]*" maxlength="3" value="<?php echo esc_attr( (string) absint( $v( 'duration', '12' ) ) ?: '' ); ?>">
				<span><?php esc_html_e( 'Weeks', 'generatepress-child' ); ?></span>
			</div>
		</div>
		<div>
			<label for="cg_mode"><?php esc_html_e( 'Mode of teaching', 'generatepress-child' ); ?></label>
			<select id="cg_mode" name="cg_mode">
				<?php foreach ( cg_course_modes() as $m ) : ?>
					<option value="<?php echo esc_attr( $m ); ?>" <?php selected( $v( 'mode', 'On Site' ), $m ); ?>><?php echo esc_html( $m ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="full">
			<label for="cg_schedule"><?php esc_html_e( 'Class schedule (one slot per line)', 'generatepress-child' ); ?></label>
			<textarea id="cg_schedule" name="cg_schedule" rows="3"><?php echo esc_textarea( $v( 'schedule' ) ); ?></textarea>
			<p class="description"><?php esc_html_e( 'Each line is shown on the card and offered as a choice in the enrollment popup, e.g. "Fri 10:00 AM - 01:00 PM".', 'generatepress-child' ); ?></p>
		</div>
		<div class="full">
			<label><?php esc_html_e( 'What you’ll learn', 'generatepress-child' ); ?></label>
			<div id="cg-learn-list" class="cg-points">
				<?php
				$points = cg_course_lines( $post->ID, 'learn' );
				if ( ! $points ) {
					$points = array( '' );
				}
				foreach ( $points as $pt ) :
					?>
					<div class="cg-point">
						<input type="text" name="cg_learn[]" value="<?php echo esc_attr( $pt ); ?>" placeholder="<?php esc_attr_e( 'e.g. Build REST APIs with ASP.NET Core', 'generatepress-child' ); ?>">
						<button type="button" class="button cg-point__remove" aria-label="<?php esc_attr_e( 'Remove point', 'generatepress-child' ); ?>">&times;</button>
					</div>
				<?php endforeach; ?>
			</div>
			<p><button type="button" class="button button-secondary" id="cg-add-point">+ <?php esc_html_e( 'Add Point', 'generatepress-child' ); ?></button></p>
			<template id="cg-point-tpl">
				<div class="cg-point">
					<input type="text" name="cg_learn[]" value="" placeholder="<?php esc_attr_e( 'e.g. Build REST APIs with ASP.NET Core', 'generatepress-child' ); ?>">
					<button type="button" class="button cg-point__remove" aria-label="<?php esc_attr_e( 'Remove point', 'generatepress-child' ); ?>">&times;</button>
				</div>
			</template>
			<p class="description"><?php esc_html_e( 'Each point shows with a check icon when a visitor opens “What you’ll learn” on the card. Leave all points empty to hide that row.', 'generatepress-child' ); ?></p>
		</div>
	</div>
	<script>
	( function () {
		// Duration takes digits only (typed or pasted).
		var weeks = document.getElementById( 'cg_duration' );
		if ( weeks ) {
			weeks.addEventListener( 'input', function () { weeks.value = weeks.value.replace( /\D+/g, '' ); } );
		}
	}() );
	( function () {
		var list =document.getElementById( 'cg-learn-list' ), tpl = document.getElementById( 'cg-point-tpl' ), add = document.getElementById( 'cg-add-point' );
		if ( ! list || ! tpl || ! add ) { return; }
		function addRow() {
			list.appendChild( tpl.content.cloneNode( true ) );
			var inputs = list.querySelectorAll( 'input' );
			inputs[ inputs.length - 1 ].focus();
		}
		add.addEventListener( 'click', addRow );
		list.addEventListener( 'click', function ( e ) {
			var rm = e.target.closest( '.cg-point__remove' );
			if ( ! rm ) { return; }
			var rows = list.querySelectorAll( '.cg-point' );
			if ( rows.length > 1 ) { rm.closest( '.cg-point' ).remove(); } else { rows[ 0 ].querySelector( 'input' ).value = ''; }
		} );
		// Enter adds the next point instead of submitting the whole course form.
		list.addEventListener( 'keydown', function ( e ) {
			if ( 'Enter' === e.key && e.target.matches( 'input' ) ) { e.preventDefault(); addRow(); }
		} );
	}() );
	</script>
	<p class="description" style="margin-top:14px"><?php esc_html_e( 'The title is the course name, the Excerpt is the short text on Upcoming / Completed cards, and the Course illustration (right side) is the picture. Use “Order” in Page Attributes to arrange cards.', 'generatepress-child' ); ?></p>
	<?php
}

add_action(
	'save_post_cg_course',
	function ( $post_id ) {
		if ( ! isset( $_POST['cg_course_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cg_course_nonce'] ) ), 'cg_course_save' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$status = isset( $_POST['cg_status'] ) ? sanitize_key( wp_unslash( $_POST['cg_status'] ) ) : 'open';
		update_post_meta( $post_id, '_cg_status', array_key_exists( $status, cg_course_statuses() ) ? $status : 'open' );
		$phase = isset( $_POST['cg_phase'] ) ? sanitize_text_field( wp_unslash( $_POST['cg_phase'] ) ) : '';
		update_post_meta( $post_id, '_cg_phase', in_array( $phase, cg_course_phases(), true ) ? $phase : 'Phase 1' );

		// Stored as "12 weeks" so the cards print it as-is.
		$weeks = isset( $_POST['cg_duration'] ) ? absint( preg_replace( '/\D+/', '', wp_unslash( $_POST['cg_duration'] ) ) ) : 0; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		update_post_meta( $post_id, '_cg_duration', $weeks ? sprintf( _n( '%d week', '%d weeks', $weeks, 'generatepress-child' ), $weeks ) : '' );
		$mode = isset( $_POST['cg_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['cg_mode'] ) ) : '';
		update_post_meta( $post_id, '_cg_mode', in_array( $mode, cg_course_modes(), true ) ? $mode : 'On Site' );

		$start = isset( $_POST['cg_start'] ) ? sanitize_text_field( wp_unslash( $_POST['cg_start'] ) ) : '';
		update_post_meta( $post_id, '_cg_start', preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start ) ? $start : '' );
		update_post_meta( $post_id, '_cg_schedule', isset( $_POST['cg_schedule'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cg_schedule'] ) ) : '' );

		$points = isset( $_POST['cg_learn'] ) ? (array) wp_unslash( $_POST['cg_learn'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$points = array_values( array_filter( array_map( 'sanitize_text_field', $points ) ) );
		update_post_meta( $post_id, '_cg_learn', implode( "\n", $points ) );
	}
);

/**
 * Cron: once an Upcoming course's start date arrives (site timezone), switch it to
 * Admission Open so it leaves the Upcoming section and shows with the Enroll Now cards.
 */
add_action(
	'init',
	function () {
		if ( ! wp_next_scheduled( 'cg_open_due_courses' ) ) {
			wp_schedule_event( time(), 'hourly', 'cg_open_due_courses' );
		}
	}
);

add_action(
	'switch_theme',
	function () {
		wp_clear_scheduled_hook( 'cg_open_due_courses' );
	}
);

function cg_open_due_courses() {
	$ids = get_posts(
		array(
			'post_type'      => 'cg_course',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => '_cg_status',
					'value' => 'upcoming',
				),
				array(
					'key'     => '_cg_start',
					'value'   => wp_date( 'Y-m-d' ),
					'compare' => '<=',
					'type'    => 'DATE',
				),
			),
		)
	);
	foreach ( $ids as $id ) {
		update_post_meta( $id, '_cg_status', 'open' );
	}
}
add_action( 'cg_open_due_courses', 'cg_open_due_courses' );

/**
 * Admin list: useful columns, default order = Order field.
 */
add_filter(
	'manage_cg_course_posts_columns',
	function () {
		return array(
			'cb'          => '<input type="checkbox" />',
			'title'       => __( 'Course', 'generatepress-child' ),
			'cg_status'   => __( 'Status', 'generatepress-child' ),
			'cg_phase'    => __( 'Phase', 'generatepress-child' ),
			'cg_start'    => __( 'Starts', 'generatepress-child' ),
			'cg_duration' => __( 'Duration', 'generatepress-child' ),
			'menu_order'  => __( 'Order', 'generatepress-child' ),
		);
	}
);

add_action(
	'manage_cg_course_posts_custom_column',
	function ( $col, $id ) {
		if ( 'cg_status' === $col ) {
			$s = cg_course_statuses();
			$k = get_post_meta( $id, '_cg_status', true );
			echo esc_html( isset( $s[ $k ] ) ? $s[ $k ] : '—' );
		} elseif ( 'menu_order' === $col ) {
			echo (int) get_post_field( 'menu_order', $id );
		} elseif ( 0 === strpos( $col, 'cg_' ) ) {
			$val = get_post_meta( $id, '_' . $col, true );
			echo esc_html( 'cg_start' === $col && $val ? cg_course_date( $val ) : ( $val ?: '—' ) );
		}
	},
	10,
	2
);

add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( is_admin() && $q->is_main_query() && 'cg_course' === $q->get( 'post_type' ) && ! $q->get( 'orderby' ) ) {
			$q->set( 'orderby', 'menu_order title' );
			$q->set( 'order', 'ASC' );
		}
	}
);

/**
 * 2026-09-11 -> "11-Sept-2026" (the design writes September as "Sept").
 */
function cg_course_date( $ymd ) {
	$ts = strtotime( $ymd );
	return $ts ? str_replace( '-Sep-', '-Sept-', gmdate( 'd-M-Y', $ts ) ) : '';
}

/**
 * Lines of a textarea field as a clean array.
 */
function cg_course_lines( $id, $key ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\R/', (string) get_post_meta( $id, '_cg_' . $key, true ) ) ) ) );
}

function cg_course_img( $id, $class ) {
	return has_post_thumbnail( $id ) ? get_the_post_thumbnail( $id, 'large', array( 'class' => $class, 'alt' => '', 'loading' => 'lazy' ) ) : '';
}

/**
 * SVG filter #cg-green, applied to a course illustration on hover. It turns the illustration's
 * light-gray fills (#dedede) brand green and leaves black line art and white untouched, which
 * reproduces the green hover artwork in Figma from the one Course illustration.
 */
function cg_green_filter() {
	// Gray level (0-255) => share of green it takes on, measured against the Figma hover artwork.
	$stops = array( 0 => 0, 205 => 0, 212 => 0.45, 217 => 1, 227 => 1, 232 => 0.6, 238 => 0.3, 243 => 0.1, 246 => 0, 255 => 0 );
	$green = array( 15, 154, 90 );
	$keys  = array_keys( $stops );
	$table = array( array(), array(), array() );
	for ( $v = 0; $v <= 255; $v += 5 ) {
		for ( $k = 1; $v > $keys[ $k ]; ++$k ) {} // phpcs:ignore Generic.CodeAnalysis.EmptyStatement
		$a = $keys[ $k - 1 ];
		$b = $keys[ $k ];
		$w = $stops[ $a ] + ( $stops[ $b ] - $stops[ $a ] ) * ( $v - $a ) / ( $b - $a );
		foreach ( $green as $c => $g ) {
			$table[ $c ][] = round( ( ( 1 - $w ) * $v + $w * $g ) / 255, 4 );
		}
	}
	$funcs = '';
	foreach ( array( 'R', 'G', 'B' ) as $c => $ch ) {
		$funcs .= '<feFunc' . $ch . ' type="table" tableValues="' . implode( ' ', $table[ $c ] ) . '"/>';
	}
	return '<svg class="cg-svg-defs" width="0" height="0" aria-hidden="true" focusable="false"><filter id="cg-green" color-interpolation-filters="sRGB"><feComponentTransfer>' . $funcs . '</feComponentTransfer></filter></svg>';
}

/**
 * Inline copy of a navy line icon drawn in currentColor, so CSS can turn it green on hover.
 */
function cg_svg_icon( $file, $class ) {
	static $cache = array();
	if ( ! isset( $cache[ $file ] ) ) {
		$svg            = (string) file_get_contents( CG_DIR . '/assets/images/' . $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$cache[ $file ] = str_replace( '#223044', 'currentColor', $svg );
	}
	return preg_replace( '/<svg\b/', '<svg class="' . esc_attr( $class ) . '" aria-hidden="true" focusable="false"', $cache[ $file ], 1 );
}

function cg_icon( $file, $class, $w, $h ) {
	return sprintf( '<img class="%s" src="%s" alt="" width="%d" height="%d" loading="lazy">', esc_attr( $class ), esc_url( CG_URI . '/assets/images/' . $file ), $w, $h );
}

/**
 * [cg_courses status="open|upcoming|completed" limit="9"]
 */
function cg_courses_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'status' => 'open', 'limit' => 12 ), $atts, 'cg_courses' );
	$q    = new WP_Query(
		array(
			'post_type'      => 'cg_course',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, (int) $atts['limit'] ),
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'meta_key'       => '_cg_status',
			'meta_value'     => sanitize_key( $atts['status'] ),
		)
	);
	$st = sanitize_key( $atts['status'] );

	if ( ! $q->have_posts() ) {
		return '<p class="cg-empty">' . esc_html__( 'No courses to show right now.', 'generatepress-child' ) . '</p>';
	}

	ob_start();
	static $filter_done = false;
	if ( ! $filter_done && 'completed' !== $st ) {
		echo cg_green_filter(); // phpcs:ignore WordPress.Security.EscapeOutput
		$filter_done = true;
	}
	$grid =array( 'open' => 'courses__grid', 'upcoming' => 'upcoming-courses__grid', 'completed' => 'completed__grid' );
	echo '<div class="' . esc_attr( isset( $grid[ $st ] ) ? $grid[ $st ] : 'courses__grid' ) . '">';
	$i = 0;
	while ( $q->have_posts() ) {
		$q->the_post();
		$id    = get_the_ID();
		$title = get_the_title();
		$phase = get_post_meta( $id, '_cg_phase', true );
		$start = cg_course_date( get_post_meta( $id, '_cg_start', true ) );

		if ( 'open' === $st ) {
			$slots = cg_course_lines( $id, 'schedule' );
			$learn = cg_course_lines( $id, 'learn' );
			?>
			<article class="course">
				<div class="course__media">
					<?php echo cg_course_img( $id, 'course__img' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php if ( $phase ) : ?><span class="course__tag"><?php echo esc_html( $phase ); ?></span><?php endif; ?>
				</div>
				<div class="course__body">
					<h3 class="course__title"><?php echo esc_html( $title ); ?></h3>
					<ul class="course__meta-list">
						<?php if ( $start ) : ?><li class="course__meta"><?php echo cg_svg_icon( 'icon-calendar.svg', 'course__meta-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><p class="course__meta-text"><strong><?php esc_html_e( 'Starting From', 'generatepress-child' ); ?></strong> <?php echo esc_html( $start ); ?></p></li><?php endif; ?>
						<?php if ( get_post_meta( $id, '_cg_duration', true ) ) : ?><li class="course__meta"><?php echo cg_svg_icon( 'icon-clock.svg', 'course__meta-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><p class="course__meta-text"><strong><?php esc_html_e( 'Duration', 'generatepress-child' ); ?></strong> <?php echo esc_html( get_post_meta( $id, '_cg_duration', true ) ); ?></p></li><?php endif; ?>
						<?php if ( get_post_meta( $id, '_cg_mode', true ) ) : ?><li class="course__meta"><?php echo cg_svg_icon( 'icon-book.svg', 'course__meta-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><p class="course__meta-text"><strong><?php esc_html_e( 'Mode of Teaching', 'generatepress-child' ); ?></strong> <?php echo esc_html( get_post_meta( $id, '_cg_mode', true ) ); ?></p></li><?php endif; ?>
						<?php if ( $slots ) : ?><li class="course__meta"><?php echo cg_svg_icon( 'icon-schedule.svg', 'course__meta-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><p class="course__meta-text"><strong><?php esc_html_e( 'Class Schedule', 'generatepress-child' ); ?></strong> <?php echo esc_html( implode( "\u{00A0} | \u{00A0}", $slots ) ); ?></p></li><?php endif; ?>
					</ul>
					<?php if ( $learn ) : ?>
						<details class="course__learn">
							<summary class="course__learn-link"><?php echo cg_icon( 'icon-learn.svg', 'course__learn-icon', 16, 20 ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'What you’ll learn', 'generatepress-child' ); ?></span></summary>
							<ul class="course__learn-list"><?php foreach ( $learn as $l ) { echo '<li>' . esc_html( $l ) . '</li>'; } ?></ul>
						</details>
					<?php endif; ?>
					<a class="cg-btn cg-btn--navy course__cta" href="#contact" data-cg-enroll data-course="<?php echo esc_attr( $title ); ?>" data-slots="<?php echo esc_attr( implode( '|', $slots ) ); ?>" data-phase="<?php echo esc_attr( $phase ); ?>"><?php esc_html_e( 'Enroll Now', 'generatepress-child' ); ?></a>
				</div>
			</article>
			<?php
		} elseif ( 'upcoming' === $st ) {
			?>
			<article class="upcoming">
				<div class="upcoming__media">
					<?php echo cg_course_img( $id, 'upcoming__img' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php if ( $phase ) : ?><span class="course__tag"><?php echo esc_html( $phase ); ?></span><?php endif; ?>
				</div>
				<div class="upcoming__body">
					<h3 class="upcoming__title"><?php echo esc_html( $title ); ?></h3>
					<?php if ( $start ) : ?><div class="upcoming__date"><?php echo cg_svg_icon( 'icon-calendar.svg', 'course__meta-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><p class="course__meta-text"><strong><?php esc_html_e( 'Starting From', 'generatepress-child' ); ?></strong> <?php echo esc_html( $start ); ?></p></div><?php endif; ?>
					<p class="upcoming__text"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<a class="cg-btn cg-btn--outline" href="#contact" data-cg-notify data-course="<?php echo esc_attr( $title ); ?>"><?php esc_html_e( 'Notify Me', 'generatepress-child' ); ?></a>
				</div>
			</article>
			<?php
		} else {
			?>
			<article class="done<?php echo 0 === $i ? ' done--active' : ''; ?>">
				<span class="done__icons">
					<?php echo cg_icon( 'course-done-2.webp', 'done__icon', 132, 144 ) . cg_icon( 'course-done-1.webp', 'done__icon done__icon--active', 132, 144 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</span>
				<h3 class="done__title"><?php echo esc_html( $title ); ?></h3>
				<p class="done__text"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<a class="done__link" href="#contact" data-cg-notify data-course="<?php echo esc_attr( $title ); ?>"><?php esc_html_e( 'I’m Interested', 'generatepress-child' ); ?></a>
			</article>
			<?php
		}
		++$i;
	}
	wp_reset_postdata();
	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'cg_courses', 'cg_courses_shortcode' );
