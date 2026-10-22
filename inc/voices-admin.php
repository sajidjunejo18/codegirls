<?php
/**
 * Voices edit screen: one form for non-technical staff (HR / admin).
 *
 * Left: numbered steps (who is she, short intro, her story, journey banner).
 * Right: status, "pin at top", position, and a live preview of her cards.
 * The story is built from "heading" and "paragraph" blocks instead of a free editor,
 * so fonts and spacing always match the site; it is saved as clean HTML in post_content
 * (the front end reads only that) and as blocks in _cg_sections (for editing again).
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/** Skills offered in the dropdown. Anything else typed in is added as a new skill. */
function cg_voice_skill_options() {
	$base = array(
		'HTML', 'CSS', 'JavaScript', 'TypeScript', 'Bootstrap', 'Tailwind CSS', 'jQuery', 'React', 'Next.js', 'Angular', 'Vue.js',
		'Node.js', 'Express.js', 'MERN', 'PHP', 'Laravel', 'WordPress', 'Shopify', 'Python', 'Django', 'Flask', 'Java', 'Spring Boot',
		'C#', '.NET', 'ASP.NET', 'C++', 'Go', 'Kotlin', 'Swift', 'Flutter', 'Dart', 'React Native', 'Android', 'iOS',
		'SQL', 'MySQL', 'PostgreSQL', 'MongoDB', 'Firebase', 'REST APIs', 'GraphQL', 'Git', 'GitHub', 'Docker', 'Kubernetes',
		'AWS', 'Azure', 'Google Cloud', 'DevOps', 'Linux', 'Cybersecurity', 'Networking', 'Ethical Hacking',
		'SQA', 'Manual Testing', 'Automation Testing', 'QA Automation', 'Selenium', 'Cypress', 'Playwright', 'Postman', 'JMeter', 'Security Testing',
		'UI/UX Design', 'Figma', 'Adobe XD', 'Photoshop', 'Illustrator', 'Graphic Design', 'Canva', 'Video Editing',
		'Data Analysis', 'Data Science', 'Machine Learning', 'AI', 'Generative AI', 'Prompt Engineering', 'Power BI', 'Tableau', 'Excel',
		'SAP', 'Salesforce', 'Salesforce Admin', 'CRM', 'ERP',
		'SEO', 'GEO', 'Digital Marketing', 'Social Media Marketing', 'Content Writing', 'Copywriting', 'Technical Writing', 'Amazon Listing Optimization',
		'E-commerce', 'Lead Generation', 'Project Management', 'Agile', 'Scrum', 'Business Analysis', 'Customer Support', 'Freelancing',
		'Communication', 'Leadership', 'HR', 'Administration',
	);
	// Plus every skill already used on a voice, so custom ones show up next time.
	global $wpdb;
	$used = $wpdb->get_col( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_cg_track' AND meta_value <> ''" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	foreach ( $used as $list ) {
		$base = array_merge( $base, cg_voice_split_skills( $list ) );
	}
	$out = array();
	foreach ( $base as $s ) {
		$out[ strtolower( $s ) ] = $s;
	}
	natcasesort( $out );
	return array_values( $out );
}

function cg_voice_split_skills( $list ) {
	// Skills are saved separated by commas only, so names like "Q&A Testing" or
	// "Research and Development" stay whole. A leading "and" (from older imported lists) is dropped.
	$out = array();
	foreach ( explode( ',', (string) $list ) as $skill ) {
		$skill = trim( preg_replace( '/^and\s+/i', '', trim( preg_replace( '/\s+/', ' ', $skill ) ) ) );
		if ( '' !== $skill ) {
			$out[] = $skill;
		}
	}
	return $out;
}

/**
 * Story blocks for the editor: saved ones, or read from the existing HTML
 * (h2/h3 = heading, everything else = paragraph; links kept).
 */
