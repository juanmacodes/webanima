<?php
/**
 * Taxonomías registradas por el plugin.
 *
 * @package anima-core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'anima_core_register_taxonomies' );

/**
 * Register custom taxonomies for CPTs.
 */
function anima_core_register_taxonomies(): void {
    register_taxonomy(
        'servicio',
        [ 'proyecto' ],
        [
            'labels'            => [
                'name'          => __( 'Servicios', 'anima-core' ),
                'singular_name' => __( 'Servicio', 'anima-core' ),
            ],
            'public'            => true,
            'hierarchical'      => true,
            'show_in_rest'      => true,
            'show_in_graphql'   => true,
            'graphql_single_name' => 'Servicio',
            'graphql_plural_name' => 'Servicios',
        ]
    );

    register_taxonomy(
        'nivel',
        [ 'curso' ],
        [
            'labels'            => [
                'name'          => __( 'Niveles', 'anima-core' ),
                'singular_name' => __( 'Nivel', 'anima-core' ),
            ],
            'public'            => true,
            'hierarchical'      => true,
            'show_in_rest'      => true,
            'show_in_graphql'   => true,
            'graphql_single_name' => 'NivelCurso',
            'graphql_plural_name' => 'NivelesCurso',
        ]
    );

    register_taxonomy(
        'modalidad',
        [ 'curso' ],
        [
            'labels'            => [
                'name'          => __( 'Modalidades', 'anima-core' ),
                'singular_name' => __( 'Modalidad', 'anima-core' ),
            ],
            'public'            => true,
            'hierarchical'      => false,
            'show_in_rest'      => true,
            'show_in_graphql'   => true,
            'graphql_single_name' => 'ModalidadCurso',
            'graphql_plural_name' => 'ModalidadesCurso',
        ]
    );
}

add_action( 'init', 'anima_core_register_default_terms', 20 );

/**
 * Ensure base terms exist.
 */
function anima_core_register_default_terms(): void {
    $servicio_terms = [ 'Streaming', 'Holográficos', 'IA', 'VR' ];

    foreach ( $servicio_terms as $term ) {
        if ( ! term_exists( $term, 'servicio' ) ) {
            wp_insert_term( $term, 'servicio' );
        }
    }

    $nivel_terms = [ 'Inicial', 'Intermedio', 'Avanzado' ];
    foreach ( $nivel_terms as $term ) {
        if ( ! term_exists( $term, 'nivel' ) ) {
            wp_insert_term( $term, 'nivel' );
        }
    }

    $modalidad_terms = [ 'Grabado', 'Directo', 'Blended' ];
    foreach ( $modalidad_terms as $term ) {
        if ( ! term_exists( $term, 'modalidad' ) ) {
            wp_insert_term( $term, 'modalidad' );
        }
    }
}
