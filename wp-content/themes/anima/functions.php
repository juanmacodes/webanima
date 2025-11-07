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

add_action( 'wp_head', 'anima_print_open_graph_tags', 5 );

if ( ! function_exists( 'anima_print_open_graph_tags' ) ) {
    /**
     * Imprime las etiquetas OG/Twitter dinámicamente.
     */
    function anima_print_open_graph_tags(): void {
        $meta = anima_collect_social_meta();

        if ( empty( $meta ) ) {
            return;
        }

        $properties = [
            'og:locale'      => determine_locale(),
            'og:type'        => $meta['type'],
            'og:title'       => $meta['title'],
            'og:description' => $meta['description'],
            'og:url'         => $meta['url'],
            'og:site_name'   => get_bloginfo( 'name' ),
            'og:image'       => $meta['image'],
        ];

        foreach ( $properties as $property => $content ) {
            if ( empty( $content ) ) {
                continue;
            }

            $escaped = in_array( $property, [ 'og:url', 'og:image' ], true ) ? esc_url( $content ) : esc_attr( $content );
            printf( '<meta property="%1$s" content="%2$s" />' . "\n", esc_attr( $property ), $escaped );
        }

        $twitter = [
            'twitter:card'        => 'summary_large_image',
            'twitter:title'       => $meta['title'],
            'twitter:description' => $meta['description'],
            'twitter:image'       => $meta['image'],
        ];

        foreach ( $twitter as $name => $content ) {
            if ( empty( $content ) ) {
                continue;
            }

            $escaped = 'twitter:image' === $name ? esc_url( $content ) : esc_attr( $content );
            printf( '<meta name="%1$s" content="%2$s" />' . "\n", esc_attr( $name ), $escaped );
        }
    }
}

if ( ! function_exists( 'anima_collect_social_meta' ) ) {
    /**
     * Prepara los valores de las etiquetas sociales.
     *
     * @return array<string, string>
     */
    function anima_collect_social_meta(): array {
        $title       = wp_get_document_title();
        $description = get_bloginfo( 'description' );
        $image       = '';
        $url         = home_url( add_query_arg( [], isset( $GLOBALS['wp'] ) && is_object( $GLOBALS['wp'] ) ? $GLOBALS['wp']->request : '' ) );
        $type        = 'website';

        if ( is_front_page() ) {
            $url = home_url( '/' );
        }

        if ( is_singular() ) {
            $post = get_post();

            if ( $post instanceof WP_Post ) {
                $title       = get_the_title( $post );
                $description = has_excerpt( $post ) ? wp_strip_all_tags( get_the_excerpt( $post ) ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 40 );
                $description = mb_substr( $description, 0, 220 );
                $image       = get_the_post_thumbnail_url( $post, 'large' ) ?: '';
                $url         = get_permalink( $post );
                $type        = in_array( get_post_type( $post ), [ 'product', 'avatar' ], true ) ? 'product' : 'article';
            }
        } elseif ( is_home() ) {
            $page_for_posts = (int) get_option( 'page_for_posts' );
            if ( $page_for_posts ) {
                $page = get_post( $page_for_posts );
                if ( $page instanceof WP_Post ) {
                    $title       = get_the_title( $page );
                    $description = has_excerpt( $page ) ? wp_strip_all_tags( get_the_excerpt( $page ) ) : wp_trim_words( wp_strip_all_tags( $page->post_content ), 40 );
                    $image       = get_the_post_thumbnail_url( $page, 'large' ) ?: '';
                    $url         = get_permalink( $page );
                }
            }
        } elseif ( is_archive() ) {
            $title = get_the_archive_title();
            $desc  = get_the_archive_description();
            if ( $desc ) {
                $description = wp_strip_all_tags( $desc );
            }

            if ( is_post_type_archive() ) {
                $object = get_queried_object();
                if ( $object && isset( $object->name ) ) {
                    $archive_link = get_post_type_archive_link( $object->name );
                    if ( $archive_link ) {
                        $url = $archive_link;
                    }
                }
            } elseif ( is_category() || is_tag() || is_tax() ) {
                $term_link = get_term_link( get_queried_object() );
                if ( ! is_wp_error( $term_link ) ) {
                    $url = $term_link;
                }
            }

            $type = 'website';
        } elseif ( is_search() ) {
            $title       = sprintf( __( 'Resultados de "%s"', 'anima' ), get_search_query() );
            $description = sprintf( __( 'Resultados de búsqueda para "%s" en %s.', 'anima' ), get_search_query(), get_bloginfo( 'name' ) );
            $url         = get_search_link();
        }

        if ( empty( $image ) ) {
            $custom_logo_id = (int) get_theme_mod( 'custom_logo' );
            if ( $custom_logo_id ) {
                $image = wp_get_attachment_image_url( $custom_logo_id, 'full' ) ?: '';
            }

            if ( empty( $image ) ) {
                $image = get_site_icon_url( 512 ) ?: '';
            }
        }

        $description = trim( $description );
        if ( strlen( $description ) > 220 ) {
            $description = mb_substr( $description, 0, 220 );
        }

        return [
            'title'       => $title,
            'description' => $description,
            'image'       => $image,
            'url'         => $url,
            'type'        => $type,
        ];
    }
}