function cg_voice_sections( $post ) {
	$saved = json_decode( (string) get_post_meta( $post->ID, '_cg_sections', true ), true );
	if ( is_array( $saved ) ) {
		return $saved;
	}
	$html = trim( (string) $post->post_content );
	if ( '' === $html ) {
		return array();
	}
	$out = array();
	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="utf-8"?><div id="r">' . wpautop( $html ) . '</div>' );
	libxml_clear_errors();
	$root = $doc->getElementById( 'r' );
	foreach ( $root ? $root->childNodes : array() as $node ) {
		$text = trim( $node->textContent );
		if ( '' === $text ) {
			continue;
		}
		if ( XML_ELEMENT_NODE === $node->nodeType && in_array( strtolower( $node->nodeName ), array( 'h1', 'h2', 'h3', 'h4' ), true ) ) {
			$out[] = array( 'type' => 'heading', 'text' => $text );
			continue;
		}
		$inner = '';
		foreach ( ( XML_ELEMENT_NODE === $node->nodeType ? $node->childNodes : array( $node ) ) as $c ) {
			$inner .= $doc->saveHTML( $c );
		}
		$out[] = array( 'type' => 'text', 'html' => cg_voice_clean_inline( $inner ) );
	}
	return $out;
}

/** Paragraph HTML allowed from the editor: text, links, bold, line breaks. */
function cg_voice_clean_inline( $html ) {
	$html = wp_kses(
		(string) $html,
		array(
			'a'      => array( 'href' => true ),
			'strong' => array(),
			'b'      => array(),
			'br'     => array(),
		)
	);
	$html = preg_replace( '#<b>(.*?)</b>#s', '<strong>$1</strong>', $html );
	return trim( preg_replace( '/[ \t]+/', ' ', str_replace( "\xc2\xa0", ' ', $html ) ) );
}

/** Blocks -> the HTML stored in post_content. */
function cg_voice_sections_html( $sections ) {
	$out = array();
	foreach ( $sections as $s ) {
		if ( 'heading' === ( $s['type'] ?? '' ) ) {
			$t = trim( sanitize_text_field( $s['text'] ?? '' ) );
			if ( '' !== $t ) {
				$out[] = '<h2>' . esc_html( $t ) . '</h2>';
			}
			continue;
		}
		// A paragraph block may hold several paragraphs separated by blank lines.
		foreach ( preg_split( '#(?:<br\s*/?>\s*){2,}|\n\s*\n#i', cg_voice_clean_inline( $s['html'] ?? '' ) ) as $p ) {
			$p = trim( preg_replace( '#^(<br\s*/?>\s*)+|(<br\s*/?>\s*)+$#i', '', trim( $p ) ) );
			if ( '' !== wp_strip_all_tags( $p ) ) {
				$out[] = '<p>' . $p . '</p>';
			}
		}
	}
	return implode( "\n", $out );
}

/** Clean, decoded block list from the submitted form. */
function cg_voice_posted_sections() {
	if ( ! isset( $_POST['cg_sections'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- checked by the caller.
		return null;
	}
	$raw = json_decode( wp_unslash( $_POST['cg_sections'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput,WordPress.Security.NonceVerification
	if ( ! is_array( $raw ) ) {
		return null;
	}
	$clean = array();
	foreach ( $raw as $s ) {
		if ( isset( $s['type'] ) && 'heading' === $s['type'] ) {
			$clean[] = array( 'type' => 'heading', 'text' => sanitize_text_field( $s['text'] ?? '' ) );
		} else {
			$clean[] = array( 'type' => 'text', 'html' => cg_voice_clean_inline( $s['html'] ?? '' ) );
		}
	}
	return $clean;
}

/**
 * Fields a voice needs before it can go live (the form checks these too, in voice-editor.js).
 * Returns the labels of the missing ones.
 */
function cg_voice_missing_required() {
	// phpcs:disable WordPress.Security.NonceVerification -- called after the nonce check.
	$missing = array();
	if ( '' === trim( wp_unslash( $_POST['post_title'] ?? '' ) ) ) {
		$missing[] = __( 'Full name', 'generatepress-child' );
	}
	if ( (int) ( $_POST['_thumbnail_id'] ?? 0 ) <= 0 ) {
		$missing[] = __( 'Photo', 'generatepress-child' );
	}
	if ( '' === trim( wp_unslash( $_POST['cg_role'] ?? '' ) ) ) {
		$missing[] = __( 'Job title', 'generatepress-child' );
	}
	// phpcs:enable
	return $missing;
}

// Never publish an incomplete voice: keep it as a draft and say what is missing.
add_filter(
	'wp_insert_post_data',
	function ( $data ) {
		if ( 'cg_voice' !== $data['post_type'] || ! in_array( $data['post_status'], array( 'publish', 'future' ), true ) || empty( $_POST['cg_voice_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cg_voice_nonce'] ) ), 'cg_voice_save' ) ) {
			return $data;
		}
		$missing = cg_voice_missing_required();
		if ( $missing ) {
			$data['post_status'] = 'draft';
			set_transient( 'cg_voice_missing_' . get_current_user_id(), $missing, 60 );
		}
		return $data;
	},
	20
);

add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'cg_voice' !== $screen->post_type ) {
			return;
		}
		$missing = get_transient( 'cg_voice_missing_' . get_current_user_id() );
		if ( ! $missing ) {
			return;
		}
		delete_transient( 'cg_voice_missing_' . get_current_user_id() );
		printf(
			'<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
			esc_html__( 'Not published yet — saved as a draft.', 'generatepress-child' ),
			esc_html( sprintf( /* translators: %s: list of fields */ __( 'Please fill in: %s.', 'generatepress-child' ), implode( ', ', $missing ) ) )
		);
	}
);

