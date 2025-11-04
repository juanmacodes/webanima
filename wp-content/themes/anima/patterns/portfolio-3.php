<?php
/**
 * Title: Casos destacados
 * Slug: anima/portfolio-3
 * Categories: anima
 */

return [
    'title'      => __( 'Casos destacados (3 columnas)', 'anima' ),
    'categories' => [ 'anima' ],
    'content'    => '<!-- wp:group {"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group"><!-- wp:heading {"fontSize":"xxl"} -->
<h2 class="has-xxl-font-size">' . esc_html__( 'Casos destacados', 'anima' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:cover {"url":"' . esc_url( ANIMA_ASSETS_URL . '/img/logo-anima.svg' ) . '","dimRatio":80,"overlayColor":"surface","minHeight":320,"contentPosition":"bottom left"} -->
<div class="wp-block-cover has-custom-content-position is-position-bottom-left" style="min-height:320px"><span aria-hidden="true" class="wp-block-cover__background has-surface-background-color has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . esc_url( ANIMA_ASSETS_URL . '/img/logo-anima.svg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textColor":"text","fontSize":"xl"} -->
<h2 class="wp-block-heading has-text-color has-text-color has-xl-font-size">' . esc_html__( 'Avatar festival Twitch LATAM', 'anima' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"text"} -->
<p class="has-text-color">' . esc_html__( '32% ↑ tiempo de visualización', 'anima' ) . '</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:cover {"url":"' . esc_url( ANIMA_ASSETS_URL . '/img/logo-anima.svg' ) . '","dimRatio":80,"overlayColor":"surface","minHeight":320,"contentPosition":"bottom left"} -->
<div class="wp-block-cover has-custom-content-position is-position-bottom-left" style="min-height:320px"><span aria-hidden="true" class="wp-block-cover__background has-surface-background-color has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . esc_url( ANIMA_ASSETS_URL . '/img/logo-anima.svg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textColor":"text","fontSize":"xl"} -->
<h2 class="wp-block-heading has-text-color has-text-color has-xl-font-size">' . esc_html__( 'Cabina holográfica Retail Expo', 'anima' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"text"} -->
<p class="has-text-color">' . esc_html__( '1.8× asistentes en stand', 'anima' ) . '</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:cover {"url":"' . esc_url( ANIMA_ASSETS_URL . '/img/logo-anima.svg' ) . '","dimRatio":80,"overlayColor":"surface","minHeight":320,"contentPosition":"bottom left"} -->
<div class="wp-block-cover has-custom-content-position is-position-bottom-left" style="min-height:320px"><span aria-hidden="true" class="wp-block-cover__background has-surface-background-color has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . esc_url( ANIMA_ASSETS_URL . '/img/logo-anima.svg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textColor":"text","fontSize":"xl"} -->
<h2 class="wp-block-heading has-text-color has-text-color has-xl-font-size">' . esc_html__( 'Metaverso marca deportiva', 'anima' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"textColor":"text"} -->
<p class="has-text-color">' . esc_html__( '92% finalización VR', 'anima' ) . '</p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->',
];
