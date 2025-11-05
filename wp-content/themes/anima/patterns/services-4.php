<?php
/**
 * Title: Grid de servicios
 * Slug: anima/services-4
 * Categories: anima
 */

return [
    'title'      => __( 'Servicios (4 columnas)', 'anima' ),
    'categories' => [ 'anima' ],
    'content'    => '<!-- wp:group {"layout":{"type":"constrained","contentSize":"1100px"},"style":{"spacing":{"padding":{"top":"5rem","bottom":"4rem"},"blockGap":"2.5rem"}}} -->
<div class="wp-block-group" style="padding-top:5rem;padding-bottom:4rem"><!-- wp:group {"layout":{"type":"constrained"},"style":{"spacing":{"blockGap":"1rem"}},"className":"anima-section__header"} -->
<div class="wp-block-group anima-section__header"><!-- wp:paragraph {"className":"anima-hero__tag"} -->
<p class="anima-hero__tag">' . esc_html__( 'Servicios end-to-end', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"textAlign":"left","fontSize":"xxl"} -->
<h2 class="wp-block-heading has-text-align-left has-xxl-font-size">' . esc_html__( 'Especialistas en el ciclo completo de personajes virtuales', 'anima' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"lg"} -->
<p class="has-lg-font-size">' . esc_html__( 'Combinamos creatividad, ingeniería y operación continua para experiencias inmersivas listas para medir.', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:columns {"className":"anima-services"} -->
<div class="wp-block-columns anima-services"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"spacing":{"padding":{"top":"2.25rem","bottom":"2.25rem","left":"2rem","right":"2rem"},"blockGap":"1rem"}},"className":"anima-service-card"} -->
<div class="wp-block-group anima-service-card" style="padding-top:2.25rem;padding-right:2rem;padding-bottom:2.25rem;padding-left:2rem"><!-- wp:paragraph {"className":"anima-service-card__icon"} -->
<p class="anima-service-card__icon">🪄</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Avatares en directo', 'anima' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Motion capture facial y corporal con animación lipsync y broadcasting multicámara.', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"anima-service-card__list"} -->
<ul class="anima-service-card__list"><li>' . esc_html__( 'Integración OBS, Unreal Live Link y vtubing tools', 'anima' ) . '</li><li>' . esc_html__( 'Triggers interactivos desde chat o webhooks', 'anima' ) . '</li><li>' . esc_html__( 'Personajes metahuman, stylized y estilizados corporativos', 'anima' ) . '</li></ul>
<!-- /wp:list --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"spacing":{"padding":{"top":"2.25rem","bottom":"2.25rem","left":"2rem","right":"2rem"},"blockGap":"1rem"}},"className":"anima-service-card"} -->
<div class="wp-block-group anima-service-card" style="padding-top:2.25rem;padding-right:2rem;padding-bottom:2.25rem;padding-left:2rem"><!-- wp:paragraph {"className":"anima-service-card__icon"} -->
<p class="anima-service-card__icon">🛰️</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Cabinas holográficas', 'anima' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Instalaciones volumétricas para ferias, retail y activaciones phygital con presencia en tiempo real.', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"anima-service-card__list"} -->
<ul class="anima-service-card__list"><li>' . esc_html__( 'Cabinas LED, Pepper’s Ghost y proyección volumétrica', 'anima' ) . '</li><li>' . esc_html__( 'Control remoto desde nuestro XR Ops Center', 'anima' ) . '</li><li>' . esc_html__( 'Analytics de interacción y dwell time en dashboards', 'anima' ) . '</li></ul>
<!-- /wp:list --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"spacing":{"padding":{"top":"2.25rem","bottom":"2.25rem","left":"2rem","right":"2rem"},"blockGap":"1rem"}},"className":"anima-service-card"} -->
<div class="wp-block-group anima-service-card" style="padding-top:2.25rem;padding-right:2rem;padding-bottom:2.25rem;padding-left:2rem"><!-- wp:paragraph {"className":"anima-service-card__icon"} -->
<p class="anima-service-card__icon">🤖</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'IA conversacional avatarizada', 'anima' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Assistants con voz neural, memoria contextual y personalidad alineada a tu marca.', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"anima-service-card__list"} -->
<ul class="anima-service-card__list"><li>' . esc_html__( 'Entrenamiento con datos propietarios y guardrails', 'anima' ) . '</li><li>' . esc_html__( 'Sincronía labial en vivo y expresiones faciales', 'anima' ) . '</li><li>' . esc_html__( 'Integraciones CRM, Zendesk y marketing automation', 'anima' ) . '</li></ul>
<!-- /wp:list --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"spacing":{"padding":{"top":"2.25rem","bottom":"2.25rem","left":"2rem","right":"2rem"},"blockGap":"1rem"}},"className":"anima-service-card"} -->
<div class="wp-block-group anima-service-card" style="padding-top:2.25rem;padding-right:2rem;padding-bottom:2.25rem;padding-left:2rem"><!-- wp:paragraph {"className":"anima-service-card__icon"} -->
<p class="anima-service-card__icon">🌌</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . esc_html__( 'Mundos VR &amp; metaverso', 'anima' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Diseño de espacios inmersivos para eventos, training y comunidad con métricas accionables.', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:list {"className":"anima-service-card__list"} -->
<ul class="anima-service-card__list"><li>' . esc_html__( 'Experiencias WebXR, Unreal, Unity y Roblox', 'anima' ) . '</li><li>' . esc_html__( 'Economías digitales y drops coleccionables', 'anima' ) . '</li><li>' . esc_html__( 'Operación continua con live ops y soporte', 'anima' ) . '</li></ul>
<!-- /wp:list --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->',
];
