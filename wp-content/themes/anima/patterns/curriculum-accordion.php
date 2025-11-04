<?php
/**
 * Title: Temario en acordeón
 * Slug: anima/curriculum-accordion
 * Categories: anima
 */

return [
    'title'      => __( 'Temario en acordeón', 'anima' ),
    'categories' => [ 'anima' ],
    'content'    => '<!-- wp:group {"layout":{"type":"constrained","contentSize":"800px"}} -->
<div class="wp-block-group"><!-- wp:heading {"fontSize":"xxl"} -->
<h2 class="has-xxl-font-size">' . esc_html__( 'Temario', 'anima' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:details -->
<details>
<summary>' . esc_html__( 'Módulo 1 · Conceptos base y setup', 'anima' ) . '</summary>
<!-- wp:list -->
<ul><li>' . esc_html__( 'Introducción al streaming con avatares', 'anima' ) . '</li><li>' . esc_html__( 'Hardware recomendado y calibración', 'anima' ) . '</li></ul>
<!-- /wp:list -->
</details>
<!-- /wp:details -->

<!-- wp:details -->
<details>
<summary>' . esc_html__( 'Módulo 2 · Producción de escenas', 'anima' ) . '</summary>
<!-- wp:list -->
<ul><li>' . esc_html__( 'Workflows en Unreal/OBS', 'anima' ) . '</li><li>' . esc_html__( 'Automatización y triggers', 'anima' ) . '</li></ul>
<!-- /wp:list -->
</details>
<!-- /wp:details -->

<!-- wp:details -->
<details>
<summary>' . esc_html__( 'Módulo 3 · IA y engagement', 'anima' ) . '</summary>
<!-- wp:list -->
<ul><li>' . esc_html__( 'Integración de asistentes conversacionales', 'anima' ) . '</li><li>' . esc_html__( 'KPIs y analítica de eventos', 'anima' ) . '</li></ul>
<!-- /wp:list -->
</details>
<!-- /wp:details --></div>
<!-- /wp:group -->',
];
