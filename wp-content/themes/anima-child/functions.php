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
