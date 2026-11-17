<?php
/**
 * Homepage "Friends of Codegirls" logo strip: all the partner logos from the live site
 * (codegirls.consulnet.net, "Friends Of Codegirls", newest first), not just the 8 in the pattern.
 *
 * The homepage keeps its partners pattern; this swaps the pattern's logo list for the partners
 * managed in wp-admin → "Friends of Codegirls" (see the bottom of this file). The list below is
 * only the starting set, imported there once; its logos are in assets/images/friends/{slug}.webp.
 * To add, remove or reorder a partner, use wp-admin → Friends of Codegirls.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Partner logos: file slug => organisation name (used as the alt text).
 */
function cg_partner_logos() {
	return array(
		'hub-power-company-limited' => 'Hub Power Company Limited',
		'kiran-foundation' => 'Kiran Foundation',
		'jaffer-family-foundation' => 'Jaffer Family Foundation',
		'contour-software' => 'Contour Software',
		'tps-worldwide' => 'TPS WorldWide',
		'genetech-solutions' => 'Genetech Solutions',
		'national-incubation-center-karachi' => 'National Incubation Center Karachi',
		's-p-global' => 'S&P Global',
		'inspire-pakistan' => 'Inspire Pakistan',
		'find-your-courses' => 'Find Your Courses',
		'helping-hands-for-relief-and-development' => 'Helping Hands for Relief and Development',
		'be-me-global' => 'Be Me Global',
		'pakistan-single-window' => 'Pakistan Single Window',
		'click-chain' => 'Click Chain',
		'11-values' => '11 Values',
		'lexa-lawyer' => 'Lexa Lawyer',
		'open-karachi' => 'Open Karachi',
		'afiniti' => 'Afiniti',
		'egb' => 'EGB',
		'cgs' => 'CGS',
		'mena-women-leaders' => 'Mena women leaders',
		'being-tester' => 'Being Tester',
		'craftooo' => 'Craftooo',
		'verve-management' => 'Verve Management',
		'google-developer-student-club-pakistan' => 'Google Developer Student Club Pakistan',
		'khoja-pirhai-shia-isna-asheri-jamaat-karachi' => 'Khoja (Pirhai) Shia Isna Asheri Jamaat Karachi',
		'jj-pak-it' => 'JJ Pak IT',
		'acm-maju-student-chapter' => 'Acm-Maju Student Chapter',
		'connected-women-pakistan' => 'Connected Women Pakistan',
		'women-techmakers-kolachi' => 'Women Techmakers Kolachi',
		'caterpillhers' => 'Caterpillhers',
		'karachi-ai' => 'Karachi AI',
		'ministry-of-information-technology-government-of-pakistan' => 'Ministry of Information Technology Government of Pakistan',
		'fast-university' => 'Fast University',
		'femployment' => 'femployment',
		'itu' => 'ITU',
		'rapid-compute' => 'Rapid Compute',
		'the-marketects-club-tmc' => 'The Marketects Club (TMC)',
		'xloop' => 'Xloop',
		'muhammad-ali-jinnah-university' => 'Muhammad Ali Jinnah university',
		'aptech-learning-pk' => 'Aptech Learning PK',
		'tech-valley-pakistan' => 'Tech Valley Pakistan',
		'circle-women' => 'Circle Women',
		'jinnah-university-for-women' => 'Jinnah University for Women',
		'women-of-wonder-pk' => 'Women Of Wonder Pk',
		'womenintechpk' => 'WomenInTechPK',
		'hemani-and-teizoon-family-trust' => 'Hemani And Teizoon Family Trust',
		'truly-komal' => 'Truly Komal',
		'teesquare' => 'TeeSquare',
		'out-perform-labs' => 'Out Perform Labs',
		'one-good-act' => 'One Good Act',
		'women-in-energy' => 'Women In Energy',
		'codebucketz' => 'CodeBucketz',
		'women-engineers-pakistan' => 'Women Engineers Pakistan',
		'sky-financial-services' => 'Sky Financial Services',
		'musavvir-by-maria-homaee' => 'Musavvir By Maria Homaee',
		'giaic' => 'GIAIC',
		'een-group' => 'Een Group',
		'ann-creations' => 'ANN Creations',
		'hbt' => 'HBT',
		'enchante' => 'Enchante',
		'lincoln-corners-pakistan' => 'Lincoln Corners Pakistan',
		'blue-ex' => 'Blue-ex',
		'al-rehman' => 'Al-Rehman',
		'lamstan-technologies' => 'Lamstan Technologies',
		'nowpdp' => 'nowpdp',
		'investors-lounge' => 'Investors Lounge',
		'pro-women' => 'Pro Women',
		'crypto-chicks' => 'Crypto Chicks',
		'manaky' => 'Manaky',
		'global-dws' => 'Global DWS',
		'equals-global-partnership' => 'EQUALS Global Partnership',
		'pakistan-civil-aviation-authority' => 'Pakistan Civil Aviation Authority',
		'article-hub' => 'Article Hub',
		'comcast-corporation' => 'Comcast Corporation',
		'samiyatex' => 'Samiyatex',
		'epiphany' => 'Epiphany',
		'remotebase' => 'Remotebase',
		'stewart' => 'Stewart',
		'sindh-madressatul-islam' => 'Sindh Madressatul Islam',
		'siemens' => 'Siemens',
		'shia-muslim-council' => 'Shia Muslim Council',
		'sadapay' => 'SadaPay',
		'riksof' => 'Riksof',
		'sunbonn' => 'Sunbonn',
		'relive-now' => 'Relive Now',
		'quick-start' => 'Quick Start',
		'qisst-pay' => 'Qisst Pay',
		'pro-pakistani' => 'Pro Pakistani',
		'payoneer' => 'Payoneer',
		'muhammadi-blood-bank' => 'Muhammadi Blood Bank',
		'midware' => 'Midware',
		'marzia-hassan' => 'Marzia Hassan',
		'keenu' => 'Keenu',
		'kalsoft' => 'Kalsoft',
		'innoventures-global' => 'INNOVentures Global',
		'inaequo' => 'Inaequo',
		'engro-corporations' => 'Engro Corporations',
		'ibex' => 'ibex',
		'hussaini-foundation' => 'Hussaini Foundation',
		'gsma' => 'GSMA',
		'google-developer-groups' => 'Google Developer Groups',
		'imamia-medics-international' => 'Imamia Medics International',
		'arthur-lawrence' => 'Arthur Lawrence',
		'madvertising' => 'MADvertising',
		'she-means-buisness' => 'She Means Buisness',
		'akhuwat' => 'Akhuwat',
		'fatimiyah-education-network' => 'Fatimiyah Education Network',
		'treehouse' => 'Treehouse',
		'techjuice' => 'TechJuice',
		'diya-interactive' => 'Diya Interactive',
		'pakistani-women-in-computing' => 'Pakistani Women In Computing',
		'digi-skills' => 'Digi Skills',
		'lmkr' => 'LMKR',
		'creative-chaos' => 'Creative Chaos',
		'systems' => 'Systems',
		'anankemag' => 'AnankeMag',
		'secure-beans' => 'Secure Beans',
		'rt' => 'RT',
		'cloud-primero' => 'Cloud Primero',
		'10pearls' => '10pearls',
		'bykea' => 'Bykea',
		'the-citizens-foundation' => 'The Citizens Foundation',
		'viper' => 'Viper',
		'w4' => 'W4',
		'ai-maria-middle-east-technologies' => 'AI Maria Middle East Technologies',
		'atlassian' => 'Atlassian',
		'upwork' => 'Upwork',
		'p-sha' => 'P@sha',
		'fatima-gobi-ventures' => 'Fatima Gobi Ventures',
		'tekrevol' => 'TekRevol',
		'motive' => 'Motive',
		'nayapay' => 'NayaPay',
		'mak-global-payment-solutions' => 'MAK Global Payment Solutions',
	);
}

