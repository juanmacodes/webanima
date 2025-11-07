<?php
namespace Anima\Engine\Elementor\Projects;

use WP_Post;

use function anima_get_trimmed_excerpt;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_url;
use function get_permalink;
use function get_post_meta;
use function get_post_thumbnail_id;
use function get_the_title;
use function has_post_thumbnail;
use function wp_get_attachment_image;

/**
 * Helper para renderizar tarjetas de proyectos reutilizables.
 */
class ProjectCardRenderer {
    /**
     * Renderiza un conjunto de tarjetas.
     *
     * @param array<int, WP_Post> $posts    Entradas a renderizar.
     * @param array<string, mixed> $settings Ajustes de visualización.
     */
    public static function render_cards( array $posts, array $settings ): string {
        if ( empty( $posts ) ) {
            return '<div class="an-empty" role="status">' . esc_html__( 'No se encontraron proyectos para este servicio.', 'anima-engine' ) . '</div>';
        }

        $cards = array_map(
            static function ( WP_Post $post ) use ( $settings ): string {
                return self::render_card( $post, $settings );
            },
            $posts
        );

        return implode( '', $cards );
    }

    /**
     * Envuelve el contenido de tarjetas con el layout solicitado.
     *
     * @param array<string, mixed> $columns  Ajustes de columnas.
     * @param array<string, mixed> $carousel Ajustes de carrusel.
     */
    public static function wrap_with_layout( string $layout, string $content, array $columns, array $carousel = [] ): string {
        if ( 'carousel' === $layout ) {
            $slides = self::wrap_carousel_slides( $content );

            return '<div class="anima-carousel swiper" data-swiper="true"><div class="swiper-wrapper">' . $slides . '</div><div class="swiper-pagination"></div><div class="swiper-button-prev"></div><div class="swiper-button-next"></div></div>';
        }

        $classes = [ 'an-grid' ];
        if ( 'masonry' === $layout ) {
            $classes[] = 'an-grid--masonry';
        }

        $style = self::build_grid_style( $columns );

        return '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" style="' . esc_attr( $style ) . '">' . $content . '</div>';
    }

    /**
     * Devuelve el estilo CSS inline para un grid.
     *
     * @param array<string, mixed> $columns Ajustes de columnas.
     */
    public static function get_grid_style( array $columns ): string {
        return self::build_grid_style( $columns );
    }

    /**
     * Normaliza los ajustes recibidos desde controles o REST.
     *
     * @param array<string, mixed> $settings Ajustes.
     *
     * @return array<string, mixed>
     */
    public static function normalize_settings( array $settings ): array {
        $defaults = [
            'show_image'        => true,
            'show_client'       => true,
            'show_year'         => true,
            'show_excerpt'      => true,
            'show_stack'        => true,
            'show_kpis'         => true,
            'excerpt_length'    => 26,
            'kpi_limit'         => 3,
            'button_text'       => esc_html__( 'Ver caso', 'anima-engine' ),
            'button_aria_label' => esc_html__( 'Ver caso', 'anima-engine' ),
        ];

        $merged = array_merge( $defaults, $settings );

        $merged['show_image']   = self::to_bool( $merged['show_image'] );
        $merged['show_client']  = self::to_bool( $merged['show_client'] );
        $merged['show_year']    = self::to_bool( $merged['show_year'] );
        $merged['show_excerpt'] = self::to_bool( $merged['show_excerpt'] );
        $merged['show_stack']   = self::to_bool( $merged['show_stack'] );
        $merged['show_kpis']    = self::to_bool( $merged['show_kpis'] );

        $merged['excerpt_length'] = max( 5, (int) $merged['excerpt_length'] );
        $merged['kpi_limit']      = max( 0, min( 3, (int) $merged['kpi_limit'] ) );

        if ( ! is_string( $merged['button_text'] ) || '' === trim( $merged['button_text'] ) ) {
            $merged['button_text'] = $defaults['button_text'];
        }

        if ( ! is_string( $merged['button_aria_label'] ) || '' === trim( $merged['button_aria_label'] ) ) {
            $merged['button_aria_label'] = $merged['button_text'];
        }

        return $merged;
    }