// WordPress would still say "Post published." after the guard above; show the draft message instead.
add_filter(
	'redirect_post_location',
	function ( $location, $post_id ) {
		if ( 'cg_voice' === get_post_type( $post_id ) && 'draft' === get_post_status( $post_id ) && get_transient( 'cg_voice_missing_' . get_current_user_id() ) ) {
			$location = add_query_arg( 'message', 10, remove_query_arg( 'message', $location ) );
		}
		return $location;
	},
	10,
	2
);

// The story is written through the blocks, so build post_content from them on save.
add_filter(
	'wp_insert_post_data',
	function ( $data, $postarr ) {
		if ( 'cg_voice' !== $data['post_type'] || empty( $_POST['cg_voice_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cg_voice_nonce'] ) ), 'cg_voice_save' ) ) {
			return $data;
		}
		$sections = cg_voice_posted_sections();
		if ( null !== $sections ) {
			$data['post_content'] = wp_slash( cg_voice_sections_html( $sections ) );
		}
		return $data;
	},
	10,
	2
);

add_action(
	'save_post_cg_voice',
	function ( $post_id ) {
		if ( empty( $_POST['cg_voice_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cg_voice_nonce'] ) ), 'cg_voice_save' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}
		$sections = cg_voice_posted_sections();
		if ( null !== $sections ) {
			update_post_meta( $post_id, '_cg_sections', wp_slash( wp_json_encode( $sections ) ) );
		}
	},
	5
);

add_filter(
	'enter_title_here',
	function ( $text, $post ) {
		return 'cg_voice' === $post->post_type ? __( 'Her full name', 'generatepress-child' ) : $text;
	},
	10,
	2
);

add_action(
	'add_meta_boxes_cg_voice',
	function () {
		remove_meta_box( 'postimagediv', 'cg_voice', 'side' );
		remove_meta_box( 'slugdiv', 'cg_voice', 'normal' );
		add_meta_box( 'cg_voice_settings', __( 'Settings', 'generatepress-child' ), 'cg_voice_settings_box', 'cg_voice', 'side', 'high' );
		add_meta_box( 'cg_voice_preview', __( 'How her cards look', 'generatepress-child' ), 'cg_voice_preview_box', 'cg_voice', 'side', 'default' );
	}
);

// GeneratePress "Layout" box: story pages are always full width.
add_action(
	'add_meta_boxes',
	function () {
		remove_meta_box( 'generate_layout_options_meta_box', 'cg_voice', 'side' );
	},
	99
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'cg_voice' !== get_current_screen()->post_type ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'cg-voice-editor', CG_URI . '/assets/admin/voice-editor.css', array(), filemtime( CG_DIR . '/assets/admin/voice-editor.css' ) );
		wp_enqueue_script( 'cg-voice-editor', CG_URI . '/assets/admin/voice-editor.js', array( 'jquery' ), filemtime( CG_DIR . '/assets/admin/voice-editor.js' ), true );
		wp_localize_script(
			'cg-voice-editor',
			'cgVoice',
			array(
				'skills'      => cg_voice_skill_options(),
				'placeholder' => CG_URI . '/assets/images/voice-meta-role.webp',
				'i18n'        => array(
					'choose' => __( 'Choose image', 'generatepress-child' ),
					'change' => __( 'Change image', 'generatepress-child' ),
					'add'    => __( 'Add “%s” as a new skill', 'generatepress-child' ),
					'none'   => __( 'No matching skill', 'generatepress-child' ),
					'url'    => __( 'Paste the link (web address):', 'generatepress-child' ),
					'sel'    => __( 'First select the words that should become the link.', 'generatepress-child' ),
					'remove' => __( 'Remove this block?', 'generatepress-child' ),
				),
			)
		);
	}
);