/**
 * Replace the partners pattern's <ul class="partners__track"> with every logo, twice:
 * the strip scrolls by half its width, so the second copy makes the loop seamless.
 * The copy is hidden from screen readers so each name is read once.
 *
 * @param string $html  Rendered block.
 * @param array  $block Block data.
 * @return string
 */
function cg_partners_track( $html, $block ) {
	if ( empty( $block['attrs']['globalClasses'] ) || ! in_array( 'partners__track', (array) $block['attrs']['globalClasses'], true ) ) {
		return $html;
	}
	$logos = cg_partner_items();
	$items = '';
	foreach ( array( false, true ) as $copy ) {
		foreach ( $logos as $logo ) {
			$items .= sprintf(
				'<li class="partners__logo"%s><img class="partners__img" src="%s" alt="%s" width="144" height="62" loading="lazy" decoding="async"></li>',
				$copy ? ' aria-hidden="true"' : '',
				esc_url( $logo[0] ),
				$copy ? '' : esc_attr( $logo[1] )
			);
		}
	}
	// Same scroll speed as the original 8 logos (8 logos in 40s), whatever the number of logos.
	return sprintf( '<ul class="partners__track" style="animation-duration:%ds">%s</ul>', count( $logos ) * 5, $items );
}
add_filter( 'render_block_generateblocks/element', 'cg_partners_track', 10, 2 );

