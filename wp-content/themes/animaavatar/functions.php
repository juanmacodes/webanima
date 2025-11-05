<?php
/**
 * Funciones principales del tema animaavatar.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'animaavatar_theme_setup' ) ) {
    function animaavatar_theme_setup() {
        add_theme_support( 'title-tag' );
        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'post-thumbnails' );
        set_post_thumbnail_size( 1280, 720, true );
        add_theme_support( 'custom-logo', array(
            'height'      => 120,
            'width'       => 120,
            'flex-height' => true,
            'flex-width'  => true,
        ) );
        add_theme_support( 'html5', array( 'search-form', 'comment-list', 'comment-form', 'gallery', 'caption', 'style', 'script' ) );
        add_theme_support( 'align-wide' );
        add_theme_support( 'responsive-embeds' );
        add_theme_support( 'customize-selective-refresh-widgets' );
        add_theme_support( 'editor-styles' );

        // Compatibilidad con constructores y plugins clave.
        add_theme_support( 'elementor-pro/theme-builder' );
        add_theme_support( 'woocommerce' );
        add_theme_support( 'wc-product-gallery-zoom' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        add_theme_support( 'wc-product-gallery-slider' );
        add_theme_support( 'buddypress-use-nouveau-template-pack' );

        register_nav_menus( array(
            'main-menu'   => __( 'Menú principal', 'animaavatar' ),
            'footer-menu' => __( 'Menú del pie de página', 'animaavatar' ),
        ) );

        add_editor_style( 'style.css' );
    }
}
add_action( 'after_setup_theme', 'animaavatar_theme_setup' );

if ( ! function_exists( 'animaavatar_asset_version' ) ) {
    function animaavatar_asset_version( $relative_path ) {
        $path = get_template_directory() . '/' . ltrim( $relative_path, '/' );

        if ( file_exists( $path ) ) {
            return filemtime( $path );
        }

        return wp_get_theme()->get( 'Version' );
    }
}

if ( ! function_exists( 'animaavatar_enqueue_assets' ) ) {
    function animaavatar_enqueue_assets() {
        $theme_version = wp_get_theme()->get( 'Version' );

        wp_enqueue_style( 'animaavatar-swiper', 'https://cdn.jsdelivr.net/npm/swiper@9.4.1/swiper-bundle.min.css', array(), '9.4.1' );
        wp_enqueue_style( 'animaavatar-style', get_stylesheet_uri(), array( 'animaavatar-swiper' ), $theme_version );

        wp_enqueue_script( 'animaavatar-swiper', 'https://cdn.jsdelivr.net/npm/swiper@9.4.1/swiper-bundle.min.js', array(), '9.4.1', true );
        wp_script_add_data( 'animaavatar-swiper', 'defer', true );

        wp_enqueue_script( 'animaavatar-main', get_template_directory_uri() . '/assets/js/main.js', array(), animaavatar_asset_version( 'assets/js/main.js' ), true );
        wp_script_add_data( 'animaavatar-main', 'defer', true );
    }
}
add_action( 'wp_enqueue_scripts', 'animaavatar_enqueue_assets' );

if ( ! function_exists( 'animaavatar_preload_fonts' ) ) {
    function animaavatar_preload_fonts() {
        ?>
        <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
        <link rel="preload" href="https://cdn.jsdelivr.net/npm/@fontsource/space-grotesk/files/space-grotesk-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="https://cdn.jsdelivr.net/npm/@fontsource/space-grotesk/files/space-grotesk-latin-700-normal.woff2" as="font" type="font/woff2" crossorigin>
        <link rel="preload" href="https://cdn.jsdelivr.net/npm/@fontsource/inter/files/inter-latin-500-normal.woff2" as="font" type="font/woff2" crossorigin>
        <?php
    }
}
add_action( 'wp_head', 'animaavatar_preload_fonts', 1 );

if ( ! function_exists( 'animaavatar_register_elementor_locations' ) ) {
    function animaavatar_register_elementor_locations( $elementor_theme_manager ) {
        if ( method_exists( $elementor_theme_manager, 'register_all_core_location' ) ) {
            $elementor_theme_manager->register_all_core_location();
        }
    }
}
add_action( 'elementor/theme/register_locations', 'animaavatar_register_elementor_locations' );

function animaavatar_woocommerce_wrapper_start() {
    echo '<main id="main-content" class="site-main container">';
}

function animaavatar_woocommerce_wrapper_end() {
    echo '</main>';
}

add_action( 'after_setup_theme', function() {
    remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
    remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
} );
add_action( 'woocommerce_before_main_content', 'animaavatar_woocommerce_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'animaavatar_woocommerce_wrapper_end', 10 );

// Preparación para futuras integraciones con BuddyPress.
add_action( 'bp_after_setup_theme', function() {
    add_theme_support( 'buddypress-use-nouveau-template-pack' );
} );

if ( ! function_exists( 'animaavatar_fallback_menu' ) ) {
    function animaavatar_fallback_menu() {
        echo '<ul class="menu menu--fallback reset-list">';
        wp_list_pages( array(
            'title_li' => '',
            'depth'    => 1,
        ) );
        echo '</ul>';
    }
}
