<?php
/**
 * Recognitions edit screen: one simple form for non-technical staff (same look as Voices / Reports).
 *
 * Steps: 1 title + website, 2 award picture, 3 badge (optional). The sidebar shows the status,
 * the position and a live preview of the card. Title and award picture are required.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

function cg_recognition_nonce_ok() {
	return ! empty( $_POST['cg_recognition_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cg_recognition_nonce'] ) ), 'cg_recognition_save' );
}

/** Labels of the required fields that are missing from the submitted form. */
function cg_recognition_missing_required() {
	// phpcs:disable WordPress.Security.NonceVerification -- called after the nonce check.
	$missing = array();
	if ( '' === trim( wp_unslash( $_POST['post_title'] ?? '' ) ) ) {
		$missing[] = __( 'Title', 'generatepress-child' );
	}
	if ( (int) ( $_POST['_thumbnail_id'] ?? 0 ) <= 0 ) {
		$missing[] = __( 'Award picture', 'generatepress-child' );
	}
	// phpcs:enable
	return $missing;
}

/** Normalise a typed website: add https:// when missing, empty if it is not a usable web address. */
function cg_recognition_clean_url( $raw ) {
	$raw = trim( (string) $raw );
	if ( '' === $raw ) {
		return '';
	}
	if ( ! preg_match( '#^https?://#i', $raw ) ) {
		$raw = 'https://' . ltrim( $raw, '/' );
	}
	$url = esc_url_raw( $raw, array( 'http', 'https' ) );
	return $url && false !== strpos( (string) wp_parse_url( $url, PHP_URL_HOST ), '.' ) ? $url : '';
}

// Never publish an incomplete recognition: keep it as a draft and say what is missing.
add_filter(
	'wp_insert_post_data',
	function ( $data ) {
		if ( 'cg_recognition' !== $data['post_type'] || ! cg_recognition_nonce_ok() || ! in_array( $data['post_status'], array( 'publish', 'future' ), true ) ) {
			return $data;
		}
		$missing = cg_recognition_missing_required();
		if ( $missing ) {
			$data['post_status'] = 'draft';
			set_transient( 'cg_recognition_missing_' . get_current_user_id(), $missing, 60 );
		}
		return $data;
	},
	20
);

add_action(
	'save_post_cg_recognition',
	function ( $post_id ) {
		if ( ! cg_recognition_nonce_ok() || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$badge = absint( $_POST['cg_badge'] ?? 0 );
		if ( $badge && wp_attachment_is_image( $badge ) ) {
			update_post_meta( $post_id, '_cg_badge', $badge );
		} else {
			delete_post_meta( $post_id, '_cg_badge' );
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
		if ( ! $screen || 'cg_recognition' !== $screen->post_type ) {
			return;
		}
		$missing = get_transient( 'cg_recognition_missing_' . get_current_user_id() );
		if ( ! $missing ) {
			return;
		}
		delete_transient( 'cg_recognition_missing_' . get_current_user_id() );
		printf(
			'<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
			esc_html__( 'Not published yet — saved as a draft.', 'generatepress-child' ),
			esc_html( sprintf( /* translators: %s: list of fields */ __( 'Please fill in: %s.', 'generatepress-child' ), implode( ', ', $missing ) ) )
		);
	}
);

// WordPress would still say "Post published." after the guard above.
add_filter(
	'redirect_post_location',
	function ( $location, $post_id ) {
		if ( 'cg_recognition' === get_post_type( $post_id ) && 'draft' === get_post_status( $post_id ) && get_transient( 'cg_recognition_missing_' . get_current_user_id() ) ) {
			$location = add_query_arg( 'message', 10, remove_query_arg( 'message', $location ) );
		}
		return $location;
	},
	10,
	2
);

add_filter(
	'enter_title_here',
	function ( $text, $post ) {
		return 'cg_recognition' === $post->post_type ? __( 'e.g. Accredited by American Skills Evaluation Institute', 'generatepress-child' ) : $text;
	},
	10,
	2
);

add_action(
	'add_meta_boxes_cg_recognition',
	function () {
		remove_meta_box( 'postimagediv', 'cg_recognition', 'side' );
		remove_meta_box( 'slugdiv', 'cg_recognition', 'normal' );
		add_meta_box( 'cg_recognition_settings', __( 'Status', 'generatepress-child' ), 'cg_recognition_settings_box', 'cg_recognition', 'side', 'high' );
		add_meta_box( 'cg_recognition_preview', __( 'How the card looks', 'generatepress-child' ), 'cg_recognition_preview_box', 'cg_recognition', 'side', 'default' );
	}
);

// GeneratePress "Layout" box is not needed here.
add_action(
	'add_meta_boxes',
	function () {
		remove_meta_box( 'generate_layout_options_meta_box', 'cg_recognition', 'side' );
	},
	99
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'cg_recognition' !== get_current_screen()->post_type ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'cg-voice-editor', CG_URI . '/assets/admin/voice-editor.css', array(), filemtime( CG_DIR . '/assets/admin/voice-editor.css' ) ); // Shared form look.
		wp_enqueue_style( 'cg-recognition-editor', CG_URI . '/assets/admin/recognition-editor.css', array( 'cg-voice-editor' ), filemtime( CG_DIR . '/assets/admin/recognition-editor.css' ) );
		wp_enqueue_script( 'cg-recognition-editor', CG_URI . '/assets/admin/recognition-editor.js', array( 'jquery' ), filemtime( CG_DIR . '/assets/admin/recognition-editor.js' ), true );
		wp_localize_script(
			'cg-recognition-editor',
			'cgRecognition',
			array(
				'i18n' => array(
					'chooseImage' => __( 'Choose image', 'generatepress-child' ),
					'changeImage' => __( 'Change image', 'generatepress-child' ),
					'title'       => __( 'Please enter the title of the award.', 'generatepress-child' ),
					'picture'     => __( 'Please choose the award picture.', 'generatepress-child' ),
					'url'         => __( 'That does not look like a web address. Example: https://www.example.com', 'generatepress-child' ),
				),
			)
		);
	}
);

