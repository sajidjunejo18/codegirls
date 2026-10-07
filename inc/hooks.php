<?php
/**
 * GeneratePress hook customizations: replaces the default header and footer
 * with the CodeGirls markup from /parts.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Swap GeneratePress header/footer output. Runs on `wp` so the parent theme's
 * own add_action() calls (which happen after this file loads) are already registered.
 */
function cg_swap_header_footer() {
	// Header + primary navigation.
	remove_action( 'generate_header', 'generate_construct_header' );
	remove_action( 'generate_after_header', 'generate_add_navigation_after_header', 5 );
	add_action( 'generate_header', 'cg_render_header' );

	// Footer widgets + copyright bar.
	remove_action( 'generate_footer', 'generate_construct_footer_widgets', 5 );
	remove_action( 'generate_footer', 'generate_construct_footer' );
	add_action( 'generate_footer', 'cg_render_footer' );
}
add_action( 'wp', 'cg_swap_header_footer' );

function cg_render_header() {
	get_template_part( 'parts/header' );
}

function cg_render_footer() {
	get_template_part( 'parts/footer' );
}

/**
 * Full-bleed layout on the homepage (see body.cg-fullwidth in main.css).
 */
function cg_body_class( $classes ) {
	if ( is_front_page() || ( is_singular( 'page' ) && 'true' === get_post_meta( get_the_ID(), '_generate-full-width-content', true ) ) ) {
		$classes[] = 'cg-fullwidth';
	}
	return $classes;
}
add_filter( 'body_class', 'cg_body_class' );

/**
 * Menu items that point at an in-page anchor (/#contact) share the homepage URL,
 * so WordPress would mark them "current". Strip that so only real pages highlight.
 */
function cg_nav_anchor_not_current( $classes, $item ) {
	if ( false !== strpos( (string) $item->url, '#' ) ) {
		$classes = array_diff( $classes, array( 'current-menu-item', 'current_page_item', 'current-menu-ancestor', 'current-menu-parent' ) );
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'cg_nav_anchor_not_current', 10, 2 );

function cg_nav_anchor_aria( $atts, $item ) {
	if ( false !== strpos( (string) $item->url, '#' ) ) {
		unset( $atts['aria-current'] );
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'cg_nav_anchor_aria', 10, 2 );
