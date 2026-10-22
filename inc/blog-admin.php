<?php
/**
 * Blogs (cg_blog) edit screen: one simple form for non-technical staff (same look as Voices / Reports).
 *
 * Steps: 1 title, category, author, date; 2 picture; 3 short summary (optional);
 * 4 the article, built from Heading / Paragraph / List / Score cards blocks so fonts and
 * spacing always match the site. The article is saved as clean HTML in post_content (the
 * front end reads only that) and as blocks in _cg_blocks (for editing again).
 *
 * Authors are a simple list of names (taxonomy cg_blog_author, Blogs → Authors): writers
 * do not need a WordPress login. In the form, pick a name or type a new one.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------------
 * Article blocks <-> HTML
 * ------------------------------------------------------------------------- */

function cg_blog_clean_inline( $html ) {
	$html = wp_kses( (string) $html, array( 'a' => array( 'href' => true ), 'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(), 'br' => array() ) );
	$html = preg_replace( array( '#<b>(.*?)</b>#s', '#<i>(.*?)</i>#s' ), array( '<strong>$1</strong>', '<em>$1</em>' ), $html );
	return trim( preg_replace( '/[ \t]+/', ' ', str_replace( array( "\xc2\xa0", '&nbsp;' ), ' ', $html ) ) );
}

/** Blocks for the editor: saved ones, or read from the existing HTML. */
function cg_blog_blocks( $post ) {
	$saved = json_decode( (string) get_post_meta( $post->ID, '_cg_blocks', true ), true );
	if ( is_array( $saved ) ) {
		return $saved;
	}
	return cg_blog_html_to_blocks( $post->post_content );
}

function cg_blog_html_to_blocks( $html ) {
	$html = trim( preg_replace( '#<!--.*?-->#s', '', (string) $html ) );
	if ( '' === $html ) {
		return array();
	}
	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="utf-8"?><div id="r">' . wpautop( $html ) . '</div>' );
	libxml_clear_errors();
	$out  = array();
	$walk = function ( $parent ) use ( &$walk, &$out, $doc ) {
		foreach ( $parent->childNodes as $n ) {
			if ( XML_ELEMENT_NODE !== $n->nodeType ) {
				if ( '' !== trim( $n->textContent ) ) {
					$out[] = array( 'type' => 'text', 'html' => cg_blog_clean_inline( $doc->saveHTML( $n ) ) );
				}
				continue;
			}
			$tag = strtolower( $n->nodeName );
			if ( in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5' ), true ) ) {
				$t = trim( preg_replace( '/\s+/u', ' ', str_replace( "\xc2\xa0", ' ', $n->textContent ) ) );
				if ( '' !== $t ) {
					$out[] = array( 'type' => 'heading', 'text' => $t );
				}
			} elseif ( 'ul' === $tag || 'ol' === $tag ) {
				$items = array();
				foreach ( $n->childNodes as $li ) {
					if ( XML_ELEMENT_NODE === $li->nodeType ) {
						$inner = '';
						foreach ( $li->childNodes as $c ) {
							$inner .= $doc->saveHTML( $c );
						}
						$inner = cg_blog_clean_inline( $inner );
						if ( '' !== trim( wp_strip_all_tags( $inner ) ) ) {
							$items[] = $inner;
						}
					}
				}
				// "Name (95.73%)" on every line = score cards (Figma "High Achievers").
				$scores = array();
				foreach ( $items as $it ) {
					if ( preg_match( '/^(.+?)\s*\(\s*([\d.,]+\s*%)\s*\)\s*$/u', wp_strip_all_tags( $it ), $m ) ) {
						$scores[] = array( 'label' => trim( $m[1] ), 'value' => preg_replace( '/\s+/', '', $m[2] ) );
					}
				}
				if ( $items && count( $scores ) === count( $items ) ) {
					$out[] = array( 'type' => 'scores', 'items' => $scores );
				} elseif ( $items ) {
					$out[] = array( 'type' => 'list', 'items' => $items );
				}
			} elseif ( 'div' === $tag && false !== strpos( (string) $n->getAttribute( 'class' ), 'cg-scores' ) ) {
				$scores = array();
				foreach ( $n->getElementsByTagName( 'div' ) as $card ) {
					$p = $card->getElementsByTagName( 'p' );
					if ( 2 === $p->length ) {
						$scores[] = array( 'label' => trim( $p->item( 0 )->textContent ), 'value' => trim( $p->item( 1 )->textContent ) );
					}
				}
				$out[] = array( 'type' => 'scores', 'items' => $scores );
			} elseif ( in_array( $tag, array( 'div', 'section', 'figure', 'blockquote' ), true ) ) {
				$walk( $n );
			} else {
				$inner = '';
				foreach ( ( 'p' === $tag ? $n->childNodes : array( $n ) ) as $c ) {
					$inner .= $doc->saveHTML( $c );
				}
				$inner = cg_blog_clean_inline( $inner );
				if ( '' !== trim( wp_strip_all_tags( $inner ) ) ) {
					$out[] = array( 'type' => 'text', 'html' => $inner );
				}
			}
		}
	};
	$walk( $doc->getElementById( 'r' ) );
	return $out;
}

function cg_blog_blocks_html( $blocks ) {
	$out = array();
	foreach ( $blocks as $b ) {
		$type = $b['type'] ?? 'text';
		if ( 'heading' === $type ) {
			$t = trim( sanitize_text_field( $b['text'] ?? '' ) );
			if ( '' !== $t ) {
				$out[] = '<h3>' . esc_html( $t ) . '</h3>';
			}
		} elseif ( 'list' === $type ) {
			$li = array();
			foreach ( (array) ( $b['items'] ?? array() ) as $it ) {
				$it = cg_blog_clean_inline( $it );
				if ( '' !== trim( wp_strip_all_tags( $it ) ) ) {
					$li[] = '<li>' . $it . '</li>';
				}
			}
			if ( $li ) {
				$out[] = "<ul>\n" . implode( "\n", $li ) . "\n</ul>";
			}
		} elseif ( 'scores' === $type ) {
			$cards = array();
			foreach ( (array) ( $b['items'] ?? array() ) as $it ) {
				$l = trim( sanitize_text_field( $it['label'] ?? '' ) );
				$v = trim( sanitize_text_field( $it['value'] ?? '' ) );
				if ( '' !== $l || '' !== $v ) {
					$cards[] = '<div class="cg-score"><p>' . esc_html( $l ) . '</p><p class="cg-score__value">' . esc_html( $v ) . '</p></div>';
				}
			}
			if ( $cards ) {
				$out[] = '<div class="cg-scores">' . implode( '', $cards ) . '</div>';
			}
		} else {
			foreach ( preg_split( '#(?:<br\s*/?>\s*){2,}|\n\s*\n#i', cg_blog_clean_inline( $b['html'] ?? '' ) ) as $p ) {
				$p = trim( preg_replace( '#^(<br\s*/?>\s*)+|(<br\s*/?>\s*)+$#i', '', trim( $p ) ) );
				if ( '' !== wp_strip_all_tags( $p ) ) {
					$out[] = '<p>' . $p . '</p>';
				}
			}
		}
	}
	return implode( "\n", $out );
}

function cg_blog_nonce_ok() {
	return ! empty( $_POST['cg_blog_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cg_blog_nonce'] ) ), 'cg_blog_save' );
}