function cg_voice_settings_box( $post ) {
	$pinned = '1' === get_post_meta( $post->ID, '_cg_featured', true );
	$auto   = ! $pinned && (int) cg_voice_featured_id() === (int) $post->ID;
	?>
	<?php
	$labels = array( 'publish' => __( 'Published', 'generatepress-child' ), 'future' => __( 'Scheduled', 'generatepress-child' ), 'pending' => __( 'Pending review', 'generatepress-child' ), 'private' => __( 'Private', 'generatepress-child' ) );
	$status = isset( $labels[ $post->post_status ] ) ? $labels[ $post->post_status ] : __( 'Draft', 'generatepress-child' );
	?>
	<div class="cgv-settings">
		<span class="cgv-label"><?php esc_html_e( 'Status', 'generatepress-child' ); ?></span>
		<span class="cgv-status cgv-status--<?php echo esc_attr( $post->post_status ); ?>">● <?php echo esc_html( $status ); ?></span>
		<?php if ( 'auto-draft' !== $post->post_status ) : ?>
			<p class="cgv-hint"><?php echo esc_html( sprintf( /* translators: %s: date and time */ __( 'Last saved %s', 'generatepress-child' ), get_the_modified_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $post ) ) ); ?></p>
		<?php endif; ?>
		<hr>
		<label class="cgv-switch">
			<input type="checkbox" name="cg_featured" value="1" <?php checked( $pinned ); ?>>
			<span class="cgv-switch__ui" aria-hidden="true"></span>
			<span><strong><?php esc_html_e( 'Pin at the top', 'generatepress-child' ); ?></strong>
			<span class="cgv-hint"><?php esc_html_e( 'The newest voice is featured automatically. Turn this on to feature her instead.', 'generatepress-child' ); ?></span></span>
		</label>
		<?php if ( $auto ) : ?>
			<p class="cgv-badge">★ <?php esc_html_e( 'Featured now (newest voice)', 'generatepress-child' ); ?></p>
		<?php endif; ?>
		<hr>
		<label class="cgv-label" for="cg_position"><?php esc_html_e( 'Position on the page', 'generatepress-child' ); ?></label>
		<input type="number" id="cg_position" name="menu_order" min="0" step="1" value="<?php echo (int) $post->menu_order; ?>" class="cgv-num">
		<p class="cgv-hint"><?php esc_html_e( 'Smaller numbers show first in the grid.', 'generatepress-child' ); ?></p>
	</div>
	<?php
}

function cg_voice_preview_box( $post ) {
	$photo = get_the_post_thumbnail_url( $post, 'medium' );
	$role  = get_post_meta( $post->ID, '_cg_role', true );
	?>
	<div class="cgv-preview" data-cgv-preview>
		<span class="cgv-label"><?php esc_html_e( 'How her cards look', 'generatepress-child' ); ?></span>
		<p class="cgv-preview__cap"><?php esc_html_e( 'At the top of the Voices page', 'generatepress-child' ); ?></p>
		<div class="cgv-feat">
			<img class="cgv-ring" data-out="photo" src="<?php echo esc_url( $photo ); ?>" alt=""<?php echo $photo ? '' : ' hidden'; ?>>
			<h4 data-out="name"><?php echo esc_html( $post->post_title ); ?></h4>
			<p class="cgv-feat__meta"><span data-out="loc"><?php echo esc_html( get_post_meta( $post->ID, '_cg_location', true ) ); ?></span> · <span data-out="role"><?php echo esc_html( $role ); ?></span></p>
			<p class="cgv-feat__intro" data-out="intro"><?php echo esc_html( $post->post_excerpt ); ?></p>
			<span class="cgv-feat__btn"><?php esc_html_e( 'Discover her Voice', 'generatepress-child' ); ?></span>
		</div>
		<p class="cgv-preview__cap"><?php esc_html_e( 'In the grid', 'generatepress-child' ); ?></p>
		<div class="cgv-gridcard">
			<img class="cgv-ring cgv-ring--sm" data-out="photo" src="<?php echo esc_url( $photo ); ?>" alt=""<?php echo $photo ? '' : ' hidden'; ?>>
			<div><strong data-out="name"><?php echo esc_html( $post->post_title ); ?></strong><span data-out="role"><?php echo esc_html( $role ); ?></span><b><?php esc_html_e( 'Discover her Voice', 'generatepress-child' ); ?> ↗</b></div>
		</div>
	</div>
	<?php
}

