<?php
/**
 * Homepage-only demo mode (toggle CG_HOMEPAGE_ONLY in functions.php).
 *
 * While on: every front-end page except the homepage redirects to the homepage,
 * and links to those pages render inert (no href) but keep their look.
 * Nothing is deleted; set the constant to false to make everything live again.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'CG_HOMEPAGE_ONLY' ) || ! CG_HOMEPAGE_ONLY ) {
	return;
}

/**
 * Redirect any other front-end URL to the homepage (temporary 302).
 * wp-admin, admin-post.php (forms), REST, AJAX and previews are untouched.
 */
function cg_homepage_only_redirect() {
	if ( is_admin() || wp_doing_ajax() || is_front_page() || is_preview() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	wp_safe_redirect( home_url( '/' ), 302 );
	exit;
}
add_action( 'template_redirect', 'cg_homepage_only_redirect', 1 );

/**
 * Is this URL a link that should stay clickable? (homepage itself or a homepage #anchor)
 */
function cg_homepage_only_allowed( $url ) {
	$url = html_entity_decode( trim( $url ) );
	if ( '#' === $url ) {
		return false; // Bare placeholder links would just jump to the top.
	}
	if ( '' === $url || '#' === $url[0] || 0 === strpos( $url, 'mailto:' ) || 0 === strpos( $url, 'tel:' ) ) {
		return true;
	}
	$home = untrailingslashit( home_url() );
	if ( 0 !== strpos( $url, $home ) ) {
		return true; // External link: not ours to pause.
	}
	$path = (string) wp_parse_url( substr( $url, strlen( $home ) ), PHP_URL_PATH );
	return '' === trim( $path, '/' ); // Homepage, with or without #anchor/query.
}

/**
 * Make paused links inert on the homepage: remove href, mark them, keep the markup/classes.
 */
function cg_homepage_only_filter_html( $html ) {
	return preg_replace_callback(
		'/<a\b([^>]*?)\shref=(["\'])(.*?)\2([^>]*)>/i',
		function ( $m ) {
			if ( cg_homepage_only_allowed( $m[3] ) ) {
				return $m[0];
			}
			$attrs = $m[1] . $m[4];
			if ( preg_match( '/\sclass=(["\'])(.*?)\1/i', $attrs ) ) {
				$attrs = preg_replace( '/\sclass=(["\'])(.*?)\1/i', ' class=$1$2 is-paused$1', $attrs, 1 );
			} else {
				$attrs .= ' class="is-paused"';
			}
			return '<a' . $attrs . ' aria-disabled="true" role="link" tabindex="-1">';
		},
		$html
	);
}

add_action(
	'template_redirect',
	function () {
		if ( is_front_page() && ! is_admin() ) {
			ob_start( 'cg_homepage_only_filter_html' );
		}
	},
	2
);

/**
 * Keep paused pages out of search engines while demo mode is on.
 */
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( ! is_front_page() ) {
			$robots['noindex'] = true;
		}
		return $robots;
	}
);
