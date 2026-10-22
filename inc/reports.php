<?php
/**
 * Reports, managed from the WordPress admin ("Reports" menu).
 *
 * Each report is one card on the Reports page: the title is the card title, the Featured
 * image is its cover, the report date is the date shown, and the PDF opens when the card
 * is clicked. Newest first.
 *
 *   [cg_reports]   Grid of all reports
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

function cg_register_reports() {
	register_post_type(
		'cg_report',
		array(
			'labels'          => array(
				'name'                  => __( 'Reports', 'generatepress-child' ),
				'singular_name'         => __( 'Report', 'generatepress-child' ),
				'add_new_item'          => __( 'Add New Report', 'generatepress-child' ),
				'edit_item'             => __( 'Edit Report', 'generatepress-child' ),
				'featured_image'        => __( 'Cover image', 'generatepress-child' ),
				'set_featured_image'    => __( 'Set cover image', 'generatepress-child' ),
				'remove_featured_image' => __( 'Remove cover image', 'generatepress-child' ),
				'not_found'             => __( 'No reports yet.', 'generatepress-child' ),
			),
			'public'          => false, // Cards open the PDF directly; no page of their own.
			'show_ui'         => true,
			'show_in_rest'    => false, // Own form instead of the block builder (inc/reports-admin.php).
			'menu_icon'       => 'dashicons-media-document',
			'menu_position'   => 27,
			'supports'        => array( 'title', 'thumbnail' ),
			'capability_type' => 'post',
		)
	);
}
add_action( 'init', 'cg_register_reports' );

require_once __DIR__ . '/reports-admin.php'; // Edit screen.

/** Attachment ID of the report's PDF (0 if none). */
function cg_report_pdf_id( $id ) {
	$pdf = (int) get_post_meta( $id, '_cg_pdf', true );
	return $pdf && 'attachment' === get_post_type( $pdf ) ? $pdf : 0;
}

/** All published reports, newest first. */
function cg_report_ids() {
	return get_posts(
		array(
			'post_type'      => 'cg_report',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array( 'date' => 'DESC', 'ID' => 'DESC' ),
			'fields'         => 'ids',
		)
	);
}

/**
 * Admin list: cover, report date, file.
 */
add_filter(
	'manage_cg_report_posts_columns',
	function () {
		return array(
			'cb'        => '<input type="checkbox" />',
			'cg_cover'  => __( 'Cover', 'generatepress-child' ),
			'title'     => __( 'Title', 'generatepress-child' ),
			'cg_date'   => __( 'Report date', 'generatepress-child' ),
			'cg_file'   => __( 'PDF', 'generatepress-child' ),
		);
	}
);

add_action(
	'manage_cg_report_posts_custom_column',
	function ( $col, $id ) {
		if ( 'cg_cover' === $col ) {
			echo get_the_post_thumbnail( $id, array( 90, 70 ), array( 'style' => 'width:90px;height:70px;object-fit:cover;border-radius:4px' ) );
		} elseif ( 'cg_date' === $col ) {
			echo esc_html( get_the_date( 'M j, Y', $id ) );
		} elseif ( 'cg_file' === $col ) {
			$pdf = cg_report_pdf_id( $id );
			if ( $pdf ) {
				printf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( wp_get_attachment_url( $pdf ) ), esc_html( basename( get_attached_file( $pdf ) ) ) );
			} else {
				echo '—';
			}
		}
	},
	10,
	2
);

add_action(
	'admin_head-edit.php',
	function () {
		if ( 'cg_report' === get_current_screen()->post_type ) {
			echo '<style>.column-cg_cover{width:110px}.column-cg_date{width:130px}.column-cg_file{width:34%}</style>';
		}
	}
);

/**
 * [cg_latest_report] The homepage "Get involved" collage: the newest report's cover on two
 * tilted cards. Clicking it opens the PDF in a new tab. Without any report it falls back to the
 * original static picture, so the band never breaks.
 */
function cg_latest_report_shortcode() {
	$ids = cg_report_ids();
	$id  = $ids ? $ids[0] : 0;
	$pdf = $id ? cg_report_pdf_id( $id ) : 0;
	$img = $id ? get_post_thumbnail_id( $id ) : 0;
	// Optional portrait picture (the report's front page); otherwise the cover fills the portrait card.
	$home = $id ? (int) get_post_meta( $id, '_cg_home_img', true ) : 0;
	if ( $home && wp_attachment_is_image( $home ) ) {
		$img = $home;
	}

	if ( ! $id || ! $pdf || ! $img ) {
		$src = esc_url( CG_URI . '/assets/images/get-involved.webp' );
		return '<div class="involve__collage"><img class="involve__card involve__card--a" src="' . $src . '" alt="" width="368" height="524" loading="lazy"><img class="involve__card involve__card--b" src="' . $src . '" alt="" width="368" height="524" loading="lazy"></div>';
	}

	$title = get_the_title( $id );
	$cover = static function ( $class, $alt ) use ( $img ) {
		return wp_get_attachment_image( $img, 'large', false, array( 'class' => 'involve__card involve__card--report ' . $class, 'alt' => $alt, 'loading' => 'lazy' ) );
	};
	ob_start();
	?>
	<div class="involve__collage involve__collage--report">
		<a class="involve__report" href="<?php echo esc_url( wp_get_attachment_url( $pdf ) ); ?>" target="_blank" rel="noopener">
			<?php echo $cover( 'involve__card--a', '' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="involve__front">
				<?php echo $cover( 'involve__card--b', $title ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<span class="involve__download">
					<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12m0 0-5-5m5 5 5-5M4 20h16"/></svg>
					<span><?php echo esc_html( $title ); ?></span>
				</span>
			</span>
			<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: report title */ __( 'Open %s (PDF)', 'generatepress-child' ), $title ) ); ?></span>
		</a>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'cg_latest_report', 'cg_latest_report_shortcode' );

/**
 * [cg_reports]
 */
function cg_reports_shortcode() {
	$ids = cg_report_ids();
	if ( ! $ids ) {
		return '';
	}
	ob_start();
	?>
	<section class="reports">
		<div class="cg-wrap reports__wrap">
			<div class="reports__grid">
				<?php
				foreach ( $ids as $i => $id ) :
					$pdf  = cg_report_pdf_id( $id );
					$url  = $pdf ? wp_get_attachment_url( $pdf ) : '';
					$name = get_the_title( $id );
					?>
					<article class="report report--file<?php echo 0 === $i ? ' report--active' : ''; ?>">
						<?php if ( has_post_thumbnail( $id ) ) : ?>
							<div class="report__tile"><?php echo get_the_post_thumbnail( $id, 'large', array( 'class' => 'report__img', 'alt' => '', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
						<?php endif; ?>
						<div class="report__body">
							<h2 class="report__title">
								<?php if ( $url ) : ?>
									<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $name ); ?><span class="screen-reader-text"> (PDF)</span></a>
								<?php else : ?>
									<?php echo esc_html( $name ); ?>
								<?php endif; ?>
							</h2>
							<p class="report__date"><?php echo esc_html( get_the_date( 'F j, Y', $id ) ); ?></p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode( 'cg_reports', 'cg_reports_shortcode' );