/** Clean block list from the submitted form (null if not sent). */
function cg_blog_posted_blocks() {
	if ( ! isset( $_POST['cg_blocks'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- checked by callers.
		return null;
	}
	$raw = json_decode( wp_unslash( $_POST['cg_blocks'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput,WordPress.Security.NonceVerification
	if ( ! is_array( $raw ) ) {
		return null;
	}
	$clean = array();
	foreach ( $raw as $b ) {
		$type = in_array( $b['type'] ?? '', array( 'heading', 'text', 'list', 'scores' ), true ) ? $b['type'] : 'text';
		if ( 'heading' === $type ) {
			$clean[] = array( 'type' => 'heading', 'text' => sanitize_text_field( $b['text'] ?? '' ) );
		} elseif ( 'list' === $type ) {
			$clean[] = array( 'type' => 'list', 'items' => array_map( 'cg_blog_clean_inline', (array) ( $b['items'] ?? array() ) ) );
		} elseif ( 'scores' === $type ) {
			$items = array();
			foreach ( (array) ( $b['items'] ?? array() ) as $it ) {
				$items[] = array( 'label' => sanitize_text_field( $it['label'] ?? '' ), 'value' => sanitize_text_field( $it['value'] ?? '' ) );
			}
			$clean[] = array( 'type' => 'scores', 'items' => $items );
		} else {
			$clean[] = array( 'type' => 'text', 'html' => cg_blog_clean_inline( $b['html'] ?? '' ) );
		}
	}
	return $clean;
}

/** Labels of the required fields missing from the submitted form. */
function cg_blog_missing_required() {
	// phpcs:disable WordPress.Security.NonceVerification -- called after the nonce check.
	$missing = array();
	if ( '' === trim( wp_unslash( $_POST['post_title'] ?? '' ) ) ) {
		$missing[] = __( 'Title', 'generatepress-child' );
	}
	if ( '' === trim( wp_unslash( $_POST['cg_category'] ?? '' ) ) ) {
		$missing[] = __( 'Category', 'generatepress-child' );
	}
	if ( '' === trim( wp_unslash( $_POST['cg_author'] ?? '' ) ) ) {
		$missing[] = __( 'Author', 'generatepress-child' );
	}
	if ( (int) ( $_POST['_thumbnail_id'] ?? 0 ) <= 0 ) {
		$missing[] = __( 'Picture', 'generatepress-child' );
	}
	$blocks = cg_blog_posted_blocks();
	if ( null !== $blocks && '' === trim( wp_strip_all_tags( cg_blog_blocks_html( $blocks ) ) ) ) {
		$missing[] = __( 'Article', 'generatepress-child' );
	}
	// phpcs:enable
	return $missing;
}

add_filter(
	'wp_insert_post_data',
	function ( $data, $postarr ) {
		if ( 'cg_blog' !== $data['post_type'] || ! cg_blog_nonce_ok() ) {
			return $data;
		}
		$blocks = cg_blog_posted_blocks();
		if ( null !== $blocks ) {
			$data['post_content'] = wp_slash( cg_blog_blocks_html( $blocks ) );
		}
		$data['post_excerpt'] = wp_slash( sanitize_textarea_field( wp_unslash( $_POST['cg_summary'] ?? '' ) ) );

		// Date only from the "Date" field, never in the future (so a post is never "scheduled" by accident).
		$stored     = ! empty( $postarr['ID'] ) ? get_post( (int) $postarr['ID'] ) : null;
		$stored_day = $stored && 'auto-draft' !== $stored->post_status ? substr( (string) $stored->post_date, 0, 10 ) : '';
		if ( $stored && $stored_day ) {
			$data['post_date']     = $stored->post_date;
			$data['post_date_gmt'] = '0000-00-00 00:00:00' === $stored->post_date_gmt ? get_gmt_from_date( $stored->post_date ) : $stored->post_date_gmt;
		}
		$day = sanitize_text_field( wp_unslash( $_POST['cg_post_date'] ?? '' ) );
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $day ) && $stored_day !== $day ) {
			$day                   = min( $day, wp_date( 'Y-m-d' ) );
			$data['post_date']     = $day . ' ' . ( $day === wp_date( 'Y-m-d' ) ? wp_date( 'H:i:s' ) : '12:00:00' );
			$data['post_date_gmt'] = get_gmt_from_date( $data['post_date'] );
		}
		if ( 'future' === $data['post_status'] ) {
			$data['post_status'] = 'publish';
		}
		if ( 'publish' === $data['post_status'] ) {
			$missing = cg_blog_missing_required();
			if ( $missing ) {
				$data['post_status'] = 'draft';
				set_transient( 'cg_blog_missing_' . get_current_user_id(), $missing, 60 );
			}
		}
		return $data;
	},
	20,
	2
);

add_action(
	'save_post_cg_blog',
	function ( $post_id ) {
		if ( ! cg_blog_nonce_ok() || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$blocks = cg_blog_posted_blocks();
		if ( null !== $blocks ) {
			update_post_meta( $post_id, '_cg_blocks', wp_slash( wp_json_encode( $blocks ) ) );
		}
		// Category and author: pick an existing name or type a new one (created here).
		$cat = trim( sanitize_text_field( wp_unslash( $_POST['cg_category'] ?? '' ) ) );
		wp_set_object_terms( $post_id, '' === $cat ? array() : array( $cat ), 'cg_blog_category' );
		$author = trim( sanitize_text_field( wp_unslash( $_POST['cg_author'] ?? '' ) ) );
		wp_set_object_terms( $post_id, '' === $author ? array() : array( $author ), 'cg_blog_author' );
	}
);

add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'cg_blog' !== $screen->post_type ) {
			return;
		}
		$missing = get_transient( 'cg_blog_missing_' . get_current_user_id() );
		if ( ! $missing ) {
			return;
		}
		delete_transient( 'cg_blog_missing_' . get_current_user_id() );
		printf(
			'<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
			esc_html__( 'Not published yet — saved as a draft.', 'generatepress-child' ),
			esc_html( sprintf( /* translators: %s: list of fields */ __( 'Please fill in: %s.', 'generatepress-child' ), implode( ', ', $missing ) ) )
		);
	}
);

add_filter(
	'redirect_post_location',
	function ( $location, $post_id ) {
		if ( 'cg_blog' === get_post_type( $post_id ) && 'draft' === get_post_status( $post_id ) && get_transient( 'cg_blog_missing_' . get_current_user_id() ) ) {
			$location = add_query_arg( 'message', 10, remove_query_arg( 'message', $location ) );
		}
		return $location;
	},
	10,
	2
);

/* ---------------------------------------------------------------------------
 * Edit screen
 * ------------------------------------------------------------------------- */

add_filter( 'enter_title_here', fn( $t, $p ) => 'cg_blog' === $p->post_type ? __( 'e.g. CodeGirls Skardu Chapter’s First Graduation Ceremony', 'generatepress-child' ) : $t, 10, 2 );

add_action(
	'add_meta_boxes_cg_blog',
	function () {
		remove_meta_box( 'postimagediv', 'cg_blog', 'side' );
		remove_meta_box( 'slugdiv', 'cg_blog', 'normal' );
		add_meta_box( 'cg_blog_settings', __( 'Status', 'generatepress-child' ), 'cg_blog_settings_box', 'cg_blog', 'side', 'high' );
		add_meta_box( 'cg_blog_preview', __( 'How the card looks', 'generatepress-child' ), 'cg_blog_preview_box', 'cg_blog', 'side', 'default' );
	}
);

add_action( 'add_meta_boxes', fn() => remove_meta_box( 'generate_layout_options_meta_box', 'cg_blog', 'side' ), 99 );

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'cg_blog' !== get_current_screen()->post_type ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'cg-voice-editor', CG_URI . '/assets/admin/voice-editor.css', array(), filemtime( CG_DIR . '/assets/admin/voice-editor.css' ) );
		wp_enqueue_style( 'cg-report-editor', CG_URI . '/assets/admin/report-editor.css', array( 'cg-voice-editor' ), filemtime( CG_DIR . '/assets/admin/report-editor.css' ) );
		wp_enqueue_style( 'cg-blog-editor', CG_URI . '/assets/admin/blog-editor.css', array( 'cg-report-editor' ), filemtime( CG_DIR . '/assets/admin/blog-editor.css' ) );
		wp_enqueue_script( 'cg-blog-editor', CG_URI . '/assets/admin/blog-editor.js', array(), filemtime( CG_DIR . '/assets/admin/blog-editor.js' ), true );
	}
);

