<?php
/**
 * Registro de Custom Post Types para la agencia Anima.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function anima_get_cpt_supports() {
    return array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' );
}

function anima_register_post_types() {
    $common_args = array(
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'supports'           => anima_get_cpt_supports(),
        'has_archive'        => true,
        'publicly_queryable' => true,
        'capability_type'    => 'post',
        'rewrite'            => array( 'with_front' => false ),
        'show_in_graphql'    => true,
    );

    register_post_type(
        'curso',
        array_merge(
            $common_args,
            array(
                'labels'      => array(
                    'name'          => __( 'Cursos', 'anima-core' ),
                    'singular_name' => __( 'Curso', 'anima-core' ),
                    'add_new'       => __( 'Añadir nuevo', 'anima-core' ),
                    'add_new_item'  => __( 'Añadir nuevo curso', 'anima-core' ),
                    'edit_item'     => __( 'Editar curso', 'anima-core' ),
                    'new_item'      => __( 'Nuevo curso', 'anima-core' ),
                    'view_item'     => __( 'Ver curso', 'anima-core' ),
                    'search_items'  => __( 'Buscar cursos', 'anima-core' ),
                    'not_found'     => __( 'No se encontraron cursos.', 'anima-core' ),
                ),
                'menu_icon'   => 'dashicons-welcome-learn-more',
                'graphql_single_name' => 'Curso',
                'graphql_plural_name' => 'Cursos',
                'rewrite'     => array( 'slug' => 'cursos' ),
            )
        )
    );

    register_post_type(
        'avatar',
        array_merge(
            $common_args,
            array(
                'labels'      => array(
                    'name'          => __( 'Avatares', 'anima-core' ),
                    'singular_name' => __( 'Avatar', 'anima-core' ),
                    'add_new_item'  => __( 'Añadir nuevo avatar', 'anima-core' ),
                    'edit_item'     => __( 'Editar avatar', 'anima-core' ),
                    'new_item'      => __( 'Nuevo avatar', 'anima-core' ),
                    'view_item'     => __( 'Ver avatar', 'anima-core' ),
                    'search_items'  => __( 'Buscar avatares', 'anima-core' ),
                    'not_found'     => __( 'No se encontraron avatares.', 'anima-core' ),
                ),
                'menu_icon'   => 'dashicons-admin-users',
                'graphql_single_name' => 'Avatar',
                'graphql_plural_name' => 'Avatares',
                'rewrite'     => array( 'slug' => 'avatares' ),
            )
        )
    );

    register_post_type(
        'proyecto',
        array_merge(
            $common_args,
            array(
                'labels'      => array(
                    'name'          => __( 'Proyectos', 'anima-core' ),
                    'singular_name' => __( 'Proyecto', 'anima-core' ),
                    'add_new_item'  => __( 'Añadir nuevo proyecto', 'anima-core' ),
                    'edit_item'     => __( 'Editar proyecto', 'anima-core' ),
                    'new_item'      => __( 'Nuevo proyecto', 'anima-core' ),
                    'view_item'     => __( 'Ver proyecto', 'anima-core' ),
                    'search_items'  => __( 'Buscar proyectos', 'anima-core' ),
                    'not_found'     => __( 'No se encontraron proyectos.', 'anima-core' ),
                ),
                'menu_icon'   => 'dashicons-portfolio',
                'graphql_single_name' => 'Proyecto',
                'graphql_plural_name' => 'Proyectos',
                'rewrite'     => array( 'slug' => 'proyectos' ),
            )
        )
    );

    register_post_type(
        'experiencia',
        array_merge(
            $common_args,
            array(
                'labels'      => array(
                    'name'          => __( 'Experiencias', 'anima-core' ),
                    'singular_name' => __( 'Experiencia', 'anima-core' ),
                    'add_new_item'  => __( 'Añadir nueva experiencia', 'anima-core' ),
                    'edit_item'     => __( 'Editar experiencia', 'anima-core' ),
                    'new_item'      => __( 'Nueva experiencia', 'anima-core' ),
                    'view_item'     => __( 'Ver experiencia', 'anima-core' ),
                    'search_items'  => __( 'Buscar experiencias', 'anima-core' ),
                    'not_found'     => __( 'No se encontraron experiencias.', 'anima-core' ),
                ),
                'menu_icon'   => 'dashicons-format-video',
                'graphql_single_name' => 'Experiencia',
                'graphql_plural_name' => 'Experiencias',
                'rewrite'     => array( 'slug' => 'experiencias' ),
            )
        )
    );
}
