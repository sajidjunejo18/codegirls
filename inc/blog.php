<?php
/**
 * Blog: filters on the posts page, pattern renderer used by templates, body class.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render a registered theme pattern (e.g. the contact section) inside a PHP template.
 */
function cg_render_pattern( $slug ) {
	$pattern = WP_Block_Patterns_Registry::get_instance()->get_registered( 'codegirls/' . $slug );
	if ( $pattern ) {
		// do_blocks() alone leaves [shortcodes] (e.g. the contact form) unprocessed.
		echo do_shortcode( do_blocks( $pattern['content'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Posts page filters: ?cg_author=ID&cg_cat=ID&cg_date=YYYY-MM-DD&q=text
 */
function cg_blog_filters( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_home() ) {
		return;
	}
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( ! empty( $_GET['cg_author'] ) ) {
		$query->set( 'author', absint( $_GET['cg_author'] ) );
	}
	if ( ! empty( $_GET['cg_cat'] ) ) {
		$query->set( 'cat', absint( $_GET['cg_cat'] ) );
	}
	if ( ! empty( $_GET['cg_date'] ) && preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', sanitize_text_field( wp_unslash( $_GET['cg_date'] ) ), $m ) ) {
		$query->set(
			'date_query',
			array(
				array(
					'year'  => (int) $m[1],
					'month' => (int) $m[2],
					'day'   => (int) $m[3],
				),
			)
		);
	}
	if ( ! empty( $_GET['q'] ) ) {
		$query->set( 's', sanitize_text_field( wp_unslash( $_GET['q'] ) ) );
	}
	// phpcs:enable
	$query->set( 'posts_per_page', 9 );
}
add_action( 'pre_get_posts', 'cg_blog_filters' );

/**
 * Full-bleed layout on the blog index and single posts.
 */
function cg_blog_body_class( $classes ) {
	if ( is_home() || is_singular( 'post' ) ) {
		$classes[] = 'cg-fullwidth';
	}
	return $classes;
}
add_filter( 'body_class', 'cg_blog_body_class' );

/**
 * Short excerpt for cards.
 */
add_filter(
	'excerpt_length',
	function () {
		return 14;
	}
);
add_filter(
	'excerpt_more',
	function () {
		return '…';
	}
);