    /**
     * Renderiza una tarjeta concreta.
     */
    protected static function render_card( WP_Post $post, array $settings ): string {
        $settings = self::normalize_settings( $settings );
        $post_id  = $post->ID;
        $title    = get_the_title( $post_id );
        $permalink = get_permalink( $post_id );
        $meta      = self::get_project_meta( $post_id );
        $excerpt   = $settings['show_excerpt'] ? anima_get_trimmed_excerpt( $post_id, $settings['excerpt_length'] ) : '';

        $thumbnail = '';
        if ( $settings['show_image'] && has_post_thumbnail( $post_id ) ) {
            $thumb_id = get_post_thumbnail_id( $post_id );
            if ( $thumb_id ) {
                $thumbnail = wp_get_attachment_image(
                    $thumb_id,
                    'large',
                    false,
                    [
                        'class'    => 'an-card__image',
                        'loading'  => 'lazy',
                        'decoding' => 'async',
                    ]
                );
            }
        }

        $meta_lines = [];
        if ( $settings['show_client'] && '' !== $meta['client'] ) {
            $meta_lines[] = esc_html__( 'Cliente:', 'anima-engine' ) . ' ' . esc_html( $meta['client'] );
        }
        if ( $settings['show_year'] && '' !== $meta['year'] ) {
            $meta_lines[] = esc_html__( 'Año:', 'anima-engine' ) . ' ' . esc_html( $meta['year'] );
        }

        $chips = [];
        if ( $settings['show_stack'] && ! empty( $meta['stack'] ) ) {
            foreach ( $meta['stack'] as $stack_item ) {
                $chips[] = '<span class="an-chip an-chip--stack">' . esc_html( $stack_item ) . '</span>';
            }
        }

        $kpis_markup = '';
        if ( $settings['show_kpis'] && ! empty( $meta['kpis'] ) ) {
            $kpis = array_slice( $meta['kpis'], 0, $settings['kpi_limit'] );
            if ( ! empty( $kpis ) ) {
                $items = array_map(
                    static function ( array $kpi ): string {
                        $label = isset( $kpi['label'] ) ? esc_html( $kpi['label'] ) : '';
                        $value = isset( $kpi['value'] ) ? esc_html( $kpi['value'] ) : '';

                        if ( '' === $label && '' === $value ) {
                            return '';
                        }

                        $parts = '';
                        if ( '' !== $label ) {
                            $parts .= '<dt>' . $label . '</dt>';
                        }
                        if ( '' !== $value ) {
                            $parts .= '<dd>' . $value . '</dd>';
                        }

                        return '' === $parts ? '' : '<div class="an-kpi">' . $parts . '</div>';
                    },
                    $kpis
                );

                $items = array_filter( $items );
                if ( ! empty( $items ) ) {
                    $kpis_markup = '<div class="an-card__kpis" aria-label="' . esc_attr__( 'Indicadores clave', 'anima-engine' ) . '">' . implode( '', $items ) . '</div>';
                }
            }
        }

        $output  = '<article class="an-card" data-anima-reveal data-project-id="' . esc_attr( (string) $post_id ) . '">';
        if ( '' !== $thumbnail ) {
            $output .= '<div class="an-card__media">' . $thumbnail;
            if ( $settings['button_text'] ) {
                $output .= '<a class="an-card__overlay" href="' . esc_url( $permalink ) . '" aria-label="' . esc_attr( sprintf( __( 'Ver caso: %s', 'anima-engine' ), $title ) ) . '">';
                $output .= '<span class="an-card__overlay-button">' . esc_html( $settings['button_text'] ) . '</span>';
                $output .= '</a>';
            }
            $output .= '</div>';
        }

        $output .= '<div class="an-card__body">';
        $output .= '<h3 class="an-card__title"><a href="' . esc_url( $permalink ) . '">' . esc_html( $title ) . '</a></h3>';

        if ( ! empty( $meta_lines ) ) {
            $output .= '<p class="an-card__meta">' . implode( ' · ', $meta_lines ) . '</p>';
        }

        if ( '' !== $excerpt ) {
            $output .= '<p class="an-card__excerpt">' . esc_html( $excerpt ) . '</p>';
        }

        if ( ! empty( $chips ) ) {
            $output .= '<div class="an-card__chips">' . implode( '', $chips ) . '</div>';
        }

        if ( '' !== $kpis_markup ) {
            $output .= $kpis_markup;
        }

        if ( $settings['button_text'] && '' === $thumbnail ) {
            $output .= '<p class="an-card__cta"><a class="an-button" href="' . esc_url( $permalink ) . '" aria-label="' . esc_attr( sprintf( __( 'Ver caso: %s', 'anima-engine' ), $title ) ) . '">' . esc_html( $settings['button_text'] ) . '</a></p>';
        }

        $output .= '</div>';
        $output .= '</article>';

        return $output;
    }

    /**
     * Envuelve cada tarjeta como slide de Swiper.
     */
    protected static function wrap_carousel_slides( string $content ): string {
        if ( '' === trim( $content ) ) {
            return '';
        }

        $dom = new \DOMDocument( '1.0', 'UTF-8' );
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = false;

        $html = '<div>' . $content . '</div>';
        if ( false === @$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD ) ) {
            return '<div class="swiper-slide">' . $content . '</div>';
        }

