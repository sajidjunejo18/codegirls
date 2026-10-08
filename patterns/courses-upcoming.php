<?php
/**
 * Title: Courses: Upcoming
 * Slug: codegirls/courses-upcoming
 * Categories: codegirls
 * Description: Upcoming course cards.
 */
?>
<!-- wp:generateblocks/element {"tagName":"section","globalClasses":["upcoming-courses"],"htmlAttributes":{"id":"upcoming"}} -->
<section class="upcoming-courses" id="upcoming">
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["cg-wrap"]} -->
<div class="cg-wrap">
<!-- wp:generateblocks/text {"tagName":"h2","globalClasses":["courses__title courses__title--lg"]} -->
<h2 class="gb-text courses__title courses__title--lg">Upcoming Courses</h2>
<!-- /wp:generateblocks/text -->
<!-- wp:shortcode -->
[cg_courses status="upcoming" limit="6"]
<!-- /wp:shortcode -->
</div>
<!-- /wp:generateblocks/element -->
</section>
<!-- /wp:generateblocks/element -->
