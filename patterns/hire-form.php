<?php
/**
 * Title: Hire Form
 * Slug: codegirls/hire-form
 * Categories: codegirls
 * Description: Photo plus employer enquiry form.
 */
?>
<!-- wp:generateblocks/element {"tagName":"section","globalClasses":["hire"],"htmlAttributes":{"id":"form"}} -->
<section class="hire" id="form">
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["cg-wrap hire__inner"]} -->
<div class="cg-wrap hire__inner">
<!-- wp:generateblocks/media {"tagName":"img","globalClasses":["hire__photo"],"htmlAttributes":{"src":"<?php echo esc_url( get_theme_file_uri( 'assets/images/hire-photo.webp' ) ); ?>","alt":"CodeGirls trainees working at computers","width":"436","height":"914","loading":"lazy"}} -->
<img class="hire__photo" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hire-photo.webp' ) ); ?>" alt="CodeGirls trainees working at computers" width="436" height="914" loading="lazy"/>
<!-- /wp:generateblocks/media -->
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["hire__main"]} -->
<div class="hire__main">
<!-- wp:generateblocks/text {"tagName":"h2","globalClasses":["hire__title"]} -->
<h2 class="gb-text hire__title">Hire talented graduates from CodeGirls!</h2>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"tagName":"p","globalClasses":["hire__subtitle"]} -->
<p class="gb-text hire__subtitle">Empower young girls and give them a boost by providing opportunities in the booming tech industry!</p>
<!-- /wp:generateblocks/text -->
<!-- wp:shortcode -->
[cg_hire_form]
<!-- /wp:shortcode -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/element -->
</section>
<!-- /wp:generateblocks/element -->
