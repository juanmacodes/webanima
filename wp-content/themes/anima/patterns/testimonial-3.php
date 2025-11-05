<?php
/**
 * Title: Testimonios 3 columnas
 * Slug: anima/testimonial-3
 * Categories: anima
 */

return [
    'title'      => __( 'Testimonios (3)', 'anima' ),
    'categories' => [ 'anima' ],
    'content'    => '<!-- wp:group {"layout":{"type":"constrained","contentSize":"1100px"},"style":{"spacing":{"padding":{"top":"4rem","bottom":"4rem"},"blockGap":"2.5rem"}}} -->
<div class="wp-block-group" style="padding-top:4rem;padding-bottom:4rem"><!-- wp:group {"layout":{"type":"constrained"},"style":{"spacing":{"blockGap":"1rem"}},"className":"anima-section__header"} -->
<div class="wp-block-group anima-section__header"><!-- wp:paragraph {"className":"anima-hero__tag"} -->
<p class="anima-hero__tag">' . esc_html__( 'Confianza de líderes', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"fontSize":"xxl"} -->
<h2 class="has-xxl-font-size">' . esc_html__( 'Experiencias que enamoran a audiencias y equipos', 'anima' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Clientes globales y scaleups confían en nuestro equipo para ejecutar activaciones con fiabilidad y métricas claras.', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:columns {"style":{"spacing":{"blockGap":"1.75rem"}},"className":"anima-testimonials"} -->
<div class="wp-block-columns anima-testimonials"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"spacing":{"blockGap":"1rem","padding":{"top":"2.25rem","bottom":"2.25rem","left":"2rem","right":"2rem"}}},"className":"anima-testimonial"} -->
<div class="wp-block-group anima-testimonial" style="padding-top:2.25rem;padding-right:2rem;padding-bottom:2.25rem;padding-left:2rem"><!-- wp:paragraph {"className":"anima-testimonial__quote"} -->
<p class="anima-testimonial__quote">' . esc_html__( '“Anima transformó nuestro evento híbrido en una narrativa interactiva que triplicó el tiempo de visionado.”', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-testimonial__role"} -->
<p class="anima-testimonial__role">' . esc_html__( 'María R. · Live Nation', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"spacing":{"blockGap":"1rem","padding":{"top":"2.25rem","bottom":"2.25rem","left":"2rem","right":"2rem"}}},"className":"anima-testimonial"} -->
<div class="wp-block-group anima-testimonial" style="padding-top:2.25rem;padding-right:2rem;padding-bottom:2.25rem;padding-left:2rem"><!-- wp:paragraph {"className":"anima-testimonial__quote"} -->
<p class="anima-testimonial__quote">' . esc_html__( '“El equipo operó la cabina holográfica con precisión y reportes en tiempo real para el board.”', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-testimonial__role"} -->
<p class="anima-testimonial__role">' . esc_html__( 'Carlos F. · Retail360', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"style":{"spacing":{"blockGap":"1rem","padding":{"top":"2.25rem","bottom":"2.25rem","left":"2rem","right":"2rem"}}},"className":"anima-testimonial"} -->
<div class="wp-block-group anima-testimonial" style="padding-top:2.25rem;padding-right:2rem;padding-bottom:2.25rem;padding-left:2rem"><!-- wp:paragraph {"className":"anima-testimonial__quote"} -->
<p class="anima-testimonial__quote">' . esc_html__( '“Su IA conversacional reduce el tiempo de respuesta a minutos sin perder el tono humano de nuestra marca.”', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"anima-testimonial__role"} -->
<p class="anima-testimonial__role">' . esc_html__( 'Lucía P. · Fintech Co', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->',
];
