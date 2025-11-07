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
    add_filter( 'nav_menu_css_class', 'animaavatar_add_mega_menu_class', 10, 4 );
    add_filter( 'walker_nav_menu_start_el', 'animaavatar_render_mega_menu', 10, 4 );
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

    add_image_size( 'an_card_16x10', 1200, 750, true );
    add_image_size( 'an_square', 1000, 1000, true );

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
        .site-header {position:sticky;top:0;background-color:rgba(5,5,16,0.78);backdrop-filter:blur(18px);}
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

/**
 * Añade la clase del mega menú al elemento correspondiente.
 */
function animaavatar_add_mega_menu_class( $classes, $item, $args, $depth ) {
    if ( isset( $args->theme_location ) && 'main-menu' === $args->theme_location && 0 === $depth ) {
        if ( 'servicios' === sanitize_title( $item->title ) ) {
            $classes[] = 'menu-item--mega';
        }
    }

    return $classes;
}

/**
 * Sobrescribe la salida del elemento "Servicios" para incluir el mega menú.
 */
function animaavatar_render_mega_menu( $item_output, $item, $depth, $args ) {
    if ( ! isset( $args->theme_location ) || 'main-menu' !== $args->theme_location || 0 !== $depth ) {
        return $item_output;
    }

    if ( 'servicios' !== sanitize_title( $item->title ) ) {
        return $item_output;
    }

    $panel_id = 'mega-servicios';
    $columns  = [
        [
            'heading' => __( 'Streaming', 'animaavatar' ),
            'links'   => [
                [
                    'label' => __( 'Ver todos los servicios', 'animaavatar' ),
                    'url'   => home_url( '/servicios' ),
                ],
                [
                    'label' => __( 'Producción multicámara 360º', 'animaavatar' ),
                    'url'   => home_url( '/servicios/streaming-360' ),
                ],
                [
                    'label' => __( 'Eventos híbridos interactivos', 'animaavatar' ),
                    'url'   => home_url( '/servicios/eventos-hibridos' ),
                ],
                [
                    'label' => __( 'Monetización y analítica en vivo', 'animaavatar' ),
                    'url'   => home_url( '/servicios/streaming-analytics' ),
                ],
            ],
        ],
        [
            'heading' => __( 'Holográficos', 'animaavatar' ),
            'links'   => [
                [
                    'label' => __( 'Escenarios volumétricos', 'animaavatar' ),
                    'url'   => home_url( '/servicios/holograficos-volumetricos' ),
                ],
                [
                    'label' => __( 'Telepresencia XR', 'animaavatar' ),
                    'url'   => home_url( '/servicios/telepresencia-xr' ),
                ],
                [
                    'label' => __( 'Instalaciones permanentes', 'animaavatar' ),
                    'url'   => home_url( '/servicios/instalaciones-holograficas' ),
                ],
            ],
        ],
        [
            'heading' => __( 'IA', 'animaavatar' ),
            'links'   => [
                [
                    'label' => __( 'Gemelos digitales con IA', 'animaavatar' ),
                    'url'   => home_url( '/servicios/gemelos-digitales' ),
                ],
                [
                    'label' => __( 'Asistentes virtuales conversacionales', 'animaavatar' ),
                    'url'   => home_url( '/servicios/asistentes-virtuales' ),
                ],
                [
                    'label' => __( 'Optimización de contenido generativo', 'animaavatar' ),
                    'url'   => home_url( '/servicios/optimizacion-ia' ),
                ],
            ],
        ],
        [
            'heading' => __( 'VR', 'animaavatar' ),
            'links'   => [
                [
                    'label' => __( 'Formación inmersiva', 'animaavatar' ),
                    'url'   => home_url( '/servicios/formacion-vr' ),
                ],
                [
                    'label' => __( 'Metaversos de marca', 'animaavatar' ),
                    'url'   => home_url( '/servicios/metaversos' ),
                ],
                [
                    'label' => __( 'Experiencias retail virtuales', 'animaavatar' ),
                    'url'   => home_url( '/servicios/retail-vr' ),
                ],
            ],
        ],
    ];

    ob_start();
    ?>
    <button type="button" class="menu-link menu-link--mega" aria-expanded="false" aria-haspopup="true" aria-controls="<?php echo esc_attr( $panel_id ); ?>">
        <?php echo esc_html( $item->title ); ?>
    </button>
    <div id="<?php echo esc_attr( $panel_id ); ?>" class="mega-menu" role="region" aria-label="<?php esc_attr_e( 'Servicios destacados de Anima', 'animaavatar' ); ?>" aria-hidden="true">
        <div class="mega-menu__grid">
            <?php foreach ( $columns as $column ) : ?>
                <div class="mega-menu__column">
                    <h3><?php echo esc_html( $column['heading'] ); ?></h3>
                    <ul>
                        <?php foreach ( $column['links'] as $link ) : ?>
                            <li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
        <a class="mega-menu__cta" href="<?php echo esc_url( home_url( '/contacto' ) ); ?>">
            <?php esc_html_e( 'Agenda una demo inmersiva', 'animaavatar' ); ?>
        </a>
    </div>
    <?php
    return ob_get_clean();
}

