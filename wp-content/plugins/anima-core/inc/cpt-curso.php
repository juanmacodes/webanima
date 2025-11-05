<?php
/**
 * Curso custom post type.
 *
 * @package anima-core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'anima_core_register_curso_cpt' );

/**
 * Register the Curso custom post type.
 */
function anima_core_register_curso_cpt(): void {
    $labels = [
        'name'               => _x( 'Cursos', 'Post Type General Name', 'anima-core' ),
        'singular_name'      => _x( 'Curso', 'Post Type Singular Name', 'anima-core' ),
        'menu_name'          => __( 'Cursos', 'anima-core' ),
        'name_admin_bar'     => __( 'Curso', 'anima-core' ),
        'add_new'            => __( 'Añadir nuevo', 'anima-core' ),
        'add_new_item'       => __( 'Añadir nuevo curso', 'anima-core' ),
        'new_item'           => __( 'Nuevo curso', 'anima-core' ),
        'edit_item'          => __( 'Editar curso', 'anima-core' ),
        'view_item'          => __( 'Ver curso', 'anima-core' ),
        'all_items'          => __( 'Todos los cursos', 'anima-core' ),
        'search_items'       => __( 'Buscar cursos', 'anima-core' ),
        'not_found'          => __( 'No se encontraron cursos.', 'anima-core' ),
        'not_found_in_trash' => __( 'No hay cursos en la papelera.', 'anima-core' ),
    ];

    $args = [
        'label'               => __( 'Curso', 'anima-core' ),
        'labels'              => $labels,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'show_in_graphql'     => true,
        'graphql_single_name' => 'Curso',
        'graphql_plural_name' => 'Cursos',
        'has_archive'         => 'cursos',
        'rewrite'             => [
            'slug'       => 'curso',
            'with_front' => false,
        ],
        'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
    ];

    register_post_type( 'curso', $args );
}

add_action( 'init', 'anima_core_register_curso_meta' );

/**
 * Register Curso meta fields.
 */
function anima_core_register_curso_meta(): void {
    register_post_meta(
        'curso',
        'anima_duration_hours',
        [
            'single'            => true,
            'show_in_rest'      => true,
            'show_in_graphql'   => true,
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
        ]
    );

    register_post_meta(
        'curso',
        'anima_price',
        [
            'single'            => true,
            'show_in_rest'      => true,
            'show_in_graphql'   => true,
            'type'              => 'number',
            'sanitize_callback' => 'floatval',
        ]
    );

    register_post_meta(
        'curso',
        'anima_syllabus',
        [
            'single'            => true,
            'show_in_graphql'   => true,
            'sanitize_callback' => 'anima_core_sanitize_meta',
            'show_in_rest'      => [
                'schema' => [
                    'type'       => 'array',
                    'items'      => [
                        'type'       => 'object',
                        'properties' => [
                            'title'   => [
                                'type' => 'string',
                            ],
                            'lessons' => [
                                'type'  => 'array',
                                'items' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]
    );

    register_post_meta(
        'curso',
        'anima_requirements',
        [
            'single'            => true,
            'show_in_rest'      => true,
            'show_in_graphql'   => true,
            'type'              => 'string',
            'sanitize_callback' => 'wp_kses_post',
        ]
    );

    register_post_meta(
        'curso',
        'anima_instructors',
        [
            'single'            => true,
            'show_in_graphql'   => true,
            'sanitize_callback' => 'anima_core_sanitize_meta',
            'show_in_rest'      => [
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
        ]
    );

    register_post_meta(
        'curso',
        'anima_upcoming_dates',
        [
            'single'            => true,
            'show_in_graphql'   => true,
            'sanitize_callback' => 'anima_core_sanitize_meta',
            'show_in_rest'      => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [
                        'type'   => 'string',
                        'format' => 'date-time',
                    ],
                ],
            ],
        ]
    );
}