function cg_recognition_settings_box( $post ) {
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
		<label class="cgv-label" for="cg_position"><?php esc_html_e( 'Position on the page', 'generatepress-child' ); ?></label>
		<input type="number" id="cg_position" name="menu_order" min="0" step="1" value="<?php echo (int) $post->menu_order; ?>" class="cgv-num">
		<p class="cgv-hint"><?php esc_html_e( 'Smaller numbers show first. Same number: the newest shows first.', 'generatepress-child' ); ?></p>
	</div>
	<?php
}

function cg_recognition_preview_box( $post ) {
	$pic   = get_the_post_thumbnail_url( $post, 'medium' );
	$badge = cg_recognition_badge_id( $post->ID );
	$bsrc  = $badge ? wp_get_attachment_image_url( $badge, 'thumbnail' ) : '';
	$url   = get_post_meta( $post->ID, '_cg_url', true );
	?>
	<div class="cgv-preview" data-cgv-preview>
		<span class="cgv-label"><?php esc_html_e( 'How the card looks', 'generatepress-child' ); ?></span>
		<div class="cgrc-card">
			<div class="cgrc-card__media">
				<img data-out="photo" src="<?php echo esc_url( $pic ); ?>" alt=""<?php echo $pic ? '' : ' hidden'; ?>>
				<div class="cgr-card__empty"<?php echo $pic ? ' hidden' : ''; ?>><?php esc_html_e( 'Award picture', 'generatepress-child' ); ?></div>
				<img class="cgrc-card__badge" data-out="badge" src="<?php echo esc_url( $bsrc ); ?>" alt=""<?php echo $bsrc ? '' : ' hidden'; ?>>
			</div>
			<strong data-out="name"><?php echo esc_html( $post->post_title ? $post->post_title : __( 'Title of the award', 'generatepress-child' ) ); ?></strong>
			<span class="cgrc-card__btn" data-out-url<?php echo $url ? '' : ' hidden'; ?>><?php esc_html_e( 'Visit Website', 'generatepress-child' ); ?></span>
		</div>
	</div>
	<?php
}

add_action(
	'edit_form_top',
	function ( $post ) {
		if ( 'cg_recognition' !== $post->post_type ) {
			return;
		}
		$is_new = 'auto-draft' === $post->post_status;
		?>
		<div class="cgv-head" data-cgv-head hidden>
			<p class="cgv-head__sub"><?php esc_html_e( 'One recognition = one card on the Recognitions page. Fill it top to bottom.', 'generatepress-child' ); ?></p>
			<div class="cgv-head__actions">
				<button type="button" class="cgv-btn cgv-btn--primary" data-cgv-save><?php echo $is_new ? esc_html__( 'Publish recognition', 'generatepress-child' ) : esc_html__( 'Save changes', 'generatepress-child' ); ?></button>
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
		if ( 'cg_recognition' !== $post->post_type ) {
			return;
		}
		wp_nonce_field( 'cg_recognition_save', 'cg_recognition_nonce' );
		?>
		<div class="cgv">
			<section class="cgv-card">
				<h2 class="cgv-step"><em>1</em> <?php esc_html_e( 'Title and website', 'generatepress-child' ); ?></h2>
				<div data-cgv-name></div>
				<div class="cgr-date" style="max-width:520px">
					<label class="cgv-label" for="cg_url"><?php esc_html_e( 'Website', 'generatepress-child' ); ?> <span class="cgv-opt"><?php esc_html_e( 'optional', 'generatepress-child' ); ?></span></label>
					<input type="text" id="cg_url" name="cg_url" data-bind="url" inputmode="url" autocomplete="off" value="<?php echo esc_attr( get_post_meta( $post->ID, '_cg_url', true ) ); ?>" placeholder="https://www.example.com">
					<p class="cgv-hint"><?php esc_html_e( 'The “Visit Website” button opens this address. Leave it empty to hide the button.', 'generatepress-child' ); ?></p>
				</div>
			</section>

			<section class="cgv-card" data-cgv-photo>
				<h2 class="cgv-step"><em>2</em> <?php esc_html_e( 'Award picture', 'generatepress-child' ); ?> <span class="cgv-req" aria-hidden="true">*</span></h2>
				<?php cg_voice_image_field( '_thumbnail_id', (int) get_post_thumbnail_id( $post ), 'large', '-1', 'cgv-image--wide cgrc-award' ); ?>
				<p class="cgv-hint"><?php esc_html_e( 'The certificate or trophy photo at the top of the card. About 464 × 302 px works best.', 'generatepress-child' ); ?></p>
			</section>

			<section class="cgv-card">
				<h2 class="cgv-step"><em>3</em> <?php esc_html_e( 'Badge', 'generatepress-child' ); ?> <span class="cgv-opt"><?php esc_html_e( 'optional', 'generatepress-child' ); ?></span></h2>
				<?php cg_voice_image_field( 'cg_badge', cg_recognition_badge_id( $post->ID ), 'thumbnail', '', 'cgv-image--wide cgrc-badge' ); ?>
				<p class="cgv-hint"><?php esc_html_e( 'The small round logo that overlaps the bottom edge of the picture (e.g. the award’s logo). A square picture, about 102 × 102 px, on a white or transparent background.', 'generatepress-child' ); ?></p>
			</section>
		</div>
		<?php
	}
);