if ( ! function_exists( 'anima_breadcrumbs' ) ) {
    /**
     * Renderiza un rastro de migas ligero sin dependencias externas.
     */
    function anima_breadcrumbs(): void {
        if ( is_front_page() ) {
            return;
        }

        $items = [];
        $items[] = [
            'label' => get_bloginfo( 'name' ),
            'url'   => home_url( '/' ),
        ];

        if ( is_singular() ) {
            $post = get_post();
            if ( $post instanceof WP_Post ) {
                $post_type = get_post_type_object( $post->post_type );
                if ( $post_type && $post_type->has_archive ) {
                    $archive_url = get_post_type_archive_link( $post->post_type );
                    if ( $archive_url ) {
                        $items[] = [
                            'label' => $post_type->labels->name ?? $post_type->label,
                            'url'   => $archive_url,
                        ];
                    }
                } elseif ( 'post' === $post->post_type ) {
                    $blog_page = (int) get_option( 'page_for_posts' );
                    if ( $blog_page ) {
                        $items[] = [
                            'label' => get_the_title( $blog_page ),
                            'url'   => get_permalink( $blog_page ),
                        ];
                    }
                }

                $ancestors = array_reverse( get_post_ancestors( $post ) );
                foreach ( $ancestors as $ancestor ) {
                    $items[] = [
                        'label' => get_the_title( $ancestor ),
                        'url'   => get_permalink( $ancestor ),
                    ];
                }

                $items[] = [
                    'label' => get_the_title( $post ),
                    'url'   => '',
                ];
            }
        } elseif ( is_archive() ) {
            if ( is_post_type_archive() ) {
                $object = get_queried_object();
                if ( $object && isset( $object->name ) ) {
                    $items[] = [
                        'label' => get_the_archive_title(),
                        'url'   => '',
                    ];
                }
            } elseif ( is_category() || is_tag() || is_tax() ) {
                $term = get_queried_object();
                if ( $term && isset( $term->name ) ) {
                    $items[] = [
                        'label' => $term->name,
                        'url'   => '',
                    ];
                }
            } else {
                $items[] = [
                    'label' => get_the_archive_title(),
                    'url'   => '',
                ];
            }
        } elseif ( is_search() ) {
            $items[] = [
                'label' => sprintf( __( 'Resultados de "%s"', 'anima' ), get_search_query() ),
                'url'   => '',
            ];
        }

        if ( count( $items ) <= 1 ) {
            return;
        }

        echo '<nav class="anima-breadcrumbs" aria-label="' . esc_attr__( 'Migas de pan', 'anima' ) . '">';
        echo '<ol class="anima-breadcrumbs__list">';

        $last_index = array_key_last( $items );

        foreach ( $items as $index => $item ) {
            $is_last = ( $index === $last_index );
            echo '<li class="anima-breadcrumbs__item">';

            if ( ! $is_last && ! empty( $item['url'] ) ) {
                echo '<a class="anima-breadcrumbs__link" href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';
            } else {
                echo '<span class="anima-breadcrumbs__current" aria-current="page">' . esc_html( $item['label'] ) . '</span>';
            }

            echo '</li>';
        }

        echo '</ol>';
        echo '</nav>';
    }
}
