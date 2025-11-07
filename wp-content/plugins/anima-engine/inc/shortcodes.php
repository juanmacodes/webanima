<?php
/**
 * Shortcodes para renderizar grids de cursos y avatares fuera de Elementor.
 *
 * @package Anima\Engine
 */

defined( 'ABSPATH' ) || exit;

add_action(
    'init',
    static function () {
        add_shortcode( 'anima_cursos_grid', 'anima_engine_shortcode_cursos_grid' );
        add_shortcode( 'anima_avatares_grid', 'anima_engine_shortcode_avatares_grid' );
    }
);

function anima_engine_shortcode_cursos_grid( array $atts = [], ?string $content = null ): string {
    unset( $content );

    $atts = shortcode_atts(
        [
            'posts_per_page' => 6,
            'order'          => 'DESC',
            'orderby'        => 'date',
            'nivel'          => '',
            'modalidad'      => '',
            'tecnologia'     => '',
            'pagination'     => 'false',
            'show_image'     => 'true',
            'show_excerpt'   => 'true',
            'show_price'     => 'true',
            'show_hours'     => 'true',
            'show_badges'    => 'true',
        ],
        $atts,
        'anima_cursos_grid'
    );

    $paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
    $posts_per_page = max( 1, (int) $atts['posts_per_page'] );
    $orderby        = sanitize_text_field( $atts['orderby'] );
    $order          = strtoupper( sanitize_text_field( $atts['order'] ) ) === 'ASC' ? 'ASC' : 'DESC';

    $query_args = [
        'post_type'      => 'curso',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'orderby'        => $orderby,
        'order'          => $order,
        'paged'          => 'true' === strtolower( $atts['pagination'] ) ? $paged : 1,
    ];

    $tax_query = [];
    foreach ( [
        'nivel'      => $atts['nivel'],
        'modalidad'  => $atts['modalidad'],
        'tecnologia' => $atts['tecnologia'],
    ] as $taxonomy => $values ) {
        $terms = anima_parse_csv_slugs( $values );
        if ( empty( $terms ) ) {
            continue;
        }

        $tax_query[] = [
            'taxonomy' => $taxonomy,
            'field'    => 'slug',
            'terms'    => $terms,
        ];
    }

    if ( ! empty( $tax_query ) ) {
        $query_args['tax_query'] = $tax_query;
    }

    $query = new \WP_Query( $query_args );

    if ( ! $query->have_posts() ) {
        return '<div class="an-grid anima-grid-empty">' . esc_html__( 'No hay cursos disponibles en este momento.', 'anima-engine' ) . '</div>';
    }

    $show_image   = 'true' === strtolower( $atts['show_image'] );
    $show_excerpt = 'true' === strtolower( $atts['show_excerpt'] );
    $show_price   = 'true' === strtolower( $atts['show_price'] );
    $show_hours   = 'true' === strtolower( $atts['show_hours'] );
    $show_badges  = 'true' === strtolower( $atts['show_badges'] );

    ob_start();

    echo '<div class="an-grid an-grid--courses" style="--an-columns-desktop:4;--an-columns-laptop:3;--an-columns-tablet:2;--an-columns-mobile:1">';

    while ( $query->have_posts() ) {
        $query->the_post();
        $post_id = get_the_ID();
        $meta    = anima_get_course_meta( $post_id );
        $badges  = $show_badges ? anima_collect_tax_badges( $post_id, [ 'nivel', 'modalidad', 'tecnologia' ] ) : [];
        $excerpt = $show_excerpt ? anima_get_trimmed_excerpt( $post_id, 20 ) : '';

        echo '<article class="an-card" data-anima-reveal>';

        if ( $show_image ) {
            $thumbnail = get_the_post_thumbnail(
                $post_id,
                'anima_course_card',
                [
                    'class'    => 'an-card__image',
                    'loading'  => 'lazy',
                    'decoding' => 'async',
                ]
            );

            if ( $thumbnail ) {
                echo '<div class="an-card__media">' . $thumbnail;
                echo '<a class="an-card__overlay" href="' . esc_url( get_permalink( $post_id ) ) . '" aria-label="' . esc_attr( sprintf( __( 'Ver curso: %s', 'anima-engine' ), get_the_title( $post_id ) ) ) . '">';
                echo '<span class="an-card__overlay-button">' . esc_html__( 'Ver curso', 'anima-engine' ) . '</span>';
                echo '</a>';
                echo '</div>';
            }
        }

        echo '<div class="an-card__body">';
        echo '<h3 class="an-card__title"><a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>';

        if ( $excerpt ) {
            echo '<p class="an-card__excerpt">' . esc_html( $excerpt ) . '</p>';
        }

        $chips = [];
        if ( $show_price && '' !== $meta['price'] ) {
            $chips[] = '<span class="an-chip an-chip--price">' . esc_html( $meta['price'] ) . '</span>';
        }
        if ( $show_hours && '' !== $meta['hours'] ) {
            $chips[] = '<span class="an-chip an-chip--hours">' . esc_html( $meta['hours'] ) . '</span>';
        }

        if ( ! empty( $chips ) ) {
            echo '<div class="an-card__chips">' . implode( '', $chips ) . '</div>';
        }

        if ( ! empty( $badges ) ) {
            echo '<div class="an-card__badges">';
            foreach ( $badges as $badge ) {
                echo '<span class="an-badge">' . esc_html( $badge['name'] ) . '</span>';
            }
            echo '</div>';
        }

        echo '</div>';
        echo '</article>';
    }

    echo '</div>';

    if ( 'true' === strtolower( $atts['pagination'] ) ) {
        $pagination = paginate_links(
            [
                'total'   => max( 1, (int) $query->max_num_pages ),
                'current' => $paged,
                'type'    => 'list',
            ]
        );

        if ( $pagination ) {
            echo '<nav class="an-pagination" aria-label="' . esc_attr__( 'Cursos', 'anima-engine' ) . '">';
            echo str_replace( [ '<ul class="page-numbers">', '</ul>' ], '', $pagination );
            echo '</nav>';
        }
    }

    wp_reset_postdata();

    return ob_get_clean();
}

