<?php
/**
 * REST API CORS configuration for Anima Core.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'allowed_http_origins', 'anima_core_extend_allowed_origins' );
add_filter( 'rest_pre_serve_request', 'anima_core_add_cors_headers', 11, 4 );

/**
 * Add the custom origins to the allowed list.
 *
 * @param array $origins Existing allowed origins.
 * @return array
 */
function anima_core_extend_allowed_origins( array $origins ): array {
    foreach ( anima_core_allowed_origins() as $origin ) {
        if ( ! in_array( $origin, $origins, true ) ) {
            $origins[] = $origin;
        }
    }

    return $origins;
}

/**
 * Inject CORS headers for the Anima namespace.
 *
 * @param bool             $served  Whether the request has already been served.
 * @param WP_HTTP_Response $result  Result to send.
 * @param WP_REST_Request  $request Request object.
 * @param WP_REST_Server   $server  Server instance.
 * @return bool
 */
function anima_core_add_cors_headers( $served, $result, $request, $server ) {
    unset( $server );

    $route  = $request instanceof WP_REST_Request ? $request->get_route() : '';
    $origin = isset( $_SERVER['HTTP_ORIGIN'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ORIGIN'] ) ) : '';

    if ( 0 !== strpos( $route, '/anima/v1' ) ) {
        return $served;
    }

    if ( anima_core_is_allowed_origin( $origin ) ) {
        header( 'Access-Control-Allow-Origin: ' . $origin );
        header( 'Vary: Origin' );
        header( 'Access-Control-Allow-Credentials: true' );
    }

    header( 'Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce' );
    header( 'Access-Control-Allow-Methods: GET, OPTIONS' );
    header( 'Access-Control-Expose-Headers: X-WP-Total, X-WP-TotalPages' );

    if ( 'OPTIONS' === $request->get_method() ) {
        status_header( 200 );
        return true;
    }

    return $served;
}
