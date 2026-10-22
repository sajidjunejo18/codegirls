<?php
/**
 * Homepage hero video (Figma shows a video behind the stats on the right of the hero).
 *
 * Upload it in Appearance → Customize → "Homepage hero video". Until a video is chosen,
 * the hero keeps its photo. The video plays muted on a loop; the round button in its corner
 * pauses / plays it (it starts paused for visitors who ask for reduced motion).
 *
 *   [cg_hero_media]   The hero's right-hand media (video or photo + the pause / play button)
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'cg_hero_video',
			array(
				'title'       => __( 'Homepage hero video', 'generatepress-child' ),
				'description' => __( 'The video behind the numbers (2000+, 70%, 55K+) at the top of the homepage. Use a short MP4 (about 10–30 seconds, under 15 MB). It plays without sound, on a loop. Leave it empty to show the photo instead.', 'generatepress-child' ),
				'priority'    => 30,
			)
		);
		$wp_customize->add_setting(
			'cg_hero_video',
			array(
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'cg_hero_video',
				array(
					'label'     => __( 'Video', 'generatepress-child' ),
					'section'   => 'cg_hero_video',
					'mime_type' => 'video',
				)
			)
		);
	}
);

/** Attachment ID of the hero video (0 if none). */
function cg_hero_video_id() {
	$id = (int) get_option( 'cg_hero_video' );
	return $id && 0 === strpos( (string) get_post_mime_type( $id ), 'video/' ) ? $id : 0;
}

function cg_hero_media_shortcode() {
	$photo = CG_URI . '/assets/images/hero.webp';
	$badge = CG_URI . '/assets/images/hero-badge.svg';
	$video = cg_hero_video_id();

	if ( ! $video ) {
		// No video yet: the photo, with Figma's pause icon as decoration (as before).
		return '<img class="hero__img" src="' . esc_url( $photo ) . '" alt="' . esc_attr__( 'Two women working on laptops in an office', 'generatepress-child' ) . '" width="960" height="678">'
			. '<img class="hero__badge" src="' . esc_url( $badge ) . '" alt="" width="52" height="52">';
	}

	ob_start();
	?>
	<video class="hero__img hero__video" autoplay muted loop playsinline preload="auto" poster="<?php echo esc_url( $photo ); ?>" aria-hidden="true" data-cg-hero-video>
		<source src="<?php echo esc_url( wp_get_attachment_url( $video ) ); ?>" type="<?php echo esc_attr( get_post_mime_type( $video ) ); ?>">
	</video>
	<button type="button" class="hero__badge hero__toggle" aria-label="<?php esc_attr_e( 'Pause background video', 'generatepress-child' ); ?>" aria-pressed="false" data-cg-hero-toggle data-label-pause="<?php esc_attr_e( 'Pause background video', 'generatepress-child' ); ?>" data-label-play="<?php esc_attr_e( 'Play background video', 'generatepress-child' ); ?>">
		<svg class="hero__toggle-pause" width="52" height="52" viewBox="0 0 52 52" aria-hidden="true"><circle cx="26" cy="26" r="26" fill="#000" fill-opacity=".55"/><rect x="18" y="18" width="6" height="16" fill="#fff"/><rect x="27" y="18" width="6" height="16" fill="#fff"/></svg>
		<svg class="hero__toggle-play" width="52" height="52" viewBox="0 0 52 52" aria-hidden="true"><circle cx="26" cy="26" r="26" fill="#000" fill-opacity=".55"/><path d="M21 17v18l15-9z" fill="#fff"/></svg>
	</button>
	<?php
	return ob_get_clean();
}
add_shortcode( 'cg_hero_media', 'cg_hero_media_shortcode' );
