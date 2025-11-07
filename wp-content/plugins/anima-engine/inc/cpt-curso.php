<?php
/**
 * Registro del tipo de contenido Curso y sus metadatos.
 *
 * @package Anima\Engine
 */

defined( 'ABSPATH' ) || exit;

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
                'label'               => __( 'Curso', 'anima-engine' ),
                'labels'              => $labels,
                'public'              => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'menu_position'       => 21,
                'menu_icon'           => 'dashicons-welcome-learn-more',
                'has_archive'         => 'cursos',
                'rewrite'             => [ 'slug' => 'curso', 'with_front' => false ],
                'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
                'show_in_rest'        => true,
                'rest_base'           => 'curso',
                'publicly_queryable'  => true,
                'map_meta_cap'        => true,
                'show_in_graphql'     => function_exists( 'register_graphql_object_type' ),
                'graphql_single_name' => 'Curso',
                'graphql_plural_name' => 'Cursos',
            ]
        );
    }
}

add_action( 'init', 'anima_engine_register_curso_post_type' );

if ( ! function_exists( 'anima_engine_register_curso_meta' ) ) {
    /**
     * Registra los metadatos disponibles para el CPT Curso.
     */
    function anima_engine_register_curso_meta(): void {
        $meta_args = [
            'anima_duration_hours' => [
                'type'              => 'number',
                'single'            => true,
                'sanitize_callback' => static function ( $value ) {
                    return is_numeric( $value ) ? absint( $value ) : 0;
                },
            ],
            'anima_price'          => [
                'type'              => 'number',
                'single'            => true,
                'sanitize_callback' => static function ( $value ) {
                    return is_numeric( $value ) ? (float) $value : 0.0;
                },
            ],
            'anima_requirements'   => [
                'type'              => 'string',
                'single'            => true,
                'sanitize_callback' => 'wp_kses_post',
            ],
            'anima_enroll_target'  => [
                'type'              => 'string',
                'single'            => true,
                'sanitize_callback' => static function ( $value ) {
                    $allowed = [ 'waitlist', 'contact', 'url' ];
                    $value   = sanitize_key( $value );
                    return in_array( $value, $allowed, true ) ? $value : 'waitlist';
                },
            ],
            'anima_enroll_url'     => [
                'type'              => 'string',
                'single'            => true,
                'sanitize_callback' => 'esc_url_raw',
            ],
        ];

        foreach ( $meta_args as $meta_key => $args ) {
            register_post_meta(
                'curso',
                $meta_key,
                array_merge(
                    [
                        'show_in_rest' => true,
                        'auth_callback' => '__return_true',
                    ],
                    $args
                )
            );
        }

        register_post_meta(
            'curso',
            'anima_syllabus',
            [
                'type'         => 'array',
                'single'       => true,
                'show_in_rest' => [
                    'schema' => [
                        'type'  => 'array',
                        'items' => [
                            'type'       => 'object',
                            'properties' => [
                                'title'   => [ 'type' => 'string' ],
                                'lessons' => [
                                    'type'  => 'array',
                                    'items' => [ 'type' => 'string' ],
                                ],
                            ],
                        ],
                    ],
                ],
                'sanitize_callback' => static function ( $value ) {
                    if ( ! is_array( $value ) ) {
                        return [];
                    }

                    $syllabus = [];

                    foreach ( $value as $module ) {
                        if ( empty( $module['title'] ) ) {
                            continue;
                        }

                        $lessons = [];
                        if ( ! empty( $module['lessons'] ) && is_array( $module['lessons'] ) ) {
                            foreach ( $module['lessons'] as $lesson ) {
                                $lesson = sanitize_text_field( $lesson );
                                if ( '' !== $lesson ) {
                                    $lessons[] = $lesson;
                                }
                            }
                        }

                        $syllabus[] = [
                            'title'   => sanitize_text_field( $module['title'] ),
                            'lessons' => $lessons,
                        ];
                    }

                    return $syllabus;
                },
                'auth_callback' => '__return_true',
            ]
        );

        register_post_meta(
            'curso',
            'anima_instructors',
            [
                'type'         => 'array',
                'single'       => true,
                'show_in_rest' => [
                    'schema' => [
                        'type'  => 'array',
                        'items' => [
                            'type'       => 'object',
                            'properties' => [
                                'name'       => [ 'type' => 'string' ],
                                'bio'        => [ 'type' => 'string' ],
                                'avatar_url' => [ 'type' => 'string', 'format' => 'uri' ],
                            ],
                        ],
                    ],
                ],
                'sanitize_callback' => static function ( $value ) {
                    if ( ! is_array( $value ) ) {
                        return [];
                    }

                    $instructors = [];
                    foreach ( $value as $instructor ) {
                        if ( empty( $instructor['name'] ) ) {
                            continue;
                        }

                        $instructors[] = [
                            'name'       => sanitize_text_field( $instructor['name'] ),
                            'bio'        => isset( $instructor['bio'] ) ? wp_kses_post( $instructor['bio'] ) : '',
                            'avatar_url' => isset( $instructor['avatar_url'] ) ? esc_url_raw( $instructor['avatar_url'] ) : '',
                        ];
                    }

                    return $instructors;
                },
                'auth_callback' => '__return_true',
            ]
        );

        register_post_meta(
            'curso',
            'anima_upcoming_dates',
            [
                'type'         => 'array',
                'single'       => true,
                'show_in_rest' => [
                    'schema' => [
                        'type'  => 'array',
                        'items' => [ 'type' => 'string', 'format' => 'date-time' ],
                    ],
                ],
                'sanitize_callback' => static function ( $value ) {
                    if ( ! is_array( $value ) ) {
                        return [];
                    }

                    $dates = [];
                    foreach ( $value as $date ) {
                        $date = sanitize_text_field( $date );
                        if ( preg_match( '/^\d{4}-\d{2}-\d{2}/', $date ) ) {
                            $dates[] = $date;
                        }
                    }

                    return $dates;
                },
                'auth_callback' => '__return_true',
            ]
        );
    }
}

add_action( 'init', 'anima_engine_register_curso_meta' );

if ( ! function_exists( 'anima_engine_register_curso_graphql_fields' ) ) {
    /**
     * Expone campos personalizados en GraphQL cuando WPGraphQL está disponible.
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
            'enrollTarget'   => 'anima_enroll_target',
            'enrollUrl'      => 'anima_enroll_url',
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
                        return is_array( $value ) ? wp_json_encode( $value ) : (string) $value;
                    },
                ]
            );
        }
    }
}

add_action( 'graphql_register_types', 'anima_engine_register_curso_graphql_fields' );
