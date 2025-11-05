<?php
/**
 * functions.php - Configuraciones y funciones principales del tema animaavatar
 */

if ( ! function_exists( 'anima_setup_theme' ) ) {
    function anima_setup_theme() {
        // Soporte básico de WordPress
        add_theme_support( 'title-tag' );                  // Manejo dinámico del <title>
        add_theme_support( 'post-thumbnails' );            // Imágenes destacadas en posts y CPT
        add_theme_support( 'custom-logo' );                // Soporte para logo personalizado
        add_theme_support( 'menus' );                      // Soporte para menús de navegación
        add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption' ) ); // HTML5 markup
        add_theme_support( 'dark-mode' );                  // *Placeholder:* soporte para modo oscuro (personalizado via CSS)

        // Soporte para WooCommerce (tienda)
        add_theme_support( 'woocommerce' );
        // Soporte para características de galería de productos WooCommerce:
        add_theme_support( 'wc-product-gallery-zoom' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        add_theme_support( 'wc-product-gallery-slider' );

        // Registrar ubicaciones de menú
        register_nav_menus( array(
            'main-menu'   => __( 'Menú principal', 'animaavatar' ),
            //'footer-menu' => __( 'Menú footer', 'animaavatar' ), // opción de menú secundario
        ) );

        // Soporte para Elementor – asegura que las secciones de Elementor puedan ser de ancho completo
        // (Elementor automáticamente añade clases, pero podemos declarar soporte explícito si es requerido).
        //add_theme_support( 'elementor' ); // Elementor normalmente no requiere un add_theme_support explícito
    }
}
add_action( 'after_setup_theme', 'anima_setup_theme' );

// Encolar estilos y scripts del tema
function anima_enqueue_assets() {
    // CSS principal del tema (style.css) - ya se enlaza automáticamente, pero reiteramos en cola por claridad:
    wp_enqueue_style( 'anima-style', get_stylesheet_uri(), array(), '1.0', 'all' );

    // Encolar biblioteca Swiper.js (slider) desde CDN con defer (placeholder: se puede empaquetar localmente si se prefiere)
    wp_enqueue_style( 'anima-swiper-css', 'https://unpkg.com/swiper@8/swiper-bundle.min.css', array(), '8.0' );
    wp_enqueue_script( 'anima-swiper-js', 'https://unpkg.com/swiper@8/swiper-bundle.min.js', array(), '8.0', true );
    // Marcar el script de Swiper para que cargue con defer
    wp_script_add_data( 'anima-swiper-js', 'defer', true );

    // Script JS principal del tema
    wp_enqueue_script( 'anima-main-js', get_template_directory_uri() . '/assets/js/main.js', array(), '1.0', true );
    // Marcar el script principal con defer
    wp_script_add_data( 'anima-main-js', 'defer', true );
}
add_action( 'wp_enqueue_scripts', 'anima_enqueue_assets' );

// Pre-cargar tipografía personalizada en la cabecera (placeholder: ruta a fuente local o Google Fonts)
function anima_preload_fonts() {
    // Ejemplo de preloading de una fuente local "CustomFont.woff2" ubicada en /assets/fonts/
    ?>
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/assets/fonts/CustomFont.woff2" as="font" type="font/woff2" crossorigin>
    <?php
}
add_action( 'wp_head', 'anima_preload_fonts' );

// WooCommerce: integrar wrappers de contenido para ajustar al tema
function anima_woocommerce_wrapper_start() {
    echo '<main id="main" class="site-main container">';  // abre contenedor principal acorde al tema
}
function anima_woocommerce_wrapper_end() {
    echo '</main>';
}
// Eliminar wrappers por defecto de WooCommerce
add_action( 'after_setup_theme', function() {
    remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
    remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
});
add_action( 'woocommerce_before_main_content', 'anima_woocommerce_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'anima_woocommerce_wrapper_end', 10 );

// TODO: Integración futura con BuddyPress o wpDiscuz (ejemplo: soporte de plantillas o hooks de actividad/comentarios)
// (No se implementa aquí porque depende de la instalación de esos plugins, pero se deja preparado)
// TODO: Integración futura con WPGraphQL (por ejemplo, exponiendo datos del tema si fuese necesario en entorno headless)
