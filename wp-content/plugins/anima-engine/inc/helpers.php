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
        $price = get_post_meta( $post_id, 'anima_price', true );
        $hours = get_post_meta( $post_id, 'anima_duration_hours', true );
        $requirements = get_post_meta( $post_id, 'anima_requirements', true );
        $syllabus = get_post_meta( $post_id, 'anima_syllabus', true );
        $instructors = get_post_meta( $post_id, 'anima_instructors', true );
        $dates = get_post_meta( $post_id, 'anima_upcoming_dates', true );
        $target = get_post_meta( $post_id, 'anima_enroll_target', true );
        $url = get_post_meta( $post_id, 'anima_enroll_url', true );

        return [
            'price'        => '' === $price ? '' : anima_format_price( $price ),
            'raw_price'    => $price,
            'hours'        => '' === $hours ? '' : anima_format_hours( $hours ),
            'raw_hours'    => $hours,
            'requirements' => wp_kses_post( (string) $requirements ),
            'syllabus'     => is_array( $syllabus ) ? $syllabus : [],
            'instructors'  => is_array( $instructors ) ? $instructors : [],
            'dates'        => is_array( $dates ) ? $dates : [],
            'target'       => $target ?: 'waitlist',
            'enroll_url'   => esc_url( (string) $url ),
        ];
    }
}

if ( ! function_exists( 'anima_get_avatar_meta' ) ) {
    function anima_get_avatar_meta( int $post_id ): array {
        $type      = get_post_meta( $post_id, 'anima_avatar_type', true );
        $thumb_id  = absint( get_post_meta( $post_id, 'anima_avatar_thumb', true ) );
        $webgl_url = esc_url( (string) get_post_meta( $post_id, 'anima_avatar_webgl', true ) );

        return [
            'type'      => $type ?: 'humano',
            'thumb_id'  => $thumb_id,
            'webgl_url' => $webgl_url,
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

if ( ! function_exists( 'anima_normalize_terms' ) ) {
    function anima_normalize_terms( array $terms ): array {
        $normalized = [];

        foreach ( $terms as $term ) {
            if ( $term instanceof \WP_Term ) {
                $normalized[] = [
                    'name' => $term->name,
                    'slug' => $term->slug,
                ];
            }
        }

        return $normalized;
    }
}
