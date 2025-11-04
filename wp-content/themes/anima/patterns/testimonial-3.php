<?php
/**
 * Title: Testimonios 3 columnas
 * Slug: anima/testimonial-3
 * Categories: anima
 */

return [
    'title'      => __( 'Testimonios (3)', 'anima' ),
    'categories' => [ 'anima' ],
    'content'    => '<!-- wp:group {"layout":{"type":"constrained","contentSize":"1100px"}} -->
<div class="wp-block-group"><!-- wp:heading {"fontSize":"xxl"} -->
<h2 class="has-xxl-font-size">' . esc_html__( 'Lo que dicen nuestros clientes', 'anima' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:quote {"className":"is-style-default"} -->
<blockquote class="wp-block-quote is-style-default"><p>' . esc_html__( '“Anima transformó nuestro evento híbrido en una experiencia memorable con avatares en vivo.”', 'anima' ) . '</p><cite>' . esc_html__( 'María R., Live Nation', 'anima' ) . '</cite></blockquote>
<!-- /wp:quote --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:quote {"className":"is-style-default"} -->
<blockquote class="wp-block-quote is-style-default"><p>' . esc_html__( '“El equipo de Anima activó nuestra cabina holográfica con métricas que superaron cualquier feria anterior.”', 'anima' ) . '</p><cite>' . esc_html__( 'Carlos F., Retail360', 'anima' ) . '</cite></blockquote>
<!-- /wp:quote --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:quote {"className":"is-style-default"} -->
<blockquote class="wp-block-quote is-style-default"><p>' . esc_html__( '“La IA conversacional entrenada con su metodología nos permite atender leads 24/7 con tono de marca.”', 'anima' ) . '</p><cite>' . esc_html__( 'Lucía P., Fintech Co', 'anima' ) . '</cite></blockquote>
<!-- /wp:quote --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->',
];
