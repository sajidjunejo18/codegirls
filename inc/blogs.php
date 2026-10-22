<?php
/**
 * Blogs: our own "Blogs" menu in the admin, separate from WordPress' built-in Posts
 * (Posts, its categories and editor are left exactly as WordPress ships them).
 *
 *   cg_blog            the blogs
 *   cg_blog_category   their categories   (Blogs → Categories)
 *   cg_blog_author     their authors      (Blogs → Authors), simple names, no WordPress login needed
 *
 *   [cg_blogs]         the "Our Blogs" listing: filters by author / category / date / search + cards
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'cg_blog',
			array(
				'labels'          => array(
					'name'                  => __( 'Blogs', 'generatepress-child' ),
					'singular_name'         => __( 'Blog', 'generatepress-child' ),
					'add_new'               => __( 'Add New Blog', 'generatepress-child' ),
					'add_new_item'          => __( 'Add New Blog', 'generatepress-child' ),
					'edit_item'             => __( 'Edit Blog', 'generatepress-child' ),
					'all_items'             => __( 'All Blogs', 'generatepress-child' ),
					'featured_image'        => __( 'Picture', 'generatepress-child' ),
					'set_featured_image'    => __( 'Set picture', 'generatepress-child' ),
					'remove_featured_image' => __( 'Remove picture', 'generatepress-child' ),
					'not_found'             => __( 'No blogs yet.', 'generatepress-child' ),
				),
				'public'          => true,
				'has_archive'     => false, // The "Our Blogs" page (/blog/) is the listing.
				'rewrite'         => array( 'slug' => 'blog-details', 'with_front' => false ),
				'show_in_rest'    => false, // Own form instead of the block builder (inc/blog-admin.php).
				'menu_icon'       => 'dashicons-edit-large',
				'menu_position'   => 24,
				'supports'        => array( 'title', 'thumbnail' ),
				'capability_type' => 'post',
			)
		);
		register_taxonomy(
			'cg_blog_category',
			'cg_blog',
			array(
				'labels'            => array(
					'name'          => __( 'Blog categories', 'generatepress-child' ),
					'singular_name' => __( 'Blog category', 'generatepress-child' ),
					'menu_name'     => __( 'Categories', 'generatepress-child' ),
					'add_new_item'  => __( 'Add New Category', 'generatepress-child' ),
					'edit_item'     => __( 'Edit Category', 'generatepress-child' ),
					'not_found'     => __( 'No categories yet.', 'generatepress-child' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => false,
				'meta_box_cb'       => false, // Chosen in the form instead.
				'hierarchical'      => false,
				'rewrite'           => false,
			)
		);
		register_taxonomy(
			'cg_blog_author',
			'cg_blog',
			array(
				'labels'            => array(
					'name'          => __( 'Blog authors', 'generatepress-child' ),
					'singular_name' => __( 'Blog author', 'generatepress-child' ),
					'menu_name'     => __( 'Authors', 'generatepress-child' ),
					'add_new_item'  => __( 'Add New Author', 'generatepress-child' ),
					'edit_item'     => __( 'Edit Author', 'generatepress-child' ),
					'not_found'     => __( 'No authors yet.', 'generatepress-child' ),
				),
				'public'            => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => false,
				'meta_box_cb'       => false,
				'hierarchical'      => false,
				'rewrite'           => false,
			)
		);
	}
);

// Flush rewrite rules once so /blog-details/<name>/ resolves right after this is deployed.
add_action(
	'init',
	function () {
		if ( get_option( 'cg_blog_rewrite' ) !== '1' ) {
			flush_rewrite_rules( false );
			update_option( 'cg_blog_rewrite', '1' );
		}
	},
	20
);

// Article pages use the same full-width layout as the other pages (no sidebar).
add_filter(
	'body_class',
	function ( $classes ) {
		if ( is_singular( 'cg_blog' ) ) {
			$classes[] = 'cg-fullwidth';
		}
		return $classes;
	}
);
add_filter( 'generate_sidebar_layout', fn( $layout ) => is_singular( 'cg_blog' ) ? 'no-sidebar' : $layout );

require_once __DIR__ . '/blog-admin.php'; // Edit screen + article blocks.

/** URL of the "Our Blogs" listing page. */
function cg_blogs_url() {
	$page = get_page_by_path( 'blog' );
	return $page ? get_permalink( $page ) : home_url( '/' );
}

/** Author name of a blog ('' if none). */
function cg_blog_author_name( $id ) {
	$t = get_the_terms( $id, 'cg_blog_author' );
	return $t && ! is_wp_error( $t ) ? $t[0]->name : '';
}

/** Card text: the short summary, or else the article's first paragraph (headings skipped). */
function cg_blog_summary( $post, $words = 14 ) {
	$text = trim( (string) $post->post_excerpt );
	if ( '' === $text && preg_match_all( '#<p[^>]*>(.*?)</p>#si', (string) $post->post_content, $m ) ) {
		foreach ( $m[1] as $p ) {
			$text = trim( html_entity_decode( wp_strip_all_tags( $p ), ENT_QUOTES, 'UTF-8' ) );
			if ( '' !== $text ) {
				break;
			}
		}
	}
	return wp_trim_words( '' !== $text ? $text : wp_strip_all_tags( (string) $post->post_content ), $words, '…' );
}

