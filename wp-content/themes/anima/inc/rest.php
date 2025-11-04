<?php
/**
 * Endpoints REST personalizados.
 *
 * @package Anima
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action(
    'rest_api_init',
    static function (): void {
        register_rest_route(
            'anima/v1',
            '/waitlist',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => static function ( WP_REST_Request $request ) {
                    $nonce = $request->get_param( 'nonce' );
                    if ( ! wp_verify_nonce( $nonce, 'anima_waitlist' ) ) {
                        return new WP_REST_Response( [ 'message' => __( 'Acceso no autorizado.', 'anima' ) ], 403 );
                    }

                    $data = [
                        'name'    => $request->get_param( 'name' ),
                        'email'   => $request->get_param( 'email' ),
                        'network' => $request->get_param( 'network' ),
                        'country' => $request->get_param( 'country' ),
                        'beta'    => $request->get_param( 'beta' ),
                    ];

                    $result = anima_waitlist_insert( $data );

                    if ( is_wp_error( $result ) ) {
                        return new WP_REST_Response(
                            [ 'message' => $result->get_error_message() ],
                            400
                        );
                    }

                    return new WP_REST_Response(
                        [
                            'message' => __( 'Registro completado', 'anima' ),
                            'id'      => (int) $result,
                        ],
                        200
                    );
                },
                'permission_callback' => '__return_true',
                'args'                => [
                    'name'    => [ 'required' => true ],
                    'email'   => [ 'required' => true ],
                    'network' => [ 'required' => true ],
                    'country' => [ 'required' => true ],
                ],
            ]
        );
    }
);
