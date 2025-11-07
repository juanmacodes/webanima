<?php
/**
 * Registro del Custom Post Type "Avatar" y metadatos relacionados.
 *
 * @package Anima\Engine
 */

defined( 'ABSPATH' ) || exit;

add_action(
    'init',
    static function () {
        $labels = [
            'name'               => __( 'Avatares', 'anima-engine' ),
            'singular_name'      => __( 'Avatar', 'anima-engine' ),
            'add_new'            => __( 'Añadir nuevo', 'anima-engine' ),
            'add_new_item'       => __( 'Añadir nuevo avatar', 'anima-engine' ),
            'edit_item'          => __( 'Editar avatar', 'anima-engine' ),
            'new_item'           => __( 'Nuevo avatar', 'anima-engine' ),
            'view_item'          => __( 'Ver avatar', 'anima-engine' ),
            'view_items'         => __( 'Ver avatares', 'anima-engine' ),
            'search_items'       => __( 'Buscar avatares', 'anima-engine' ),
            'not_found'          => __( 'No se encontraron avatares.', 'anima-engine' ),
            'not_found_in_trash' => __( 'No hay avatares en la papelera.', 'anima-engine' ),
            'all_items'          => __( 'Todos los avatares', 'anima-engine' ),
        ];

        register_post_type(
            'avatar',
            [
                'labels'             => $labels,
                'public'             => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'menu_icon'          => 'dashicons-id-alt',
                'supports'           => [ 'title', 'thumbnail', 'editor', 'excerpt' ],
                'show_in_rest'       => true,
                'rewrite'            => [
                    'slug'       => 'avatar',
                    'with_front' => false,
                ],
                'has_archive'        => 'avatares',
                'publicly_queryable' => true,
            ]
        );

        register_taxonomy(
            'avatar_categoria',
            [ 'avatar' ],
            [
                'labels'            => [
                    'name'          => __( 'Categorías de avatar', 'anima-engine' ),
                    'singular_name' => __( 'Categoría de avatar', 'anima-engine' ),
                ],
                'public'            => true,
                'hierarchical'      => true,
                'show_ui'           => true,
                'show_in_rest'      => true,
                'show_admin_column' => true,
                'rewrite'           => [ 'slug' => 'avatar-categoria' ],
            ]
        );

        $default_categories = [
            'humanos'    => __( 'Humanos', 'anima-engine' ),
            'cartoon'    => __( 'Cartoon', 'anima-engine' ),
            'stylized'   => __( 'Stylized', 'anima-engine' ),
            'animales'   => __( 'Animales', 'anima-engine' ),
            'aliens'     => __( 'Aliens', 'anima-engine' ),
        ];

        foreach ( $default_categories as $slug => $name ) {
            if ( ! term_exists( $slug, 'avatar_categoria' ) ) {
                wp_insert_term( $name, 'avatar_categoria', [ 'slug' => $slug ] );
            }
        }
    }
);

add_action(
    'init',
    static function () {
        register_post_meta(
            'avatar',
            'anima_avatar_type',
            [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => static function ( $value ) {
                    $value = sanitize_key( $value );
                    $allowed = [ 'humano', 'cartoon', 'stylized', 'animal', 'alien' ];
                    return in_array( $value, $allowed, true ) ? $value : 'humano';
                },
                'auth_callback'     => '__return_true',
            ]
        );

        register_post_meta(
            'avatar',
            'anima_avatar_thumb',
            [
                'type'         => 'integer',
                'single'       => true,
                'show_in_rest' => true,
                'sanitize_callback' => static function ( $value ) {
                    return absint( $value );
                },
                'auth_callback' => '__return_true',
            ]
        );

        register_post_meta(
            'avatar',
            'anima_avatar_webgl',
            [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => 'esc_url_raw',
                'auth_callback'     => '__return_true',
            ]
        );
    }
);
