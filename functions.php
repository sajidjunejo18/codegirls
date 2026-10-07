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

require_once CG_DIR . '/inc/enqueue.php';
require_once CG_DIR . '/inc/setup.php';
require_once CG_DIR . '/inc/hooks.php';
require_once CG_DIR . '/inc/contact.php';
require_once CG_DIR . '/inc/hire.php';