function cg_blog_settings_box( $post ) {
	$labels = array( 'publish' => __( 'Published', 'generatepress-child' ), 'pending' => __( 'Pending review', 'generatepress-child' ), 'private' => __( 'Private', 'generatepress-child' ) );
	$status = $labels[ $post->post_status ] ?? __( 'Draft', 'generatepress-child' );
	?>
	<div class="cgv-settings">
		<span class="cgv-label"><?php esc_html_e( 'Status', 'generatepress-child' ); ?></span>
		<span class="cgv-status cgv-status--<?php echo esc_attr( $post->post_status ); ?>">● <?php echo esc_html( $status ); ?></span>
		<?php if ( 'auto-draft' !== $post->post_status ) : ?>
			<p class="cgv-hint"><?php echo esc_html( sprintf( /* translators: %s: date */ __( 'Last saved %s', 'generatepress-child' ), get_the_modified_date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $post ) ) ); ?></p>
		<?php endif; ?>
		<hr>
		<p class="cgv-hint" style="margin:0"><?php esc_html_e( 'Blogs are listed newest first, by date. Visitors can filter them by author, category and date.', 'generatepress-child' ); ?></p>
	</div>
	<?php
}

function cg_blog_preview_box( $post ) {
	$pic = get_the_post_thumbnail_url( $post, 'medium' );
	?>
	<div class="cgv-preview">
		<span class="cgv-label"><?php esc_html_e( 'How the card looks', 'generatepress-child' ); ?></span>
		<div class="cgr-card cgb-card">
			<img data-out="photo" src="<?php echo esc_url( $pic ); ?>" alt=""<?php echo $pic ? '' : ' hidden'; ?>>
			<div class="cgr-card__empty"<?php echo $pic ? ' hidden' : ''; ?>><?php esc_html_e( 'Picture', 'generatepress-child' ); ?></div>
			<strong data-out="name"><?php echo esc_html( $post->post_title ? $post->post_title : __( 'Blog title', 'generatepress-child' ) ); ?></strong>
			<span class="cgb-card__meta"><span data-out="author"><?php echo esc_html( cg_blog_author_name( $post->ID ) ? cg_blog_author_name( $post->ID ) : __( 'Author', 'generatepress-child' ) ); ?></span> · <span data-out="date"><?php echo esc_html( get_the_date( 'M j, Y', $post ) ); ?></span></span>
			<span class="cgb-card__sum" data-out="summary"><?php echo esc_html( cg_blog_summary( $post ) ); ?></span>
			<i aria-hidden="true">↗</i>
		</div>
	</div>
	<?php
}

