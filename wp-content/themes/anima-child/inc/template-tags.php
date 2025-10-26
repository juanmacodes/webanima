<?php
/**
 * Template helpers para componentes Elementor / bloques reutilizables
 */

add_action( 'wp_head', function () {
    echo '<a class="skip-to-content" href="#main">' . esc_html__( 'Saltar al contenido', 'anima-child' ) . '</a>';
} );

add_filter( 'body_class', function ( $classes ) {
    $classes[] = 'anima-dark';
    return $classes;
} );

add_filter( 'block_type_metadata', function ( $metadata ) {
    if ( ! empty( $metadata['supports']['color']['link'] ) ) {
        $metadata['supports']['color']['gradients'] = true;
    }
    return $metadata;
} );
