<?php
/**
 * Title: CodeGirls Contact
 * Slug: codegirls/contact
 * Categories: codegirls
 * Description: Contact section with form.
 */
?>
<!-- wp:generateblocks/element {"tagName":"section","globalClasses":["contact"],"htmlAttributes":{"id":"contact"}} -->
<section class="contact" id="contact">
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["contact__media"]} -->
<div class="contact__media">
<!-- wp:generateblocks/media {"tagName":"img","globalClasses":["contact__img"],"htmlAttributes":{"src":"<?php echo esc_url( get_theme_file_uri( 'assets/images/contact.webp' ) ); ?>","alt":"Women attending a CodeGirls session","width":"1024","height":"683","loading":"lazy"}} -->
<img class="contact__img" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/contact.webp' ) ); ?>" alt="Women attending a CodeGirls session" width="1024" height="683" loading="lazy"/>
<!-- /wp:generateblocks/media -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["contact__panel"]} -->
<div class="contact__panel">
<!-- wp:generateblocks/text {"tagName":"h2","globalClasses":["contact__title"]} -->
<h2 class="gb-text contact__title">Get In Touch With Us!</h2>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"tagName":"p","globalClasses":["contact__subtitle"]} -->
<p class="gb-text contact__subtitle">Get involved: Learn, sponsor, contribute, or register!</p>
<!-- /wp:generateblocks/text -->
<!-- wp:shortcode -->
[cg_contact_form]
<!-- /wp:shortcode -->
</div>
<!-- /wp:generateblocks/element -->
</section>
<!-- /wp:generateblocks/element -->
