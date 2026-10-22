<?php
/**
 * Press edit screen: one simple form for non-technical staff (same look as Reports / Recognitions).
 *
 * Steps: 1 headline + link + date, 2 publisher logo. The sidebar shows the status and a live
 * preview of the card. Headline, link and logo are required. Uses the Recognitions form script
 * (assets/admin/recognition-editor.js), which handles the same title / link / picture fields.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

function cg_press_nonce_ok() {
	return ! empty( $_POST['cg_press_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cg_press_nonce'] ) ), 'cg_press_save' );
}

/** Labels of the required fields missing from the submitted form. */
function cg_press_missing_required() {
	// phpcs:disable WordPress.Security.NonceVerification -- called after the nonce check.
	$missing = array();
	if ( '' === trim( wp_unslash( $_POST['post_title'] ?? '' ) ) ) {
		$missing[] = __( 'Headline', 'generatepress-child' );
	}
	if ( '' === cg_recognition_clean_url( wp_unslash( $_POST['cg_url'] ?? '' ) ) ) {
		$missing[] = __( 'Link', 'generatepress-child' );
	}
	$link = cg_recognition_clean_url( wp_unslash( $_POST['cg_url'] ?? '' ) );
	if ( $link && cg_press_is_internal( $link ) && '' === trim( wp_strip_all_tags( wp_unslash( $_POST['cg_press_content'] ?? '' ) ) ) ) {
		$missing[] = __( 'Article text (needed because the link is on the CodeGirls site)', 'generatepress-child' );
	}
	if ( (int) ( $_POST['_thumbnail_id'] ?? 0 ) <= 0 ) {
		$missing[] = __( 'Publisher logo', 'generatepress-child' );
	}
	// phpcs:enable
	return $missing;
}

