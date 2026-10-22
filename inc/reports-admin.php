<?php
/**
 * Reports edit screen: one simple form for non-technical staff (same look as Voices).
 *
 * Steps: 1 title + report date, 2 cover image, 3 the PDF. The sidebar shows the status
 * and a live preview of the card. Title, cover image and PDF are required.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/** Labels of the required fields that are missing from the submitted form. */
function cg_report_missing_required() {
	// phpcs:disable WordPress.Security.NonceVerification -- called after the nonce check.
	$missing = array();
	if ( '' === trim( wp_unslash( $_POST['post_title'] ?? '' ) ) ) {
		$missing[] = __( 'Title', 'generatepress-child' );
	}
	if ( (int) ( $_POST['_thumbnail_id'] ?? 0 ) <= 0 ) {
		$missing[] = __( 'Cover image', 'generatepress-child' );
	}
	$pdf = absint( $_POST['cg_pdf'] ?? 0 );
	if ( ! $pdf || 'application/pdf' !== get_post_mime_type( $pdf ) ) {
		$missing[] = __( 'PDF file', 'generatepress-child' );
	}
	// phpcs:enable
	return $missing;
}

function cg_report_nonce_ok() {
	return ! empty( $_POST['cg_report_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cg_report_nonce'] ) ), 'cg_report_save' );
}

// Report date (not in the future, so the report never gets "scheduled" by accident).
add_filter(
	'wp_insert_post_data',
	function ( $data, $postarr ) {
		if ( 'cg_report' !== $data['post_type'] || ! cg_report_nonce_ok() ) {
			return $data;
		}
		// Never publish an incomplete report: keep it as a draft and say what is missing.
		if ( in_array( $data['post_status'], array( 'publish', 'future' ), true ) ) {
			$missing = cg_report_missing_required();
			if ( $missing ) {
				$data['post_status'] = 'draft';
				set_transient( 'cg_report_missing_' . get_current_user_id(), $missing, 60 );
			}
		}
			// The date comes only from the "Report date" field. WordPress' own (hidden) date fields are
			// ignored, so saving never moves the date by accident or schedules the report.
			$stored     = ! empty( $postarr['ID'] ) ? get_post( (int) $postarr['ID'] ) : null;
			$stored_day = $stored ? substr( (string) $stored->post_date, 0, 10 ) : '';
			if ( $stored ) {
				$data['post_date']     = $stored->post_date;
				$data['post_date_gmt'] = '0000-00-00 00:00:00' === $stored->post_date_gmt ? get_gmt_from_date( $stored->post_date ) : $stored->post_date_gmt;
			}
			$day = isset( $_POST['cg_report_date'] ) ? sanitize_text_field( wp_unslash( $_POST['cg_report_date'] ) ) : '';
			if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $day ) && $stored_day !== $day ) {
				$day                   = min( $day, wp_date( 'Y-m-d' ) ); // Never a future date.
				$data['post_date']     = $day . ' 12:00:00';
				$data['post_date_gmt'] = get_gmt_from_date( $data['post_date'] );
			}
			if ( 'future' === $data['post_status'] ) {
				$data['post_status'] = 'publish'; // Reports are never "scheduled".
			}
		return $data;
	},
	20,
	2
);

add_action(
	'save_post_cg_report',
	function ( $post_id ) {
		if ( ! cg_report_nonce_ok() || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$home = absint( $_POST['cg_home_img'] ?? 0 );
		if ( $home && wp_attachment_is_image( $home ) ) {
			update_post_meta( $post_id, '_cg_home_img', $home );
		} else {
			delete_post_meta( $post_id, '_cg_home_img' );
		}
		$pdf = absint( $_POST['cg_pdf'] ?? 0 );
		if ( $pdf && 'application/pdf' === get_post_mime_type( $pdf ) ) {
			update_post_meta( $post_id, '_cg_pdf', $pdf );
		} else {
			delete_post_meta( $post_id, '_cg_pdf' );
		}
	}
);

add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'cg_report' !== $screen->post_type ) {
			return;
		}
		$missing = get_transient( 'cg_report_missing_' . get_current_user_id() );
		if ( ! $missing ) {
			return;
		}
		delete_transient( 'cg_report_missing_' . get_current_user_id() );
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
		if ( 'cg_report' === get_post_type( $post_id ) && 'draft' === get_post_status( $post_id ) && get_transient( 'cg_report_missing_' . get_current_user_id() ) ) {
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
		return 'cg_report' === $post->post_type ? __( 'e.g. 7 Years of Impact 2018-25', 'generatepress-child' ) : $text;
	},
	10,
	2
);

