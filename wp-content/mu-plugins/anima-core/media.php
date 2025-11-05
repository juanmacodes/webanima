<?php
/**
 * Media helpers and image sizes.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'after_setup_theme', 'anima_core_register_image_sizes' );

/**
 * Register custom image sizes for immersive layouts.
 */
function anima_core_register_image_sizes(): void {
    add_image_size( 'anima-card-1x', 800, 600, true );
    add_image_size( 'anima-card-2x', 1600, 1200, true );
    add_image_size( 'anima-hero-2x', 2400, 1400, true );
}

add_filter( 'upload_mimes', 'anima_core_allow_webp_uploads' );

/**
 * Allow WebP uploads if the server supports them.
 *
 * @param array $mimes Allowed mime types.
 * @return array
 */
function anima_core_allow_webp_uploads( array $mimes ): array {
    $mimes['webp'] = 'image/webp';

    return $mimes;
}