function cg_voice_image_field( $name, $id, $size, $empty, $class ) {
	?>
	<div class="cgv-image <?php echo esc_attr( $class ); ?>" data-cg-image>
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $id ? $id : $empty ); ?>" data-empty="<?php echo esc_attr( $empty ); ?>">
		<div class="cgv-image__preview" data-pick><?php echo $id ? wp_get_attachment_image( $id, $size ) : '<span class="cgv-image__empty">+</span>'; ?></div>
		<div class="cgv-image__actions">
			<button type="button" class="button" data-pick><?php echo $id ? esc_html__( 'Change image', 'generatepress-child' ) : esc_html__( 'Choose image', 'generatepress-child' ); ?></button>
			<button type="button" class="button-link button-link-delete" data-clear<?php echo $id ? '' : ' hidden'; ?>><?php esc_html_e( 'Remove', 'generatepress-child' ); ?></button>
		</div>
	</div>
	<?php
}

add_action(
	'edit_form_top',
	function ( $post ) {
		if ( 'cg_voice' !== $post->post_type ) {
			return;
		}
		$is_new = 'auto-draft' === $post->post_status;
		?>
		<div class="cgv-head" data-cgv-head hidden>
			<p class="cgv-head__sub"><?php esc_html_e( 'Everything for one alumna on one page. Fill it top to bottom.', 'generatepress-child' ); ?></p>
			<div class="cgv-head__actions">
				<?php if ( ! $is_new ) : ?>
					<a class="cgv-btn" href="<?php echo esc_url( 'publish' === $post->post_status ? get_permalink( $post ) : get_preview_post_link( $post ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Preview', 'generatepress-child' ); ?></a>
				<?php endif; ?>
				<button type="button" class="cgv-btn cgv-btn--primary" data-cgv-save><?php echo $is_new ? esc_html__( 'Publish voice', 'generatepress-child' ) : esc_html__( 'Save changes', 'generatepress-child' ); ?></button>
			</div>
		</div>
		<?php if ( ! $is_new && current_user_can( 'delete_post', $post->ID ) ) : ?>
			<p class="cgv-trash" data-cgv-trash hidden><a href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>"><?php esc_html_e( 'Move to Trash', 'generatepress-child' ); ?></a></p>
		<?php endif; ?>
		<?php
	}
);

