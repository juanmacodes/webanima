<?php
/**
 * Tema hijo Anima Child
 */

define( 'ANIMA_CHILD_VERSION', '0.1.0' );

function anima_child_brand_asset_url() {
    return apply_filters( 'anima_child_brand_asset_url', get_stylesheet_directory_uri() . '/assets/img/anima-brand.svg' );
}

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style( 'anima-child', get_stylesheet_uri(), [ 'anima-style' ], ANIMA_CHILD_VERSION );
    wp_enqueue_style( 'anima-child-theme', get_stylesheet_directory_uri() . '/assets/css/theme.css', [ 'anima-child' ], ANIMA_CHILD_VERSION );
    wp_enqueue_script( 'anima-child-effects', get_stylesheet_directory_uri() . '/assets/js/effects.js', [ 'jquery' ], ANIMA_CHILD_VERSION, true );
}, 20 );

add_action( 'after_setup_theme', function () {
    add_theme_support( 'post-thumbnails' );

    register_nav_menus(
        [
            'primary' => __( 'Primary Menu', 'anima-child' ),
        ]
    );
} );

add_filter( 'wp_nav_menu_items', function ( $items, $args ) {
    if ( isset( $args->theme_location ) && 'primary' === $args->theme_location ) {
        $contact_page = get_page_by_path( 'contacto' );
        $fallback_id  = (int) get_option( 'page_for_posts' );
        $contact_url  = $contact_page ? get_permalink( $contact_page ) : ( $fallback_id ? get_permalink( $fallback_id ) : home_url( '#contacto' ) );

        $cta = sprintf(
            '<li class="menu-item menu-item--cta"><a href="%1$s">%2$s</a></li>',
            esc_url( $contact_url ),
            esc_html__( 'Solicitar demo', 'anima-child' )
        );
        $items .= $cta;
    }

    return $items;
}, 10, 2 );

require_once get_stylesheet_directory() . '/inc/template-tags.php';

add_action( 'wp_head', function () {
    $brand_asset = anima_child_brand_asset_url();

    if ( $brand_asset ) {
        if ( ! has_site_icon() ) {
            printf( '<link rel="icon" href="%1$s" sizes="any" type="image/svg+xml" />' . "\n", esc_url( $brand_asset ) );
            printf( '<link rel="apple-touch-icon" href="%1$s" />' . "\n", esc_url( $brand_asset ) );
        }

        if ( ! has_action( 'wpseo_head' ) ) {
            printf( '<meta property="og:image" content="%1$s" />' . "\n", esc_url( $brand_asset ) );
            printf( '<meta name="twitter:image" content="%1$s" />' . "\n", esc_url( $brand_asset ) );
        }
    }
}, 20 );

add_action( 'init', function () {
    $avatar_labels = [
        'name'                  => __( 'Avatares', 'anima-child' ),
        'singular_name'         => __( 'Avatar', 'anima-child' ),
        'menu_name'             => __( 'Avatares', 'anima-child' ),
        'name_admin_bar'        => __( 'Avatar', 'anima-child' ),
        'add_new'               => __( 'Añadir nuevo', 'anima-child' ),
        'add_new_item'          => __( 'Añadir nuevo avatar', 'anima-child' ),
        'edit_item'             => __( 'Editar avatar', 'anima-child' ),
        'new_item'              => __( 'Nuevo avatar', 'anima-child' ),
        'view_item'             => __( 'Ver avatar', 'anima-child' ),
        'search_items'          => __( 'Buscar avatares', 'anima-child' ),
        'not_found'             => __( 'No se encontraron avatares.', 'anima-child' ),
        'not_found_in_trash'    => __( 'No hay avatares en la papelera.', 'anima-child' ),
        'all_items'             => __( 'Todos los avatares', 'anima-child' ),
        'archives'              => __( 'Archivo de avatares', 'anima-child' ),
        'attributes'            => __( 'Atributos de avatar', 'anima-child' ),
        'featured_image'        => __( 'Imagen destacada', 'anima-child' ),
        'set_featured_image'    => __( 'Establecer imagen destacada', 'anima-child' ),
        'remove_featured_image' => __( 'Eliminar imagen destacada', 'anima-child' ),
        'use_featured_image'    => __( 'Usar como imagen destacada', 'anima-child' ),
    ];

    register_post_type(
        'avatar',
        [
            'label'               => __( 'Avatar', 'anima-child' ),
            'labels'              => $avatar_labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_icon'           => 'dashicons-id',
            'show_in_rest'        => true,
            'has_archive'         => true,
            'rewrite'             => [
                'slug'       => 'avatares',
                'with_front' => false,
            ],
            'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
            'taxonomies'          => [ 'avatar_tech' ],
        ]
    );

    $tech_labels = [
        'name'              => __( 'Tecnologías', 'anima-child' ),
        'singular_name'     => __( 'Tecnología', 'anima-child' ),
        'search_items'      => __( 'Buscar tecnologías', 'anima-child' ),
        'all_items'         => __( 'Todas las tecnologías', 'anima-child' ),
        'parent_item'       => __( 'Tecnología superior', 'anima-child' ),
        'parent_item_colon' => __( 'Tecnología superior:', 'anima-child' ),
        'edit_item'         => __( 'Editar tecnología', 'anima-child' ),
        'update_item'       => __( 'Actualizar tecnología', 'anima-child' ),
        'add_new_item'      => __( 'Añadir nueva tecnología', 'anima-child' ),
        'new_item_name'     => __( 'Nombre de la nueva tecnología', 'anima-child' ),
        'menu_name'         => __( 'Tecnologías', 'anima-child' ),
    ];

    register_taxonomy(
        'avatar_tech',
        [ 'avatar' ],
        [
            'labels'            => $tech_labels,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_rest'      => true,
            'rewrite'           => [
                'slug'       => 'tecnologia-avatar',
                'with_front' => false,
            ],
        ]
    );

    if ( ! post_type_exists( 'curso' ) ) {
        $curso_labels = [
            'name'               => __( 'Cursos', 'anima-child' ),
            'singular_name'      => __( 'Curso', 'anima-child' ),
            'menu_name'          => __( 'Cursos', 'anima-child' ),
            'name_admin_bar'     => __( 'Curso', 'anima-child' ),
            'add_new'            => __( 'Añadir nuevo', 'anima-child' ),
            'add_new_item'       => __( 'Añadir nuevo curso', 'anima-child' ),
            'edit_item'          => __( 'Editar curso', 'anima-child' ),
            'new_item'           => __( 'Nuevo curso', 'anima-child' ),
            'view_item'          => __( 'Ver curso', 'anima-child' ),
            'search_items'       => __( 'Buscar cursos', 'anima-child' ),
            'not_found'          => __( 'No se encontraron cursos.', 'anima-child' ),
            'not_found_in_trash' => __( 'No hay cursos en la papelera.', 'anima-child' ),
            'all_items'          => __( 'Todos los cursos', 'anima-child' ),
            'archives'           => __( 'Archivo de cursos', 'anima-child' ),
        ];

        register_post_type(
            'curso',
            [
                'label'              => __( 'Curso', 'anima-child' ),
                'labels'             => $curso_labels,
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'menu_icon'          => 'dashicons-welcome-learn-more',
                'show_in_rest'       => true,
                'has_archive'        => true,
                'rewrite'            => [
                    'slug'       => 'cursos',
                    'with_front' => false,
                ],
                'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
            ]
        );
    }
} );

add_filter( 'anima_child_curso_form_shortcode', function ( $shortcode, $post_id ) {
    return $shortcode;
}, 10, 2 );
