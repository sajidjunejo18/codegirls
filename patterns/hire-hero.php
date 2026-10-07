<?php
/**
 * Title: Page Hero (breadcrumb + title)
 * Slug: codegirls/hire-hero
 * Categories: codegirls
 * Description: Green banner with breadcrumb and page title.
 */
?>
<!-- wp:generateblocks/element {"tagName":"section","globalClasses":["page-hero"]} -->
<section class="page-hero">
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["cg-wrap page-hero__inner"]} -->
<div class="cg-wrap page-hero__inner">
<!-- wp:generateblocks/element {"tagName":"nav","globalClasses":["page-hero__crumbs"],"htmlAttributes":{"aria-label":"Breadcrumb"}} -->
<nav class="page-hero__crumbs" aria-label="Breadcrumb">
<!-- wp:generateblocks/text {"tagName":"a","globalClasses":["page-hero__crumb"],"htmlAttributes":{"href":"<?php echo esc_url( home_url( '/' ) ); ?>"}} -->
<a class="gb-text page-hero__crumb" href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"tagName":"span","globalClasses":["page-hero__sep"],"htmlAttributes":{"aria-hidden":"true"}} -->
<span class="gb-text page-hero__sep" aria-hidden="true">&gt;</span>
<!-- /wp:generateblocks/text -->
<!-- wp:generateblocks/text {"tagName":"span","globalClasses":["page-hero__crumb page-hero__crumb--current"],"htmlAttributes":{"aria-current":"page"}} -->
<span class="gb-text page-hero__crumb page-hero__crumb--current" aria-current="page">Hire a Codegirl</span>
<!-- /wp:generateblocks/text -->
</nav>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/text {"tagName":"h1","globalClasses":["page-hero__title"]} -->
<h1 class="gb-text page-hero__title">Hire a Codegirl</h1>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</section>
<!-- /wp:generateblocks/element -->
