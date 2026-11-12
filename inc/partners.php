<?php
/**
 * Homepage "Friends of Codegirls" logo strip: all the partner logos from the live site
 * (codegirls.consulnet.net, "Friends Of Codegirls", newest first), not just the 8 in the pattern.
 *
 * The homepage keeps its partners pattern; this swaps the pattern's logo list for the full list
 * below when the page is shown. Logos are in assets/images/friends/{slug}.webp.
 * To add a partner: add its logo there and a line here.
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
	$logos = cg_partner_logos();
	$items = '';
	foreach ( array( false, true ) as $copy ) {
		foreach ( $logos as $slug => $name ) {
			$items .= sprintf(
				'<li class="partners__logo"%s><img class="partners__img" src="%s" alt="%s" width="144" height="62" loading="lazy" decoding="async"></li>',
				$copy ? ' aria-hidden="true"' : '',
				esc_url( get_theme_file_uri( 'assets/images/friends/' . $slug . '.webp' ) ),
				$copy ? '' : esc_attr( $name )
			);
		}
	}
	// Same scroll speed as the original 8 logos (8 logos in 40s), whatever the number of logos.
	return sprintf( '<ul class="partners__track" style="animation-duration:%ds">%s</ul>', count( $logos ) * 5, $items );
}
add_filter( 'render_block_generateblocks/element', 'cg_partners_track', 10, 2 );
