<?php
/**
 * Registro de taxonomías asociadas al CPT Curso.
 */

defined('ABSPATH') || exit;

if ( ! function_exists( 'anima_engine_register_curso_taxonomies' ) ) {
    /**
     * Registra las taxonomías para los cursos.
     */
    function anima_engine_register_curso_taxonomies(): void {
        $taxonomies = [
            'nivel'      => [
                'labels'        => [ 'name' => __( 'Niveles', 'anima-engine' ), 'singular_name' => __( 'Nivel', 'anima-engine' ) ],
                'hierarchical'  => true,
                'rewrite'       => [ 'slug' => 'nivel' ],
                'default_terms' => [
                    'inicial'    => __( 'Inicial', 'anima-engine' ),
                    'intermedio' => __( 'Intermedio', 'anima-engine' ),
                    'avanzado'   => __( 'Avanzado', 'anima-engine' ),
                ],
            ],
            'modalidad'  => [
                'labels'        => [ 'name' => __( 'Modalidades', 'anima-engine' ), 'singular_name' => __( 'Modalidad', 'anima-engine' ) ],
                'hierarchical'  => false,
                'rewrite'       => [ 'slug' => 'modalidad' ],
                'default_terms' => [
                    'grabado'   => __( 'Grabado', 'anima-engine' ),
                    'directo'   => __( 'Directo', 'anima-engine' ),
                    'blended'   => __( 'Blended', 'anima-engine' ),
                ],
            ],
            'tecnologia' => [
                'labels'        => [ 'name' => __( 'Tecnologías', 'anima-engine' ), 'singular_name' => __( 'Tecnología', 'anima-engine' ) ],
                'hierarchical'  => false,
                'rewrite'       => [ 'slug' => 'tecnologia' ],
                'default_terms' => [
                    'ia'           => __( 'IA', 'anima-engine' ),
                    'vr'           => __( 'VR', 'anima-engine' ),
                    'holograficos' => __( 'Holográficos', 'anima-engine' ),
                    'streaming'    => __( 'Streaming', 'anima-engine' ),
                ],
            ],
        ];

        foreach ( $taxonomies as $slug => $args ) {
            $defaults = [
                'labels'            => $args['labels'],
                'hierarchical'      => $args['hierarchical'],
                'show_ui'           => true,
                'show_in_menu'      => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => $args['rewrite'],
                'show_in_graphql'   => function_exists( 'register_graphql_object_type' ),
                'graphql_single_name' => ucfirst( $args['labels']['singular_name'] ?? $slug ),
                'graphql_plural_name' => $args['labels']['name'] ?? $slug . 's',
            ];

            if ( taxonomy_exists( $slug ) ) {
                register_taxonomy_for_object_type( $slug, 'curso' );
            } else {
                register_taxonomy( $slug, [ 'curso' ], $defaults );
            }

            if ( empty( $args['default_terms'] ) ) {
                continue;
            }

            foreach ( $args['default_terms'] as $term_slug => $term_name ) {
                if ( ! term_exists( $term_slug, $slug ) ) {
                    wp_insert_term( $term_name, $slug, [ 'slug' => $term_slug ] );
                }
            }
        }
    }
}

add_action( 'init', 'anima_engine_register_curso_taxonomies' );
