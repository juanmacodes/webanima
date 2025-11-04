<?php
/**
 * Title: CTA final
 * Slug: anima/cta-full
 * Categories: anima
 */

return [
    'title'      => __( 'CTA a contacto', 'anima' ),
    'categories' => [ 'anima' ],
    'content'    => '<!-- wp:cover {"url":"' . esc_url( ANIMA_ASSETS_URL . '/img/hero-bg.svg' ) . '","dimRatio":80,"overlayColor":"surface","minHeight":60,"minHeightUnit":"vh","layout":{"type":"constrained"}} -->
<div class="wp-block-cover" style="min-height:60vh"><span aria-hidden="true" class="wp-block-cover__background has-surface-background-color has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . esc_url( ANIMA_ASSETS_URL . '/img/hero-bg.svg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:group {"layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group"><!-- wp:heading {"textAlign":"center","fontSize":"xxl"} -->
<h2 class="has-text-align-center has-xxl-font-size">' . esc_html__( '¿Listo para activar tu universo virtual?', 'anima' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"lg"} -->
<p class="has-text-align-center has-lg-font-size">' . esc_html__( 'Agenda una demo y co-creemos la próxima experiencia con avatares, IA y XR.', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link" href="/contacto/">' . esc_html__( 'Solicitar demo', 'anima' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->',
];
