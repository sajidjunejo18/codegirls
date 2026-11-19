<?php
/**
 * Site footer (matches the Figma "Contact" component footer).
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

$img = static function ( $file ) {
	return esc_url( CG_URI . '/assets/images/' . $file );
};

// Editable in wp-admin (inc/footer-settings.php): quick links = Appearance → Menus → "CodeGirls Footer";
// locations and social links = Appearance → Customize → Footer. Real URLs open in a new tab.
$locations = cg_footer_locations();
$social    = cg_footer_social();
?>
<footer class="cg-footer">
	<div class="cg-footer__cols">
		<div class="cg-footer__brand">
			<div class="cg-footer__logos">
				<img src="<?php echo $img( 'logo-footer.svg' ); ?>" alt="CodeGirls" width="235" height="43" loading="lazy">
				<img src="<?php echo $img( 'badge-accredited.webp' ); ?>" alt="Accredited by American Skills Evaluation Institute" width="53" height="53" loading="lazy">
				<img src="<?php echo $img( 'badge-award.webp' ); ?>" alt="Gold winner, Community Initiative of the Year (non-corporate)" width="55" height="55" loading="lazy">
			</div>
			<p class="cg-footer__about"><?php esc_html_e( 'Empowering women in tech across Karachi, Skardu, and Cape Town. Launching careers, building futures.', 'generatepress-child' ); ?></p>
			<div class="cg-footer__sdgs">
				<img src="<?php echo $img( 'sdg-1.svg' ); ?>" alt="SDG 1: No Poverty" width="76" height="77" loading="lazy">
				<img src="<?php echo $img( 'sdg-4.svg' ); ?>" alt="SDG 4: Quality Education" width="96" height="80" loading="lazy">
				<img src="<?php echo $img( 'sdg-5.svg' ); ?>" alt="SDG 5: Gender Equality" width="83" height="87" loading="lazy">
				<img src="<?php echo $img( 'sdg-8.svg' ); ?>" alt="SDG 8: Decent Work and Economic Growth" width="146" height="79" loading="lazy">
				<img src="<?php echo $img( 'sdg-17.svg' ); ?>" alt="SDG 17: Partnership for the Goals" width="130" height="79" loading="lazy">
			</div>
			<p class="cg-footer__caption"><?php esc_html_e( 'Working towards these United Nations Sustainable Development Goals (SDGs)', 'generatepress-child' ); ?></p>
		</div>

		<nav aria-label="<?php esc_attr_e( 'Quick links', 'generatepress-child' ); ?>">
			<h2><?php esc_html_e( 'Quick links', 'generatepress-child' ); ?></h2>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'cg-footer__links',
					'depth'          => 1,
					'fallback_cb'    => 'cg_footer_links_fallback',
				)
			);
			?>
		</nav>

		<div>
			<h2><?php esc_html_e( 'Our locations', 'generatepress-child' ); ?></h2>
			<ul class="cg-footer__locations">
				<?php foreach ( $locations as $loc ) : ?>
					<li class="cg-footer__location">
						<img src="<?php echo $img( 'icon-pin-yellow.svg' ); ?>" alt="" width="24" height="33" loading="lazy">
						<span>
							<strong><?php echo esc_html( $loc[0] ); ?>:</strong> <?php echo esc_html( $loc[1] ); ?><br>
							<a href="mailto:<?php echo esc_attr( $loc[2] ); ?>"><?php echo esc_html( $loc[2] ); ?></a> &nbsp;|&nbsp; <a href="<?php echo esc_attr( cg_phone_tel_link( $loc[3] ) ); ?>"><?php echo esc_html( $loc[3] ); ?></a>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div>
			<h2><?php esc_html_e( 'Social', 'generatepress-child' ); ?></h2>
			<ul class="cg-footer__social">
				<?php foreach ( $social as $label => $s ) : ?>
					<li><a href="<?php echo esc_url( $s[0] ); ?>"<?php echo '#' !== $s[0] ? ' target="_blank" rel="noopener"' : ''; ?>><img src="<?php echo $img( $s[1] ); ?>" alt="" width="26" height="26" loading="lazy"><?php echo esc_html( $label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>

	<div class="cg-footer__bottom">
		<p>
			<?php
			printf(
				/* translators: %s: current year */
				esc_html__( '© Copyright %s | Genetech Solutions – ConsulNet Corporation | All Rights Reserved.', 'generatepress-child' ),
				esc_html( gmdate( 'Y' ) )
			);
			?>
		</p>
		<ul class="cg-footer__legal">
			<li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'generatepress-child' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/terms-and-conditions/' ) ); ?>"><?php esc_html_e( 'Terms and Conditions', 'generatepress-child' ); ?></a></li>
		</ul>
	</div>
</footer>
