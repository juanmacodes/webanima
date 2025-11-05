<?php
/**
 * Funciones del tema Anima Avatar
 */

define( 'ANIMAAVATAR_VERSION', '1.0.0' );

action_hook_setup();

/**
 * Configura soportes del tema.
 */
function action_hook_setup() {
    add_action( 'after_setup_theme', 'animaavatar_setup' );
    add_action( 'after_setup_theme', 'animaavatar_content_width', 0 );
    add_action( 'wp_enqueue_scripts', 'animaavatar_enqueue_assets' );
    add_action( 'wp_head', 'animaavatar_preload_fonts', 1 );
    add_action( 'wp_head', 'animaavatar_print_critical_css', 5 );
    add_filter( 'nav_menu_link_attributes', 'animaavatar_aria_current', 10, 3 );
}

/**
 * Inicializa opciones básicas del tema.
 */
function animaavatar_setup() {
    load_theme_textdomain( 'animaavatar', get_template_directory() . '/languages' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo', [
        'height'      => 120,
        'width'       => 320,
        'flex-height' => true,
        'flex-width'  => true,
    ] );
    add_theme_support( 'html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ] );
    add_theme_support( 'woocommerce' );

    register_nav_menus( [
        'main-menu' => __( 'Menú principal', 'animaavatar' ),
    ] );
}

/**
 * Define el ancho de contenido para embeds.
 */
function animaavatar_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'animaavatar_content_width', 800 );
}

/**
 * Encola estilos y scripts del tema.
 */
function animaavatar_enqueue_assets() {
    $theme = wp_get_theme();

    wp_enqueue_style(
        'animaavatar-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Rajdhani:wght@500;700&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'animaavatar-utilities',
        get_template_directory_uri() . '/assets/css/utilities.css',
        [],
        ANIMAAVATAR_VERSION
    );

    wp_enqueue_style(
        'animaavatar-style',
        get_stylesheet_uri(),
        [ 'animaavatar-utilities' ],
        $theme->get( 'Version' )
    );

    if ( is_front_page() ) {
        wp_enqueue_style(
            'swiper',
            'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css',
            [],
            '9.4.1'
        );

        wp_enqueue_script(
            'swiper',
            'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js',
            [],
            '9.4.1',
            true
        );
        wp_script_add_data( 'swiper', 'defer', true );
    }

    wp_enqueue_script(
        'animaavatar-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        ANIMAAVATAR_VERSION,
        true
    );

    wp_script_add_data( 'animaavatar-main', 'defer', true );
}

/**
 * Preload de fuentes críticas.
 */
function animaavatar_preload_fonts() {
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="font" type="font/woff2" crossorigin href="https://fonts.gstatic.com/s/rajdhani/v17/LDI2apCSOBgSBmjxlS8.woff2" />
    <link rel="preload" as="font" type="font/woff2" crossorigin href="https://fonts.gstatic.com/s/inter/v12/UcC73FwrKc4bCk9G2T-j.woff2" />
    <?php
}

/**
 * CSS crítico mínimo para evitar FOUC.
 */
function animaavatar_print_critical_css() {
    ?>
    <style>
        body {background:#050510;color:#f5f7ff;margin:0;font-family:'Inter','Segoe UI',sans-serif;}
        .site-header {position:sticky;top:0;backdrop-filter:blur(12px);}
        a:focus-visible {outline:3px solid #2fd4ff;outline-offset:4px;}
    </style>
    <?php
}

/**
 * Añade aria-current al enlace activo.
 */
function animaavatar_aria_current( $atts, $item, $args ) {
    if ( isset( $args->theme_location ) && 'main-menu' === $args->theme_location && in_array( 'current-menu-item', $item->classes, true ) ) {
        $atts['aria-current'] = 'page';
    }

    return $atts;
}


if ( ! function_exists( 'animaavatar_default_menu' ) ) {
    /**
     * Menú por defecto cuando no hay menú asignado.
     */
    function animaavatar_default_menu() {
        echo '<ul id="primary-menu" class="primary-menu">';
        wp_list_pages( [
            'title_li' => '',
        ] );
        echo '</ul>';
    }
}

