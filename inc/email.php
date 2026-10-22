<?php
/**
 * Branded HTML emails for form notifications (logo header, field table, footer).
 * Table layout + inline styles so it renders in Gmail/Outlook; the logo is a PNG
 * because most mail clients block SVG.
 *
 * @package generatepress-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the HTML body. $pairs = array( 'Label' => 'value', … ); $entry_id links to the admin entry.
 */
function cg_email_html( $heading, $pairs, $entry_id = 0 ) {
	$navy  = '#223044';
	$green = '#0a9d58';
	$font  = "'Onest', Arial, Helvetica, sans-serif";
	$site  = wp_specialchars_decode( get_bloginfo( 'name' ) );
	$logo  = CG_URI . '/assets/images/logo-email.png';

	$rows = '';
	foreach ( (array) $pairs as $label => $val ) {
		if ( '' === (string) $val ) {
			$val = '—';
		}
		$rows .= sprintf(
			'<tr><td style="padding:12px 16px;border-bottom:1px solid #eeeeee;width:38%%;vertical-align:top;font:600 14px/1.5 %3$s;color:%4$s;">%1$s</td><td style="padding:12px 16px;border-bottom:1px solid #eeeeee;vertical-align:top;font:14px/1.5 %3$s;color:#444444;">%2$s</td></tr>',
			esc_html( $label ),
			nl2br( esc_html( $val ) ),
			$font,
			$navy
		);
	}

	$button = '';
	if ( $entry_id ) {
		$button = sprintf(
			'<tr><td style="padding:8px 32px 32px;" align="center"><a href="%s" style="display:inline-block;padding:12px 28px;border-radius:999px;background:%s;color:#ffffff;text-decoration:none;font:600 14px %s;">%s</a></td></tr>',
			esc_url( admin_url( 'post.php?post=' . (int) $entry_id . '&action=edit' ) ),
			$green,
			$font,
			esc_html__( 'View entry in dashboard', 'generatepress-child' )
		);
	}

	ob_start();
	?>
<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?php echo esc_html( $heading ); ?></title></head>
<body style="margin:0;padding:0;background:#f4f5f7;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7;"><tr><td align="center" style="padding:32px 12px;">
	<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;">
		<tr><td style="height:6px;background:<?php echo esc_attr( $green ); ?>;font-size:0;line-height:0;">&nbsp;</td></tr>
		<tr><td align="center" style="padding:28px 32px 20px;border-bottom:1px solid #eeeeee;">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( $logo ); ?>" width="332" height="36" alt="<?php echo esc_attr( $site ); ?>" style="display:block;width:100%;max-width:332px;height:auto;border:0;"></a>
		</td></tr>
		<tr><td style="padding:28px 32px 8px;font:700 22px/1.3 <?php echo esc_attr( $font ); ?>;color:<?php echo esc_attr( $navy ); ?>;"><?php echo esc_html( $heading ); ?></td></tr>
		<tr><td style="padding:0 32px 20px;font:14px/1.5 <?php echo esc_attr( $font ); ?>;color:#777777;"><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></td></tr>
		<tr><td style="padding:0 32px 24px;">
			<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eeeeee;border-radius:8px;border-collapse:separate;"><?php echo $rows; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?></table>
		</td></tr>
		<?php echo $button; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?>
		<tr><td align="center" style="padding:24px 32px;background:<?php echo esc_attr( $navy ); ?>;font:13px/1.6 <?php echo esc_attr( $font ); ?>;color:#c9d1dc;">
			<strong style="color:#ffffff;"><?php echo esc_html( $site ); ?></strong> &middot; Learn. Create. Launch.<br>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color:#f2b41b;text-decoration:none;"><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ); ?></a><br>
			<span style="color:#8a96a8;"><?php esc_html_e( 'You received this because a form was submitted on the website.', 'generatepress-child' ); ?></span>
		</td></tr>
	</table>
</td></tr></table>
</body></html>
	<?php
	return ob_get_clean();
}

/** Send a branded notification to the site admin. Returns wp_mail()'s result. */
function cg_mail_admin( $subject, $heading, $pairs, $reply_name, $reply_email, $entry_id = 0, $attachments = array() ) {
	return wp_mail(
		get_option( 'admin_email' ),
		$subject,
		cg_email_html( $heading, $pairs, $entry_id ),
		array(
			'Content-Type: text/html; charset=UTF-8',
			'Reply-To: ' . $reply_name . ' <' . $reply_email . '>',
		),
		$attachments
	);
}
