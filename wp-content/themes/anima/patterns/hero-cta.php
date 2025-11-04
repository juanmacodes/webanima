<?php
/**
 * Title: Hero con CTA
 * Slug: anima/hero-cta
 * Categories: anima
 * Block Types: core/group
 * Keywords: hero, portada
 */

return [
    'title'      => __( 'Hero con CTA', 'anima' ),
    'categories' => [ 'anima' ],
    'content'    => '<!-- wp:cover {"url":"' . esc_url( ANIMA_ASSETS_URL . '/img/hero-bg.svg' ) . '","dimRatio":70,"overlayColor":"surface","isUserOverlayColor":true,"minHeight":80,"minHeightUnit":"vh","style":{"spacing":{"padding":{"top":"8rem","bottom":"8rem"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-cover" style="padding-top:8rem;padding-bottom:8rem"><span aria-hidden="true" class="wp-block-cover__background has-surface-background-color has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . esc_url( ANIMA_ASSETS_URL . '/img/hero-bg.svg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container">
<!-- wp:group {"layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group"><!-- wp:heading {"textAlign":"left","level":1,"fontSize":"display"} -->
<h1 class="wp-block-heading has-text-align-left has-display-font-size">' . esc_html__( 'Avatares y mundos 3D que conectan con audiencias reales', 'anima' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"lg"} -->
<p class="has-lg-font-size">' . esc_html__( 'Producción 3D, animación facial y experiencias interactivas en tiempo real para marcas, eventos y creadores.', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex"},"style":{"spacing":{"blockGap":"1rem"}}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link" href="/anima-world/">' . esc_html__( 'Explorar Anima World', 'anima' ) . '</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/proyectos/">' . esc_html__( 'Ver proyectos', 'anima' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div></div></div>
<!-- /wp:cover -->',
];
