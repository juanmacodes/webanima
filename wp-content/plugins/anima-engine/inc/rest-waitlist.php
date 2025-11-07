<?php
/**
 * Endpoint REST para gestionar inscripciones y listas de espera.
 *
 * @package Anima\Engine
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'anima_engine_register_waitlist_route' ) ) {
    /**
     * Registra la ruta REST.
     */
    function anima_engine_register_waitlist_route(): void {
        register_rest_route(
            'anima/v' . ANIMA_ENGINE_API_VERSION,
            '/waitlist',
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => 'anima_engine_handle_waitlist_request',
                'permission_callback' => '__return_true',
                'args'                => [
                    'name'     => [ 'type' => 'string', 'required' => true ],
                    'email'    => [ 'type' => 'string', 'required' => true ],
                    'country'  => [ 'type' => 'string', 'required' => false ],
                    'consent'  => [ 'type' => 'boolean', 'required' => true ],
                    'course_id'=> [ 'type' => 'integer', 'required' => false ],
                    'mode'     => [ 'type' => 'string', 'required' => false ],
                ],
            ]
        );
    }
}

add_action( 'rest_api_init', 'anima_engine_register_waitlist_route' );

if ( ! function_exists( 'anima_engine_handle_waitlist_request' ) ) {
    /**
     * Procesa la solicitud REST.
     */
    function anima_engine_handle_waitlist_request( \WP_REST_Request $request ) {
        $nonce = $request->get_header( 'x_wp_nonce' );
        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new \WP_Error( 'anima_waitlist_nonce', __( 'La sesión ha caducado. Actualiza la página e inténtalo de nuevo.', 'anima-engine' ), [ 'status' => 403 ] );
        }

        $name  = sanitize_text_field( (string) $request->get_param( 'name' ) );
        $email = sanitize_email( (string) $request->get_param( 'email' ) );
        $country = sanitize_text_field( (string) $request->get_param( 'country' ) );
        $consent = (bool) $request->get_param( 'consent' );
        $course_id = absint( $request->get_param( 'course_id' ) );
        $mode = sanitize_key( (string) $request->get_param( 'mode' ) );

        if ( '' === $name || '' === $email ) {
            return new \WP_Error( 'anima_waitlist_required', __( 'Nombre y email son obligatorios.', 'anima-engine' ), [ 'status' => 400 ] );
        }

        if ( ! is_email( $email ) ) {
            return new \WP_Error( 'anima_waitlist_email', __( 'El email indicado no es válido.', 'anima-engine' ), [ 'status' => 400 ] );
        }

        if ( ! $consent ) {
            return new \WP_Error( 'anima_waitlist_consent', __( 'Debes aceptar la política de privacidad.', 'anima-engine' ), [ 'status' => 400 ] );
        }

        $mode = in_array( $mode, [ 'waitlist', 'contact', 'url' ], true ) ? $mode : 'waitlist';

        $course_title = $course_id ? get_the_title( $course_id ) : '';
        $admin_email  = get_option( 'admin_email' );
        $subject      = sprintf( __( 'Nueva solicitud para el curso %s', 'anima-engine' ), $course_title ?: __( 'general', 'anima-engine' ) );

        $body_lines = [
            sprintf( __( 'Nombre: %s', 'anima-engine' ), $name ),
            sprintf( __( 'Email: %s', 'anima-engine' ), $email ),
        ];

        if ( $country ) {
            $body_lines[] = sprintf( __( 'País: %s', 'anima-engine' ), $country );
        }

        if ( $course_title ) {
            $body_lines[] = sprintf( __( 'Curso: %s (#%d)', 'anima-engine' ), $course_title, $course_id );
        }

        $body_lines[] = sprintf( __( 'Modo: %s', 'anima-engine' ), $mode );

        $body = implode( "\n", $body_lines );

        if ( 'waitlist' === $mode ) {
            do_action( 'anima_engine_waitlist_submitted', $course_id, $name, $email, $country );
        }

        wp_mail( $admin_email, $subject, $body );

        $message = 'waitlist' === $mode
            ? __( '¡Gracias! Te avisaremos en cuanto haya plaza.', 'anima-engine' )
            : __( 'Hemos recibido tu solicitud y nos pondremos en contacto contigo.', 'anima-engine' );

        return new \WP_REST_Response(
            [
                'success' => true,
                'message' => $message,
            ],
            200
        );
    }
}
