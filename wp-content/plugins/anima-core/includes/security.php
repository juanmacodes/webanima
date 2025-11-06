<?php
/**
 * Cabeceras de seguridad básicas para el tema.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registra el hook que envía las cabeceras de seguridad.
 */
function anima_register_security_headers() {
    add_action( 'send_headers', 'anima_send_security_headers', 20 );
}

/**
 * Envía cabeceras HTTP enfocadas en hardening.
 */
function anima_send_security_headers() {
    if ( true !== apply_filters( 'anima_security_headers_enabled', true ) ) {
        return;
    }

    $existing_headers = array();

    if ( function_exists( 'headers_list' ) ) {
        foreach ( headers_list() as $header_line ) {
            $parts = explode( ':', $header_line, 2 );
            if ( 2 === count( $parts ) ) {
                $existing_headers[ strtolower( trim( $parts[0] ) ) ] = trim( $parts[1] );
            }
        }
    }

    $nonce = apply_filters( 'anima_security_csp_nonce', '' );
    $nonce = is_string( $nonce ) ? trim( $nonce ) : '';
    if ( $nonce ) {
        $nonce = preg_replace( '/[^A-Za-z0-9+\/=\-]/', '', $nonce );
    }

    $csp_directives = array(
        "default-src 'self'",
        "img-src 'self' data: https:",
        "script-src 'self'" . ( $nonce ? " 'nonce-" . $nonce . "'" : '' ),
        "style-src 'self' 'unsafe-inline'",
        "font-src 'self' data: https:",
        "connect-src 'self'",
        "frame-ancestors 'self'",
    );

    /**
     * Permite modificar las directivas CSP generadas por defecto.
     */
    $csp_directives = apply_filters( 'anima_security_csp_directives', $csp_directives, $nonce );
    $csp_directives = array_filter( array_map( 'trim', $csp_directives ) );

    $headers = array(
        'content-security-policy' => implode( '; ', $csp_directives ),
        'x-frame-options'         => 'SAMEORIGIN',
        'referrer-policy'         => 'strict-origin-when-cross-origin',
        'x-content-type-options'  => 'nosniff',
        'permissions-policy'      => "camera=(), microphone=(), geolocation=()",
    );

    /**
     * Permite modificar las cabeceras antes de enviarlas.
     */
    $headers = apply_filters( 'anima_security_headers', $headers, $nonce );

    foreach ( $headers as $header => $value ) {
        $header_key = strtolower( $header );
        if ( isset( $existing_headers[ $header_key ] ) ) {
            continue;
        }

        if ( empty( $value ) ) {
            continue;
        }

        header( sprintf( '%s: %s', $header, $value ) );
    }
}