function anima_engine_shortcode_avatares_grid( array $atts = [], ?string $content = null ): string {
    unset( $content );

    $atts = shortcode_atts(
        [
            'posts_per_page' => 9,
            'order'          => 'DESC',
            'orderby'        => 'date',
            'tipo'           => '',
            'pagination'     => 'false',
            'layout'         => 'grid',
            'lightbox'       => 'false',
            'show_link'      => 'true',
        ],
        $atts,
        'anima_avatares_grid'
    );

    $paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
    $posts_per_page = max( 1, (int) $atts['posts_per_page'] );
    $orderby        = sanitize_text_field( $atts['orderby'] );
    $order          = strtoupper( sanitize_text_field( $atts['order'] ) ) === 'ASC' ? 'ASC' : 'DESC';

    $query_args = [
        'post_type'      => 'avatar',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'orderby'        => $orderby,
        'order'          => $order,
        'paged'          => 'true' === strtolower( $atts['pagination'] ) ? $paged : 1,
    ];

    $terms = anima_parse_csv_slugs( $atts['tipo'] );
    if ( ! empty( $terms ) ) {
        $query_args['tax_query'] = [
            [
                'taxonomy' => 'avatar_tipo',
                'field'    => 'slug',
                'terms'    => $terms,
            ],
        ];
    }

    $query = new \WP_Query( $query_args );

    if ( ! $query->have_posts() ) {
        return '<div class="an-grid anima-grid-empty">' . esc_html__( 'No hay avatares disponibles en este momento.', 'anima-engine' ) . '</div>';
    }

    $use_lightbox = 'true' === strtolower( $atts['lightbox'] );
    $show_link    = 'true' === strtolower( $atts['show_link'] );
    $layout       = 'masonry' === strtolower( $atts['layout'] ) ? 'masonry' : 'grid';

    ob_start();

    echo '<div class="an-grid an-grid--avatares" data-layout="' . esc_attr( $layout ) . '" style="--an-columns-desktop:4;--an-columns-laptop:3;--an-columns-tablet:2;--an-columns-mobile:1">';

    while ( $query->have_posts() ) {
        $query->the_post();
        $post_id = get_the_ID();
        $meta    = anima_get_avatar_meta( $post_id );

        echo '<article class="an-avatar-card" data-anima-reveal>';
        echo '<div class="an-avatar-card__media">';
        $thumbnail = get_the_post_thumbnail(
            $post_id,
            'anima_avatar_square',
            [
                'class'    => 'an-avatar-card__image',
                'loading'  => 'lazy',
                'decoding' => 'async',
            ]
        );

        if ( $thumbnail ) {
            echo $thumbnail;
        }

        if ( $use_lightbox ) {
            $full = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
            if ( $full ) {
                echo '<a class="an-lightbox-trigger" href="' . esc_url( $full[0] ) . '" data-elementor-open-lightbox="yes" aria-label="' . esc_attr( sprintf( __( 'Ver avatar: %s', 'anima-engine' ), get_the_title( $post_id ) ) ) . '">' . esc_html__( 'Ver avatar', 'anima-engine' ) . '</a>';
            }
        } elseif ( $show_link ) {
            echo '<a class="an-lightbox-trigger" href="' . esc_url( get_permalink( $post_id ) ) . '" aria-label="' . esc_attr( sprintf( __( 'Ver avatar: %s', 'anima-engine' ), get_the_title( $post_id ) ) ) . '"></a>';
        }

        echo '</div>';

        echo '<div class="an-avatar-card__body">';
        $title_tag = $show_link ? 'a' : 'span';
        $title_attr = $show_link ? ' href="' . esc_url( get_permalink( $post_id ) ) . '"' : '';
        echo '<h3 class="an-avatar-card__title"><' . $title_tag . $title_attr . '>' . esc_html( get_the_title( $post_id ) ) . '</' . $title_tag . '></h3>';

        $chips = [];
        if ( ! empty( $meta['engine'] ) ) {
            $chips[] = '<span class="an-badge">' . esc_html( $meta['engine'] ) . '</span>';
        }
        if ( $meta['rig'] ) {
            $chips[] = '<span class="an-badge an-badge--success">' . esc_html__( 'Rig listo', 'anima-engine' ) . '</span>';
        }

        $tax_terms = wp_get_post_terms( $post_id, 'avatar_tipo' );
        if ( ! is_wp_error( $tax_terms ) ) {
            foreach ( $tax_terms as $term ) {
                $chips[] = '<span class="an-chip">' . esc_html( $term->name ) . '</span>';
            }
        }

        if ( ! empty( $meta['tags'] ) ) {
            foreach ( $meta['tags'] as $tag ) {
                $chips[] = '<span class="an-chip an-chip--hours">' . esc_html( $tag ) . '</span>';
            }
        }

        if ( ! empty( $chips ) ) {
            echo '<div class="an-avatar-card__tags">' . implode( '', $chips ) . '</div>';
        }

        echo '</div>';
        echo '</article>';
    }

    echo '</div>';

    if ( 'true' === strtolower( $atts['pagination'] ) ) {
        $pagination = paginate_links(
            [
                'total'   => max( 1, (int) $query->max_num_pages ),
                'current' => $paged,
                'type'    => 'list',
            ]
        );

        if ( $pagination ) {
            echo '<nav class="an-pagination" aria-label="' . esc_attr__( 'Avatares', 'anima-engine' ) . '">';
            echo str_replace( [ '<ul class="page-numbers">', '</ul>' ], '', $pagination );
            echo '</nav>';
        }
    }

    wp_reset_postdata();

    return ob_get_clean();
}
