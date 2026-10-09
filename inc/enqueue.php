<?php
/**
 * Front-end and editor assets.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Preload the two self-hosted variable fonts (they are used above the fold).
 */
function cg_preload_fonts() {
	foreach ( array( 'parkinsans-variable', 'onest-variable' ) as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( CG_URI . "/assets/fonts/{$font}.woff2" )
		);
	}
}
add_action( 'wp_head', 'cg_preload_fonts', 1 );

/**
 * Front-end styles and scripts.
 */
function cg_enqueue_assets() {
	wp_enqueue_style(
		'cg-main',
		CG_URI . '/assets/css/main.css',
		array( 'generate-style' ),
		filemtime( CG_DIR . '/assets/css/main.css' )
	);

	wp_enqueue_script(
		'cg-main',
		CG_URI . '/assets/js/main.js',
		array(),
		filemtime( CG_DIR . '/assets/js/main.js' ),
		array( 'strategy' => 'defer', 'in_footer' => true )
	);
}
add_action( 'wp_enqueue_scripts', 'cg_enqueue_assets' );

/**
 * Load the same stylesheet in the block editor so patterns look right while editing.
 */
function cg_editor_assets() {
	wp_enqueue_style(
		'cg-main-editor',
		CG_URI . '/assets/css/main.css',
		array(),
		filemtime( CG_DIR . '/assets/css/main.css' )
	);
}
add_action( 'enqueue_block_editor_assets', 'cg_editor_assets' );

/**
 * Flag JS-capable browsers early so scroll animations can start from their "before" state
 * without a flash (see "Pathway" in main.css). Without JS the final state is shown.
 */
function cg_js_flag() {
	echo "<script>document.documentElement.className+=' cg-js';</script>\n";
}
add_action( 'wp_head', 'cg_js_flag', 0 );
