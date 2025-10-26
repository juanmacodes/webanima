<?php
add_action( 'init', function () {
    $labels = [
        'name'               => __( 'Proyectos', 'anima-core' ),
        'singular_name'      => __( 'Proyecto', 'anima-core' ),
        'add_new_item'       => __( 'Añadir nuevo proyecto', 'anima-core' ),
        'edit_item'          => __( 'Editar proyecto', 'anima-core' ),
        'new_item'           => __( 'Nuevo proyecto', 'anima-core' ),
        'view_item'          => __( 'Ver proyecto', 'anima-core' ),
        'search_items'       => __( 'Buscar proyectos', 'anima-core' ),
        'not_found'          => __( 'No se encontraron proyectos', 'anima-core' ),
        'not_found_in_trash' => __( 'No hay proyectos en la papelera', 'anima-core' ),
        'all_items'          => __( 'Todos los proyectos', 'anima-core' ),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_icon'          => 'dashicons-portfolio',
        'supports'           => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ],
        'show_in_rest'       => true,
        'rewrite'            => [ 'slug' => 'proyectos' ],
    ];

    register_post_type( 'project', $args );

    register_taxonomy(
        'stack',
        [ 'project' ],
        [
            'label'        => __( 'Stack tecnológico', 'anima-core' ),
            'rewrite'      => [ 'slug' => 'stack' ],
            'show_in_rest' => true,
            'hierarchical' => false,
        ]
    );
} );