add_action(
	'add_meta_boxes_cg_report',
	function () {
		remove_meta_box( 'postimagediv', 'cg_report', 'side' );
		remove_meta_box( 'slugdiv', 'cg_report', 'normal' );
		add_meta_box( 'cg_report_settings', __( 'Status', 'generatepress-child' ), 'cg_report_settings_box', 'cg_report', 'side', 'high' );
		add_meta_box( 'cg_report_preview', __( 'How the card looks', 'generatepress-child' ), 'cg_report_preview_box', 'cg_report', 'side', 'default' );
	}
);

// GeneratePress "Layout" box is not needed here.
add_action(
	'add_meta_boxes',
	function () {
		remove_meta_box( 'generate_layout_options_meta_box', 'cg_report', 'side' );
	},
	99
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'cg_report' !== get_current_screen()->post_type ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'cg-voice-editor', CG_URI . '/assets/admin/voice-editor.css', array(), filemtime( CG_DIR . '/assets/admin/voice-editor.css' ) ); // Shared form look.
		wp_enqueue_style( 'cg-report-editor', CG_URI . '/assets/admin/report-editor.css', array( 'cg-voice-editor' ), filemtime( CG_DIR . '/assets/admin/report-editor.css' ) );
		wp_enqueue_script( 'cg-report-editor', CG_URI . '/assets/admin/report-editor.js', array( 'jquery' ), filemtime( CG_DIR . '/assets/admin/report-editor.js' ), true );
		wp_localize_script(
			'cg-report-editor',
			'cgReport',
			array(
				'i18n' => array(
					'chooseImage' => __( 'Choose image', 'generatepress-child' ),
					'changeImage' => __( 'Change image', 'generatepress-child' ),
					'choosePdf'   => __( 'Choose PDF', 'generatepress-child' ),
					'changePdf'   => __( 'Change PDF', 'generatepress-child' ),
					'title'       => __( 'Please enter the report title.', 'generatepress-child' ),
					'cover'       => __( 'Please choose the cover image.', 'generatepress-child' ),
					'pdf'         => __( 'Please choose the PDF file.', 'generatepress-child' ),
				),
			)
		);
	}
);

function cg_report_settings_box( $post ) {
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
		<p class="cgv-hint" style="margin:0"><?php esc_html_e( 'Reports are listed newest first, by report date. The newest one is highlighted on the page.', 'generatepress-child' ); ?></p>
	</div>
	<?php
}

function cg_report_preview_box( $post ) {
	$cover = get_the_post_thumbnail_url( $post, 'medium' );
	?>
	<div class="cgv-preview" data-cgv-preview>
		<span class="cgv-label"><?php esc_html_e( 'How the card looks', 'generatepress-child' ); ?></span>
		<div class="cgr-card">
			<img data-out="photo" src="<?php echo esc_url( $cover ); ?>" alt=""<?php echo $cover ? '' : ' hidden'; ?>>
			<div class="cgr-card__empty"<?php echo $cover ? ' hidden' : ''; ?>><?php esc_html_e( 'Cover image', 'generatepress-child' ); ?></div>
			<strong data-out="name"><?php echo esc_html( $post->post_title ? $post->post_title : __( 'Report title', 'generatepress-child' ) ); ?></strong>
			<span data-out="date"><?php echo esc_html( get_the_date( 'F j, Y', $post ) ); ?></span>
			<i aria-hidden="true">↗</i>
		</div>
	</div>
	<?php
}

