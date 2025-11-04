<?php
/**
 * Registro de Custom Post Types.
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
        $proyecto_labels = [
            'name'               => __( 'Proyectos', 'anima' ),
            'singular_name'      => __( 'Proyecto', 'anima' ),
            'add_new_item'       => __( 'Añadir nuevo proyecto', 'anima' ),
            'edit_item'          => __( 'Editar proyecto', 'anima' ),
            'new_item'           => __( 'Nuevo proyecto', 'anima' ),
            'view_item'          => __( 'Ver proyecto', 'anima' ),
            'search_items'       => __( 'Buscar proyectos', 'anima' ),
            'not_found'          => __( 'No se han encontrado proyectos', 'anima' ),
            'not_found_in_trash' => __( 'No hay proyectos en la papelera', 'anima' ),
            'all_items'          => __( 'Todos los proyectos', 'anima' ),
            'archives'           => __( 'Archivo de proyectos', 'anima' ),
        ];

        register_post_type(
            'proyecto',
            [
                'labels'              => $proyecto_labels,
                'public'              => true,
                'show_in_rest'        => true,
                'menu_icon'           => 'dashicons-awards',
                'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
                'has_archive'         => 'proyectos',
                'rewrite'             => [ 'slug' => 'proyecto', 'with_front' => false ],
                'show_in_nav_menus'   => true,
                'capability_type'     => 'post',
                'menu_position'       => 21,
                'publicly_queryable'  => true,
                'hierarchical'        => false,
            ]
        );

        $curso_labels = [
            'name'               => __( 'Cursos', 'anima' ),
            'singular_name'      => __( 'Curso', 'anima' ),
            'add_new_item'       => __( 'Añadir nuevo curso', 'anima' ),
            'edit_item'          => __( 'Editar curso', 'anima' ),
            'new_item'           => __( 'Nuevo curso', 'anima' ),
            'view_item'          => __( 'Ver curso', 'anima' ),
            'search_items'       => __( 'Buscar cursos', 'anima' ),
            'not_found'          => __( 'No se han encontrado cursos', 'anima' ),
            'not_found_in_trash' => __( 'No hay cursos en la papelera', 'anima' ),
            'all_items'          => __( 'Todos los cursos', 'anima' ),
            'archives'           => __( 'Archivo de cursos', 'anima' ),
        ];

        register_post_type(
            'curso',
            [
                'labels'              => $curso_labels,
                'public'              => true,
                'show_in_rest'        => true,
                'menu_icon'           => 'dashicons-welcome-learn-more',
                'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
                'has_archive'         => 'cursos',
                'rewrite'             => [ 'slug' => 'curso', 'with_front' => false ],
                'show_in_nav_menus'   => true,
                'capability_type'     => 'post',
                'menu_position'       => 22,
                'publicly_queryable'  => true,
                'hierarchical'        => false,
            ]
        );
    }
);
