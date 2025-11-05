<?php
/**
 * Title: Proceso 4 pasos
 * Slug: anima/process-4
 * Categories: anima
 */

return [
    'title'      => __( 'Proceso en 4 pasos', 'anima' ),
    'categories' => [ 'anima' ],
    'content'    => '<!-- wp:group {"layout":{"type":"constrained","contentSize":"1100px"},"style":{"spacing":{"padding":{"top":"4.5rem","bottom":"4rem"},"blockGap":"2.5rem"}}} -->
<div class="wp-block-group" style="padding-top:4.5rem;padding-bottom:4rem"><!-- wp:group {"layout":{"type":"constrained"},"style":{"spacing":{"blockGap":"1rem"}},"className":"anima-section__header"} -->
<div class="wp-block-group anima-section__header"><!-- wp:paragraph {"className":"anima-hero__tag"} -->
<p class="anima-hero__tag">' . esc_html__( 'Cómo trabajamos', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"fontSize":"xxl"} -->
<h2 class="has-xxl-font-size">' . esc_html__( 'Metodología inmersiva en ciclos cortos', 'anima' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Alineamos estrategia, creatividad y operación XR en cuatro fases iterativas con entregables accionables.', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:list {"className":"anima-timeline"} -->
<ul class="anima-timeline"><li><span class="anima-timeline__step">01</span><div class="anima-timeline__body"><h3>' . esc_html__( 'Descubrimiento inmersivo', 'anima' ) . '</h3><p>' . esc_html__( 'Workshops con stakeholders, definición de KPIs y mapa de audiencias y canales.', 'anima' ) . '</p></div></li><li><span class="anima-timeline__step">02</span><div class="anima-timeline__body"><h3>' . esc_html__( 'Prototipo en tiempo real', 'anima' ) . '</h3><p>' . esc_html__( 'Mockups y rigs iniciales en Unreal/Unity, pruebas de captura y look &amp; feel de avatar.', 'anima' ) . '</p></div></li><li><span class="anima-timeline__step">03</span><div class="anima-timeline__body"><h3>' . esc_html__( 'Producción y training', 'anima' ) . '</h3><p>' . esc_html__( 'Modelado final, automatización de escenas, voice coaching y playbooks para tu equipo.', 'anima' ) . '</p></div></li><li><span class="anima-timeline__step">04</span><div class="anima-timeline__body"><h3>' . esc_html__( 'Live ops y evolución', 'anima' ) . '</h3><p>' . esc_html__( 'Operación continua, monitoreo de métricas, AB testing y roadmap de nuevas experiencias.', 'anima' ) . '</p></div></li></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->',
];
