<?php
/**
 * Comandos WP-CLI para inicializar contenido.
 *
 * @package Anima
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'anima setup', 'anima_cli_setup_site' );
}

function anima_cli_setup_site(): void {
    $pages = [
        'home'        => [ 'title' => __( 'Inicio', 'anima' ), 'content' => '<!-- wp:pattern {"slug":"anima/hero-cta"} /-->' ],
        'servicios'   => [ 'title' => __( 'Servicios', 'anima' ), 'content' => '<!-- wp:pattern {"slug":"anima/services-4"} /-->' ],
        'proyectos'   => [ 'title' => __( 'Proyectos', 'anima' ), 'type' => 'page' ],
        'anima-world' => [ 'title' => __( 'Anima World', 'anima' ), 'content' => '[anima_world_loft]' ],
        'cursos'      => [ 'title' => __( 'Cursos', 'anima' ) ],
        'sobre'       => [ 'title' => __( 'Sobre', 'anima' ) ],
        'historias'   => [ 'title' => __( 'Historias', 'anima' ) ],
        'contacto'    => [ 'title' => __( 'Contacto', 'anima' ), 'content' => '<!-- wp:shortcode -->[contact-form-7 id="anima-contacto"]<!-- /wp:shortcode -->' ],
        'anima-live'  => [ 'title' => __( 'Anima Live', 'anima' ), 'content' => '<!-- wp:pattern {"slug":"anima/pricing-3"} /-->' ],
        'cookies'     => [ 'title' => __( 'Política de Cookies', 'anima' ) ],
        'privacidad'  => [ 'title' => __( 'Política de Privacidad', 'anima' ) ],
    ];

    foreach ( $pages as $slug => $page ) {
        $existing = get_page_by_path( $slug );
        if ( $existing ) {
            continue;
        }
        $postarr = [
            'post_title'   => $page['title'],
            'post_name'    => $slug,
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_content' => $page['content'] ?? '',
        ];
        wp_insert_post( $postarr );
    }

    $front = get_page_by_path( 'home' );
    $blog  = get_page_by_path( 'historias' );

    if ( $front && $blog ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $front->ID );
        update_option( 'page_for_posts', $blog->ID );
    }

    $menu_primary = wp_get_nav_menu_object( 'Principal' );
    if ( ! $menu_primary ) {
        $menu_primary = wp_create_nav_menu( 'Principal' );
    }

    $menu_footer = wp_get_nav_menu_object( 'Footer' );
    if ( ! $menu_footer ) {
        $menu_footer = wp_create_nav_menu( 'Footer' );
    }

    $primary_items = [ 'home', 'servicios', 'proyectos', 'anima-world', 'cursos', 'historias', 'anima-world', 'sobre', 'contacto' ];
    $primary_items = array_unique( $primary_items );

    foreach ( $primary_items as $slug ) {
        $page = get_page_by_path( $slug );
        if ( ! $page ) {
            continue;
        }
        wp_update_nav_menu_item(
            $menu_primary,
            0,
            [
                'menu-item-title'      => $page->post_title,
                'menu-item-object'     => 'page',
                'menu-item-object-id'  => $page->ID,
                'menu-item-type'       => 'post_type',
                'menu-item-status'     => 'publish',
            ]
        );
    }

    $footer_items = [ 'sobre', 'proyectos', 'servicios', 'cursos', 'contacto', 'privacidad', 'cookies' ];
    foreach ( $footer_items as $slug ) {
        $page = get_page_by_path( $slug );
        if ( ! $page ) {
            continue;
        }
        wp_update_nav_menu_item(
            $menu_footer,
            0,
            [
                'menu-item-title'      => $page->post_title,
                'menu-item-object'     => 'page',
                'menu-item-object-id'  => $page->ID,
                'menu-item-type'       => 'post_type',
                'menu-item-status'     => 'publish',
            ]
        );
    }

    set_theme_mod(
        'nav_menu_locations',
        [
            'header_primary' => $menu_primary,
            'footer_primary' => $menu_footer,
        ]
    );

    if ( class_exists( '\WP_CLI' ) ) {
        \WP_CLI::success( 'Sitio Anima inicializado.' );
    }
}
