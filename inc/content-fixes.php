<?php
/**
 * One-time content fixes from the staging QA pass, run once per site after deploy (option
 * cg_content_fix_1), so staging and production get them without a manual database edit:
 * - links to the old site codegirls.consulnet.net (privacy, terms, blog posts) point to this site,
 * - "Curriculam" typo on the homepage,
 * - placeholder text under the homepage "Your Pathway to Success" steps.
 * Press items keep their old-site URL in _cg_url (post meta), which is not touched here.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/** Copy for the five pathway steps, in page order (Apply, Learn, Project & Viva, Graduate, Stay tuned). */
function cg_pathway_step_texts() {
	return array(
		'Submit your application online for the next cohort.',
		'Build in-demand tech skills with hands-on training.',
		'Apply your skills to a real project and present it in a viva.',
		'Earn your certificate and celebrate with your cohort.',
		'Stay connected for placements, events and new opportunities.',
	);
}

/** Apply the text fixes to one piece of post content. */
function cg_fix_content( $content ) {
	$home    = trailingslashit( home_url() );
	$content = preg_replace( '#https?://(?:www\.)?codegirls\.consulnet\.net/?#', $home, $content );
	$content = str_replace( 'Curriculam', 'Curriculum', $content );

	$texts = cg_pathway_step_texts();
	$i     = 0;
	return preg_replace_callback(
		'#(pathway__step-text">)Lorem ipsum dolor sit amet, consectetur adipiscing\.#',
		function ( $m ) use ( $texts, &$i ) {
			return $m[1] . esc_html( $texts[ $i++ % count( $texts ) ] );
		},
		$content
	);
}

add_action(
	'init',
	function () {
		if ( get_option( 'cg_content_fix_1' ) === '1' ) {
			return;
		}
		global $wpdb;
		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			"SELECT ID, post_content FROM {$wpdb->posts}
			WHERE post_type <> 'revision'
			AND ( post_content LIKE '%codegirls.consulnet.net%' OR post_content LIKE '%Curriculam%' OR post_content LIKE '%pathway__step-text\">Lorem ipsum%' )"
		);
		foreach ( $rows as $row ) {
			$fixed = cg_fix_content( $row->post_content );
			if ( $fixed !== $row->post_content ) {
				// Direct update: wp_update_post() would run kses for logged-out visitors and strip block markup.
				$wpdb->update( $wpdb->posts, array( 'post_content' => $fixed ), array( 'ID' => $row->ID ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				clean_post_cache( $row->ID );
			}
		}
		update_option( 'cg_content_fix_1', '1' );
	},
	30
);