add_action(
	'edit_form_top',
	function ( $post ) {
		if ( 'cg_report' !== $post->post_type ) {
			return;
		}
		$is_new = 'auto-draft' === $post->post_status;
		?>
		<div class="cgv-head" data-cgv-head hidden>
			<p class="cgv-head__sub"><?php esc_html_e( 'One report = one card on the Reports page. Fill it top to bottom.', 'generatepress-child' ); ?></p>
			<div class="cgv-head__actions">
				<button type="button" class="cgv-btn cgv-btn--primary" data-cgv-save><?php echo $is_new ? esc_html__( 'Publish report', 'generatepress-child' ) : esc_html__( 'Save changes', 'generatepress-child' ); ?></button>
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
		if ( 'cg_report' !== $post->post_type ) {
			return;
		}
		wp_nonce_field( 'cg_report_save', 'cg_report_nonce' );
		$pdf  = cg_report_pdf_id( $post->ID );
		$file = $pdf ? get_attached_file( $pdf ) : '';
		$size = $file && file_exists( $file ) ? size_format( filesize( $file ) ) : '';
		?>
		<div class="cgv">
			<section class="cgv-card">
				<h2 class="cgv-step"><em>1</em> <?php esc_html_e( 'Title and date', 'generatepress-child' ); ?></h2>
				<div data-cgv-name></div>
				<div class="cgr-date">
					<label class="cgv-label" for="cg_report_date"><?php esc_html_e( 'Report date', 'generatepress-child' ); ?></label>
					<input type="date" id="cg_report_date" name="cg_report_date" data-bind="date" value="<?php echo esc_attr( get_the_date( 'Y-m-d', $post ) ); ?>" max="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>">
					<p class="cgv-hint"><?php esc_html_e( 'Shown on the card. Newest reports appear first.', 'generatepress-child' ); ?></p>
				</div>
			</section>

			<section class="cgv-card" data-cgv-photo>
				<h2 class="cgv-step"><em>2</em> <?php esc_html_e( 'Cover image', 'generatepress-child' ); ?> <span class="cgv-req" aria-hidden="true">*</span></h2>
				<?php cg_voice_image_field( '_thumbnail_id', (int) get_post_thumbnail_id( $post ), 'large', '-1', 'cgv-image--wide cgr-cover' ); ?>
				<p class="cgv-hint"><?php esc_html_e( 'The picture shown on the card (the report’s front page). About 450 × 350 px works best.', 'generatepress-child' ); ?></p>
			</section>

			<section class="cgv-card" data-cgv-file>
				<h2 class="cgv-step"><em>3</em> <?php esc_html_e( 'Report file (PDF)', 'generatepress-child' ); ?> <span class="cgv-req" aria-hidden="true">*</span></h2>
				<input type="hidden" name="cg_pdf" value="<?php echo esc_attr( $pdf ? $pdf : '' ); ?>">
				<div class="cgr-file<?php echo $pdf ? ' has-file' : ''; ?>" data-cgr-file>
					<span class="cgr-file__icon" aria-hidden="true">PDF</span>
					<span class="cgr-file__info">
						<strong data-cgr-name><?php echo $pdf ? esc_html( basename( $file ) ) : esc_html__( 'No file chosen yet', 'generatepress-child' ); ?></strong>
						<span data-cgr-meta><?php echo esc_html( $size ); ?></span>
					</span>
					<a class="button button-small" data-cgr-view href="<?php echo $pdf ? esc_url( wp_get_attachment_url( $pdf ) ) : '#'; ?>" target="_blank" rel="noopener"<?php echo $pdf ? '' : ' hidden'; ?>><?php esc_html_e( 'Open', 'generatepress-child' ); ?></a>
				</div>
				<div class="cgv-image__actions" style="justify-content:flex-start;margin-top:12px">
					<button type="button" class="button" data-cgr-pick><?php echo $pdf ? esc_html__( 'Change PDF', 'generatepress-child' ) : esc_html__( 'Choose PDF', 'generatepress-child' ); ?></button>
					<button type="button" class="button-link button-link-delete" data-cgr-clear<?php echo $pdf ? '' : ' hidden'; ?>><?php esc_html_e( 'Remove', 'generatepress-child' ); ?></button>
				</div>
				<p class="cgv-hint"><?php esc_html_e( 'Visitors open this file when they click the card. You can upload a new PDF from the same window.', 'generatepress-child' ); ?></p>
			</section>

			<section class="cgv-card">
				<h2 class="cgv-step"><em>4</em> <?php esc_html_e( 'Homepage picture', 'generatepress-child' ); ?> <span class="cgv-opt"><?php esc_html_e( 'optional', 'generatepress-child' ); ?></span></h2>
				<?php cg_voice_image_field( 'cg_home_img', (int) get_post_meta( $post->ID, '_cg_home_img', true ), 'medium', '', 'cgv-image--wide cgr-portrait' ); ?>
				<p class="cgv-hint"><?php esc_html_e( 'The newest report is shown on the homepage (“Get involved”) on tall cards. Upload the report’s portrait front page here for the best look; if you leave it empty, the cover image above is used (and cropped to fit).', 'generatepress-child' ); ?></p>
			</section>
		</div>
		<?php
	}
);
