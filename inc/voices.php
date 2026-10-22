<?php
/**
 * Voices of CodeGirls, managed from the WordPress admin ("Voices" menu).
 *
 * Each voice is one alumna: the title is her name, the Featured image is her photo
 * (any plain photo; the round / pill frames are drawn in CSS), the Excerpt is the intro
 * shown when she is featured, and the editor holds her full story at /voices/<name>/.
 *
 *   [cg_voice_featured]   Featured voice at the top of the Voices page
 *   [cg_voices]           Grid of all other voices
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

function cg_register_voices() {
	register_post_type(
		'cg_voice',
		array(
			'labels'          => array(
				'name'                  => __( 'Voices', 'generatepress-child' ),
				'singular_name'         => __( 'Voice', 'generatepress-child' ),
				'add_new_item'          => __( 'Add New Voice', 'generatepress-child' ),
				'edit_item'             => __( 'Edit Voice', 'generatepress-child' ),
				'featured_image'        => __( 'Photo', 'generatepress-child' ),
				'set_featured_image'    => __( 'Set photo', 'generatepress-child' ),
				'remove_featured_image' => __( 'Remove photo', 'generatepress-child' ),
				'not_found'             => __( 'No voices yet.', 'generatepress-child' ),
			),
			'public'          => true,
			'has_archive'     => false, // The "Voices of CodeGirls" page (/voices/) is the listing.
			'rewrite'         => array( 'slug' => 'voices', 'with_front' => false ),
			'show_in_rest'    => false, // Own form instead of the block builder (inc/voices-admin.php).
			'menu_icon'       => 'dashicons-format-quote',
			'menu_position'   => 26,
			'supports'        => array( 'title', 'thumbnail' ), // Intro, position and the story are fields in the form (inc/voices-admin.php).
			'capability_type' => 'post',
		)
	);
}
add_action( 'init', 'cg_register_voices' );

// Flush rewrite rules once so /voices/<name>/ resolves right after this is deployed.
add_action(
	'init',
	function () {
		if ( get_option( 'cg_voice_rewrite' ) !== '1' ) {
			flush_rewrite_rules( false );
			update_option( 'cg_voice_rewrite', '1' );
		}
	},
	20
);

/**
 * Story pages: full-bleed layout without the GeneratePress sidebar, like blog posts.
 */
add_filter(
	'body_class',
	function ( $classes ) {
		if ( is_singular( 'cg_voice' ) ) {
			$classes[] = 'cg-fullwidth';
		}
		return $classes;
	}
);

add_filter(
	'generate_sidebar_layout',
	function ( $layout ) {
		return is_singular( 'cg_voice' ) ? 'no-sidebar' : $layout;
	}
);

/** Story-page meta row: field key => label, icon, placeholder (shown with its icon, in this order). */
function cg_voice_meta_fields() {
	return array(
		'location' => array( __( 'Location', 'generatepress-child' ), 'voice-meta-pin.webp', __( 'e.g. Karachi, Pakistan', 'generatepress-child' ) ),
		'role'     => array( __( 'Job title', 'generatepress-child' ), 'voice-meta-job.webp', __( 'e.g. Senior SQA Engineer', 'generatepress-child' ) ),
		'track'    => array( __( 'Skills', 'generatepress-child' ), 'voice-meta-role.webp', __( 'e.g. SQA, HTML, CSS', 'generatepress-child' ) ),
	);
}

require_once __DIR__ . '/voices-admin.php'; // Edit screen (form, skills, story blocks, preview).

add_action(
	'save_post_cg_voice',
	function ( $post_id ) {
		if ( ! isset( $_POST['cg_voice_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cg_voice_nonce'] ) ), 'cg_voice_save' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( array_keys( cg_voice_meta_fields() ) as $k ) {
			update_post_meta( $post_id, '_cg_' . $k, isset( $_POST[ 'cg_' . $k ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'cg_' . $k ] ) ) : '' );
		}
		update_post_meta( $post_id, '_cg_featured', empty( $_POST['cg_featured'] ) ? '' : '1' );

		$banner = isset( $_POST['cg_banner'] ) ? absint( $_POST['cg_banner'] ) : 0;
		if ( $banner && wp_attachment_is_image( $banner ) ) {
			update_post_meta( $post_id, '_cg_banner', $banner );
		} else {
			delete_post_meta( $post_id, '_cg_banner' );
		}
	}
);

/**
 * Admin list: photo, job title, featured, order.
 */
add_filter(
	'manage_cg_voice_posts_columns',
	function () {
		return array(
			'cb'          => '<input type="checkbox" />',
			'cg_photo'    => __( 'Photo', 'generatepress-child' ),
			'title'       => __( 'Name', 'generatepress-child' ),
			'cg_role'     => __( 'Job title', 'generatepress-child' ),
			'cg_featured' => __( 'Featured', 'generatepress-child' ),
			'cg_story'    => __( 'Story', 'generatepress-child' ),
			'menu_order'  => __( 'Order', 'generatepress-child' ),
		);
	}
);

