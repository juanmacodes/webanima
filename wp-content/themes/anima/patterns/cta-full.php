<?php
/**
 * Title: CTA final
 * Slug: anima/cta-full
 * Categories: anima
 */

return [
    'title'      => __( 'CTA a contacto', 'anima' ),
    'categories' => [ 'anima' ],
    'content'    => '<!-- wp:cover {"url":"' . esc_url( ANIMA_ASSETS_URL . '/img/hero-bg.svg' ) . '","dimRatio":85,"overlayColor":"surface","minHeight":60,"minHeightUnit":"vh","style":{"spacing":{"padding":{"top":"5rem","bottom":"5rem"}}},"className":"anima-cta"} -->
<div class="wp-block-cover anima-cta" style="padding-top:5rem;padding-bottom:5rem;min-height:60vh"><span aria-hidden="true" class="wp-block-cover__background has-surface-background-color has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . esc_url( ANIMA_ASSETS_URL . '/img/hero-bg.svg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:group {"layout":{"type":"constrained","contentSize":"760px"},"style":{"spacing":{"blockGap":"1.75rem"}}} -->
<div class="wp-block-group"><!-- wp:paragraph {"className":"anima-hero__tag"} -->
<p class="anima-hero__tag">' . esc_html__( 'Hablemos de tu próximo universo', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"center","fontSize":"xxl"} -->
<h2 class="has-text-align-center has-xxl-font-size">' . esc_html__( '¿Listo para activar un avatar, cabina holográfica o mundo XR?', 'anima' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"lg"} -->
<p class="has-text-align-center has-lg-font-size">' . esc_html__( 'Agendemos una sesión estratégica para mapear objetivos, guionizar la experiencia y definir KPIs accionables.', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:group {"layout":{"type":"flex","justifyContent":"center","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"0.75rem"}},"className":"anima-chip-list"} -->
<div class="wp-block-group anima-chip-list"><!-- wp:paragraph {"className":"anima-chip"} -->
<p class="anima-chip">' . esc_html__( 'Discovery XR 45 min', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-chip"} -->
<p class="anima-chip">' . esc_html__( 'Roadmap personalizado', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-chip"} -->
<p class="anima-chip">' . esc_html__( 'Estimación de inversión', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"blockGap":"1rem"}}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link" href="/contacto/">' . esc_html__( 'Solicitar demo', 'anima' ) . '</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/agenda/">' . esc_html__( 'Descargar credenciales', 'anima' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->',
];
