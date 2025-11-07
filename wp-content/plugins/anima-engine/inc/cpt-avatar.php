<?php
/**
 * Registro del Custom Post Type "Avatar" y sus taxonomías.
 *
 * @package Anima\Engine
 */

defined( 'ABSPATH' ) || exit;

add_action(
    'init',
    static function () {
        register_post_type(
            'avatar',
            [
                'labels' => [
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
                ],
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
                'has_archive'        => true,
            ]
        );

        register_taxonomy(
            'avatar_tipo',
            [ 'avatar' ],
            [
                'labels'       => [
                    'name'          => __( 'Tipos', 'anima-engine' ),
                    'singular_name' => __( 'Tipo', 'anima-engine' ),
                ],
                'public'       => true,
                'hierarchical' => false,
                'show_ui'      => true,
                'show_in_rest' => true,
            ]
        );
    }
);

add_action(
    'init',
    static function () {
        register_post_meta(
            'avatar',
            'anima_avatar_rig',
            [
                'type'              => 'boolean',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => static function ( $value ) {
                    return (bool) $value;
                },
                'auth_callback'     => '__return_true',
            ]
        );

        register_post_meta(
            'avatar',
            'anima_avatar_engine',
            [
                'type'          => 'string',
                'single'        => true,
                'show_in_rest'  => true,
                'auth_callback' => '__return_true',
            ]
        );

        register_post_meta(
            'avatar',
            'anima_avatar_tags',
            [
                'type'              => 'array',
                'single'            => true,
                'show_in_rest'      => [
                    'schema' => [
                        'type'  => 'array',
                        'items' => [ 'type' => 'string' ],
                    ],
                ],
                'sanitize_callback' => static function ( $value ) {
                    if ( is_array( $value ) ) {
                        return array_values( array_filter( array_map( 'sanitize_text_field', $value ) ) );
                    }

                    $value = array_map( 'trim', explode( ',', (string) $value ) );

                    return array_values( array_filter( array_map( 'sanitize_text_field', $value ) ) );
                },
                'auth_callback'     => '__return_true',
            ]
        );
    }
);
