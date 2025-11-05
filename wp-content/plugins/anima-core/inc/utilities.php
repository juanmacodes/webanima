<?php
/**
 * Utility helpers for Anima Core.
 *
 * @package anima-core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return filtered list of allowed CORS origins.
 *
 * @return array<int, string>
 */
function anima_core_get_allowed_origins(): array {
	$origins = [
		'https://animaavataragency.com',
		'https://staging.animaavataragency.com',
	];

	/**
	 * Filter to extend the list of allowed origins.
	 *
	 * @param array<int, string> $origins Allowed origins.
	 */
	return apply_filters( 'anima_core_cors_allowed_origins', $origins );
}

/**
 * Output CORS headers for REST and GraphQL responses.
 */
function anima_core_output_cors_headers(): void {
	$origin  = $_SERVER['HTTP_ORIGIN'] ?? '';
	$origins = anima_core_get_allowed_origins();

	if ( headers_sent() ) {
		return;
	}

	if ( $origin && in_array( $origin, $origins, true ) ) {
		header( 'Access-Control-Allow-Origin: ' . esc_url_raw( $origin ) );
		header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS' );
		header( 'Access-Control-Allow-Credentials: true' );
		header( 'Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce' );
		header( 'Vary: Origin' );
	}
}
add_action( 'rest_api_init', static function (): void {
	add_filter( 'rest_pre_serve_request', static function ( bool $served ): bool {
		anima_core_output_cors_headers();

		return $served;
	}, 11 );
} );

add_action( 'send_headers', 'anima_core_output_cors_headers' );

/**
 * Handle OPTIONS preflight requests early.
 */
function anima_core_handle_preflight(): void {
	if ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'OPTIONS' ) {
		return;
	}

	anima_core_output_cors_headers();
	status_header( 200 );
	exit;
}
add_action( 'init', 'anima_core_handle_preflight', 0 );

/**
 * Sanitize a scalar or array meta value.
 *
 * @param mixed $value The incoming value.
 * @return mixed
 */
function anima_core_sanitize_meta( $value ) {
	if ( is_array( $value ) ) {
		return array_map( 'anima_core_sanitize_meta', $value );
	}

	if ( is_object( $value ) ) {
		return json_decode( wp_json_encode( $value ), true );
	}

	if ( is_string( $value ) ) {
		return wp_kses_post( $value );
	}

	return $value;
}

/**
 * Retrieve meta value with default array fallback.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @return array<mixed>|string|int|float|null
 */
function anima_core_get_meta( int $post_id, string $key ) {
	$value = get_post_meta( $post_id, $key, true );

	if ( '' === $value ) {
		return [];
	}

	return $value;
}
