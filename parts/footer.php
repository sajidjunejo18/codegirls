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

$links = array(
	'Home'       => home_url( '/' ),
	'About'      => home_url( '/about/' ),
	'Impact'     => home_url( '/impact/' ),
	'Courses'    => home_url( '/courses/' ),
	'Partners'   => home_url( '/partners/' ),
	'Contact Us' => home_url( '/#contact' ),
);

$locations = array(
	array( 'Karachi', 'Tipu Sultan Rd, Block 7/8', 'info@consulnet.net', '0333-3654348' ),
	array( 'Skardu', 'Tipu Sultan Rd, Block 7/8', 'info@consulnet.net', '0333-3654348' ),
	array( 'South Africa', 'Tipu Sultan Rd, Block 7/8', 'info@codegirls.pro', '+2781 451 5347' ),
);

$social = array(
	'Facebook'  => array( '#', 'social-facebook.svg' ),
	'Instagram' => array( '#', 'social-instagram.svg' ),
	'Linkedin'  => array( '#', 'social-linkedin.svg' ),
	'Whatsapp'  => array( '#', 'social-whatsapp.svg' ),
);
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
			<ul class="cg-footer__links">
				<?php foreach ( $links as $label => $url ) : ?>
					<li<?php echo is_front_page() && 'Home' === $label ? ' class="is-current"' : ''; ?>><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<div>
			<h2><?php esc_html_e( 'Our locations', 'generatepress-child' ); ?></h2>
			<ul class="cg-footer__locations">
				<?php foreach ( $locations as $loc ) : ?>
					<li class="cg-footer__location">
						<img src="<?php echo $img( 'icon-pin-yellow.svg' ); ?>" alt="" width="24" height="33" loading="lazy">
						<span>
							<strong><?php echo esc_html( $loc[0] ); ?>:</strong> <?php echo esc_html( $loc[1] ); ?><br>
							<a href="mailto:<?php echo esc_attr( $loc[2] ); ?>"><?php echo esc_html( $loc[2] ); ?></a> &nbsp;|&nbsp; <?php echo esc_html( $loc[3] ); ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div>
			<h2><?php esc_html_e( 'Social', 'generatepress-child' ); ?></h2>
			<ul class="cg-footer__social">
				<?php foreach ( $social as $label => $s ) : ?>
					<li><a href="<?php echo esc_url( $s[0] ); ?>"><img src="<?php echo $img( $s[1] ); ?>" alt="" width="26" height="26" loading="lazy"><?php echo esc_html( $label ); ?></a></li>
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
