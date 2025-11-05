<?php
/**
 * REST endpoint for the waitlist.
 *
 * @package anima-core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', 'anima_core_register_waitlist_endpoint' );

/**
 * Register the waitlist REST endpoint.
 */
function anima_core_register_waitlist_endpoint(): void {
    register_rest_route(
        'anima/v1',
        '/waitlist',
        [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'anima_core_handle_waitlist_submission',
            'permission_callback' => '__return_true',
            'args'                => [
                'name'    => [
                    'type'        => 'string',
                    'required'    => false,
                    'description' => __( 'Nombre de la persona interesada.', 'anima-core' ),
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'email'   => [
                    'type'        => 'string',
                    'required'    => true,
                    'description' => __( 'Correo electrónico de contacto.', 'anima-core' ),
                    'validate_callback' => 'is_email',
                    'sanitize_callback' => 'sanitize_email',
                ],
                'network' => [
                    'type'        => 'string',
                    'required'    => false,
                    'description' => __( 'Red social o plataforma de origen.', 'anima-core' ),
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'country' => [
                    'type'        => 'string',
                    'required'    => false,
                    'description' => __( 'País del solicitante.', 'anima-core' ),
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'consent' => [
                    'type'        => 'boolean',
                    'required'    => true,
                    'description' => __( 'Confirmación de consentimiento.', 'anima-core' ),
                ],
            ],
        ]
    );
}

/**
 * Handle waitlist submissions.
 *
 * @param WP_REST_Request $request Request instance.
 * @return WP_REST_Response|WP_Error
 */
function anima_core_handle_waitlist_submission( WP_REST_Request $request ) {
    $email   = $request->get_param( 'email' );
    $consent = $request->get_param( 'consent' );

    if ( empty( $email ) || ! is_email( $email ) ) {
        return new WP_Error( 'invalid_email', __( 'El correo electrónico es obligatorio y debe ser válido.', 'anima-core' ), [ 'status' => 400 ] );
    }

    if ( true !== $consent ) {
        return new WP_Error( 'invalid_consent', __( 'Es necesario aceptar el consentimiento.', 'anima-core' ), [ 'status' => 400 ] );
    }

    $data = [
        'name'    => $request->get_param( 'name' ) ? sanitize_text_field( (string) $request->get_param( 'name' ) ) : '',
        'email'   => sanitize_email( (string) $email ),
        'network' => $request->get_param( 'network' ) ? sanitize_text_field( (string) $request->get_param( 'network' ) ) : '',
        'country' => $request->get_param( 'country' ) ? sanitize_text_field( (string) $request->get_param( 'country' ) ) : '',
        'consent' => 1,
    ];

    global $wpdb;

    $table = $wpdb->prefix . 'anima_waitlist';
    $inserted = $wpdb->insert(
        $table,
        [
            'name'    => $data['name'],
            'email'   => $data['email'],
            'network' => $data['network'],
            'country' => $data['country'],
            'consent' => $data['consent'],
        ],
        [ '%s', '%s', '%s', '%s', '%d' ]
    );

    if ( false === $inserted ) {
        return new WP_Error( 'database_error', __( 'No se pudo guardar el registro en la base de datos.', 'anima-core' ), [ 'status' => 500 ] );
    }

    /**
     * Dispara una acción cuando se añade un registro a la lista de espera.
     *
     * @param array $data Datos registrados.
     */
    do_action( 'anima_waitlist_added', $data );

    return rest_ensure_response(
        [
            'ok' => true,
        ]
    );
}
