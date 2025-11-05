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
    'content'    => '<!-- wp:cover {"url":"' . esc_url( ANIMA_ASSETS_URL . '/img/hero-bg.svg' ) . '","dimRatio":80,"overlayColor":"surface","isUserOverlayColor":true,"minHeight":90,"minHeightUnit":"vh","style":{"spacing":{"padding":{"top":"6rem","bottom":"6rem"}}},"className":"anima-hero"} -->
<div class="wp-block-cover anima-hero" style="padding-top:6rem;padding-bottom:6rem;min-height:90vh"><span aria-hidden="true" class="wp-block-cover__background has-surface-background-color has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . esc_url( ANIMA_ASSETS_URL . '/img/hero-bg.svg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:group {"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group"><!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":"3rem"}},"className":"anima-hero__grid"} -->
<div class="wp-block-columns are-vertically-aligned-center anima-hero__grid"><!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:group {"style":{"spacing":{"blockGap":"1.5rem"}},"className":"anima-hero__content"} -->
<div class="wp-block-group anima-hero__content"><!-- wp:paragraph {"className":"anima-hero__tag"} -->
<p class="anima-hero__tag">' . esc_html__( 'Anima Avatar Agency', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"left","level":1,"fontSize":"display"} -->
<h1 class="wp-block-heading has-text-align-left has-display-font-size">' . esc_html__( 'Avatares hiperrealistas, mundos inmersivos y experiencias XR en tiempo real', 'anima' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"lg"} -->
<p class="has-lg-font-size">' . esc_html__( 'Diseñamos, animamos y operamos personajes virtuales y entornos 3D que combinan IA generativa, motion capture y narrativas interactivas.', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"anima-hero__features"} -->
<ul class="anima-hero__features"><li>' . esc_html__( 'Laboratorio XR con captura volumétrica y facial in-house', 'anima' ) . '</li><li>' . esc_html__( 'Estrategia multicanal: streaming, ferias, metaverso y retail', 'anima' ) . '</li><li>' . esc_html__( 'Equipo senior en Unreal Engine, Unity, WebXR y narrativas AI', 'anima' ) . '</li></ul>
<!-- /wp:list -->

<!-- wp:buttons {"layout":{"type":"flex"},"style":{"spacing":{"blockGap":"1rem"}}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link" href="/contacto/">' . esc_html__( 'Agendar consultoría', 'anima' ) . '</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/proyectos/">' . esc_html__( 'Ver portfolio', 'anima' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"1.25rem","margin":{"top":"0.5rem"}}},"className":"anima-hero__metrics"} -->
<div class="wp-block-group anima-hero__metrics" style="margin-top:0.5rem"><!-- wp:group {"layout":{"type":"constrained"},"className":"anima-metric"} -->
<div class="wp-block-group anima-metric"><!-- wp:paragraph {"className":"anima-metric__value"} -->
<p class="anima-metric__value">24h</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-metric__label"} -->
<p class="anima-metric__label">' . esc_html__( 'Para activar pilotos inmersivos', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"constrained"},"className":"anima-metric"} -->
<div class="wp-block-group anima-metric"><!-- wp:paragraph {"className":"anima-metric__value"} -->
<p class="anima-metric__value">+120</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-metric__label"} -->
<p class="anima-metric__label">' . esc_html__( 'Assets 3D y personajes producidos', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"constrained"},"className":"anima-metric"} -->
<div class="wp-block-group anima-metric"><!-- wp:paragraph {"className":"anima-metric__value"} -->
<p class="anima-metric__value">9.7</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-metric__label"} -->
<p class="anima-metric__label">' . esc_html__( 'NPS promedio de clientes', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center"><!-- wp:group {"style":{"spacing":{"padding":{"top":"2.5rem","bottom":"2.5rem","left":"2.5rem","right":"2.5rem"},"blockGap":"1.5rem"}},"className":"anima-hero__visual"} -->
<div class="wp-block-group anima-hero__visual" style="padding-top:2.5rem;padding-right:2.5rem;padding-bottom:2.5rem;padding-left:2.5rem"><!-- wp:group {"style":{"spacing":{"blockGap":"0.5rem"}},"className":"anima-hero__signal"} -->
<div class="wp-block-group anima-hero__signal"><!-- wp:paragraph {"className":"anima-chip"} -->
<p class="anima-chip">XR Control Room</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-chip"} -->
<p class="anima-chip">Live avatars</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-chip"} -->
<p class="anima-chip">AI copilots</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:heading {"level":3,"fontSize":"xl"} -->
<h3 class="wp-block-heading has-xl-font-size">' . esc_html__( 'Pipeline modular que escala con tu roadmap', 'anima' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Integramos captura facial en vivo, animación corporal, render en Unreal/Unity y capas conversacionales con IA entrenada en tu marca.', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"0.75rem"}},"className":"anima-chip-list"} -->
<div class="wp-block-group anima-chip-list"><!-- wp:paragraph {"className":"anima-chip"} -->
<p class="anima-chip">Metahuman</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-chip"} -->
<p class="anima-chip">Ready Player Me</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-chip"} -->
<p class="anima-chip">Live Link Face</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-chip"} -->
<p class="anima-chip">TensorFlow RT</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"0.5rem"}},"className":"anima-hero__status"} -->
<div class="wp-block-group anima-hero__status"><!-- wp:paragraph {"className":"anima-hero__status-label"} -->
<p class="anima-hero__status-label">' . esc_html__( 'Status laboratorio', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"},"style":{"spacing":{"blockGap":"0.75rem"}},"className":"anima-hero__status-bar"} -->
<div class="wp-block-group anima-hero__status-bar"><!-- wp:paragraph {"className":"anima-hero__status-value"} -->
<p class="anima-hero__status-value">' . esc_html__( 'Producción 78%', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-hero__status-value"} -->
<p class="anima-hero__status-value">' . esc_html__( 'Live ops listo', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div></div></div>
<!-- /wp:cover -->',
];
