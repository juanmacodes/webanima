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
            ),
        )
    );
}

function anima_handle_contact_endpoint( WP_REST_Request $request ) {
    $nombre   = $request->get_param( 'nombre' );
    $email    = $request->get_param( 'email' );
    $consulta = $request->get_param( 'consulta' );

    if ( empty( $nombre ) || empty( $email ) || empty( $consulta ) ) {
        return new WP_REST_Response(
            array(
                'ok'    => false,
                'error' => __( 'Por favor, complete todos los campos obligatorios.', 'anima-core' ),
            ),
            400
        );
    }

    $admin_email = get_option( 'admin_email' );
    $subject     = sprintf( __( 'Nuevo contacto desde %s', 'anima-core' ), get_bloginfo( 'name' ) );
    $message     = sprintf( "Nombre: %s\nEmail: %s\nConsulta:\n%s", $nombre, $email, $consulta );

    wp_mail( $admin_email, $subject, $message, array( 'Content-Type: text/plain; charset=UTF-8' ) );

    do_action(
        'anima_contact_request_received',
        array(
            'nombre'   => $nombre,
            'email'    => $email,
            'consulta' => $consulta,
        )
    );

    return new WP_REST_Response(
        array(
            'ok'      => true,
            'mensaje' => __( 'Gracias, nos pondremos en contacto contigo muy pronto.', 'anima-core' ),
        ),
        200
    );
}
