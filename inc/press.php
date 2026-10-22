<?php
/**
 * In The Press: news articles about CodeGirls, managed from the admin ("Press" menu).
 *
 * Each item is one card on the In The Press page: the title is the headline, the Featured
 * image is the publisher's logo tile, the date is the publication date, and the link opens
 * the article (in a new tab). Newest first.
 *
 *   [cg_press]   Grid of all press items
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'cg_press',
			array(
				'labels'          => array(
					'name'                  => __( 'Press', 'generatepress-child' ),
					'singular_name'         => __( 'Press item', 'generatepress-child' ),
					'add_new'               => __( 'Add New Press Item', 'generatepress-child' ),
					'add_new_item'          => __( 'Add New Press Item', 'generatepress-child' ),
					'edit_item'             => __( 'Edit Press Item', 'generatepress-child' ),
					'all_items'             => __( 'All Press Items', 'generatepress-child' ),
					'featured_image'        => __( 'Publisher logo', 'generatepress-child' ),
					'set_featured_image'    => __( 'Set publisher logo', 'generatepress-child' ),
					'remove_featured_image' => __( 'Remove publisher logo', 'generatepress-child' ),
					'not_found'             => __( 'No press items yet.', 'generatepress-child' ),
				),
				'public'          => true, // Items linking to the old codegirls site open their own page here.
				'has_archive'     => false, // The In The Press page is the listing.
				'rewrite'         => array( 'slug' => 'press-details', 'with_front' => false ),
				'exclude_from_search' => true,
				'show_ui'         => true,
				'show_in_rest'    => false,
				'menu_icon'       => 'dashicons-megaphone',
				'menu_position'   => 28,
				'supports'        => array( 'title', 'thumbnail' ),
				'capability_type' => 'post',
			)
		);
	}
);

// Flush rewrite rules once so /press-details/<name>/ resolves right after this is deployed.
add_action(
	'init',
	function () {
		if ( get_option( 'cg_press_rewrite' ) !== '1' ) {
			flush_rewrite_rules( false );
			update_option( 'cg_press_rewrite', '1' );
		}
	},
	20
);

// Press article pages use the same full-width layout as the other pages (no sidebar).
add_filter(
	'body_class',
	function ( $classes ) {
		if ( is_singular( 'cg_press' ) ) {
			$classes[] = 'cg-fullwidth';
		}
		return $classes;
	}
);
add_filter( 'generate_sidebar_layout', fn( $layout ) => is_singular( 'cg_press' ) ? 'no-sidebar' : $layout );

require_once __DIR__ . '/press-admin.php'; // Edit screen.

/** URL of the In The Press listing page. */
function cg_press_page_url() {
	$page = get_page_by_path( 'in-the-press' );
	return $page ? get_permalink( $page ) : home_url( '/' );
}

/** Hosts of the old CodeGirls site (plus this site), whose press links should stay on this site. */
function cg_press_internal_hosts() {
	$hosts = array( 'codegirls.consulnet.net', 'codegirls.org','codegirls.pk', 'codegirls.com', wp_parse_url( home_url(), PHP_URL_HOST ) );
	return array_filter( array_map( fn( $h ) => preg_replace( '#^www\.#', '', strtolower( (string) $h ) ), apply_filters( 'cg_press_internal_hosts', $hosts ) ) );
}

/** True when the link points at the old CodeGirls site (or this one). */
function cg_press_is_internal( $url ) {
	$host = wp_parse_url( (string) $url, PHP_URL_HOST );
	return $host && in_array( preg_replace( '#^www\.#', '', strtolower( $host ) ), cg_press_internal_hosts(), true );
}

/** Where a card goes: its own page here for old-site links, otherwise the publisher's URL. */
function cg_press_card_url( $id ) {
	$url = get_post_meta( $id, '_cg_url', true );
	return $url && cg_press_is_internal( $url ) ? get_permalink( $id ) : $url;
}

/** All published press items, newest first. */
function cg_press_ids() {
	return get_posts(
		array(
			'post_type'      => 'cg_press',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array( 'date' => 'DESC', 'ID' => 'DESC' ),
			'fields'         => 'ids',
		)
	);
}

/** "4th December, 2025" (the format the design and the old site use). */
function cg_press_date( $id ) {
	return get_the_date( 'jS F, Y', $id );
}

/**
 * Admin list: logo, title, date, link.
 */
add_filter(
	'manage_cg_press_posts_columns',
	function () {
		return array(
			'cb'       => '<input type="checkbox" />',
			'cg_logo'  => __( 'Logo', 'generatepress-child' ),
			'title'    => __( 'Headline', 'generatepress-child' ),
			'cg_date'  => __( 'Date', 'generatepress-child' ),
			'cg_url'   => __( 'Link', 'generatepress-child' ),
		);
	}
);

add_action(
	'manage_cg_press_posts_custom_column',
	function ( $col, $id ) {
		if ( 'cg_logo' === $col ) {
			echo get_the_post_thumbnail( $id, array( 96, 68 ), array( 'style' => 'width:96px;height:68px;object-fit:cover;border-radius:4px' ) );
		} elseif ( 'cg_date' === $col ) {
			echo esc_html( cg_press_date( $id ) );
		} elseif ( 'cg_url' === $col ) {
			$url = get_post_meta( $id, '_cg_url', true );
			echo $url ? '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html( preg_replace( '#^https?://(www\.)?#', '', $url ) ) . '</a>' . ( cg_press_is_internal( $url ) ? ' <em>' . esc_html__( '(opens a page on this site)', 'generatepress-child' ) . '</em>' : '' ) :'—';
		}
	},
	10,
	2
);

add_action(
	'admin_head-edit.php',
	function () {
		if ( 'cg_press' === get_current_screen()->post_type ) {
			echo '<style>.column-cg_logo{width:116px}.column-cg_date{width:150px}.column-cg_url{width:30%}</style>';
		}
	}
);

/**
 * [cg_press]
 */
function cg_press_shortcode() {
	$ids = cg_press_ids();
	if ( ! $ids ) {
		return '';
	}
	ob_start();
	?>
	<section class="reports reports--press">
		<div class="cg-wrap reports__wrap">
			<div class="reports__grid">
				<?php
				foreach ( $ids as $i => $id ) :
					$url   = cg_press_card_url( $id );
						$on_site = cg_press_is_internal( get_post_meta( $id, '_cg_url', true ) );
					$title = get_the_title( $id );
					?>
					<article class="report report--press<?php echo 0 === $i ? ' report--active' : ''; ?>">
						<?php if ( has_post_thumbnail( $id ) ) : ?>
							<div class="report__tile"><?php echo get_the_post_thumbnail( $id, 'medium_large', array( 'class' => 'report__img', 'alt' => '', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
						<?php endif; ?>
						<div class="report__body">
							<h2 class="report__title">
								<?php if ( $url ) : ?>
									<?php if ( $on_site ) : ?>
										<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a>
									<?php else : ?>
										<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $title ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'generatepress-child' ); ?></span></a>
									<?php endif; ?>
								<?php else : ?>
									<?php echo esc_html( $title ); ?>
								<?php endif; ?>
							</h2>
							<div class="report__when">
								<img class="report__clock" src="<?php echo esc_url( CG_URI . '/assets/images/icon-clock.svg' ); ?>" alt="" width="16" height="16" loading="lazy">
								<time class="report__date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $id ) ); ?>"><?php echo esc_html( cg_press_date( $id ) ); ?></time>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode( 'cg_press', 'cg_press_shortcode' );
