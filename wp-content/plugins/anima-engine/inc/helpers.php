<?php
/**
 * Utilidades y funciones helper para Anima Engine.
 *
 * @package Anima\Engine
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'anima_format_price' ) ) {
    function anima_format_price( $value ): string {
        if ( '' === $value || null === $value ) {
            return '';
        }

        return sprintf( '€%s', number_format_i18n( (float) $value, 2 ) );
    }
}

if ( ! function_exists( 'anima_format_hours' ) ) {
    function anima_format_hours( $value ): string {
        if ( '' === $value || null === $value ) {
            return '';
        }

        return sprintf( '%s h', number_format_i18n( (float) $value, 0 ) );
    }
}

if ( ! function_exists( 'anima_get_course_meta' ) ) {
    function anima_get_course_meta( int $post_id ): array {
        $meta = get_post_meta( $post_id );

        $price = isset( $meta['anima_price'][0] ) ? $meta['anima_price'][0] : '';
        $hours = isset( $meta['anima_duration_hours'][0] ) ? $meta['anima_duration_hours'][0] : '';

        return [
            'price' => '' === $price ? '' : anima_format_price( $price ),
            'hours' => '' === $hours ? '' : anima_format_hours( $hours ),
        ];
    }
}

if ( ! function_exists( 'anima_get_avatar_meta' ) ) {
    function anima_get_avatar_meta( int $post_id ): array {
        $meta = get_post_meta( $post_id );

        $rig    = isset( $meta['anima_avatar_rig'][0] ) ? (bool) $meta['anima_avatar_rig'][0] : false;
        $engine = isset( $meta['anima_avatar_engine'][0] ) ? $meta['anima_avatar_engine'][0] : '';
        $tags   = isset( $meta['anima_avatar_tags'][0] ) ? maybe_unserialize( $meta['anima_avatar_tags'][0] ) : [];

        if ( ! is_array( $tags ) ) {
            $tags = array_filter( array_map( 'trim', explode( ',', (string) $tags ) ) );
        }

        return [
            'rig'    => $rig,
            'engine' => $engine,
            'tags'   => $tags,
        ];
    }
}

if ( ! function_exists( 'anima_collect_tax_badges' ) ) {
    function anima_collect_tax_badges( int $post_id, array $taxonomies ): array {
        $badges = [];

        foreach ( $taxonomies as $taxonomy ) {
            $terms = wp_get_post_terms( $post_id, $taxonomy );

            if ( is_wp_error( $terms ) || empty( $terms ) ) {
                continue;
            }

            foreach ( $terms as $term ) {
                $badges[] = [
                    'taxonomy' => $taxonomy,
                    'name'     => $term->name,
                    'slug'     => $term->slug,
                ];
            }
        }

        return $badges;
    }
}

if ( ! function_exists( 'anima_get_trimmed_excerpt' ) ) {
    function anima_get_trimmed_excerpt( int $post_id, int $words = 20 ): string {
        $excerpt = get_the_excerpt( $post_id );

        if ( empty( $excerpt ) ) {
            $post = get_post( $post_id );
            if ( $post instanceof \WP_Post ) {
                $excerpt = $post->post_content;
            }
        }

        $excerpt = wp_strip_all_tags( (string) $excerpt );

        return wp_trim_words( $excerpt, $words, '&hellip;' );
    }
}

if ( ! function_exists( 'anima_parse_csv_slugs' ) ) {
    function anima_parse_csv_slugs( $input ): array {
        if ( empty( $input ) ) {
            return [];
        }

        $list = is_array( $input ) ? $input : explode( ',', (string) $input );

        return array_values(
            array_filter(
                array_map(
                    static function ( $value ) {
                        $value = trim( (string) $value );
                        return '' === $value ? null : sanitize_title( $value );
                    },
                    $list
                )
            )
        );
    }
}
