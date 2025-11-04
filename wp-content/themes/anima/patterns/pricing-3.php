<?php
/**
 * Title: Planes Anima Live
 * Slug: anima/pricing-3
 * Categories: anima
 */

return [
    'title'      => __( 'Planes (3 columnas)', 'anima' ),
    'categories' => [ 'anima' ],
    'content'    => '<!-- wp:group {"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group"><!-- wp:heading {"textAlign":"center","fontSize":"xxl"} -->
<h2 class="has-text-align-center has-xxl-font-size">' . esc_html__( 'Planes para creadores', 'anima' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"style":{"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"1.5rem","right":"1.5rem"}}},"backgroundColor":"surface"} -->
<div class="wp-block-column has-surface-background-color has-background" style="padding-top:2rem;padding-right:1.5rem;padding-bottom:2rem;padding-left:1.5rem"><!-- wp:heading {"level":3,"textAlign":"center"} -->
<h3 class="has-text-align-center">' . esc_html__( 'Free', 'anima' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Marca de agua, escenas básicas, streaming 720p.', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center","fontSize":"xl"} -->
<p class="has-text-align-center has-xl-font-size">€0</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link" href="#">' . esc_html__( 'Empezar', 'anima' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"1.5rem","right":"1.5rem"}}},"backgroundColor":"surface"} -->
<div class="wp-block-column has-surface-background-color has-background" style="padding-top:2rem;padding-right:1.5rem;padding-bottom:2rem;padding-left:1.5rem"><!-- wp:heading {"level":3,"textAlign":"center"} -->
<h3 class="has-text-align-center">' . esc_html__( 'Creator', 'anima' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Avatar premium, presets de escenas, analítica básica.', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center","fontSize":"xl"} -->
<p class="has-text-align-center has-xl-font-size">€19</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link" href="#">' . esc_html__( 'Unirme', 'anima' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column -->

<!-- wp:column {"style":{"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"1.5rem","right":"1.5rem"}}},"backgroundColor":"surface"} -->
<div class="wp-block-column has-surface-background-color has-background" style="padding-top:2rem;padding-right:1.5rem;padding-bottom:2rem;padding-left:1.5rem"><!-- wp:heading {"level":3,"textAlign":"center"} -->
<h3 class="has-text-align-center">' . esc_html__( 'Pro', 'anima' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'RTMP dual, escenas ilimitadas, soporte prioritario.', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center","fontSize":"xl"} -->
<p class="has-text-align-center has-xl-font-size">€49</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link" href="#">' . esc_html__( 'Solicitar beta', 'anima' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->',
];