add_action(
	'edit_form_top',
	function ( $post ) {
		if ( 'cg_blog' !== $post->post_type ) {
			return;
		}
		$is_new = 'auto-draft' === $post->post_status;
		?>
		<div class="cgv-head" data-cgv-head hidden>
			<p class="cgv-head__sub"><?php esc_html_e( 'One blog = one card on the Blogs page and its own article page. Fill it top to bottom.', 'generatepress-child' ); ?></p>
			<div class="cgv-head__actions">
				<?php if ( ! $is_new ) : ?>
					<a class="cgv-btn" href="<?php echo esc_url( 'publish' === $post->post_status ? get_permalink( $post ) : get_preview_post_link( $post ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Preview', 'generatepress-child' ); ?></a>
				<?php endif; ?>
				<button type="button" class="cgv-btn cgv-btn--primary" data-cgv-save><?php echo $is_new ? esc_html__( 'Publish blog', 'generatepress-child' ) : esc_html__( 'Save changes', 'generatepress-child' ); ?></button>
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
		if ( 'cg_blog' !== $post->post_type ) {
			return;
		}
		wp_nonce_field( 'cg_blog_save', 'cg_blog_nonce' );
		$cats    = get_the_terms( $post->ID, 'cg_blog_category' );
		$cat     = $cats && ! is_wp_error( $cats ) ? $cats[0]->name : '';
		$authors = get_terms( array( 'taxonomy' => 'cg_blog_author', 'hide_empty' => false ) );
		$catlist = get_terms( array( 'taxonomy' => 'cg_blog_category', 'hide_empty' => false ) );
		?>
		<div class="cgv">
			<section class="cgv-card">
				<h2 class="cgv-step"><em>1</em> <?php esc_html_e( 'Title and details', 'generatepress-child' ); ?></h2>
				<div data-cgv-name></div>
				<div class="cgb-3">
					<div>
						<label class="cgv-label" for="cg_category"><?php esc_html_e( 'Category', 'generatepress-child' ); ?> <span class="cgv-req" aria-hidden="true">*</span></label>
						<input type="text" id="cg_category" name="cg_category" list="cg-cat-list" autocomplete="off" value="<?php echo esc_attr( $cat ); ?>" placeholder="<?php esc_attr_e( 'Pick or type a new one', 'generatepress-child' ); ?>">
						<datalist id="cg-cat-list"><?php foreach ( is_array( $catlist ) ? $catlist : array() as $t ) : ?><option value="<?php echo esc_attr( $t->name ); ?>"></option><?php endforeach; ?></datalist>
					</div>
					<div>
						<label class="cgv-label" for="cg_author"><?php esc_html_e( 'Author', 'generatepress-child' ); ?> <span class="cgv-req" aria-hidden="true">*</span></label>
						<input type="text" id="cg_author" name="cg_author" list="cg-author-list" autocomplete="off" data-bind="author" value="<?php echo esc_attr( cg_blog_author_name( $post->ID ) ); ?>" placeholder="<?php esc_attr_e( 'Pick or type a new name', 'generatepress-child' ); ?>">
						<datalist id="cg-author-list"><?php foreach ( is_array( $authors ) ? $authors : array() as $t ) : ?><option value="<?php echo esc_attr( $t->name ); ?>"></option><?php endforeach; ?></datalist>
					</div>
					<div>
						<label class="cgv-label" for="cg_post_date"><?php esc_html_e( 'Date', 'generatepress-child' ); ?></label>
						<input type="date" id="cg_post_date" name="cg_post_date" value="<?php echo esc_attr( get_the_date( 'Y-m-d', $post ) ); ?>" max="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>">
					</div>
				</div>
				<p class="cgv-hint"><?php esc_html_e( 'Choose from the list, or type a new category or author name — it is added automatically.', 'generatepress-child' ); ?></p>
			</section>

			<section class="cgv-card" data-cgv-photo>
				<h2 class="cgv-step"><em>2</em> <?php esc_html_e( 'Picture', 'generatepress-child' ); ?> <span class="cgv-req" aria-hidden="true">*</span></h2>
				<?php cg_voice_image_field( '_thumbnail_id', (int) get_post_thumbnail_id( $post ), 'large', '-1', 'cgv-image--wide cgr-cover' ); ?>
				<p class="cgv-hint"><?php esc_html_e( 'Shown on the card and at the top of the article. A landscape photo works best.', 'generatepress-child' ); ?></p>
			</section>

			<section class="cgv-card">
				<h2 class="cgv-step"><em>3</em> <?php esc_html_e( 'Short summary', 'generatepress-child' ); ?> <span class="cgv-opt"><?php esc_html_e( 'optional', 'generatepress-child' ); ?></span></h2>
				<textarea name="cg_summary" rows="3" data-bind="summary" maxlength="300" placeholder="<?php esc_attr_e( 'One or two sentences for the card…', 'generatepress-child' ); ?>"><?php echo esc_textarea( $post->post_excerpt ); ?></textarea>
				<p class="cgv-hint"><?php esc_html_e( 'Shown under the title on the Blogs page. Leave empty to use the start of the article.', 'generatepress-child' ); ?></p>
			</section>

			<section class="cgv-card" data-cgv-article>
				<h2 class="cgv-step"><em>4</em> <?php esc_html_e( 'Article', 'generatepress-child' ); ?> <span class="cgv-req" aria-hidden="true">*</span></h2>
				<p class="cgv-hint cgv-hint--top"><?php esc_html_e( 'Build the article from blocks. To add a link, select the words and click “Link”.', 'generatepress-child' ); ?></p>
				<input type="hidden" name="cg_blocks" value="<?php echo esc_attr( wp_json_encode( cg_blog_blocks( $post ) ) ); ?>">
				<ol class="cgv-blocks" data-cgb-blocks></ol>
				<div class="cgv-add">
					<button type="button" class="button button-primary" data-add="heading">+ <?php esc_html_e( 'Heading', 'generatepress-child' ); ?></button>
					<button type="button" class="button" data-add="text">+ <?php esc_html_e( 'Paragraph', 'generatepress-child' ); ?></button>
					<button type="button" class="button" data-add="list">+ <?php esc_html_e( 'Bullet list', 'generatepress-child' ); ?></button>
					<button type="button" class="button" data-add="scores">+ <?php esc_html_e( 'Score cards', 'generatepress-child' ); ?></button>
				</div>
			</section>
		</div>
		<?php
	}
);
