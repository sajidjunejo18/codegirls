<?php
/**
 * 404 (page not found): the site's green page banner with a short message and links to
 * Home and Courses, instead of GeneratePress's default page with search / recent-posts widgets.
 * There is no Figma design for this page, so it reuses the inner-page banner and buttons.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main class="cg-main" id="main">
	<section class="page-hero not-found">
		<div class="cg-wrap page-hero__inner">
			<p class="not-found__code" aria-hidden="true">404</p>
			<h1 class="page-hero__title"><?php esc_html_e( 'Page not found', 'generatepress-child' ); ?></h1>
			<p class="page-hero__lead"><?php esc_html_e( 'Sorry, the page you are looking for doesn’t exist or has been moved.', 'generatepress-child' ); ?></p>
			<div class="not-found__buttons">
				<a class="cg-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go to Home', 'generatepress-child' ); ?></a>
				<a class="cg-btn cg-btn--navy" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>"><?php esc_html_e( 'View Courses', 'generatepress-child' ); ?></a>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
