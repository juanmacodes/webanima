<?php
/**
 * Registro de taxonomías personalizadas.
 *
 * @package Anima
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action(
    'init',
    static function (): void {
        register_taxonomy(
            'servicio',
            [ 'proyecto', 'curso' ],
            [
                'hierarchical'      => true,
                'labels'            => [
                    'name'          => __( 'Servicios', 'anima' ),
                    'singular_name' => __( 'Servicio', 'anima' ),
                ],
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_rest'      => true,
                'rewrite'           => [ 'slug' => 'servicios', 'with_front' => false ],
            ]
        );

        register_taxonomy(
            'nivel',
            [ 'curso' ],
            [
                'hierarchical'      => false,
                'labels'            => [
                    'name'          => __( 'Niveles', 'anima' ),
                    'singular_name' => __( 'Nivel', 'anima' ),
                ],
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_rest'      => true,
                'rewrite'           => [ 'slug' => 'nivel', 'with_front' => false ],
            ]
        );

        register_taxonomy(
            'modalidad',
            [ 'curso' ],
            [
                'hierarchical'      => false,
                'labels'            => [
                    'name'          => __( 'Modalidades', 'anima' ),
                    'singular_name' => __( 'Modalidad', 'anima' ),
                ],
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_rest'      => true,
                'rewrite'           => [ 'slug' => 'modalidad', 'with_front' => false ],
            ]
        );
    }
);