/**
 * [cg_blogs]  filters + card grid. Filters: ?cg_author=ID&cg_cat=ID&cg_date=YYYY-MM-DD&q=text&cg_page=N
 */
function cg_blogs_shortcode() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$sel_author = isset( $_GET['cg_author'] ) ? absint( $_GET['cg_author'] ) : 0;
	$sel_cat    = isset( $_GET['cg_cat'] ) ? absint( $_GET['cg_cat'] ) : 0;
	$sel_date   = isset( $_GET['cg_date'] ) ? sanitize_text_field( wp_unslash( $_GET['cg_date'] ) ) : '';
	$sel_q      = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	$paged      = isset( $_GET['cg_page'] ) ? max( 1, absint( $_GET['cg_page'] ) ) : 1;
	// phpcs:enable

	$args = array(
		'post_type'      => 'cg_blog',
		'post_status'    => 'publish',
		'posts_per_page' => 9,
		'paged'          => $paged,
		'orderby'        => array( 'date' => 'DESC', 'ID' => 'DESC' ),
	);
	$tax = array();
	if ( $sel_author ) {
		$tax[] = array( 'taxonomy' => 'cg_blog_author', 'terms' => $sel_author );
	}
	if ( $sel_cat ) {
		$tax[] = array( 'taxonomy' => 'cg_blog_category', 'terms' => $sel_cat );
	}
	if ( $tax ) {
		$args['tax_query'] = $tax; // phpcs:ignore WordPress.DB.SlowDBQuery
	}
	if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $sel_date, $m ) ) {
		$args['date_query'] = array( array( 'year' => (int) $m[1], 'month' => (int) $m[2], 'day' => (int) $m[3] ) );
	}
	if ( '' !== $sel_q ) {
		$args['s'] = $sel_q;
	}
	$q = new WP_Query( $args );

	$authors    = get_terms( array( 'taxonomy' => 'cg_blog_author', 'hide_empty' => true ) );
	$categories = get_terms( array( 'taxonomy' => 'cg_blog_category', 'hide_empty' => true ) );
	$authors    = is_wp_error( $authors ) ? array() : $authors;
	$categories = is_wp_error( $categories ) ? array() : $categories;
	$blog_url   = cg_blogs_url();

	ob_start();
	?>
	<section class="blog-list">
		<div class="cg-wrap blog__wrap">
			<form class="blog-filters" method="get" action="<?php echo esc_url( $blog_url ); ?>">
				<div class="blog-filters__field">
					<label for="cg_author"><?php esc_html_e( 'Select Author:', 'generatepress-child' ); ?></label>
					<select id="cg_author" name="cg_author" onchange="this.form.submit()">
						<option value="0"><?php esc_html_e( 'All', 'generatepress-child' ); ?></option>
						<?php foreach ( $authors as $a ) : ?>
							<option value="<?php echo esc_attr( $a->term_id ); ?>" <?php selected( $sel_author, $a->term_id ); ?>><?php echo esc_html( $a->name ); ?></option>
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

			<?php if ( $q->have_posts() ) : ?>
				<div class="blog__grid">
					<?php
					while ( $q->have_posts() ) :
						$q->the_post();
						?>
						<article class="post-card">
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
									<?php if ( cg_blog_author_name( get_the_ID() ) ) : ?>
										<li class="post-card__meta-item post-card__meta-item--author"><?php echo esc_html( cg_blog_author_name( get_the_ID() ) ); ?></li>
									<?php endif; ?>
									<li class="post-card__meta-item post-card__meta-item--date"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></time></li>
								</ul>
								<p class="post-card__excerpt"><?php echo esc_html( cg_blog_summary( get_post() ) ); ?></p>
							</div>
						</article>
					<?php endwhile; ?>
				</div>
				<?php
				wp_reset_postdata();
				if ( $q->max_num_pages > 1 ) {
					$links = paginate_links(
						array(
							'base'      => esc_url_raw( add_query_arg( 'cg_page', '%#%', $blog_url ) ),
							'format'    => '',
							'current'   => $paged,
							'total'     => $q->max_num_pages,
							'mid_size'  => 1,
							'prev_text' => '‹',
							'next_text' => '›',
							'add_args'  => array_filter( array( 'cg_author' => $sel_author, 'cg_cat' => $sel_cat, 'cg_date' => $sel_date, 'q' => $sel_q ) ),
							'type'      => 'list',
						)
					);
					if ( $links ) {
						echo '<nav class="blog__pagination navigation pagination" aria-label="' . esc_attr__( 'Blogs', 'generatepress-child' ) . '"><div class="nav-links">' . str_replace( array( "<ul class='page-numbers'>", '</ul>', '<li>', '</li>' ), '', $links ) . '</div></nav>'; // phpcs:ignore WordPress.Security.EscapeOutput
					}
				}
				?>
			<?php else : ?>
				<p class="blog__empty"><?php esc_html_e( 'No blog posts match your filters.', 'generatepress-child' ); ?> <a href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'Clear filters', 'generatepress-child' ); ?></a></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode( 'cg_blogs', 'cg_blogs_shortcode' );