add_action(
	'edit_form_after_title',
	function ( $post ) {
		if ( 'cg_voice' !== $post->post_type ) {
			return;
		}
		wp_nonce_field( 'cg_voice_save', 'cg_voice_nonce' );
		$skills = cg_voice_split_skills( get_post_meta( $post->ID, '_cg_track', true ) );
		?>
		<div class="cgv">
			<section class="cgv-card">
				<h2 class="cgv-step"><em>1</em> <?php esc_html_e( 'Who is she?', 'generatepress-child' ); ?></h2>
				<div class="cgv-profile">
					<div class="cgv-profile__photo" data-cgv-photo>
						<span class="cgv-label"><?php esc_html_e( 'Photo', 'generatepress-child' ); ?> <span class="cgv-req" aria-hidden="true">*</span></span>
						<?php cg_voice_image_field( '_thumbnail_id', (int) get_post_thumbnail_id( $post ), 'medium', '-1', 'cgv-image--round' ); ?>
						<p class="cgv-hint"><?php esc_html_e( 'A clear photo of her face. Shown in a circle.', 'generatepress-child' ); ?></p>
					</div>
					<div class="cgv-profile__fields">
						<div data-cgv-name></div>
						<div class="cgv-3">
							<div>
								<label class="cgv-label" for="cg_role"><?php esc_html_e( 'Job title', 'generatepress-child' ); ?> <span class="cgv-req" aria-hidden="true">*</span></label>
								<input type="text" id="cg_role" name="cg_role" data-bind="role" required aria-required="true" value="<?php echo esc_attr( get_post_meta( $post->ID, '_cg_role', true ) ); ?>" placeholder="<?php esc_attr_e( 'e.g. Senior SQA Engineer', 'generatepress-child' ); ?>">
							</div>
							<div>
								<label class="cgv-label" for="cg_location"><?php esc_html_e( 'Location', 'generatepress-child' ); ?></label>
								<input type="text" id="cg_location" name="cg_location" data-bind="loc" value="<?php echo esc_attr( get_post_meta( $post->ID, '_cg_location', true ) ); ?>" placeholder="<?php esc_attr_e( 'e.g. Karachi, Pakistan', 'generatepress-child' ); ?>">
							</div>
						</div>
						<label class="cgv-label" for="cgv-skill-search"><?php esc_html_e( 'Skills', 'generatepress-child' ); ?></label>
						<div class="cgv-skills" data-cgv-skills>
							<input type="hidden" name="cg_track" value="<?php echo esc_attr( implode( ', ', $skills ) ); ?>">
							<div class="cgv-skills__box">
								<span class="cgv-skills__chips"></span>
								<input type="text" id="cgv-skill-search" class="cgv-skills__search" autocomplete="off" placeholder="<?php esc_attr_e( 'Type to search, e.g. React', 'generatepress-child' ); ?>" role="combobox" aria-expanded="false" aria-controls="cgv-skill-list">
							</div>
							<ul class="cgv-skills__list" id="cgv-skill-list" role="listbox" hidden></ul>
						</div>
						<p class="cgv-hint"><?php esc_html_e( 'Pick from the list. If a skill is missing, type it and press Enter to add it.', 'generatepress-child' ); ?></p>
					</div>
				</div>
			</section>

			<section class="cgv-card">
				<h2 class="cgv-step"><em>2</em> <?php esc_html_e( 'Short intro', 'generatepress-child' ); ?></h2>
				<textarea name="excerpt" rows="4" data-bind="intro" class="cgv-intro" maxlength="600" placeholder="<?php esc_attr_e( 'Two or three sentences in her words…', 'generatepress-child' ); ?>"><?php echo esc_textarea( $post->post_excerpt ); ?></textarea>
				<p class="cgv-hint"><?php esc_html_e( 'Shown next to her photo when she is featured at the top of the Voices page.', 'generatepress-child' ); ?> <span data-cgv-count></span></p>
			</section>

			<section class="cgv-card">
				<h2 class="cgv-step"><em>3</em> <?php esc_html_e( 'Her story', 'generatepress-child' ); ?></h2>
				<p class="cgv-hint cgv-hint--top"><?php esc_html_e( 'Build the story from headings and paragraphs. To add a link, select the words and click “Link”.', 'generatepress-child' ); ?></p>
				<input type="hidden" name="cg_sections" value="<?php echo esc_attr( wp_json_encode( cg_voice_sections( $post ) ) ); ?>">
				<ol class="cgv-blocks" data-cgv-blocks></ol>
				<div class="cgv-add">
					<button type="button" class="button button-primary" data-add="heading">+ <?php esc_html_e( 'Add heading', 'generatepress-child' ); ?></button>
					<button type="button" class="button" data-add="text">+ <?php esc_html_e( 'Add paragraph', 'generatepress-child' ); ?></button>
				</div>
			</section>

			<section class="cgv-card">
				<h2 class="cgv-step"><em>4</em> <?php esc_html_e( 'Journey banner', 'generatepress-child' ); ?> <span class="cgv-opt"><?php esc_html_e( 'optional', 'generatepress-child' ); ?></span></h2>
				<?php cg_voice_image_field( 'cg_banner', (int) get_post_meta( $post->ID, '_cg_banner', true ), 'large', '', 'cgv-image--wide' ); ?>
				<p class="cgv-hint"><?php esc_html_e( 'The wide “Beyond the Job…” style graphic shown above her story.', 'generatepress-child' ); ?></p>
			</section>
		</div>
		<?php
	}
);
