<?php
/**
 * Title: Tarjeta de curso
 * Slug: anima/course-card
 * Categories: anima
 */

return [
    'title'      => __( 'Tarjeta de curso', 'anima' ),
    'categories' => [ 'anima' ],
    'blockTypes' => [ 'core/group' ],
    'content'    => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"1.5rem","bottom":"1.5rem","left":"1.5rem","right":"1.5rem"},"blockGap":"1rem"}},"backgroundColor":"surface","className":"anima-course-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group anima-course-card has-surface-background-color has-background" style="padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem"><!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="' . esc_url( ANIMA_ASSETS_URL . '/img/logo-anima.svg' ) . '" alt=""/></figure>
<!-- /wp:image -->

<!-- wp:heading {"level":3,"fontSize":"xl"} -->
<h3 class="wp-block-heading has-xl-font-size">' . esc_html__( 'Curso intensivo de streaming virtual', 'anima' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Aprende a emitir con avatares en tiempo real, configurar escenas y automatizar chatbots.', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap"},"style":{"spacing":{"blockGap":"0.5rem"}}} -->
<div class="wp-block-group"><!-- wp:paragraph {"backgroundColor":"violet","textColor":"background","style":{"typography":{"fontSize":"0.8rem"},"border":{"radius":"999px"},"spacing":{"padding":{"top":"0.25rem","bottom":"0.25rem","left":"0.75rem","right":"0.75rem"}}}} -->
<p class="has-background-color has-violet-background-color has-text-color" style="border-radius:999px;font-size:0.8rem;padding-top:0.25rem;padding-right:0.75rem;padding-bottom:0.25rem;padding-left:0.75rem">' . esc_html__( 'Intermedio', 'anima' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"backgroundColor":"accent","textColor":"background","style":{"typography":{"fontSize":"0.8rem"},"border":{"radius":"999px"},"spacing":{"padding":{"top":"0.25rem","bottom":"0.25rem","left":"0.75rem","right":"0.75rem"}}}} -->
<p class="has-background-color has-accent-background-color has-text-color" style="border-radius:999px;font-size:0.8rem;padding-top:0.25rem;padding-right:0.75rem;padding-bottom:0.25rem;padding-left:0.75rem">' . esc_html__( 'Blended', 'anima' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link" href="#">' . esc_html__( 'Ver curso', 'anima' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->',
];
