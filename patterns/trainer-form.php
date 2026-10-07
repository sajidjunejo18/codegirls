<?php
/**
 * Title: Trainer Form
 * Slug: codegirls/trainer-form
 * Categories: codegirls
 * Description: Trainer application form.
 */
?>
<!-- wp:generateblocks/element {"tagName":"section","globalClasses":["hire hire--trainer"],"htmlAttributes":{"id":"form"}} -->
<section class="hire hire--trainer" id="form">
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["cg-wrap hire__inner"]} -->
<div class="cg-wrap hire__inner">
<!-- wp:generateblocks/media {"tagName":"img","globalClasses":["hire__photo"],"htmlAttributes":{"src":"<?php echo esc_url( get_theme_file_uri( 'assets/images/hire-photo.webp' ) ); ?>","alt":"CodeGirls trainees working at computers","width":"436","height":"694","loading":"lazy"}} -->
<img class="hire__photo" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hire-photo.webp' ) ); ?>" alt="CodeGirls trainees working at computers" width="436" height="694" loading="lazy"/>
<!-- /wp:generateblocks/media -->
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["hire__main"]} -->
<div class="hire__main">
<!-- wp:generateblocks/text {"tagName":"h2","globalClasses":["hire__title"]} -->
<h2 class="gb-text hire__title">Be a Part of the CodeGirls Boot Camp!</h2>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"tagName":"p","globalClasses":["hire__subtitle"]} -->
<p class="gb-text hire__subtitle">If you would like to lend a hand as a trainer, mentor, facilitator or guest speaker at one of the CodeGirls Boot camps, please fill out the form below to apply:</p>
<!-- /wp:generateblocks/text -->
<!-- wp:shortcode -->
[cg_trainer_form]
<!-- /wp:shortcode -->
</div>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/element -->
</section>
<!-- /wp:generateblocks/element -->
