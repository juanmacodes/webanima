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
<div class="wp-block-group"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"style":{"spacing":{"padding":{"top":"1.5rem","bottom":"1.5rem"}}},"backgroundColor":"surface"} -->
<div class="wp-block-column has-surface-background-color has-background" style="padding-top:1.5rem;padding-bottom:1.5rem"><!-- wp:paragraph {"align":"center","fontSize":"display"} -->
<p class="has-text-align-center has-display-font-size">32%</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( '↑ tiempo de visualización', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"spacing":{"padding":{"top":"1.5rem","bottom":"1.5rem"}}},"backgroundColor":"surface"} -->
<div class="wp-block-column has-surface-background-color has-background" style="padding-top:1.5rem;padding-bottom:1.5rem"><!-- wp:paragraph {"align":"center","fontSize":"display"} -->
<p class="has-text-align-center has-display-font-size">1.8×</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Asistentes en stand', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"spacing":{"padding":{"top":"1.5rem","bottom":"1.5rem"}}},"backgroundColor":"surface"} -->
<div class="wp-block-column has-surface-background-color has-background" style="padding-top:1.5rem;padding-bottom:1.5rem"><!-- wp:paragraph {"align":"center","fontSize":"display"} -->
<p class="has-text-align-center has-display-font-size">92%</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Finalización VR', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->',
];
