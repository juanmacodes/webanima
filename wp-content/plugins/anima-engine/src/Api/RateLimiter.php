<?php
namespace Anima\Engine\Api;

use WP_Error;
use WP_REST_Request;

use const MINUTE_IN_SECONDS;

use function __;
use function apply_filters;
use function get_transient;
use function sanitize_text_field;
use function set_transient;
use function wp_unslash;

/**
 * Simple rate limiting helper for REST endpoints.
 */
class RateLimiter {
    /**
     * Checks and increments the counter for a request.
     */
    public static function check( WP_REST_Request $request, string $endpoint, int $limit = 10, int $window = MINUTE_IN_SECONDS ) {
        $ip    = self::resolve_ip( $request );
        $key   = self::build_key( $ip, $endpoint );
        $limit = (int) apply_filters( 'anima_engine_rate_limit_threshold', $limit, $endpoint, $request );
        $window = (int) apply_filters( 'anima_engine_rate_limit_window', $window, $endpoint, $request );

        if ( $limit <= 0 ) {
            return true;
        }

        if ( $window <= 0 ) {
            $window = MINUTE_IN_SECONDS;
        }

        $hits = (int) get_transient( $key );

        if ( $hits >= $limit ) {
            return new WP_Error(
                'anima_engine_rate_limited',
                __( 'Has superado el límite temporal de solicitudes. Inténtalo de nuevo en unos minutos.', 'anima-engine' ),
                [ 'status' => 429 ]
            );
        }

        set_transient( $key, $hits + 1, $window );

        return true;
    }

    /**
     * Resets the counter for the given request and endpoint.
     */
    public static function reset( WP_REST_Request $request, string $endpoint ): void {
        $ip  = self::resolve_ip( $request );
        $key = self::build_key( $ip, $endpoint );

        set_transient( $key, 0, 1 );
    }

    /**
     * Builds the transient key based on the IP and endpoint.
     */
    protected static function build_key( string $ip, string $endpoint ): string {
        $base = sprintf( '%s:%s', $ip, $endpoint );

        return 'anima_engine_rate_' . md5( $base );
    }

    /**
     * Resolves the IP address for the current request.
     */
    protected static function resolve_ip( WP_REST_Request $request ): string {
        $ip = $request->get_header( 'X-Forwarded-For' );

        if ( is_string( $ip ) && '' !== $ip ) {
            $parts = explode( ',', $ip );
            $ip    = trim( $parts[0] );
        } else {
            $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
        }

        if ( '' === $ip ) {
            $ip = 'unknown';
        }

        return $ip;
    }
}