/*
 * Managing the logos in wp-admin (QA-11): the "Friends of Codegirls" menu.
 * Each partner is one entry: the title is the organisation name (the logo's alt text), the
 * Featured image is the logo, and "Order" sets its position (lower first; ties show newest first).
 * The 134 logos above are imported once as entries that point at the theme's logo files;
 * after that, the entries are the only source, so deleting one removes it from the homepage.
 */
function cg_register_partners() {
	register_post_type(
		'cg_partner',
		array(
			'labels'          => array(
				'name'                  => __( 'Friends of Codegirls', 'generatepress-child' ),
				'singular_name'         => __( 'Partner', 'generatepress-child' ),
				'add_new_item'          => __( 'Add New Partner', 'generatepress-child' ),
				'edit_item'             => __( 'Edit Partner', 'generatepress-child' ),
				'featured_image'        => __( 'Logo', 'generatepress-child' ),
				'set_featured_image'    => __( 'Set logo', 'generatepress-child' ),
				'remove_featured_image' => __( 'Remove logo', 'generatepress-child' ),
				'not_found'             => __( 'No partners yet.', 'generatepress-child' ),
			),
			'description'     => __( 'Logos in the scrolling "Friends of Codegirls" strip on the homepage.', 'generatepress-child' ),
			'public'          => false, // No page of their own.
			'show_ui'         => true,
			'show_in_rest'    => false,
			'menu_icon'       => 'dashicons-groups',
			'menu_position'   => 29,
			'supports'        => array( 'title', 'thumbnail', 'page-attributes' ),
			'capability_type' => 'post',
		)
	);
}
add_action( 'init', 'cg_register_partners' );

/**
 * One time: turn the built-in list into "Friends of Codegirls" entries (same names, order and logos).
 * Runs in batches of 25 per page load so no request gets near PHP's time limit, under a database
 * lock so two simultaneous requests can't import the same logos twice. Logos that already have an
 * entry (in any status, including Trash) are skipped. Until every logo has an entry, the homepage
 * keeps using the built-in list.
 */
