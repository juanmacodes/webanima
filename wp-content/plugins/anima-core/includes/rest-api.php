<?php
/**
 * Endpoints REST personalizados.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function anima_register_rest_routes() {
    register_rest_route(
        'anima/v1',
        '/contacto',
        array(
            'methods'             => 'POST',
            'callback'            => 'anima_handle_contact_endpoint',
            'permission_callback' => '__return_true',
            'args'                => array(
                'nombre'  => array(
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'email'   => array(
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_email',
                    'validate_callback' => 'is_email',
                ),
                'consulta' => array(
                    'required'          => true,
                    'sanitize_callback' => 'anima_sanitize_multiline_text',
                ),
                'consentimiento' => array(
                    'required'          => true,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ),
                'captcha_token' => array(
                    'required'          => false,
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        )
    );
}

function anima_handle_contact_endpoint( WP_REST_Request $request ) {
    $nombre         = $request->get_param( 'nombre' );
    $email          = $request->get_param( 'email' );
    $consulta       = $request->get_param( 'consulta' );
    $consentimiento = filter_var( $request->get_param( 'consentimiento' ), FILTER_VALIDATE_BOOLEAN );
    $captcha_token  = $request->get_param( 'captcha_token' );

    if ( empty( $nombre ) || empty( $email ) || empty( $consulta ) ) {
        return new WP_Error(
            'anima_contact_missing_fields',
            __( 'Por favor, complete todos los campos obligatorios.', 'anima-core' ),
            array( 'status' => 400 )
        );
    }

    if ( true !== $consentimiento ) {
        return new WP_Error(
            'anima_contact_consent_required',
            __( 'Debes aceptar la política de privacidad para continuar.', 'anima-core' ),
            array( 'status' => 400 )
        );
    }

    $ip_address = anima_contact_get_client_ip( $request );

    $rate_check = anima_contact_apply_rate_limit( $ip_address );
    if ( is_wp_error( $rate_check ) ) {
        return $rate_check;
    }

    $captcha_check = anima_contact_maybe_verify_captcha( $captcha_token, $ip_address );
    if ( is_wp_error( $captcha_check ) ) {
        return $captcha_check;
    }

    $admin_email = get_option( 'admin_email' );
    $subject     = sprintf( __( 'Nuevo contacto desde %s', 'anima-core' ), get_bloginfo( 'name' ) );
    $message     = sprintf(
        "Nombre: %s\nEmail: %s\nConsentimiento RGPD: %s\nConsulta:\n%s",
        $nombre,
        $email,
        $consentimiento ? __( 'Sí', 'anima-core' ) : __( 'No', 'anima-core' ),
        $consulta
    );

    $headers = array( 'Content-Type: text/plain; charset=UTF-8' );

    $mail_sent = wp_mail( $admin_email, $subject, $message, $headers );

    if ( false === $mail_sent ) {
        return new WP_Error(
            'anima_contact_mail_failed',
            __( 'No se pudo enviar tu mensaje en este momento. Inténtalo de nuevo más tarde.', 'anima-core' ),
            array( 'status' => 500 )
        );
    }

    do_action(
        'anima_contact_request_received',
        array(
            'nombre'         => $nombre,
            'email'          => $email,
            'consulta'       => $consulta,
            'consentimiento' => $consentimiento,
            'ip'             => $ip_address,
        )
    );

    return rest_ensure_response(
        array(
            'ok'      => true,
            'mensaje' => __( 'Gracias, nos pondremos en contacto contigo muy pronto.', 'anima-core' ),
        )
    );
}

function anima_contact_get_client_ip( WP_REST_Request $request ) {
    $ip = $request->get_header( 'x-forwarded-for' );

    if ( ! empty( $ip ) ) {
        $ip_parts = explode( ',', $ip );
        $ip       = trim( $ip_parts[0] );
    }

    if ( empty( $ip ) && isset( $_SERVER['REMOTE_ADDR'] ) ) {
        $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
    }

    return $ip ? $ip : '0.0.0.0';
}

function anima_contact_apply_rate_limit( $ip_address ) {
    $ip_address = sanitize_text_field( $ip_address );
    $key        = 'anima_contact_rate_' . md5( $ip_address );
    $attempts   = get_transient( $key );

    if ( false === $attempts ) {
        $attempts = 0;
    }

    $max_attempts = apply_filters( 'anima_contact_rate_limit', 5 );

    if ( $attempts >= $max_attempts ) {
        return new WP_Error(
            'anima_contact_rate_limited',
            __( 'Has enviado demasiadas solicitudes. Inténtalo de nuevo en un minuto.', 'anima-core' ),
            array( 'status' => 429 )
        );
    }

    set_transient( $key, $attempts + 1, MINUTE_IN_SECONDS );

    return true;
}

function anima_contact_maybe_verify_captcha( $token, $ip_address ) {
    $provider = get_option( 'anima_contact_captcha_provider', 'none' );

    if ( 'none' === $provider ) {
        return true;
    }

    $token = is_string( $token ) ? trim( $token ) : '';

    if ( empty( $token ) ) {
        return new WP_Error(
            'anima_contact_captcha_missing',
            __( 'Falta el token de verificación. Actualiza la página e inténtalo de nuevo.', 'anima-core' ),
            array( 'status' => 400 )
        );
    }

    if ( 'recaptcha_v3' === $provider ) {
        return anima_contact_verify_recaptcha_v3( $token, $ip_address );
    }

    if ( 'hcaptcha' === $provider ) {
        return anima_contact_verify_hcaptcha( $token, $ip_address );
    }

    return true;
}

function anima_contact_verify_recaptcha_v3( $token, $ip_address ) {
    $secret_key = get_option( 'anima_recaptcha_secret_key', '' );

    if ( empty( $secret_key ) ) {
        return new WP_Error(
            'anima_contact_recaptcha_missing_key',
            __( 'La protección reCAPTCHA no está configurada correctamente.', 'anima-core' ),
            array( 'status' => 500 )
        );
    }

    $response = wp_remote_post(
        'https://www.google.com/recaptcha/api/siteverify',
        array(
            'body'      => array(
                'secret'   => $secret_key,
                'response' => $token,
                'remoteip' => $ip_address,
            ),
            'timeout'   => 10,
            'sslverify' => true,
        )
    );

    if ( is_wp_error( $response ) ) {
        return new WP_Error(
            'anima_contact_recaptcha_request_failed',
            __( 'No se pudo validar el reCAPTCHA. Inténtalo más tarde.', 'anima-core' ),
            array( 'status' => 500 )
        );
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( 200 !== $code ) {
        return new WP_Error(
            'anima_contact_recaptcha_http_error',
            __( 'La validación reCAPTCHA devolvió un estado inesperado.', 'anima-core' ),
            array( 'status' => 500 )
        );
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( empty( $body['success'] ) ) {
        return new WP_Error(
            'anima_contact_recaptcha_failed',
            __( 'No se pudo verificar que eres una persona.', 'anima-core' ),
            array( 'status' => 400 )
        );
    }

    $score      = isset( $body['score'] ) ? (float) $body['score'] : 0;
    $threshold  = (float) apply_filters( 'anima_contact_recaptcha_score_threshold', 0.5 );
    $action     = isset( $body['action'] ) ? sanitize_text_field( $body['action'] ) : '';
    $action_req = apply_filters( 'anima_contact_recaptcha_expected_action', '' );

    if ( $action_req && $action && $action_req !== $action ) {
        return new WP_Error(
            'anima_contact_recaptcha_action_mismatch',
            __( 'La acción del token no coincide con la esperada.', 'anima-core' ),
            array( 'status' => 400 )
        );
    }

    if ( $score < $threshold ) {
        return new WP_Error(
            'anima_contact_recaptcha_low_score',
            __( 'La verificación automática ha fallado. Intenta de nuevo.', 'anima-core' ),
            array( 'status' => 400 )
        );
    }

    return true;
}

function anima_contact_verify_hcaptcha( $token, $ip_address ) {
    $secret_key = get_option( 'anima_hcaptcha_secret_key', '' );

    if ( empty( $secret_key ) ) {
        return new WP_Error(
            'anima_contact_hcaptcha_missing_key',
            __( 'La protección hCaptcha no está configurada correctamente.', 'anima-core' ),
            array( 'status' => 500 )
        );
    }

    $response = wp_remote_post(
        'https://hcaptcha.com/siteverify',
        array(
            'body'      => array(
                'secret'   => $secret_key,
                'response' => $token,
                'remoteip' => $ip_address,
            ),
            'timeout'   => 10,
            'sslverify' => true,
        )
    );

    if ( is_wp_error( $response ) ) {
        return new WP_Error(
            'anima_contact_hcaptcha_request_failed',
            __( 'No se pudo validar el hCaptcha. Inténtalo más tarde.', 'anima-core' ),
            array( 'status' => 500 )
        );
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( 200 !== $code ) {
        return new WP_Error(
            'anima_contact_hcaptcha_http_error',
            __( 'La validación hCaptcha devolvió un estado inesperado.', 'anima-core' ),
            array( 'status' => 500 )
        );
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( empty( $body['success'] ) ) {
        return new WP_Error(
            'anima_contact_hcaptcha_failed',
            __( 'No se pudo verificar la respuesta de hCaptcha.', 'anima-core' ),
            array( 'status' => 400 )
        );
    }

    return true;
}
