<?php
/**
 * Posts page: "Our Blogs" with author/category/date/search filters.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

get_header();

// phpcs:disable WordPress.Security.NonceVerification.Recommended
$sel_author = isset( $_GET['cg_author'] ) ? absint( $_GET['cg_author'] ) : 0;
$sel_cat    = isset( $_GET['cg_cat'] ) ? absint( $_GET['cg_cat'] ) : 0;
$sel_date   = isset( $_GET['cg_date'] ) ? sanitize_text_field( wp_unslash( $_GET['cg_date'] ) ) : '';
$sel_q      = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
// phpcs:enable

$authors    = get_users( array( 'has_published_posts' => array( 'post' ), 'orderby' => 'display_name' ) );
$categories = get_categories( array( 'hide_empty' => true ) );
$blog_url   = get_permalink( get_option( 'page_for_posts' ) ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
?>
<main class="cg-main" id="main">
	<section class="page-hero page-hero--short">
		<div class="cg-wrap page-hero__inner">
			<nav class="page-hero__crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'generatepress-child' ); ?>">
				<a class="page-hero__crumb" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'generatepress-child' ); ?></a>
				<span class="page-hero__sep" aria-hidden="true">&gt;</span>
				<span class="page-hero__crumb page-hero__crumb--current" aria-current="page"><?php esc_html_e( 'Our Blog', 'generatepress-child' ); ?></span>
			</nav>
			<h1 class="page-hero__title"><?php esc_html_e( 'Our Blogs', 'generatepress-child' ); ?></h1>
		</div>
	</section>

	<section class="blog-list">
		<div class="cg-wrap blog__wrap">
			<form class="blog-filters" method="get" action="<?php echo esc_url( $blog_url ); ?>">
				<div class="blog-filters__field">
					<label for="cg_author"><?php esc_html_e( 'Select Author:', 'generatepress-child' ); ?></label>
					<select id="cg_author" name="cg_author" onchange="this.form.submit()">
						<option value="0"><?php esc_html_e( 'All', 'generatepress-child' ); ?></option>
						<?php foreach ( $authors as $a ) : ?>
							<option value="<?php echo esc_attr( $a->ID ); ?>" <?php selected( $sel_author, $a->ID ); ?>><?php echo esc_html( $a->display_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="blog-filters__field">
					<label for="cg_cat"><?php esc_html_e( 'Select Category:', 'generatepress-child' ); ?></label>
					<select id="cg_cat" name="cg_cat" onchange="this.form.submit()">
						<option value="0"><?php esc_html_e( 'All', 'generatepress-child' ); ?></option>
						<?php foreach ( $categories as $c ) : ?>
							<option value="<?php echo esc_attr( $c->term_id ); ?>" <?php selected( $sel_cat, $c->term_id ); ?>><?php echo esc_html( $c->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="blog-filters__field">
					<label for="cg_date"><?php esc_html_e( 'Select Date:', 'generatepress-child' ); ?></label>
					<input id="cg_date" name="cg_date" type="date" value="<?php echo esc_attr( $sel_date ); ?>" onchange="this.form.submit()">
				</div>
				<div class="blog-filters__field blog-filters__field--search">
					<label class="screen-reader-text" for="cg_q"><?php esc_html_e( 'Search blogs', 'generatepress-child' ); ?></label>
					<input id="cg_q" name="q" type="search" value="<?php echo esc_attr( $sel_q ); ?>" placeholder="<?php esc_attr_e( 'Search any blog by title, description', 'generatepress-child' ); ?>">
					<button type="submit" class="blog-filters__go" aria-label="<?php esc_attr_e( 'Search', 'generatepress-child' ); ?>"></button>
				</div>
			</form>

			<?php if ( have_posts() ) : ?>
				<div class="blog__grid">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<article <?php post_class( 'post-card' ); ?>>
							<a class="post-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
								<?php
								if ( has_post_thumbnail() ) {
									the_post_thumbnail( 'large', array( 'class' => 'post-card__img', 'alt' => '' ) );
								}
								?>
							</a>
							<div class="post-card__body">
								<h2 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
								<ul class="post-card__meta">
									<li class="post-card__meta-item post-card__meta-item--author"><?php echo esc_html( get_the_author() ); ?></li>
									<li class="post-card__meta-item post-card__meta-item--date"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></time></li>
								</ul>
								<p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 14, '…' ) ); ?></p>
							</div>
						</article>
					<?php endwhile; ?>
				</div>

				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => '‹',
						'next_text' => '›',
						'class'     => 'blog__pagination',
					)
				);
				?>
			<?php else : ?>
				<p class="blog__empty"><?php esc_html_e( 'No blog posts match your filters.', 'generatepress-child' ); ?> <a href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'Clear filters', 'generatepress-child' ); ?></a></p>
			<?php endif; ?>
		</div>
	</section>

	<?php cg_render_pattern( 'contact' ); ?>
</main>
<?php
get_footer();
