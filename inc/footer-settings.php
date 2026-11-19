<?php
/**
 * Footer content editable from wp-admin (QA-11):
 *
 *   Appearance → Menus → "CodeGirls Footer"   the "Quick links" column (menu location "Footer quick links")
 *   Appearance → Customize → "Footer"          the three locations (name, address, email, phone)
 *                                             and the social links
 *
 * The defaults below are what the footer showed before these settings existed, so nothing
 * changes on the site until someone edits them. A social link left empty is hidden.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/** Convert phone string to an international tel: link. */
function cg_phone_tel_link( $phone ) {
	$clean = preg_replace( '/[^0-9+]/', '', $phone );
	if ( 0 === strpos( $clean, '03' ) ) {
		$clean = '+92' . substr( $clean, 1 );
	}
	return 'tel:' . $clean;
}

/** Quick links shown when no menu is assigned to "Footer quick links" (and used to create that menu). */
function cg_footer_default_links() {
	return array(
		'Home'       => home_url( '/' ),
		'About'      => home_url( '/about/' ),
		'Impact'     => home_url( '/recognitions/' ),
		'Courses'    => home_url( '/courses/' ),
		'Partners'   => home_url( '/#partners' ),
		'Contact Us' => home_url( '/#contact' ),
	);
}

/**
 * Default footer locations: name, address, email, phone.
 * TODO(content): Skardu and South Africa reuse Karachi's address, and Skardu also Karachi's
 * email and phone, until the client sends the real details (they can then enter them in the Customizer).
 */
function cg_footer_default_locations() {
	return array(
		1 => array( 'Karachi', 'Tipu Sultan Rd, Block 7/8', 'info@consulnet.net', '0333-3654348' ),
		2 => array( 'Skardu', 'Tipu Sultan Rd, Block 7/8', 'info@consulnet.net', '0333-3654348' ),
		3 => array( 'South Africa', 'Tipu Sultan Rd, Block 7/8', 'info@codegirls.pro', '+2781 451 5347' ),
	);
}

/**
 * Social links: key => [ label, icon, default URL ].
 * Facebook and LinkedIn come from the old site. TODO(content): real Instagram URL and WhatsApp (https://wa.me/<number>).
 */
function cg_footer_social_defs() {
	return array(
		'facebook'  => array( 'Facebook', 'social-facebook.svg', 'https://www.facebook.com/CodeGirlsbyConsulNetCorp/' ),
		'instagram' => array( 'Instagram', 'social-instagram.svg', '#' ),
		'linkedin'  => array( 'Linkedin', 'social-linkedin.svg', 'https://www.linkedin.com/company/codegirlskhi/' ),
		'whatsapp'  => array( 'Whatsapp', 'social-whatsapp.svg', '#' ),
	);
}

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'cg_footer',
			array(
				'title'       => __( 'Footer', 'generatepress-child' ),
				'description' => __( 'The "Our locations" and "Social" columns of the footer on every page. The "Quick links" column is a menu: Appearance → Menus → "CodeGirls Footer". Leave a location name empty to hide that location, or a social link empty to hide that icon.', 'generatepress-child' ),
				'priority'    => 31,
			)
		);
		$fields = array(
			'name'    => array( __( 'name', 'generatepress-child' ), 'sanitize_text_field', 'text' ),
			'address' => array( __( 'address', 'generatepress-child' ), 'sanitize_text_field', 'text' ),
			'email'   => array( __( 'email', 'generatepress-child' ), 'sanitize_email', 'email' ),
			'phone'   => array( __( 'phone', 'generatepress-child' ), 'sanitize_text_field', 'text' ),
		);
		foreach ( cg_footer_default_locations() as $i => $def ) {
			$n = 0;
			foreach ( $fields as $field => $f ) {
				$id = "cg_footer_loc{$i}_{$field}";
				$wp_customize->add_setting( $id, array( 'default' => $def[ $n++ ], 'sanitize_callback' => $f[1] ) );
				/* translators: 1: location number, 2: field name */
				$wp_customize->add_control( $id, array( 'label' => sprintf( __( 'Location %1$d %2$s', 'generatepress-child' ), $i, $f[0] ), 'section' => 'cg_footer', 'type' => $f[2] ) );
			}
		}
		foreach ( cg_footer_social_defs() as $key => $def ) {
			$id = "cg_footer_social_{$key}";
			$wp_customize->add_setting( $id, array( 'default' => $def[2], 'sanitize_callback' => 'esc_url_raw' ) );
			/* translators: %s: social network */
			$wp_customize->add_control( $id, array( 'label' => sprintf( __( '%s link', 'generatepress-child' ), $def[0] ), 'section' => 'cg_footer', 'type' => 'url' ) );
		}
	}
);

/** Footer locations to show: list of [ name, address, email, phone ]; a location with no name is skipped. */
function cg_footer_locations() {
	$out = array();
	foreach ( cg_footer_default_locations() as $i => $def ) {
		$loc = array();
		foreach ( array( 'name', 'address', 'email', 'phone' ) as $n => $field ) {
			$loc[] = (string) get_theme_mod( "cg_footer_loc{$i}_{$field}", $def[ $n ] );
		}
		if ( '' !== trim( $loc[0] ) ) {
			$out[] = $loc;
		}
	}
	return $out;
}

/** Footer social links to show: label => [ url, icon ]; an empty link is skipped. */
function cg_footer_social() {
	$out = array();
	foreach ( cg_footer_social_defs() as $key => $def ) {
		$url = (string) get_theme_mod( "cg_footer_social_{$key}", $def[2] );
		if ( '' !== trim( $url ) ) {
			$out[ $def[0] ] = array( $url, $def[1] );
		}
	}
	return $out;
}

/** Quick links fallback (no footer menu assigned): the default list, same markup as the menu. */
function cg_footer_links_fallback() {
	echo '<ul class="cg-footer__links">';
	foreach ( cg_footer_default_links() as $label => $url ) {
		echo '<li' . ( is_front_page() && 'Home' === $label ? ' class="is-current"' : '' ) . '><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * One time: create the "CodeGirls Footer" menu with the default quick links and assign it to
 * the footer location, so it shows up ready to edit in Appearance → Menus.
 */
add_action(
	'init',
	function () {
		if ( get_option( 'cg_footer_menu_created' ) ) {
			return;
		}
		update_option( 'cg_footer_menu_created', '1' );
		$locations = get_theme_mod( 'nav_menu_locations', array() );
		if ( ! empty( $locations['footer'] ) ) {
			return; // Someone already assigned a footer menu.
		}
		$menu = wp_get_nav_menu_object( 'CodeGirls Footer' );
		$id   = $menu ? $menu->term_id : wp_create_nav_menu( 'CodeGirls Footer' );
		if ( is_wp_error( $id ) ) {
			return;
		}
		if ( ! $menu ) {
			$pos = 1;
			foreach ( cg_footer_default_links() as $label => $url ) {
				wp_update_nav_menu_item(
					$id,
					0,
					array(
						'menu-item-title'    => $label,
						'menu-item-url'      => $url,
						'menu-item-status'   => 'publish',
						'menu-item-type'     => 'custom',
						'menu-item-position' => $pos++,
					)
				);
			}
		}
		$locations['footer'] = $id;
		set_theme_mod( 'nav_menu_locations', $locations );
	},
	40
);
