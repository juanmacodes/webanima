<?php
/**
 * Registro de taxonomías personalizadas.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function anima_register_taxonomies() {
    $common_args = array(
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'hierarchical'      => false,
        'show_in_graphql'   => true,
    );

    register_taxonomy(
        'nivel',
        array( 'curso', 'experiencia' ),
        array_merge(
            $common_args,
            array(
                'labels' => array(
                    'name'          => __( 'Niveles', 'anima-core' ),
                    'singular_name' => __( 'Nivel', 'anima-core' ),
                    'search_items'  => __( 'Buscar niveles', 'anima-core' ),
                    'all_items'     => __( 'Todos los niveles', 'anima-core' ),
                    'edit_item'     => __( 'Editar nivel', 'anima-core' ),
                    'update_item'   => __( 'Actualizar nivel', 'anima-core' ),
                    'add_new_item'  => __( 'Añadir nuevo nivel', 'anima-core' ),
                    'new_item_name' => __( 'Nombre del nuevo nivel', 'anima-core' ),
                    'menu_name'     => __( 'Niveles', 'anima-core' ),
                ),
                'graphql_single_name' => 'Nivel',
                'graphql_plural_name' => 'Niveles',
                'rewrite'             => array( 'slug' => 'nivel' ),
            )
        )
    );

    register_taxonomy(
        'modalidad',
        array( 'curso', 'experiencia' ),
        array_merge(
            $common_args,
            array(
                'labels' => array(
                    'name'          => __( 'Modalidades', 'anima-core' ),
                    'singular_name' => __( 'Modalidad', 'anima-core' ),
                    'search_items'  => __( 'Buscar modalidades', 'anima-core' ),
                    'all_items'     => __( 'Todas las modalidades', 'anima-core' ),
                    'edit_item'     => __( 'Editar modalidad', 'anima-core' ),
                    'update_item'   => __( 'Actualizar modalidad', 'anima-core' ),
                    'add_new_item'  => __( 'Añadir nueva modalidad', 'anima-core' ),
                    'new_item_name' => __( 'Nombre de la nueva modalidad', 'anima-core' ),
                    'menu_name'     => __( 'Modalidades', 'anima-core' ),
                ),
                'graphql_single_name' => 'Modalidad',
                'graphql_plural_name' => 'Modalidades',
                'rewrite'             => array( 'slug' => 'modalidad' ),
            )
        )
    );

    register_taxonomy(
        'tecnologia',
        array( 'curso', 'avatar', 'proyecto', 'experiencia' ),
        array_merge(
            $common_args,
            array(
                'labels' => array(
                    'name'          => __( 'Tecnologías', 'anima-core' ),
                    'singular_name' => __( 'Tecnología', 'anima-core' ),
                    'search_items'  => __( 'Buscar tecnologías', 'anima-core' ),
                    'all_items'     => __( 'Todas las tecnologías', 'anima-core' ),
                    'edit_item'     => __( 'Editar tecnología', 'anima-core' ),
                    'update_item'   => __( 'Actualizar tecnología', 'anima-core' ),
                    'add_new_item'  => __( 'Añadir nueva tecnología', 'anima-core' ),
                    'new_item_name' => __( 'Nombre de la nueva tecnología', 'anima-core' ),
                    'menu_name'     => __( 'Tecnologías', 'anima-core' ),
                ),
                'graphql_single_name' => 'Tecnologia',
                'graphql_plural_name' => 'Tecnologias',
                'rewrite'             => array( 'slug' => 'tecnologia' ),
            )
        )
    );
}
