<?php
/**
 * Single voice: an alumna's story (/voices/<name>/).
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="cg-main" id="main">
	<?php
	while ( have_posts() ) :
		the_post();
		$banner = (int) get_post_meta( get_the_ID(), '_cg_banner', true );
		?>
		<section class="vstory">
			<div class="cg-wrap vstory__inner">
				<?php echo cg_voice_photo( get_the_ID(), 'vstory__photo', 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<div class="vstory__main">
					<h1 class="vstory__name"><?php the_title(); ?></h1>
					<ul class="vstory__meta">
						<?php
						foreach ( cg_voice_meta_fields() as $k => $f ) :
							$val = get_post_meta( get_the_ID(), '_cg_' . $k, true );
							if ( '' === $val ) {
								continue;
							}
							?>
							<li class="vstory__meta-item">
								<?php echo cg_icon( $f[1], 'vstory__meta-icon', 24, 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<span class="vstory__meta-text"><?php echo esc_html( $val ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
					<div class="vstory__scroll" tabindex="0" aria-label="<?php esc_attr_e( 'Her story', 'generatepress-child' ); ?>">
						<?php
						if ( $banner ) {
							echo wp_get_attachment_image( $banner, 'full', false, array( 'class' => 'vstory__banner' ) );
						}
						?>
						<div class="vstory__article entry-content">
							<?php the_content(); ?>
						</div>
					</div>
				</div>
			</div>
		</section>
	<?php endwhile; ?>

	<?php cg_render_pattern( 'contact' ); ?>
</main>
<?php
get_footer();
