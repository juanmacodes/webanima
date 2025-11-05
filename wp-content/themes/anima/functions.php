<?php
/**
 * Funciones principales del tema Anima.
 */

declare( strict_types=1 );

if ( ! function_exists( 'anima_setup' ) ) {
    /**
     * Configuración inicial del tema.
     */
    function anima_setup(): void {
        // Títulos dinámicos gestionados por WordPress.
        add_theme_support( 'title-tag' );
        // Imágenes destacadas.
        add_theme_support( 'post-thumbnails' );
        // HTML5 en formularios y otros elementos comunes.
        add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'script', 'style' ] );
        // Logo personalizado opcional.
        add_theme_support( 'custom-logo', [
            'height'      => 120,
            'width'       => 120,
            'flex-height' => true,
            'flex-width'  => true,
            'header-text' => [ 'site-title' ],
        ] );
        // Registrar menú principal.
        register_nav_menus( [
            'menu-principal' => __( 'Menú Principal', 'anima' ),
        ] );

        // Declarar compatibilidad con Elementor si el plugin está activo.
        if ( class_exists( '\\Elementor\\Plugin' ) ) {
            add_theme_support( 'elementor' );
        }
    }
}
add_action( 'after_setup_theme', 'anima_setup' );

if ( ! function_exists( 'anima_enqueue_assets' ) ) {
    /**
     * Encolar estilos y scripts del tema.
     */
    function anima_enqueue_assets(): void {
        $theme_version = wp_get_theme()->get( 'Version' );

        wp_enqueue_style( 'anima-style', get_stylesheet_uri(), [], $theme_version );

        $script_path = get_template_directory_uri() . '/assets/js/main.js';
        wp_enqueue_script( 'anima-main', $script_path, [], $theme_version, true );

        wp_localize_script( 'anima-main', 'ANIMA_VARS', [
            'enableAnimations' => true,
        ] );
    }
}
add_action( 'wp_enqueue_scripts', 'anima_enqueue_assets' );

/**
 * Mostrar mensaje amigable si Elementor no está activo cuando se edita con su constructor.
 */
function anima_elementor_notice(): void {
    if ( ! class_exists( '\\Elementor\\Plugin' ) && is_admin() ) {
        add_action( 'admin_notices', static function (): void {
            echo '<div class="notice notice-warning"><p>' . esc_html__( 'El tema Anima está optimizado para Elementor. Instala y activa Elementor para disfrutar de toda la experiencia de edición visual.', 'anima' ) . '</p></div>';
        } );
    }
}
add_action( 'after_setup_theme', 'anima_elementor_notice', 15 );
