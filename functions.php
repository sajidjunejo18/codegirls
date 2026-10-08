<?php
/**
 * GeneratePress Child – bootstrap.
 *
 * This file only loads the modules in /inc. Keep logic out of here.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

define( 'CG_VERSION', '0.2.0' );
define( 'CG_DIR', get_stylesheet_directory() );
define( 'CG_URI', get_stylesheet_directory_uri() );

// Demo mode: only the homepage is live; other pages redirect home and their links are inert.
// Set to false to make every page live again. Nothing is deleted.
define( 'CG_HOMEPAGE_ONLY', false );

require_once CG_DIR . '/inc/enqueue.php';
require_once CG_DIR . '/inc/setup.php';
require_once CG_DIR . '/inc/hooks.php';
require_once CG_DIR . '/inc/contact.php';
require_once CG_DIR . '/inc/forms.php';
require_once CG_DIR . '/inc/blog.php';
require_once CG_DIR . '/inc/enroll.php';
require_once CG_DIR . '/inc/homepage-only.php';
