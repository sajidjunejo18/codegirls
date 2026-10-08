<?php
/**
 * Title: Courses: Completed
 * Slug: codegirls/courses-completed
 * Categories: codegirls
 * Description: Completed course cards.
 */
?>
<!-- wp:generateblocks/element {"tagName":"section","globalClasses":["completed"],"htmlAttributes":{"id":"completed"}} -->
<section class="completed" id="completed">
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["cg-wrap"]} -->
<div class="cg-wrap">
<!-- wp:generateblocks/text {"tagName":"h2","globalClasses":["courses__title courses__title--lg"]} -->
<h2 class="gb-text courses__title courses__title--lg">Completed Courses</h2>
<!-- /wp:generateblocks/text -->
<!-- wp:shortcode -->
[cg_courses status="completed" limit="9"]
<!-- /wp:shortcode -->
</div>
<!-- /wp:generateblocks/element -->
</section>
<!-- /wp:generateblocks/element -->
