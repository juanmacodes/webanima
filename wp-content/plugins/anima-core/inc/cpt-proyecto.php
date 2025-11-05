<?php
/**
 * Proyecto custom post type.
 *
 * @package anima-core
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'anima_core_register_proyecto_cpt' );

/**
 * Register the Proyecto custom post type.
 */
function anima_core_register_proyecto_cpt(): void {
    $labels = [
        'name'               => _x( 'Proyectos', 'Post Type General Name', 'anima-core' ),
        'singular_name'      => _x( 'Proyecto', 'Post Type Singular Name', 'anima-core' ),
        'menu_name'          => __( 'Proyectos', 'anima-core' ),
        'name_admin_bar'     => __( 'Proyecto', 'anima-core' ),
        'add_new'            => __( 'Añadir nuevo', 'anima-core' ),
        'add_new_item'       => __( 'Añadir nuevo proyecto', 'anima-core' ),
        'new_item'           => __( 'Nuevo proyecto', 'anima-core' ),
        'edit_item'          => __( 'Editar proyecto', 'anima-core' ),
        'view_item'          => __( 'Ver proyecto', 'anima-core' ),
        'all_items'          => __( 'Todos los proyectos', 'anima-core' ),
        'search_items'       => __( 'Buscar proyectos', 'anima-core' ),
        'not_found'          => __( 'No se encontraron proyectos.', 'anima-core' ),
        'not_found_in_trash' => __( 'No hay proyectos en la papelera.', 'anima-core' ),
    ];

    $args = [
        'label'               => __( 'Proyecto', 'anima-core' ),
        'labels'              => $labels,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'show_in_graphql'     => true,
        'graphql_single_name' => 'Proyecto',
        'graphql_plural_name' => 'Proyectos',
        'has_archive'         => 'proyectos',
        'rewrite'             => [
            'slug'       => 'proyecto',
            'with_front' => false,
        ],
        'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
    ];

    register_post_type( 'proyecto', $args );
}

add_action( 'init', 'anima_core_register_proyecto_meta' );

/**
 * Register Proyecto meta fields.
 */
function anima_core_register_proyecto_meta(): void {
    register_post_meta(
        'proyecto',
        'anima_client',
        [
            'single'            => true,
            'show_in_rest'      => true,
            'show_in_graphql'   => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
        ]
    );

    register_post_meta(
        'proyecto',
        'anima_year',
        [
            'single'            => true,
            'show_in_rest'      => true,
            'show_in_graphql'   => true,
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
        ]
    );

    register_post_meta(
        'proyecto',
        'anima_stack',
        [
            'single'            => true,
            'show_in_graphql'   => true,
            'sanitize_callback' => 'anima_core_sanitize_meta',
            'show_in_rest'      => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                ],
            ],
        ]
    );

    register_post_meta(
        'proyecto',
        'anima_kpis',
        [
            'single'            => true,
            'show_in_graphql'   => true,
            'sanitize_callback' => 'anima_core_sanitize_meta',
            'show_in_rest'      => [
                'schema' => [
                    'type'       => 'array',
                    'description' => __( 'Lista de KPIs', 'anima-core' ),
                    'items'      => [
                        'type'       => 'object',
                        'properties' => [
                            'label' => [
                                'type' => 'string',
                            ],
                            'value' => [
                                'type' => 'string',
                            ],
                        ],
                    ],
                ],
            ],
        ]
    );

    register_post_meta(
        'proyecto',
        'anima_gallery',
        [
            'single'            => true,
            'show_in_graphql'   => true,
            'sanitize_callback' => 'anima_core_sanitize_meta',
            'show_in_rest'      => [
                'schema' => [
                    'type'  => 'array',
                    'items' => [
                        'type' => 'integer',
                    ],
                ],
            ],
        ]
    );

    register_post_meta(
        'proyecto',
        'anima_video_url',
        [
            'single'            => true,
            'show_in_rest'      => true,
            'show_in_graphql'   => true,
            'type'              => 'string',
            'sanitize_callback' => 'esc_url_raw',
        ]
    );
}
