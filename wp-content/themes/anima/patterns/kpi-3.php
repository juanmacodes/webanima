<?php
/**
 * Title: Bloque KPIs
 * Slug: anima/kpi-3
 * Categories: anima
 */

return [
    'title'      => __( 'KPIs (3 columnas)', 'anima' ),
    'categories' => [ 'anima' ],
    'content'    => '<!-- wp:group {"layout":{"type":"constrained","contentSize":"900px"}} -->
<div class="wp-block-group"><!-- wp:group {"layout":{"type":"flex","justifyContent":"space-between","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"1.5rem"}},"className":"anima-kpi-grid"} -->
<div class="wp-block-group anima-kpi-grid"><!-- wp:group {"style":{"spacing":{"blockGap":"0.35rem","padding":{"top":"1.75rem","bottom":"1.75rem","left":"1.75rem","right":"1.75rem"}}},"className":"anima-kpi-card"} -->
<div class="wp-block-group anima-kpi-card" style="padding-top:1.75rem;padding-right:1.75rem;padding-bottom:1.75rem;padding-left:1.75rem"><!-- wp:paragraph {"className":"anima-kpi-card__value"} -->
<p class="anima-kpi-card__value">32%</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-kpi-card__label"} -->
<p class="anima-kpi-card__label">' . esc_html__( '↑ tiempo de visualización', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-kpi-card__meta"} -->
<p class="anima-kpi-card__meta">' . esc_html__( 'Avatar host Twitch LATAM 2023', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"0.35rem","padding":{"top":"1.75rem","bottom":"1.75rem","left":"1.75rem","right":"1.75rem"}}},"className":"anima-kpi-card"} -->
<div class="wp-block-group anima-kpi-card" style="padding-top:1.75rem;padding-right:1.75rem;padding-bottom:1.75rem;padding-left:1.75rem"><!-- wp:paragraph {"className":"anima-kpi-card__value"} -->
<p class="anima-kpi-card__value">1.8×</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-kpi-card__label"} -->
<p class="anima-kpi-card__label">' . esc_html__( 'Más asistentes en stand', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-kpi-card__meta"} -->
<p class="anima-kpi-card__meta">' . esc_html__( 'Cabina holográfica Retail Expo', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"0.35rem","padding":{"top":"1.75rem","bottom":"1.75rem","left":"1.75rem","right":"1.75rem"}}},"className":"anima-kpi-card"} -->
<div class="wp-block-group anima-kpi-card" style="padding-top:1.75rem;padding-right:1.75rem;padding-bottom:1.75rem;padding-left:1.75rem"><!-- wp:paragraph {"className":"anima-kpi-card__value"} -->
<p class="anima-kpi-card__value">92%</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-kpi-card__label"} -->
<p class="anima-kpi-card__label">' . esc_html__( 'Finalización experiencia VR', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-kpi-card__meta"} -->
<p class="anima-kpi-card__meta">' . esc_html__( 'Metaverso marca deportiva', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->',
];
