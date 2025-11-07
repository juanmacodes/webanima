<?php
/**
 * Registro del tipo de contenido Curso.
 */

defined('ABSPATH') || exit;

if ( ! function_exists( 'anima_engine_register_curso_post_type' ) ) {
    /**
     * Registra el CPT Curso.
     */
    function anima_engine_register_curso_post_type(): void {
        if ( post_type_exists( 'curso' ) ) {
            return;
        }

        $labels = [
            'name'               => _x( 'Cursos', 'Post Type General Name', 'anima-engine' ),
            'singular_name'      => _x( 'Curso', 'Post Type Singular Name', 'anima-engine' ),
            'menu_name'          => __( 'Cursos', 'anima-engine' ),
            'name_admin_bar'     => __( 'Curso', 'anima-engine' ),
            'add_new'            => __( 'Añadir nuevo', 'anima-engine' ),
            'add_new_item'       => __( 'Añadir nuevo curso', 'anima-engine' ),
            'edit_item'          => __( 'Editar curso', 'anima-engine' ),
            'new_item'           => __( 'Nuevo curso', 'anima-engine' ),
            'view_item'          => __( 'Ver curso', 'anima-engine' ),
            'search_items'       => __( 'Buscar cursos', 'anima-engine' ),
            'not_found'          => __( 'No se encontraron cursos.', 'anima-engine' ),
            'not_found_in_trash' => __( 'No hay cursos en la papelera.', 'anima-engine' ),
            'all_items'          => __( 'Todos los cursos', 'anima-engine' ),
        ];

        register_post_type(
            'curso',
            [
                'label'         => __( 'Curso', 'anima-engine' ),
                'labels'        => $labels,
                'public'        => true,
                'show_ui'       => true,
                'show_in_menu'  => true,
                'menu_position' => 21,
                'menu_icon'     => 'dashicons-welcome-learn-more',
                'has_archive'   => 'cursos',
                'rewrite'       => [ 'slug' => 'curso', 'with_front' => false ],
                'supports'      => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
                'show_in_rest'  => true,
                'map_meta_cap'  => true,
                'publicly_queryable' => true,
            ]
        );
    }
}

add_action( 'init', 'anima_engine_register_curso_post_type' );

if ( ! function_exists( 'anima_engine_register_curso_graphql_fields' ) ) {
    /**
     * Expone campos personalizados en GraphQL.
     */
    function anima_engine_register_curso_graphql_fields(): void {
        if ( ! function_exists( 'register_graphql_field' ) ) {
            return;
        }

        $taxonomy_fields = [
            'nivelTerms'      => 'nivel',
            'modalidadTerms'  => 'modalidad',
            'tecnologiaTerms' => 'tecnologia',
        ];

        foreach ( $taxonomy_fields as $field_name => $taxonomy ) {
            register_graphql_field(
                'Curso',
                $field_name,
                [
                    'type'        => [ 'list_of' => 'String' ],
                    'description' => sprintf( __( 'Términos de la taxonomía %s asignados al curso.', 'anima-engine' ), $taxonomy ),
                    'resolve'     => static function ( $post ) use ( $taxonomy ) {
                        $terms = wp_get_post_terms( $post->ID, $taxonomy, [ 'fields' => 'names' ] );
                        return is_wp_error( $terms ) ? [] : $terms;
                    },
                ]
            );
        }

        $meta_fields = [
            'price'          => 'anima_price',
            'durationHours'  => 'anima_duration_hours',
            'requirements'   => 'anima_requirements',
            'upcomingDates'  => 'anima_upcoming_dates',
            'syllabus'       => 'anima_syllabus',
            'instructors'    => 'anima_instructors',
        ];

        foreach ( $meta_fields as $graphql_key => $meta_key ) {
            register_graphql_field(
                'Curso',
                $graphql_key,
                [
                    'type'        => 'String',
                    'description' => sprintf( __( 'Valor del meta %s.', 'anima-engine' ), $meta_key ),
                    'resolve'     => static function ( $post ) use ( $meta_key ) {
                        $value = get_post_meta( $post->ID, $meta_key, true );
                        if ( in_array( $meta_key, [ 'anima_upcoming_dates', 'anima_syllabus', 'anima_instructors' ], true ) ) {
                            return is_array( $value ) ? wp_json_encode( $value ) : (string) $value;
                        }

                        return is_array( $value ) ? wp_json_encode( $value ) : (string) $value;
                    },
                ]
            );
        }
    }
}

add_action( 'graphql_register_types', 'anima_engine_register_curso_graphql_fields' );
