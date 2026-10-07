<?php
/**
 * Theme supports, image sizes, pattern categories and palette.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Brand palette: single source of truth for GeneratePress Global Colors,
 * the block editor palette and the CSS variables in main.css.
 */
function cg_palette() {
	return array(
		array( 'slug' => 'cg-green',  'name' => 'CodeGirls Green', 'color' => '#0A9D58' ),
		array( 'slug' => 'cg-yellow', 'name' => 'CodeGirls Yellow', 'color' => '#F2B41B' ),
		array( 'slug' => 'cg-navy',   'name' => 'CodeGirls Navy', 'color' => '#223044' ),
		array( 'slug' => 'cg-white',  'name' => 'White', 'color' => '#FFFFFF' ),
	);
}

function cg_setup() {
	add_theme_support( 'editor-color-palette', cg_palette() );
	add_theme_support( 'responsive-embeds' );
	add_image_size( 'cg-card', 980, 502, true );
	add_image_size( 'cg-partner', 288, 124, false );
}
add_action( 'after_setup_theme', 'cg_setup' );

/**
 * Add the palette to GeneratePress Global Colors (Customizer + editor).
 */
function cg_generatepress_global_colors( $colors ) {
	$existing = wp_list_pluck( (array) $colors, 'slug' );
	foreach ( cg_palette() as $c ) {
		if ( ! in_array( $c['slug'], $existing, true ) ) {
			$colors[] = $c;
		}
	}
	return $colors;
}
add_filter( 'generate_default_color_palettes', 'cg_generatepress_global_colors' );

/**
 * Block pattern category for homepage sections.
 */
function cg_pattern_categories() {
	register_block_pattern_category( 'codegirls', array( 'label' => __( 'CodeGirls', 'generatepress-child' ) ) );
}
add_action( 'init', 'cg_pattern_categories' );
