<?php
/**
 * Title: CodeGirls Current Courses
 * Slug: codegirls/courses
 * Categories: codegirls
 * Description: Three course cards.
 */
?>
<!-- wp:generateblocks/element {"tagName":"section","globalClasses":["courses"],"htmlAttributes":{"id":"courses"}} -->
<section class="courses" id="courses">
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["cg-wrap"]} -->
<div class="cg-wrap">
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["courses__head"]} -->
<div class="courses__head">
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["courses__intro"]} -->
<div class="courses__intro">
<!-- wp:generateblocks/text {"tagName":"h2","globalClasses":["courses__title"]} -->
<h2 class="gb-text courses__title">Current Courses</h2>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"tagName":"p","globalClasses":["courses__subtitle"]} -->
<p class="gb-text courses__subtitle">Master the most relevant technologies in today's digital economy.</p>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/text {"tagName":"a","globalClasses":["courses__all"],"htmlAttributes":{"href":"<?php echo esc_url( home_url( '/courses/' ) ); ?>"}} -->
<a class="gb-text courses__all" href="<?php echo esc_url( home_url( '/courses/' ) ); ?>">View all courses</a>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
<!-- wp:shortcode -->
[cg_courses status="open" limit="3"]
<!-- /wp:shortcode -->
</div>
<!-- /wp:generateblocks/element -->
</section>
<!-- /wp:generateblocks/element -->
