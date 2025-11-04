<?php
/**
 * Title: Proceso 4 pasos
 * Slug: anima/process-4
 * Categories: anima
 */

return [
    'title'      => __( 'Proceso en 4 pasos', 'anima' ),
    'categories' => [ 'anima' ],
    'content'    => '<!-- wp:group {"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group"><!-- wp:heading {"fontSize":"xxl"} -->
<h2 class="has-xxl-font-size">' . esc_html__( 'Cómo trabajamos', 'anima' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"anima-process"} -->
<ul class="anima-process"><li><strong>' . esc_html__( 'Descubrimiento inmersivo', 'anima' ) . '</strong> · ' . esc_html__( 'Mapeamos objetivos, audiencias y canales.', 'anima' ) . '</li><li><strong>' . esc_html__( 'Prototipo en tiempo real', 'anima' ) . '</strong> · ' . esc_html__( 'Validamos avatar, entorno y dinámicas en días.', 'anima' ) . '</li><li><strong>' . esc_html__( 'Producción y training', 'anima' ) . '</strong> · ' . esc_html__( 'Integramos stack técnico y formamos al equipo.', 'anima' ) . '</li><li><strong>' . esc_html__( 'Live ops y datos', 'anima' ) . '</strong> · ' . esc_html__( 'Medimos KPIs y iteramos experiencias.', 'anima' ) . '</li></ul>
<!-- /wp:list --></div>
<!-- /wp:group -->',
];
