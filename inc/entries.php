<?php
/**
 * Form Entries: every submission (contact, enrollment, notify, hire, trainer) is saved here
 * before any email is attempted, so nothing is lost if mail delivery fails.
 * Admin: "Form Entries" menu. Uploaded files are stored privately and downloaded via a
 * capability-checked link. Export all entries (or one form) as CSV.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

function cg_entry_forms() {
	return array(
		'contact' => 'Contact',
		'enroll'  => 'Enrollment',
		'notify'  => 'Course notify',
		'hire'    => 'Hire a Codegirl',
		'trainer' => 'Become a Trainer',
	);
}

function cg_register_entries() {
	register_post_type(
		'cg_entry',
		array(
			'labels'          => array(
				'name'          => __( 'Form Entries', 'generatepress-child' ),
				'singular_name' => __( 'Form Entry', 'generatepress-child' ),
				'menu_name'     => __( 'Form Entries', 'generatepress-child' ),
				'all_items'     => __( 'All Entries', 'generatepress-child' ),
				'edit_item'     => __( 'View Entry', 'generatepress-child' ),
				'search_items'  => __( 'Search Entries', 'generatepress-child' ),
				'not_found'     => __( 'No entries yet.', 'generatepress-child' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'menu_icon'       => 'dashicons-feedback',
			'menu_position'   => 26,
			'supports'        => false,
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'cg_register_entries' );

/**
 * Store a submission. $pairs = array( 'Label' => 'value', … ). Returns the entry ID (0 on failure).
 */
