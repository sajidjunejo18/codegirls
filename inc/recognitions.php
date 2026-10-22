<?php
/**
 * Recognitions (awards, accreditations), managed from the WordPress admin ("Recognitions" menu).
 *
 * Each recognition is one card on the Recognitions page: the title is the award text, the
 * Featured image is the certificate / award picture, the optional badge is the round logo
 * that overlaps its bottom edge, and the optional website opens from the "Visit Website" button.
 *
 *   [cg_recognitions]   Grid of all recognitions
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

function cg_register_recognitions() {
	register_post_type(
		'cg_recognition',
		array(
			'labels'          => array(
				'name'                  => __( 'Recognitions', 'generatepress-child' ),
				'singular_name'         => __( 'Recognition', 'generatepress-child' ),
				'add_new_item'          => __( 'Add New Recognition', 'generatepress-child' ),
				'edit_item'             => __( 'Edit Recognition', 'generatepress-child' ),
				'featured_image'        => __( 'Award picture', 'generatepress-child' ),
				'set_featured_image'    => __( 'Set award picture', 'generatepress-child' ),
				'remove_featured_image' => __( 'Remove award picture', 'generatepress-child' ),
				'not_found'             => __( 'No recognitions yet.', 'generatepress-child' ),
			),
			'public'          => false, // Cards link out; no page of their own.
			'show_ui'         => true,
			'show_in_rest'    => false, // Own form instead of the block builder (inc/recognitions-admin.php).
			'menu_icon'       => 'dashicons-awards',
			'menu_position'   => 28,
			'supports'        => array( 'title', 'thumbnail' ),
			'capability_type' => 'post',
		)
	);
}
add_action( 'init', 'cg_register_recognitions' );

require_once __DIR__ . '/recognitions-admin.php'; // Edit screen.

/** All published recognitions: by position, then newest first. */
function cg_recognition_ids() {
	return get_posts(
		array(
			'post_type'      => 'cg_recognition',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC', 'ID' => 'DESC' ),
			'fields'         => 'ids',
		)
	);
}

/** Valid badge attachment ID (0 if none). */
function cg_recognition_badge_id( $id ) {
	$badge = (int) get_post_meta( $id, '_cg_badge', true );
	return $badge && wp_attachment_is_image( $badge ) ? $badge : 0;
}

/**
 * Admin list: picture, position, website.
 */
add_filter(
	'manage_cg_recognition_posts_columns',
	function () {
		return array(
			'cb'         => '<input type="checkbox" />',
			'cg_pic'     => __( 'Picture', 'generatepress-child' ),
			'title'      => __( 'Title', 'generatepress-child' ),
			'cg_url'     => __( 'Website', 'generatepress-child' ),
			'menu_order' => __( 'Position', 'generatepress-child' ),
		);
	}
);

add_action(
	'manage_cg_recognition_posts_custom_column',
	function ( $col, $id ) {
		if ( 'cg_pic' === $col ) {
			echo get_the_post_thumbnail( $id, array( 96, 62 ), array( 'style' => 'width:96px;height:62px;object-fit:cover;border-radius:4px' ) );
		} elseif ( 'cg_url' === $col ) {
			$url = get_post_meta( $id, '_cg_url', true );
			echo $url ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( preg_replace( '#^https?://(www\.)?#', '', $url ) ) . '</a>' : '—';
		} elseif ( 'menu_order' === $col ) {
			echo (int) get_post_field( 'menu_order', $id );
		}
	},
	10,
	2
);

add_action(
	'admin_head-edit.php',
	function () {
		if ( 'cg_recognition' === get_current_screen()->post_type ) {
			echo '<style>.column-cg_pic{width:116px}.column-menu_order{width:90px}.column-cg_url{width:26%}</style>';
		}
	}
);

add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( is_admin() && $q->is_main_query() && 'cg_recognition' === $q->get( 'post_type' ) && ! $q->get( 'orderby' ) ) {
			$q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		}
	}
);

/**
 * [cg_recognitions]
 */
function cg_recognitions_shortcode() {
	$ids = cg_recognition_ids();
	if ( ! $ids ) {
		return '';
	}
	ob_start();
	?>
	<section class="recogs">
		<div class="cg-wrap recogs__wrap">
			<div class="recogs__grid">
				<?php
				foreach ( $ids as $id ) :
					$title = get_the_title( $id );
					$badge = cg_recognition_badge_id( $id );
					$url   = get_post_meta( $id, '_cg_url', true );
					$thumb = get_post_thumbnail_id( $id );
					$alt   = $thumb ? trim( (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true ) ) : '';
					?>
					<article class="recog<?php echo $badge ? '' : ' recog--nobadge'; ?>">
						<?php if ( $thumb ) : ?>
							<div class="recog__media">
								<?php echo wp_get_attachment_image( $thumb, 'large', false, array( 'class' => 'recog__img', 'alt' => $alt ? $alt : $title, 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<?php if ( $badge ) : ?>
									<?php echo wp_get_attachment_image( $badge, 'medium', false, array( 'class' => 'recog__badge', 'alt' => '', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<div class="recog__body">
							<h2 class="recog__title"><?php echo esc_html( $title ); ?></h2>
							<?php if ( $url ) : ?>
								<a class="cg-btn cg-btn--navy recog__btn" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Visit Website', 'generatepress-child' ); ?><span class="screen-reader-text"> — <?php echo esc_html( $title ); ?></span></a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode( 'cg_recognitions', 'cg_recognitions_shortcode' );
