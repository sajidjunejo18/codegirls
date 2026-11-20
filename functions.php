<?php
/**
 * GeneratePress Child – bootstrap.
 *
 * This file only loads the modules in /inc. Keep logic out of here.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

// Keep the WordPress version out of public page metadata.
remove_action( 'wp_head', 'wp_generator' );

define( 'CG_VERSION', '0.2.0' );
define( 'CG_DIR', get_stylesheet_directory() );
define( 'CG_URI', get_stylesheet_directory_uri() );

// Demo mode: only the homepage is live; other pages redirect home and their links are inert.
// Set to false to make every page live again. Nothing is deleted.
define( 'CG_HOMEPAGE_ONLY', false );

require_once CG_DIR . '/inc/enqueue.php';
require_once CG_DIR . '/inc/setup.php';
require_once CG_DIR . '/inc/hooks.php';
require_once CG_DIR . '/inc/blog.php';
require_once CG_DIR . '/inc/footer-settings.php';
require_once CG_DIR . '/inc/hero-video.php';
require_once CG_DIR . '/inc/homepage-only.php';

// Content types, forms, Form Entries, email and Site Stats live in the "CodeGirls Core" plugin
// (wp-content/plugins/codegirls-core). The theme only presents them.
if ( ! defined( 'CGC_VERSION' ) ) {
	add_action(
		'admin_notices',
		function () {
			if ( current_user_can( 'activate_plugins' ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'The CodeGirls theme needs the "CodeGirls Core" plugin. Please activate it in Plugins: courses, forms, form entries and other site content depend on it.', 'generatepress-child' ) . '</p></div>';
			}
		}
	);
}