add_filter( 'upload_mimes', 'animaavatar_enable_modern_image_formats' );
/**
 * Permite subir imágenes WebP y AVIF.
 */
function animaavatar_enable_modern_image_formats( array $mimes ): array {
    $mimes['webp'] = 'image/webp';
    $mimes['avif'] = 'image/avif';

    return $mimes;
}

add_filter( 'wp_get_attachment_image_attributes', 'animaavatar_default_lazy_loading', 10, 3 );
/**
 * Asegura que las imágenes utilicen lazy loading por defecto.
 */
function animaavatar_default_lazy_loading( array $attr, $attachment, string $size ): array { // phpcs:ignore WordPressVIPMinimum.Hooks.RestrictedHooks.wp_get_attachment_image_attributes
    if ( empty( $attr['loading'] ) ) {
        $attr['loading'] = 'lazy';
    }

    return $attr;
}

add_filter( 'wp_image_default_to_lazy', '__return_true' );


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

add_action( 'comment_form_after_fields', 'animaavatar_render_privacy_consent_field' );
add_action( 'comment_form_logged_in_after', 'animaavatar_render_privacy_consent_field' );

/**
 * Devuelve la etiqueta con el enlace a la política de privacidad.
 */
function animaavatar_get_privacy_consent_label(): string {
    $privacy_url = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '';

    if ( $privacy_url ) {
        $link  = sprintf(
            '<a href="%1$s" target="_blank" rel="noreferrer noopener">%2$s</a>',
            esc_url( $privacy_url ),
            esc_html__( 'política de privacidad', 'animaavatar' )
        );
        $label = sprintf( __( 'He leído y acepto la %s.', 'animaavatar' ), $link );
    } else {
        $label = __( 'Consiento el tratamiento de mis datos personales.', 'animaavatar' );
    }

    /**
     * Permite personalizar la etiqueta del consentimiento.
     */
    return apply_filters( 'animaavatar_privacy_consent_label', $label, $privacy_url );
}

/**
 * Genera el marcado del checkbox de consentimiento.
 */
function animaavatar_get_privacy_consent_field_markup(): string {
    $label  = animaavatar_get_privacy_consent_label();
    $markup = sprintf(
        '<label class="anima-form-consent"><input type="checkbox" name="anima_privacy_consent" value="1" required /> %s</label>',
        wp_kses_post( $label )
    );

    /**
     * Permite modificar el HTML del checkbox antes de imprimirse.
     */
    return apply_filters( 'animaavatar_privacy_consent_field_markup', $markup, $label );
}

/**
 * Imprime el checkbox de consentimiento en formularios compatibles.
 */
function animaavatar_render_privacy_consent_field(): void {
    echo '<p class="comment-form-privacy">' . animaavatar_get_privacy_consent_field_markup() . '</p>';
}