function cg_save_entry( $form, $pairs, $name, $email, $phone = '', $course = '', $file_rel = '' ) {
	$forms = cg_entry_forms();
	$id    = wp_insert_post(
		array(
			'post_type'   => 'cg_entry',
			'post_status' => 'publish',
			'post_title'  => sprintf( '%s – %s', isset( $forms[ $form ] ) ? $forms[ $form ] : $form, $name ),
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	update_post_meta( $id, '_cg_form', $form );
	update_post_meta( $id, '_cg_name', $name );
	update_post_meta( $id, '_cg_email', $email );
	update_post_meta( $id, '_cg_phone', $phone );
	update_post_meta( $id, '_cg_course', $course );
	update_post_meta( $id, '_cg_fields', wp_slash( wp_json_encode( $pairs ) ) );
	if ( $file_rel ) {
		update_post_meta( $id, '_cg_file', $file_rel );
	}
	return (int) $id;
}

/**
 * Private upload folder (.htaccess + index.php block direct access).
 */
function cg_private_dir() {
	$u   = wp_upload_dir();
	$dir = trailingslashit( $u['basedir'] ) . 'cg-private';
	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
		file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" );
		file_put_contents( $dir . '/index.php', "<?php // Silence is golden.\n" );
	}
	return $dir;
}

/**
 * Admin list columns.
 */
add_filter(
	'manage_cg_entry_posts_columns',
	function () {
		return array(
			'cb'       => '<input type="checkbox" />',
			'title'    => __( 'Entry', 'generatepress-child' ),
			'cg_form'  => __( 'Form', 'generatepress-child' ),
			'cg_email' => __( 'Email', 'generatepress-child' ),
			'cg_phone' => __( 'Phone', 'generatepress-child' ),
			'cg_course' => __( 'Course', 'generatepress-child' ),
			'cg_mail'  => __( 'Emailed', 'generatepress-child' ),
			'date'     => __( 'Date', 'generatepress-child' ),
		);
	}
);

add_action(
	'manage_cg_entry_posts_custom_column',
	function ( $col, $id ) {
		switch ( $col ) {
			case 'cg_form':
				$f = cg_entry_forms();
				$k = get_post_meta( $id, '_cg_form', true );
				echo esc_html( isset( $f[ $k ] ) ? $f[ $k ] : $k );
				break;
			case 'cg_email':
				$e = get_post_meta( $id, '_cg_email', true );
				echo $e ? '<a href="mailto:' . esc_attr( $e ) . '">' . esc_html( $e ) . '</a>' : '—'; // phpcs:ignore WordPress.Security.EscapeOutput
				break;
			case 'cg_phone':
				echo esc_html( get_post_meta( $id, '_cg_phone', true ) ?: '—' );
				break;
			case 'cg_course':
				echo esc_html( get_post_meta( $id, '_cg_course', true ) ?: '—' );
				break;
			case 'cg_mail':
				echo get_post_meta( $id, '_cg_mailed', true ) ? '✔' : '—';
				break;
		}
	},
	10,
	2
);

/**
 * Filter by form + CSV export button on the list screen.
 */
add_action(
	'restrict_manage_posts',
	function ( $post_type ) {
		if ( 'cg_entry' !== $post_type ) {
			return;
		}
		$cur = isset( $_GET['cg_form'] ) ? sanitize_key( wp_unslash( $_GET['cg_form'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<select name="cg_form"><option value="">' . esc_html__( 'All forms', 'generatepress-child' ) . '</option>';
		foreach ( cg_entry_forms() as $k => $label ) {
			printf( '<option value="%s"%s>%s</option>', esc_attr( $k ), selected( $cur, $k, false ), esc_html( $label ) );
		}
		echo '</select> ';
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=cg_export_entries&cg_form=' . rawurlencode( $cur ) ), 'cg_export_entries' );
		echo '<a class="button" href="' . esc_url( $url ) . '">' . esc_html__( 'Export CSV', 'generatepress-child' ) . '</a>';
	}
);

/**
 * Separate sidebar links per form (Enrollments, Course notify, …) under Form Entries.
 */
add_action(
	'admin_menu',
	function () {
		foreach ( cg_entry_forms() as $k => $label ) {
			add_submenu_page(
				'edit.php?post_type=cg_entry',
				$label,
				$label,
				'edit_posts',
				'edit.php?post_type=cg_entry&cg_form=' . $k
			);
		}
	}
);

add_filter(
	'submenu_file',
	function ( $submenu_file ) {
		global $typenow;
		if ( 'cg_entry' === $typenow && ! empty( $_GET['cg_form'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return 'edit.php?post_type=cg_entry&cg_form=' . sanitize_key( wp_unslash( $_GET['cg_form'] ) );
		}
		return $submenu_file;
	}
);

/**
 * Per-form tabs (with counts) above the list table.
 */
add_filter(
	'views_edit-cg_entry',
	function ( $views ) {
		global $wpdb;
		$cur    = isset( $_GET['cg_form'] ) ? sanitize_key( wp_unslash( $_GET['cg_form'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$counts = $wpdb->get_results( "SELECT pm.meta_value f, COUNT(*) n FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = '_cg_form' AND p.post_type = 'cg_entry' AND p.post_status = 'publish' GROUP BY pm.meta_value", OBJECT_K ); // phpcs:ignore WordPress.DB
		if ( $cur && isset( $views['all'] ) ) {
			$views['all'] = str_replace( 'class="current"', '', $views['all'] );
		}
		foreach ( cg_entry_forms() as $k => $label ) {
			$views[ 'cg_' . $k ] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
				esc_url( admin_url( 'edit.php?post_type=cg_entry&cg_form=' . $k ) ),
				$cur === $k ? ' class="current" aria-current="page"' : '',
				esc_html( $label ),
				isset( $counts[ $k ] ) ? (int) $counts[ $k ]->n : 0
			);
		}
		return $views;
	}
);

add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( is_admin() && $q->is_main_query() && 'cg_entry' === $q->get( 'post_type' ) && ! empty( $_GET['cg_form'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$q->set( 'meta_key', '_cg_form' );
			$q->set( 'meta_value', sanitize_key( wp_unslash( $_GET['cg_form'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}
);

/**
 * Entry screen: read-only table of everything that was submitted.
 */
add_action(
	'add_meta_boxes_cg_entry',
	function () {
		add_meta_box(
			'cg_entry_data',
			__( 'Submission', 'generatepress-child' ),
			function ( $post ) {
				$pairs = json_decode( (string) get_post_meta( $post->ID, '_cg_fields', true ), true );
				echo '<table class="widefat striped"><tbody>';
				foreach ( (array) $pairs as $label => $val ) {
					printf( '<tr><th style="width:240px">%s</th><td>%s</td></tr>', esc_html( $label ), nl2br( esc_html( $val ) ) );
				}
				$file = get_post_meta( $post->ID, '_cg_file', true );
				if ( $file ) {
					$url = wp_nonce_url( admin_url( 'admin-post.php?action=cg_entry_file&id=' . $post->ID ), 'cg_entry_file_' . $post->ID );
					printf( '<tr><th>%s</th><td><a href="%s">%s</a></td></tr>', esc_html__( 'Attachment', 'generatepress-child' ), esc_url( $url ), esc_html( preg_replace( '/^[a-f0-9]+-/', '', basename( $file ) ) ) );
				}
				printf( '<tr><th>%s</th><td>%s</td></tr>', esc_html__( 'Email sent to admin', 'generatepress-child' ), get_post_meta( $post->ID, '_cg_mailed', true ) ? 'Yes' : 'No (check mail settings; the data is safe here)' );
				echo '</tbody></table>';
			},
			null,
			'normal',
			'high'
		);
	}
);

/**
 * Secure attachment download (administrators only).
 */
add_action(
	'admin_post_cg_entry_file',
	function () {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		if ( ! $id || ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'cg_entry_file_' . $id ) ) {
			wp_die( esc_html__( 'Not allowed.', 'generatepress-child' ), '', array( 'response' => 403 ) );
		}
		$rel  = get_post_meta( $id, '_cg_file', true );
		$path = trailingslashit( cg_private_dir() ) . basename( (string) $rel );
		if ( ! $rel || ! is_file( $path ) ) {
			wp_die( esc_html__( 'File not found.', 'generatepress-child' ), '', array( 'response' => 404 ) );
		}
		nocache_headers();
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . rawurlencode( preg_replace( '/^[a-f0-9]+-/', '', basename( $path ) ) ) . '"' );
		header( 'Content-Length: ' . filesize( $path ) );
		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
);

/**
 * CSV export (all entries, or one form).
 */
add_action(
	'admin_post_cg_export_entries',
	function () {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'cg_export_entries' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'generatepress-child' ), '', array( 'response' => 403 ) );
		}
		$form = isset( $_GET['cg_form'] ) ? sanitize_key( wp_unslash( $_GET['cg_form'] ) ) : '';
		$args = array( 'post_type' => 'cg_entry', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'date', 'order' => 'DESC' );
		if ( $form ) {
			$args['meta_key']   = '_cg_form';
			$args['meta_value'] = $form;
		}
		$rows = array();
		$cols = array( 'Date', 'Form', 'Name', 'Email', 'Phone', 'Course' );
		foreach ( get_posts( $args ) as $p ) {
			$pairs = json_decode( (string) get_post_meta( $p->ID, '_cg_fields', true ), true );
			foreach ( array_keys( (array) $pairs ) as $k ) {
				if ( ! in_array( $k, $cols, true ) ) {
					$cols[] = $k;
				}
			}
			$rows[] = array(
				'Date'   => $p->post_date,
				'Form'   => get_post_meta( $p->ID, '_cg_form', true ),
				'Name'   => get_post_meta( $p->ID, '_cg_name', true ),
				'Email'  => get_post_meta( $p->ID, '_cg_email', true ),
				'Phone'  => get_post_meta( $p->ID, '_cg_phone', true ),
				'Course' => get_post_meta( $p->ID, '_cg_course', true ),
			) + (array) $pairs;
		}
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="codegirls-entries-' . gmdate( 'Y-m-d' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" ); // UTF-8 BOM so Excel reads it correctly.
		fputcsv( $out, $cols );
		foreach ( $rows as $r ) {
			$line = array();
			foreach ( $cols as $c ) {
				$v      = isset( $r[ $c ] ) ? (string) $r[ $c ] : '';
				$line[] = preg_match( '/^[=+\-@]/', $v ) ? "'" . $v : $v; // Neutralise spreadsheet formulas.
			}
			fputcsv( $out, $line );
		}
		fclose( $out );
		exit;
	}
);

/**
 * Mail "From": WordPress defaults to wordpress@localhost on local sites, which PHPMailer rejects.
 */
add_filter(
	'wp_mail_from',
	function ( $from ) {
		return is_email( $from ) && false !== strpos( $from, '.' ) ? $from : get_option( 'admin_email' );
	}
);
