<?php
/**
 * Title: Hire Stats
 * Slug: codegirls/hire-stats
 * Categories: codegirls
 * Description: Three stats card overlapping the page banner.
 */
?>
<!-- wp:generateblocks/element {"tagName":"section","globalClasses":["hire-stats"]} -->
<section class="hire-stats">
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["cg-wrap"]} -->
<div class="cg-wrap">
<!-- wp:generateblocks/element {"tagName":"ul","globalClasses":["hire-stats__list"]} -->
<ul class="hire-stats__list">
<!-- wp:generateblocks/element {"tagName":"li","globalClasses":["hire-stats__item"]} -->
<li class="hire-stats__item">
<!-- wp:generateblocks/media {"tagName":"img","globalClasses":["hire-stats__icon"],"htmlAttributes":{"src":"<?php echo esc_url( get_theme_file_uri( 'assets/images/hire-stat-1.webp' ) ); ?>","alt":"","width":"110","height":"108"}} -->
<img class="hire-stats__icon" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hire-stat-1.webp' ) ); ?>" alt="" width="110" height="108"/>
<!-- /wp:generateblocks/media -->
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["hire-stats__text"]} -->
<div class="hire-stats__text">
<!-- wp:shortcode -->
[cg_stat_number key="trainings" class="gb-text hire-stats__num"]
<!-- /wp:shortcode -->
<!-- wp:generateblocks/text {"tagName":"p","globalClasses":["hire-stats__label"]} -->
<p class="gb-text hire-stats__label">[cg_stat_label key="trainings"]</p>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</li>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"tagName":"li","globalClasses":["hire-stats__item"]} -->
<li class="hire-stats__item">
<!-- wp:generateblocks/media {"tagName":"img","globalClasses":["hire-stats__icon"],"htmlAttributes":{"src":"<?php echo esc_url( get_theme_file_uri( 'assets/images/hire-stat-2.webp' ) ); ?>","alt":"","width":"110","height":"108"}} -->
<img class="hire-stats__icon" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hire-stat-2.webp' ) ); ?>" alt="" width="110" height="108"/>
<!-- /wp:generateblocks/media -->
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["hire-stats__text"]} -->
<div class="hire-stats__text">
<!-- wp:shortcode -->
[cg_stat_number key="placement" class="gb-text hire-stats__num"]
<!-- /wp:shortcode -->
<!-- wp:generateblocks/text {"tagName":"p","globalClasses":["hire-stats__label"]} -->
<p class="gb-text hire-stats__label">[cg_stat_label key="placement"]</p>
<!-- /wp:generateblocks/text -->
</div>
<!-- /wp:generateblocks/element -->
</li>
<!-- /wp:generateblocks/element -->
<!-- wp:generateblocks/element {"tagName":"li","globalClasses":["hire-stats__item"]} -->
<li class="hire-stats__item">
<!-- wp:generateblocks/media {"tagName":"img","globalClasses":["hire-stats__icon"],"htmlAttributes":{"src":"<?php echo esc_url( get_theme_file_uri( 'assets/images/hire-stat-3.webp' ) ); ?>","alt":"","width":"88","height":"108"}} -->
<img class="hire-stats__icon" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hire-stat-3.webp' ) ); ?>" alt="" width="88" height="108"/>
<!-- /wp:generateblocks/media -->
<!-- wp:generateblocks/element {"tagName":"div","globalClasses":["hire-stats__text"]} -->
<div class="hire-stats__text">
<!-- wp:shortcode -->
[cg_stat_number key="salary" class="gb-text hire-stats__num"]
<!-- /wp:shortcode -->
<!-- wp:generateblocks/text {"tagName":"p","globalClasses":["hire-stats__label"]} -->
<p class="gb-text hire-stats__label">[cg_stat_label key="salary"]</p>
<!-- /wp:generateblocks/text -->
<!-- wp:shortcode -->
[cg_stat_note key="salary" class="gb-text hire-stats__note"]
<!-- /wp:shortcode -->
</div>
<!-- /wp:generateblocks/element -->
</li>
<!-- /wp:generateblocks/element -->
</ul>
<!-- /wp:generateblocks/element -->
</div>
<!-- /wp:generateblocks/element -->
</section>
<!-- /wp:generateblocks/element -->