add_filter(
	'wp_insert_post_data',
	function ( $data, $postarr ) {
		if ( 'cg_press' !== $data['post_type'] || ! cg_press_nonce_ok() ) {
			return $data;
		}
		// Date only from the "Date" field, never in the future (so an item is never "scheduled").
		$stored     = ! empty( $postarr['ID'] ) ? get_post( (int) $postarr['ID'] ) : null;
		$stored_day = $stored && 'auto-draft' !== $stored->post_status ? substr( (string) $stored->post_date, 0, 10 ) : '';
		if ( $stored && $stored_day ) {
			$data['post_date']     = $stored->post_date;
			$data['post_date_gmt'] = '0000-00-00 00:00:00' === $stored->post_date_gmt ? get_gmt_from_date( $stored->post_date ) : $stored->post_date_gmt;
		}
		$day = sanitize_text_field( wp_unslash( $_POST['cg_press_date'] ?? '' ) );
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $day ) && $stored_day !== $day ) {
			$day                   = min( $day, wp_date( 'Y-m-d' ) );
			$data['post_date']     = $day . ' 12:00:00';
			$data['post_date_gmt'] = get_gmt_from_date( $data['post_date'] );
		}
		if ( isset( $_POST['cg_press_content'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- checked above.
				$data['post_content'] = wp_slash( wp_kses_post( wp_unslash( $_POST['cg_press_content'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			}
			if ( 'future' === $data['post_status'] ) {
			$data['post_status'] = 'publish';
		}
		if ( 'publish' === $data['post_status'] ) {
			$missing = cg_press_missing_required();
			if ( $missing ) {
				$data['post_status'] = 'draft';
				set_transient( 'cg_press_missing_' . get_current_user_id(), $missing, 60 );
			}
		}
		return $data;
	},
	20,
	2
);

add_action(
	'save_post_cg_press',
	function ( $post_id ) {
		if ( ! cg_press_nonce_ok() || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$url = cg_recognition_clean_url( wp_unslash( $_POST['cg_url'] ?? '' ) );
		if ( $url ) {
			update_post_meta( $post_id, '_cg_url', $url );
		} else {
			delete_post_meta( $post_id, '_cg_url' );
		}
	}
);

add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'cg_press' !== $screen->post_type ) {
			return;
		}
		$missing = get_transient( 'cg_press_missing_' . get_current_user_id() );
		if ( ! $missing ) {
			return;
		}
		delete_transient( 'cg_press_missing_' . get_current_user_id() );
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
		if ( 'cg_press' === get_post_type( $post_id ) && 'draft' === get_post_status( $post_id ) && get_transient( 'cg_press_missing_' . get_current_user_id() ) ) {
			$location = add_query_arg( 'message', 10, remove_query_arg( 'message', $location ) );
		}
		return $location;
	},
	10,
	2
);

add_filter( 'enter_title_here', fn( $t, $p ) => 'cg_press' === $p->post_type ? __( 'e.g. CodeGirls 7th Karachi Graduation Ceremony', 'generatepress-child' ) : $t, 10, 2 );

add_action(
	'add_meta_boxes_cg_press',
	function () {
		remove_meta_box( 'postimagediv', 'cg_press', 'side' );
		remove_meta_box( 'slugdiv', 'cg_press', 'normal' );
		add_meta_box( 'cg_recognition_settings', __( 'Status', 'generatepress-child' ), 'cg_press_settings_box', 'cg_press', 'side', 'high' );
		add_meta_box( 'cg_recognition_preview', __( 'How the card looks', 'generatepress-child' ), 'cg_press_preview_box', 'cg_press', 'side', 'default' );
	}
);

add_action( 'add_meta_boxes', fn() => remove_meta_box( 'generate_layout_options_meta_box', 'cg_press', 'side' ), 99 );

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'cg_press' !== get_current_screen()->post_type ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'cg-voice-editor', CG_URI . '/assets/admin/voice-editor.css', array(), filemtime( CG_DIR . '/assets/admin/voice-editor.css' ) );
		wp_enqueue_style( 'cg-report-editor', CG_URI . '/assets/admin/report-editor.css', array( 'cg-voice-editor' ), filemtime( CG_DIR . '/assets/admin/report-editor.css' ) );
		wp_enqueue_style( 'cg-recognition-editor', CG_URI . '/assets/admin/recognition-editor.css', array( 'cg-voice-editor' ), filemtime( CG_DIR . '/assets/admin/recognition-editor.css' ) );
		wp_enqueue_script( 'cg-recognition-editor', CG_URI . '/assets/admin/recognition-editor.js', array( 'jquery' ), filemtime( CG_DIR . '/assets/admin/recognition-editor.js' ), true );
		wp_localize_script(
			'cg-recognition-editor',
			'cgRecognition',
			array(
				'i18n' => array(
					'chooseImage' => __( 'Choose image', 'generatepress-child' ),
					'changeImage' => __( 'Change image', 'generatepress-child' ),
					'title'       => __( 'Please enter the headline.', 'generatepress-child' ),
					'picture'     => __( 'Please choose the publisher logo.', 'generatepress-child' ),
					'url'         => __( 'Please paste the article link, e.g. https://www.techjuice.pk/…', 'generatepress-child' ),
					'urlRequired' => true,
				),
			)
		);
	}
);

function cg_press_settings_box( $post ) {
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
		<p class="cgv-hint" style="margin:0"><?php esc_html_e( 'Press items are listed newest first, by date. The newest one is highlighted on the page.', 'generatepress-child' ); ?></p>
	</div>
	<?php
}

function cg_press_preview_box( $post ) {
	$pic = get_the_post_thumbnail_url( $post, 'medium' );
	?>
	<div class="cgv-preview">
		<span class="cgv-label"><?php esc_html_e( 'How the card looks', 'generatepress-child' ); ?></span>
		<div class="cgr-card">
			<img data-out="photo" src="<?php echo esc_url( $pic ); ?>" alt=""<?php echo $pic ? '' : ' hidden'; ?>>
			<div class="cgr-card__empty"<?php echo $pic ? ' hidden' : ''; ?>><?php esc_html_e( 'Publisher logo', 'generatepress-child' ); ?></div>
			<strong data-out="name"><?php echo esc_html( $post->post_title ? $post->post_title : __( 'Headline', 'generatepress-child' ) ); ?></strong>
			<span>🕒 <?php echo esc_html( cg_press_date( $post->ID ) ); ?></span>
			<i aria-hidden="true">↗</i>
		</div>
	</div>
	<?php
}

