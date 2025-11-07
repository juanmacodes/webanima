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
                'labels'       => [ 'name' => __( 'Niveles', 'anima-engine' ), 'singular_name' => __( 'Nivel', 'anima-engine' ) ],
                'hierarchical' => true,
                'show_ui'      => true,
                'show_in_menu' => true,
                'show_in_rest' => true,
                'rewrite'      => [ 'slug' => 'nivel' ],
            ],
            'modalidad'  => [
                'labels'       => [ 'name' => __( 'Modalidades', 'anima-engine' ), 'singular_name' => __( 'Modalidad', 'anima-engine' ) ],
                'hierarchical' => false,
                'show_ui'      => true,
                'show_in_menu' => true,
                'show_in_rest' => true,
                'rewrite'      => [ 'slug' => 'modalidad' ],
            ],
            'tecnologia' => [
                'labels'       => [ 'name' => __( 'Tecnologías', 'anima-engine' ), 'singular_name' => __( 'Tecnología', 'anima-engine' ) ],
                'hierarchical' => false,
                'show_ui'      => true,
                'show_in_menu' => true,
                'show_in_rest' => true,
                'rewrite'      => [ 'slug' => 'tecnologia' ],
            ],
        ];

        foreach ( $taxonomies as $slug => $args ) {
            $defaults = [
                'labels'            => $args['labels'],
                'hierarchical'      => $args['hierarchical'],
                'show_ui'           => $args['show_ui'],
                'show_in_menu'      => $args['show_in_menu'],
                'show_in_rest'      => $args['show_in_rest'],
                'show_admin_column' => true,
                'rewrite'           => $args['rewrite'],
            ];

            if ( taxonomy_exists( $slug ) ) {
                register_taxonomy_for_object_type( $slug, 'curso' );
            } else {
                if ( function_exists( 'register_taxonomy' ) ) {
                    $defaults['show_in_graphql']     = true;
                    $defaults['graphql_single_name'] = ucfirst( $args['labels']['singular_name'] ?? $slug );
                    $defaults['graphql_plural_name'] = $args['labels']['name'] ?? $slug . 's';
                    register_taxonomy( $slug, [ 'curso' ], $defaults );
                }
            }
        }
    }
}

add_action( 'init', 'anima_engine_register_curso_taxonomies' );