add_action(
	'manage_cg_voice_posts_custom_column',
	function ( $col, $id ) {
		if ( 'cg_photo' === $col ) {
			echo get_the_post_thumbnail( $id, array( 48, 48 ), array( 'style' => 'width:48px;height:48px;object-fit:cover;border-radius:50%' ) );
		} elseif ( 'cg_role' === $col ) {
			echo esc_html( get_post_meta( $id, '_cg_role', true ) ?: '—' );
		} elseif ( 'cg_featured' === $col ) {
			static $featured = null;
			$featured = null === $featured ? cg_voice_featured_id() : $featured;
			if ( (int) $featured === (int) $id ) {
				echo get_post_meta( $id, '_cg_featured', true ) ? esc_html__( '★ Pinned', 'generatepress-child' ) : esc_html__( '★ Newest', 'generatepress-child' );
			}
		} elseif ( 'cg_story' === $col ) {
			echo cg_voice_has_story( $id ) ? esc_html__( 'Yes', 'generatepress-child' ) : '—';
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
		if ( 'cg_voice' === get_current_screen()->post_type ) {
			echo '<style>.column-cg_photo{width:64px}.column-cg_featured,.column-cg_story,.column-menu_order{width:80px}</style>';
		}
	}
);

add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( is_admin() && $q->is_main_query() && 'cg_voice' === $q->get( 'post_type' ) && ! $q->get( 'orderby' ) ) {
			$q->set( 'orderby', 'menu_order title' );
			$q->set( 'order', 'ASC' );
		}
	}
);

function cg_voice_has_story( $id ) {
	return '' !== trim( wp_strip_all_tags( (string) get_post_field( 'post_content', $id ) ) );
}

/** All published voices in display order. */
function cg_voice_ids() {
	return get_posts(
		array(
			'post_type'      => 'cg_voice',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'fields'         => 'ids',
		)
	);
}

/** The featured voice: the newest pinned one, otherwise simply the newest voice. */
function cg_voice_featured_id() {
	$newest = function ( $meta ) {
		$ids = get_posts(
			array(
				'post_type'      => 'cg_voice',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
				'meta_query'     => $meta, // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		return $ids ? (int) $ids[0] : 0;
	};
	return $newest( array( array( 'key' => '_cg_featured', 'value' => '1' ) ) ) ?: $newest( array() );
}

function cg_voice_photo( $id, $class, $size = 'medium' ) {
	return has_post_thumbnail( $id ) ? get_the_post_thumbnail( $id, $size, array( 'class' => $class, 'alt' => get_the_title( $id ), 'loading' => 'lazy' ) ) : '';
}

/**
 * [cg_voice_featured]
 */
function cg_voice_featured_shortcode() {
	$id = cg_voice_featured_id();
	if ( ! $id ) {
		return '';
	}
	ob_start();
	?>
	<section class="voice-featured">
		<div class="cg-wrap voice-featured__inner">
			<?php echo cg_voice_photo( $id, 'voice-featured__img', 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="voice-featured__body">
				<h2 class="voice-featured__name"><?php echo esc_html( get_the_title( $id ) ); ?></h2>
				<?php if ( has_excerpt( $id ) ) : ?>
					<p class="voice-featured__text"><?php echo esc_html( get_the_excerpt( $id ) ); ?></p>
				<?php endif; ?>
				<?php if ( cg_voice_has_story( $id ) ) : ?>
					<a class="cg-btn cg-btn--navy" href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php esc_html_e( 'Discover her Voice', 'generatepress-child' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode( 'cg_voice_featured', 'cg_voice_featured_shortcode' );

/**
 * [cg_voices] All voices except the featured one.
 */
function cg_voices_shortcode() {
	$featured = cg_voice_featured_id();
	$ids      = array_diff( cg_voice_ids(), array( $featured ) );
	if ( ! $ids ) {
		return '';
	}
	ob_start();
	?>
	<section class="voices">
		<div class="cg-wrap">
			<div class="voices__grid">
				<?php foreach ( $ids as $id ) : ?>
					<article class="voice">
						<?php echo cg_voice_photo( $id, 'voice__img' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<div class="voice__body">
							<h3 class="voice__name"><?php echo esc_html( get_the_title( $id ) ); ?></h3>
							<?php if ( get_post_meta( $id, '_cg_role', true ) ) : ?>
								<p class="voice__role"><?php echo esc_html( get_post_meta( $id, '_cg_role', true ) ); ?></p>
							<?php endif; ?>
							<?php if ( cg_voice_has_story( $id ) ) : ?>
								<a class="voice__link" href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php esc_html_e( 'Discover her Voice', 'generatepress-child' ); ?></a>
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
add_shortcode( 'cg_voices', 'cg_voices_shortcode' );