add_action(
	'edit_form_top',
	function ( $post ) {
		if ( 'cg_press' !== $post->post_type ) {
			return;
		}
		$is_new = 'auto-draft' === $post->post_status;
		?>
		<div class="cgv-head" data-cgv-head hidden>
			<p class="cgv-head__sub"><?php esc_html_e( 'One press item = one card on the In The Press page. Clicking it opens the article.', 'generatepress-child' ); ?></p>
			<div class="cgv-head__actions">
				<button type="button" class="cgv-btn cgv-btn--primary" data-cgv-save><?php echo $is_new ? esc_html__( 'Publish press item', 'generatepress-child' ) : esc_html__( 'Save changes', 'generatepress-child' ); ?></button>
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
		if ( 'cg_press' !== $post->post_type ) {
			return;
		}
		wp_nonce_field( 'cg_press_save', 'cg_press_nonce' );
		?>
		<div class="cgv">
			<section class="cgv-card">
				<h2 class="cgv-step"><em>1</em> <?php esc_html_e( 'Headline, link and date', 'generatepress-child' ); ?></h2>
				<div data-cgv-name></div>
				<div class="cgb-2" style="display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr);gap:14px;margin-top:18px">
					<div class="cgr-date" style="margin:0">
						<label class="cgv-label" for="cg_url"><?php esc_html_e( 'Article link', 'generatepress-child' ); ?> <span class="cgv-req" aria-hidden="true">*</span></label>
						<input type="text" id="cg_url" name="cg_url" inputmode="url" autocomplete="off" value="<?php echo esc_attr( get_post_meta( $post->ID, '_cg_url', true ) ); ?>" placeholder="https://www.techjuice.pk/…">
						<p class="cgv-hint"><?php esc_html_e( 'Where the article is published. It opens in a new tab.', 'generatepress-child' ); ?></p>
					</div>
					<div class="cgr-date" style="margin:0">
						<label class="cgv-label" for="cg_press_date"><?php esc_html_e( 'Date', 'generatepress-child' ); ?></label>
						<input type="date" id="cg_press_date" name="cg_press_date" value="<?php echo esc_attr( get_the_date( 'Y-m-d', $post ) ); ?>" max="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>">
						<p class="cgv-hint"><?php esc_html_e( 'When the article was published.', 'generatepress-child' ); ?></p>
					</div>
				</div>
			</section>

			<section class="cgv-card">
				<h2 class="cgv-step"><em>2</em> <?php esc_html_e( 'Article text', 'generatepress-child' ); ?></h2>
				<p class="cgv-hint"><?php esc_html_e( 'Only needed when the link above is on the CodeGirls website. The card then opens this text as a page on the new site. For links to other publishers, leave it empty.', 'generatepress-child' ); ?></p>
				<?php
				wp_editor(
					$post->post_content,
					'cg_press_content',
					array(
						'textarea_name' => 'cg_press_content',
						'media_buttons' => true,
						'textarea_rows' => 12,
						'teeny'         => true,
					)
				);
				?>
			</section>

			<section class="cgv-card" data-cgv-photo>
				<h2 class="cgv-step"><em>3</em> <?php esc_html_e( 'Publisher logo', 'generatepress-child' ); ?> <span class="cgv-req" aria-hidden="true">*</span></h2>
				<?php cg_voice_image_field( '_thumbnail_id', (int) get_post_thumbnail_id( $post ), 'medium_large', '-1', 'cgv-image--wide cgr-cover' ); ?>
				<p class="cgv-hint"><?php esc_html_e( 'The picture on the card, e.g. the TechJuice or P@SHA logo tile. Pick an existing one from the Media Library to reuse it. About 440 × 311 px works best.', 'generatepress-child' ); ?></p>
			</section>
		</div>
		<?php
	}
);
