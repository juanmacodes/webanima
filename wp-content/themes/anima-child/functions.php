<?php
/**
 * Tema hijo Anima Child
 */

define( 'ANIMA_CHILD_VERSION', '0.1.0' );

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style( 'anima-child', get_stylesheet_uri(), [ 'twentytwentyfour-style' ], ANIMA_CHILD_VERSION );
    wp_enqueue_style( 'anima-child-theme', get_stylesheet_directory_uri() . '/assets/css/theme.css', [ 'anima-child' ], ANIMA_CHILD_VERSION );
    wp_enqueue_script( 'anima-child-effects', get_stylesheet_directory_uri() . '/assets/js/effects.js', [ 'jquery' ], ANIMA_CHILD_VERSION, true );
}, 20 );

add_action( 'after_setup_theme', function () {
    register_nav_menus(
        [
            'primary' => __( 'Primary Menu', 'anima-child' ),
        ]
    );
} );

require_once get_stylesheet_directory() . '/inc/template-tags.php';
