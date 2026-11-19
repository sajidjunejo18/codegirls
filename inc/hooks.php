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
	if ( is_front_page() || is_404() || ( is_singular( 'page' ) && 'true' === get_post_meta( get_the_ID(), '_generate-full-width-content', true ) ) ) {
		$classes[] = 'cg-fullwidth';
	}
	return $classes;
}
add_filter( 'body_class', 'cg_body_class' );

// The branded 404 page (404.php) has no sidebar.
add_filter( 'generate_sidebar_layout', fn( $layout ) => is_404() ? 'no-sidebar' : $layout );

/**
 * Descriptive homepage <title> ("Codegirls – Empowering Women…") while the Settings → General
 * tagline is empty. No SEO plugin is active; filling in the tagline there overrides this.
 */
function cg_front_page_title( $parts ) {
	if ( is_front_page() && empty( $parts['tagline'] ) ) {
		$parts['tagline'] = __( 'IT & Business Training for Women in Pakistan', 'generatepress-child' ); // 56 chars with the site name (QA-68: 50–60).
	}
	// Keep the whole <title> ("Page title – Site name") within 60 characters, cut at a word boundary.
	$max = 60 - 3 - mb_strlen( (string) ( $parts['site'] ?? get_bloginfo( 'name' ) ) );
	$title = html_entity_decode( (string) ( $parts['title'] ?? '' ), ENT_QUOTES, 'UTF-8' ); // Never cut inside an entity like &#8217;.
	if ( '' !== $title && ! is_front_page() && mb_strlen( $title ) > $max ) {
		$cut            = mb_substr( $title, 0, $max );
		$space          = mb_strrpos( $cut, ' ' );
		$parts['title'] = rtrim( false !== $space && $space > $max / 2 ? mb_substr( $cut, 0, $space ) : mb_substr( $cut, 0, $max - 1 ), " \t:;,.–-" ) . '…';
	}
	return $parts;
}
add_filter( 'document_title_parts', 'cg_front_page_title' );

/** Open article links to other domains in a new tab with safe rel attributes. */
function cg_external_content_links_new_tab( $content ) {
	$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
	return preg_replace_callback(
		'~<a\b([^>]*\bhref\s*=\s*(["\'])(https?://[^"\']+)\2[^>]*)>~i',
		static function ( $match ) use ( $site_host ) {
			$link_host = wp_parse_url( $match[3], PHP_URL_HOST );
			if ( ! $link_host || strtolower( $link_host ) === strtolower( $site_host ) ) {
				return $match[0];
			}
			$attrs = preg_replace( '/\s+target\s*=\s*(["\']).*?\1/i', '', $match[1] );
			$attrs = preg_replace( '/\s+rel\s*=\s*(["\']).*?\1/i', '', $attrs );
			return '<a' . $attrs . ' target="_blank" rel="noopener noreferrer">';
		},
		$content
	);
}
add_filter( 'the_content', 'cg_external_content_links_new_tab', 20 );

/**
 * Menu items that point at an in-page anchor (/#contact) share the homepage URL,
 * so WordPress would mark them "current". Strip that so only real pages highlight.
 */
function cg_nav_anchor_not_current( $classes, $item ) {
	if ( false !== strpos( (string) $item->url, '#' ) ) {
		$classes = array_diff( $classes, array( 'current-menu-item', 'current_page_item', 'current-menu-ancestor', 'current-menu-parent' ) );
	}

	$url   = (string) $item->url;
	$title = (string) $item->title;

	if ( is_singular( array( 'cg_blog', 'post' ) ) ) {
		if ( 'Publication' === $title || false !== strpos( $url, '/blog' ) ) {
			$classes[] = 'current-menu-ancestor';
			$classes[] = 'current-menu-parent';
			if ( false !== strpos( $url, '/blog' ) ) {
				$classes[] = 'current-menu-item';
			}
		}
	} elseif ( is_singular( 'cg_press' ) ) {
		if ( 'Publication' === $title || false !== strpos( $url, '/in-the-press' ) ) {
			$classes[] = 'current-menu-ancestor';
			$classes[] = 'current-menu-parent';
			if ( false !== strpos( $url, '/in-the-press' ) ) {
				$classes[] = 'current-menu-item';
			}
		}
	} elseif ( is_singular( 'cg_voice' ) ) {
		if ( 'Impact' === $title || false !== strpos( $url, '/voices' ) ) {
			$classes[] = 'current-menu-ancestor';
			$classes[] = 'current-menu-parent';
			if ( false !== strpos( $url, '/voices' ) ) {
				$classes[] = 'current-menu-item';
			}
		}
	}

	return array_values( array_unique( $classes ) );
}
add_filter( 'nav_menu_css_class', 'cg_nav_anchor_not_current', 10, 2 );

function cg_nav_anchor_aria( $atts, $item ) {
	if ( false !== strpos( (string) $item->url, '#' ) ) {
		unset( $atts['aria-current'] );
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'cg_nav_anchor_aria', 10, 2 );