add_action(
	'init',
	function () {
		global $wpdb;
		if ( get_option( 'cg_partners_imported' ) ) {
			return;
		}
		// INSERT IGNORE is atomic: only the request that creates the lock row goes on.
		if ( ! $wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$wpdb->options} (option_name, option_value, autoload) VALUES ('cg_partners_import_lock', %s, 'no')", (string) time() ) ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$since = (int) $wpdb->get_var( "SELECT option_value FROM {$wpdb->options} WHERE option_name = 'cg_partners_import_lock'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			if ( $since && time() - $since > 300 ) {
				$wpdb->delete( $wpdb->options, array( 'option_name' => 'cg_partners_import_lock' ) ); // Stale lock from a request that died.
			}
			return;
		}
		$have = array_flip( $wpdb->get_col( "SELECT pm.meta_value FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '_cg_logo_file' AND p.post_type = 'cg_partner'" ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$added = 0;
		$left  = 0;
		$pos   = 0;
		foreach ( cg_partner_logos() as $slug => $name ) {
			$order = $pos++;
			if ( isset( $have[ $slug ] ) ) {
				continue;
			}
			if ( $added >= 25 ) {
				++$left;
				continue;
			}
			wp_insert_post(
				array(
					'post_type'   => 'cg_partner',
					'post_status' => 'publish',
					'post_title'  => $name,
					'post_name'   => $slug,
					'menu_order'  => $order,
					'meta_input'  => array( '_cg_logo_file' => $slug ),
				)
			);
			++$added;
		}
		if ( ! $left ) {
			update_option( 'cg_partners_imported', '1' );
		}
		$wpdb->delete( $wpdb->options, array( 'option_name' => 'cg_partners_import_lock' ) );
	},
	20
);

/** Logo URL for a partner entry: its Featured image, else the imported theme file (empty if neither). */
function cg_partner_logo_url( $id ) {
	$thumb = get_post_thumbnail_id( $id );
	if ( $thumb ) {
		return (string) wp_get_attachment_image_url( $thumb, 'cg-partner' );
	}
	$slug = sanitize_file_name( (string) get_post_meta( $id, '_cg_logo_file', true ) );
	return $slug && file_exists( CG_DIR . '/assets/images/friends/' . $slug . '.webp' ) ? get_theme_file_uri( 'assets/images/friends/' . $slug . '.webp' ) : '';
}

/** Logos for the strip: list of [ url, name ]. Entries once imported, otherwise the built-in list. */
function cg_partner_items() {
	if ( ! get_option( 'cg_partners_imported' ) ) {
		$out = array();
		foreach ( cg_partner_logos() as $slug => $name ) {
			$out[] = array( get_theme_file_uri( 'assets/images/friends/' . $slug . '.webp' ), $name );
		}
		return $out;
	}
	$out = array();
	foreach ( get_posts( array( 'post_type' => 'cg_partner', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC', 'ID' => 'DESC' ) ) ) as $p ) {
		$url = cg_partner_logo_url( $p->ID );
		if ( $url ) {
			$out[] = array( $url, get_the_title( $p ) );
		}
	}
	return $out;
}

/** Admin list: show each logo and its order. */
add_filter(
	'manage_cg_partner_posts_columns',
	function ( $cols ) {
		return array_merge( array_slice( $cols, 0, 1 ), array( 'cg_logo' => __( 'Logo', 'generatepress-child' ) ), array_slice( $cols, 1, 1 ), array( 'cg_order' => __( 'Order', 'generatepress-child' ) ), array_slice( $cols, 2 ) );
	}
);
add_action(
	'manage_cg_partner_posts_custom_column',
	function ( $col, $id ) {
		if ( 'cg_logo' === $col ) {
			$url = cg_partner_logo_url( $id );
			echo $url ? '<img src="' . esc_url( $url ) . '" alt="" style="max-width:144px;max-height:62px;object-fit:contain">' : '—';
		} elseif ( 'cg_order' === $col ) {
			echo (int) get_post_field( 'menu_order', $id );
		}
	},
	10,
	2
);
add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( is_admin() && $q->is_main_query() && 'cg_partner' === $q->get( 'post_type' ) && ! $q->get( 'orderby' ) ) {
			$q->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
		}
	}
);
