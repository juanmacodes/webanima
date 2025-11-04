<?php
/**
 * Configuración básica del tema.
 *
 * @package Anima
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action(
    'after_setup_theme',
    static function (): void {
        load_theme_textdomain( 'anima', ANIMA_THEME_PATH . '/languages' );

        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'responsive-embeds' );
        add_theme_support( 'editor-styles' );
        add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style' ] );

        register_nav_menus(
            [
                'header_primary' => __( 'Menú principal', 'anima' ),
                'footer_primary' => __( 'Menú pie', 'anima' ),
            ]
        );
    }
);

add_action(
    'wp_head',
    static function (): void {
        echo '<a class="skip-to-content" href="#main">' . esc_html__( 'Saltar al contenido', 'anima' ) . '</a>';
    }
);

add_filter(
    'body_class',
    static function ( array $classes ): array {
        $classes[] = 'anima-dark';
        return $classes;
    }
);

add_filter(
    'allowed_block_types_all',
    static function ( $allowed_block_types, $editor_context ) {
        $core_blocks = [
            'core/paragraph',
            'core/heading',
            'core/list',
            'core/list-item',
            'core/image',
            'core/gallery',
            'core/quote',
            'core/audio',
            'core/video',
            'core/file',
            'core/cover',
            'core/group',
            'core/columns',
            'core/column',
            'core/buttons',
            'core/button',
            'core/separator',
            'core/spacer',
            'core/table',
            'core/embed',
            'core/media-text',
            'core/code',
            'core/html',
            'core/pullquote',
            'core/shortcode',
            'core/verse',
            'core/preformatted',
            'core/latest-posts',
            'core/latest-comments',
            'core/post-title',
            'core/post-content',
            'core/post-featured-image',
            'core/query',
            'core/query-loop',
            'core/post-template',
            'core/post-terms',
            'core/post-date',
            'core/read-more',
            'core/calendar',
            'core/navigation',
            'core/navigation-link',
            'core/social-links',
            'core/social-link',
            'core/site-title',
            'core/site-logo',
            'core/site-tagline',
            'core/search',
            'core/pattern',
        ];

        return $core_blocks;
    },
    10,
    2
);

add_filter(
    'wp_lazy_loading_enabled',
    static function ( bool $default, string $tag ) : bool {
        if ( in_array( $tag, [ 'img', 'iframe' ], true ) ) {
            return true;
        }

        return $default;
    },
    10,
    2
);

add_action(
    'init',
    static function (): void {
        add_image_size( 'anima-card', 640, 400, true );
        add_image_size( 'anima-portrait', 640, 800, true );
    }
);
add_action(
    'init',
    static function (): void {
        register_block_pattern_category( 'anima', [ 'label' => __( 'Anima', 'anima' ) ] );
    }
);

