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

/**
 * Fix 2 (QA, accessibility): homepage partner logos had generic alt text ("Partner logo 1"...).
 * Replaces it with the partner names in both the block JSON and the saved HTML.
 */
add_action(
	'init',
	function () {
		if ( get_option( 'cg_content_fix_2' ) === '1' ) {
			return;
		}
		$names = array( 'Jaffer Family Foundation', 'Contour Software', 'TPS', 'Genetech', 'National Incubation Center Karachi', 'S&P Global', 'Kiran Foundation', 'InspirePakistan' );
		$map   = array();
		foreach ( $names as $i => $name ) {
			$n = $i + 1;
			$map[ '"alt":"Partner logo ' . $n . '"' ] = '"alt":' . wp_json_encode( $name, JSON_HEX_AMP | JSON_UNESCAPED_SLASHES );
			$map[ 'alt="Partner logo ' . $n . '"' ]   = 'alt="' . esc_attr( $name ) . '"';
		}
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type <> 'revision' AND post_content LIKE '%Partner logo %'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		foreach ( $rows as $row ) {
			$fixed = strtr( $row->post_content, $map );
			if ( $fixed !== $row->post_content ) {
				$wpdb->update( $wpdb->posts, array( 'post_content' => $fixed ), array( 'ID' => $row->ID ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				clean_post_cache( $row->ID );
			}
		}
		update_option( 'cg_content_fix_2', '1' );
	},
	31
);

/**
 * Fix 3 (QA vs Figma, text styling only):
 * - Support page: keep "(Phase 1 / 2)" on one line in the first sponsorship card title.
 * - Blog "CodeGirls Skardu Chapter's First Graduation Ceremony": "Media Coverage:" in title case,
 *   bold link labels, bold "top 5 achievers" / "Congratulations…" lines, and Arfa Batool /
 *   Sukaina Abbas no longer bold — all as in Figma. Matched by text, so it is a no-op elsewhere.
 */
add_action(
	'init',
	function () {
		if ( get_option( 'cg_content_fix_3' ) === '1' ) {
			return;
		}
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type <> 'revision' AND post_content LIKE '%tier__title\">Sponsor a Girl (Phase 1 / 2)<%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		foreach ( $rows as $row ) {
			$fixed = str_replace( 'tier__title">Sponsor a Girl (Phase 1 / 2)<', 'tier__title">Sponsor a Girl (Phase&nbsp;1&nbsp;/&nbsp;2)<', $row->post_content );
			$wpdb->update( $wpdb->posts, array( 'post_content' => $fixed ), array( 'ID' => $row->ID ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			clean_post_cache( $row->ID );
		}

		$labels = array( 'Photos Link:', 'Graduation Event Video Skardu TV:', 'Orientation Event Video GBTrue:', 'Graduation Event Video GB True:' );
		$posts  = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_cg_blocks' AND meta_value LIKE '%\"MEDIA COVERAGE:\"%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		foreach ( $posts as $post_id ) {
			$blocks = json_decode( (string) get_post_meta( $post_id, '_cg_blocks', true ), true );
			if ( ! is_array( $blocks ) ) {
				continue;
			}
			foreach ( $blocks as &$b ) {
				if ( 'heading' === ( $b['type'] ?? '' ) && 'MEDIA COVERAGE:' === ( $b['text'] ?? '' ) ) {
					$b['text'] = 'Media Coverage:';
				}
				if ( 'text' !== ( $b['type'] ?? '' ) || ! isset( $b['html'] ) ) {
					continue;
				}
				$h = $b['html'];
				foreach ( $labels as $label ) {
					if ( 0 === strpos( $h, $label ) ) {
						$h = '<strong>' . $label . '</strong>' . substr( $h, strlen( $label ) );
					}
				}
				if ( 0 === strpos( $h, 'The top 5 achievers of Cohort 1 Skardu Chapter are:' ) || 0 === strpos( $h, 'Congratulations to the awardees!' ) ) {
					$h = '<strong>' . $h . '</strong>';
				}
				$h        = str_replace( array( '<strong>Arfa Batool</strong>', '<strong>Sukaina Abbas</strong>' ), array( 'Arfa Batool', 'Sukaina Abbas' ), $h );
				$b['html'] = $h;
			}
			unset( $b );
			update_post_meta( $post_id, '_cg_blocks', wp_slash( wp_json_encode( $blocks ) ) );
			// The front end reads post_content, rendered from the blocks exactly as the blog editor saves it.
			$wpdb->update( $wpdb->posts, array( 'post_content' => cg_blog_blocks_html( $blocks ) ), array( 'ID' => $post_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			clean_post_cache( $post_id );
		}
		update_option( 'cg_content_fix_3', '1' );
	},
	32
);

/**
 * Fix 4 (QA round 3, Basecamp "QA - Wordpress Staging"):
 * - QA-01 Support page: Pakistan account number was missing a digit (live site: 10080081003301015).
 * - QA-02 Courses: "Java & Python" listed the GEO intro twice as its learning points; it gets the Java
 *   points from the live site (same as the "Java" course). GEO loses the stray "REST APIs" line.
 * - QA-06 Press: the 3rd-graduation article's PDFs now point at copies in the theme (assets/pdfs).
 *   TODO(content): the 4th-graduation article links CG-AssessmentReport2021-compressed.pdf and
 *   CG-SponsorshipProposal-compressed.pdf, which are missing on the live site too — need the files.
 * - QA-08 Nine duplicate standard posts "CodeGirls Skardu Chapter's First Graduation Ceremony"
 *   (the real one is the cg_blog post) are set to draft, so they no longer have public URLs.
 * - QA-07 Homepage hero video: the 14 MB 4K upload is swapped for a 1.5 MB 1080p copy
 *   (assets/video/hero-1080.mp4), added to the Media Library and selected in the Customizer.
 */
add_action(
	'init',
	function () {
		if ( get_option( 'cg_content_fix_4' ) === '1' ) {
			return;
		}
		global $wpdb;
		$pdfs = trailingslashit( CG_URI ) . 'assets/pdfs/';
		$rows = $wpdb->get_results( "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type <> 'revision' AND ( post_content LIKE '%1008008103301015%' OR post_content LIKE '%/pdfs/Assessment-Report.pdf%' OR post_content LIKE '%/pdfs/Sponsorship\_Proposal.pdf%' )" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		foreach ( $rows as $row ) {
			$fixed = str_replace( '1008008103301015', '10080081003301015', $row->post_content );
			$fixed = preg_replace( '#href="[^"]*/pdfs/(Assessment-Report|Sponsorship_Proposal)\.pdf"#', 'href="' . $pdfs . '$1.pdf" target="_blank" rel="noopener"', $fixed );
			if ( $fixed !== $row->post_content ) {
				$wpdb->update( $wpdb->posts, array( 'post_content' => $fixed ), array( 'ID' => $row->ID ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				clean_post_cache( $row->ID );
			}
		}

		$geo_intro = 'A 30-class Generative Engine Optimization (GEO) training program for SEO professionals';
		$java      = "Master Java fundamentals and polish OOP concepts\nLearn collections, functional programming, and design patterns\nBuild backend applications with Spring Boot, JPA, and Hibernate\nExplore security, testing, and Git for real-world development\nComplete a hands-on Java & Spring Boot project";
		foreach ( get_posts( array( 'post_type' => 'cg_course', 'post_status' => 'any', 'numberposts' => -1 ) ) as $c ) {
			$learn = (string) get_post_meta( $c->ID, '_cg_learn', true );
			$lines = array_map( 'trim', preg_split( '/\R/', $learn ) );
			if ( false !== stripos( $c->post_title, 'Java' ) && array( $geo_intro, $geo_intro ) === array_values( array_filter( $lines ) ) ) {
				update_post_meta( $c->ID, '_cg_learn', $java );
			} elseif ( false !== stripos( $c->post_title, 'Generative Engine' ) && in_array( 'REST APIs', $lines, true ) ) {
				update_post_meta( $c->ID, '_cg_learn', implode( "\n", array_diff( $lines, array( 'REST APIs' ) ) ) );
			}
		}

		$dupes = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' AND post_name LIKE 'codegirls-skardu-chapters-first-graduation-ceremony%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		foreach ( $dupes as $id ) {
			$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			clean_post_cache( $id );
		}

		$video = (int) get_option( 'cg_hero_video' );
		$small = CG_DIR . '/assets/video/hero-1080.mp4';
		if ( $video && '8179078-uhd_3840_2160_25fps.mp4' === wp_basename( (string) get_attached_file( $video ) ) && is_readable( $small ) ) {
			$up = wp_upload_bits( 'codegirls-hero-1080.mp4', null, file_get_contents( $small ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( empty( $up['error'] ) ) {
				$id = wp_insert_attachment( array( 'post_mime_type' => 'video/mp4', 'post_title' => 'CodeGirls hero video (1080p)', 'post_status' => 'inherit' ), $up['file'] );
				if ( $id && ! is_wp_error( $id ) ) {
					require_once ABSPATH . 'wp-admin/includes/media.php';
					require_once ABSPATH . 'wp-admin/includes/image.php';
					wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $up['file'] ) );
					update_option( 'cg_hero_video', $id );
				}
			}
		}
		update_option( 'cg_content_fix_4', '1' );
	},
	33
);
