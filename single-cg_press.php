<?php
/**
 * Single press item (cg_press): the on-site page for press links that used to open the old
 * CodeGirls website, at /press-details/<name>/. No Figma exists for it, so it reuses the blog article layout.
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
		?>
		<section class="page-hero page-hero--short">
			<div class="cg-wrap page-hero__inner">
				<nav class="page-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'generatepress-child' ); ?>">
					<a class="page-hero__crumb" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'generatepress-child' ); ?></a>
					<span class="page-hero__sep" aria-hidden="true">&gt;</span>
					<a class="page-hero__crumb" href="<?php echo esc_url( cg_press_page_url() ); ?>"><?php esc_html_e( 'In The Press', 'generatepress-child' ); ?></a>
					<span class="page-hero__sep" aria-hidden="true">&gt;</span>
					<span class="page-hero__crumb page-hero__crumb--current" aria-current="page"><?php the_title(); ?></span>
				</nav>
				<h1 class="page-hero__title page-hero__title--wide"><?php the_title(); ?></h1>
				<div class="report__when">
					<time class="report__date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( cg_press_date( get_the_ID() ) ); ?></time>
				</div>
			</div>
		</section>

		<article <?php post_class( 'post' ); ?>>
			<div class="cg-wrap post__wrap">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="post__feature"><?php the_post_thumbnail( 'large' ); ?></figure>
				<?php endif; ?>
				<div class="post__content entry-content">
					<?php the_content(); ?>
				</div>
			</div>
		</article>
	<?php endwhile; ?>

	<?php cg_render_pattern( 'contact' ); ?>
</main>
<?php
get_footer();