        $wrapper = $dom->getElementsByTagName( 'div' )->item( 0 );
        if ( ! $wrapper ) {
            return '<div class="swiper-slide">' . $content . '</div>';
        }

        $slides = [];
        foreach ( $wrapper->childNodes as $child ) {
            $fragment = $dom->saveHTML( $child );
            if ( null === $fragment ) {
                continue;
            }
            $slides[] = '<div class="swiper-slide">' . $fragment . '</div>';
        }

        return implode( '', $slides );
    }

    /**
     * Recupera y normaliza metadatos del proyecto.
     *
     * @return array{client:string,year:string,stack:array<int,string>,kpis:array<int,array<string,string>>}
     */
    protected static function get_project_meta( int $post_id ): array {
        $client = get_post_meta( $post_id, 'anima_cliente', true );
        $year   = get_post_meta( $post_id, 'anima_anio', true );
        $stack  = get_post_meta( $post_id, 'anima_stack', true );
        $kpis   = get_post_meta( $post_id, 'anima_kpis', true );

        $stack_list = [];
        if ( is_array( $stack ) ) {
            $stack_list = array_filter(
                array_map(
                    static fn( $item ): string => trim( (string) $item ),
                    $stack
                )
            );
        } elseif ( is_string( $stack ) ) {
            $parts = preg_split( '/[,|]/', $stack ) ?: [];
            $stack_list = array_filter(
                array_map(
                    static fn( $item ): string => trim( $item ),
                    $parts
                )
            );
        }

        $normalized_kpis = [];
        if ( is_array( $kpis ) ) {
            foreach ( $kpis as $item ) {
                if ( ! is_array( $item ) ) {
                    continue;
                }

                $label = isset( $item['label'] ) ? trim( (string) $item['label'] ) : '';
                $value = isset( $item['value'] ) ? trim( (string) $item['value'] ) : '';

                if ( '' === $label && '' === $value ) {
                    continue;
                }

                $normalized_kpis[] = [
                    'label' => $label,
                    'value' => $value,
                ];
            }
        } elseif ( is_string( $kpis ) && '' !== trim( $kpis ) ) {
            $normalized_kpis[] = [
                'label' => esc_html__( 'Resultado', 'anima-engine' ),
                'value' => trim( $kpis ),
            ];
        }

        return [
            'client' => is_string( $client ) ? trim( $client ) : '',
            'year'   => is_string( $year ) || is_int( $year ) ? (string) $year : '',
            'stack'  => array_values( $stack_list ),
            'kpis'   => array_values( $normalized_kpis ),
        ];
    }

    /**
     * Convierte distintos tipos en booleano.
     *
     * @param mixed $value Valor a convertir.
     */
    protected static function to_bool( $value ): bool {
        if ( is_bool( $value ) ) {
            return $value;
        }

        if ( is_numeric( $value ) ) {
            return (bool) (int) $value;
        }

        if ( is_string( $value ) ) {
            $value = strtolower( trim( $value ) );
            return in_array( $value, [ '1', 'true', 'yes', 'on' ], true );
        }

        return false;
    }

    /**
     * Genera estilos inline para el grid a partir de columnas.
     *
     * @param array<string, mixed> $columns Ajustes de columnas.
     */
    protected static function build_grid_style( array $columns ): string {
        $desktop = isset( $columns['desktop'] ) ? (int) $columns['desktop'] : 3;
        $tablet  = isset( $columns['tablet'] ) ? (int) $columns['tablet'] : 2;
        $mobile  = isset( $columns['mobile'] ) ? (int) $columns['mobile'] : 1;
        $gap     = $columns['gap'] ?? [ 'size' => 28, 'unit' => 'px' ];

        $gap_value = '';
        if ( is_array( $gap ) ) {
            $size = isset( $gap['size'] ) ? (float) $gap['size'] : 28.0;
            $unit = isset( $gap['unit'] ) ? $gap['unit'] : 'px';
            $gap_value = $size . $unit;
        } elseif ( is_numeric( $gap ) ) {
            $gap_value = $gap . 'px';
        } elseif ( is_string( $gap ) && '' !== $gap ) {
            $gap_value = $gap;
        }

        $style = sprintf(
            '--an-columns-desktop:%d;--an-columns-tablet:%d;--an-columns-mobile:%d;',
            max( 1, $desktop ),
            max( 1, $tablet ),
            max( 1, $mobile )
        );

        if ( '' !== $gap_value ) {
            $style .= sprintf( '--an-grid-gap:%s;', $gap_value );
        }

        return $style;
    }
}
