<?php
/**
 * Single blog post.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

$blog_page = (int) get_option( 'page_for_posts' );
$blog_url  = $blog_page ? get_permalink( $blog_page ) : home_url( '/' );
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
					<a class="page-hero__crumb" href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'Our Blog', 'generatepress-child' ); ?></a>
					<span class="page-hero__sep" aria-hidden="true">&gt;</span>
					<span class="page-hero__crumb page-hero__crumb--current" aria-current="page"><?php the_title(); ?></span>
				</nav>
				<h1 class="page-hero__title page-hero__title--wide"><?php the_title(); ?></h1>
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
