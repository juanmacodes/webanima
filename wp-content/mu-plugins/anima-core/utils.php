<?php
/**
 * Utility helpers for Anima Core.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Returns the list of allowed CORS origins.
 *
 * @return string[]
 */
function anima_core_allowed_origins(): array {
    return array(
        'http://localhost:3000',
        'https://animaavataragency.com',
    );
}

/**
 * Determines whether the provided origin is allowed (supports *.vercel.app).
 *
 * @param string $origin Origin header value.
 * @return bool
 */
function anima_core_is_allowed_origin( string $origin ): bool {
    $origin = trim( $origin );

    if ( '' === $origin ) {
        return false;
    }

    if ( in_array( $origin, anima_core_allowed_origins(), true ) ) {
        return true;
    }

    if ( preg_match( '#^https://([a-z0-9-]+\.)?vercel\.app$#i', $origin ) ) {
        return true;
    }

    return false;
}

/**
 * Retrieve post meta with default value.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param mixed  $default Default value if meta is empty.
 * @return mixed
 */
function anima_core_get_meta( int $post_id, string $key, $default = '' ) {
    $value = get_post_meta( $post_id, $key, true );

    if ( null === $value || '' === $value ) {
        return $default;
    }

    return $value;
}

/**
 * Retrieve post meta that should be an array.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @return array
 */
function anima_core_get_meta_array( int $post_id, string $key ): array {
    $value = get_post_meta( $post_id, $key, true );

    if ( empty( $value ) ) {
        return array();
    }

    if ( is_array( $value ) ) {
        return array_values( array_filter( array_map( 'trim', $value ) ) );
    }

    if ( is_string( $value ) ) {
        $parts = preg_split( '/\r\n|\r|\n/', $value );
        if ( false !== $parts ) {
            return array_values( array_filter( array_map( 'trim', $parts ) ) );
        }
    }

    return array_values( array_filter( array_map( 'trim', (array) $value ) ) );
}

/**
 * Format an attachment for API responses.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $size          Image size.
 * @return array|null
 */
function anima_core_prepare_image( int $attachment_id, string $size = 'full' ): ?array {
    if ( $attachment_id <= 0 ) {
        return null;
    }

    $src = wp_get_attachment_image_src( $attachment_id, $size );

    if ( ! $src ) {
        return null;
    }

    $srcset = wp_get_attachment_image_srcset( $attachment_id, $size );
    $alt    = trim( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );

    return array(
        'src'    => esc_url_raw( $src[0] ),
        'width'  => (int) $src[1],
        'height' => (int) $src[2],
        'srcset' => $srcset ? sanitize_text_field( $srcset ) : '',
        'alt'    => sanitize_text_field( $alt ),
    );
}

/**
 * Normalize CTA fields for API responses.
 *
 * @param string $label CTA label.
 * @param string $url   CTA URL.
 * @return array|null
 */
function anima_core_prepare_cta( string $label, string $url ): ?array {
    $label = trim( wp_strip_all_tags( $label ) );
    $url   = esc_url_raw( $url );

    if ( '' === $label && '' === $url ) {
        return null;
    }

    return array(
        'label' => $label,
        'url'   => $url,
    );
}

/**
 * Prepare a REST response with cache headers.
 *
 * @param mixed $data Data to send.
 * @return WP_REST_Response
 */
function anima_core_prepare_rest_response( $data ): WP_REST_Response {
    $response = rest_ensure_response( $data );
    $response->header( 'Cache-Control', 'public, max-age=60, stale-while-revalidate=120' );

    return $response;
}

/**
 * Convert a value to float with fallback.
 *
 * @param mixed $value Value to convert.
 * @return float
 */
function anima_core_to_float( $value ): float {
    return (float) ( is_numeric( $value ) ? $value : 0 );
}
