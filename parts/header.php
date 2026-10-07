<?php
/**
 * Site header: logo, primary menu (Appearance → Menus), CTA buttons, mobile toggle.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="cg-header" id="cg-header">
	<a class="cg-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'CodeGirls home', 'generatepress-child' ); ?>">
		<img src="<?php echo esc_url( CG_URI . '/assets/images/logo.svg' ); ?>" alt="CodeGirls" width="332" height="36">
	</a>

	<button class="cg-header__toggle" type="button" aria-expanded="false" aria-controls="cg-panel">
		<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'generatepress-child' ); ?></span>
		<span class="cg-header__bars" aria-hidden="true"></span>
	</button>

	<div class="cg-header__panel" id="cg-panel">
		<nav class="cg-nav" aria-label="<?php esc_attr_e( 'Primary', 'generatepress-child' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'cg-nav__list',
					'fallback_cb'    => false,
					'depth'          => 2,
				)
			);
			?>
		</nav>
		<div class="cg-header__cta">
			<a class="cg-btn" href="<?php echo esc_url( home_url( '/hire-a-codegirl/' ) ); ?>"><?php esc_html_e( 'Hire a Codegirl', 'generatepress-child' ); ?></a>
			<a class="cg-btn" href="<?php echo esc_url( home_url( '/support-codegirls/' ) ); ?>"><?php esc_html_e( 'Sponsor Now', 'generatepress-child' ); ?></a>
		</div>
	</div>
</header>
