<?php
/**
 * Shortcodes para renderizar grids de cursos y avatares fuera de Elementor.
 *
 * @package Anima\Engine
 */

defined( 'ABSPATH' ) || exit;

use Anima\Engine\Elementor\Projects\ProjectCardRenderer;

add_action(
    'init',
    static function () {
        add_shortcode( 'anima_cursos_grid', 'anima_engine_shortcode_cursos_grid' );
        add_shortcode( 'anima_avatares_grid', 'anima_engine_shortcode_avatares_grid' );
        add_shortcode( 'anima_proyectos_tabs', 'anima_engine_shortcode_proyectos_tabs' );
    }
);

function anima_engine_shortcode_proyectos_tabs( array $atts = [], ?string $content = null ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    wp_enqueue_script( 'anima-projects-tabs' );

    $atts = shortcode_atts(
        [
            'servicios'       => '',
            'layout'          => 'grid',
            'per_page'        => 6,
            'orderby'         => 'date',
            'order'           => 'DESC',
            'year_min'        => '',
            'year_max'        => '',
            'search'          => '',
            'columns_desktop' => 3,
            'columns_tablet'  => 2,
            'columns_mobile'  => 1,
            'gap'             => '28px',
            'show_image'      => 'yes',
            'show_client'     => 'yes',
            'show_year'       => 'yes',
            'show_excerpt'    => 'yes',
            'show_stack'      => 'yes',
            'show_kpis'       => 'yes',
            'excerpt_length'  => 26,
            'kpi_limit'       => 3,
            'button_text'     => __( 'Ver caso', 'anima-engine' ),
            'ajax'            => '0',
            'prefetch'        => '0',
            'cache_ttl'       => 0,
        ],
        $atts,
        'anima_proyectos_tabs'
    );

    $layout  = in_array( strtolower( $atts['layout'] ), [ 'grid', 'masonry', 'carousel' ], true ) ? strtolower( $atts['layout'] ) : 'grid';
    $orderby = in_array( strtolower( $atts['orderby'] ), [ 'date', 'title', 'meta_value', 'rand' ], true ) ? strtolower( $atts['orderby'] ) : 'date';
    $order   = 'ASC' === strtoupper( $atts['order'] ) ? 'ASC' : 'DESC';

    $per_page = max( 1, min( 24, (int) $atts['per_page'] ) );

    $year_min = '' === $atts['year_min'] ? 0 : absint( $atts['year_min'] );
    $year_max = '' === $atts['year_max'] ? 0 : absint( $atts['year_max'] );
    $search   = sanitize_text_field( $atts['search'] );

    $servicios = anima_parse_csv_slugs( $atts['servicios'] );

    $term_args = [
        'taxonomy'   => 'servicio',
        'hide_empty' => true,
    ];

    if ( ! empty( $servicios ) ) {
        $term_args['slug'] = $servicios;
    }

    $terms = get_terms( $term_args );

    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return '<div class="an-empty" role="status">' . esc_html__( 'No hay servicios configurados.', 'anima-engine' ) . '</div>';
    }

    $card_settings = [
        'show_image'     => $atts['show_image'],
        'show_client'    => $atts['show_client'],
        'show_year'      => $atts['show_year'],
        'show_excerpt'   => $atts['show_excerpt'],
        'show_stack'     => $atts['show_stack'],
        'show_kpis'      => $atts['show_kpis'],
        'excerpt_length' => absint( $atts['excerpt_length'] ),
        'kpi_limit'      => absint( $atts['kpi_limit'] ),
        'button_text'    => $atts['button_text'],
    ];

    $columns = [
        'desktop' => max( 1, (int) $atts['columns_desktop'] ),
        'tablet'  => max( 1, (int) $atts['columns_tablet'] ),
        'mobile'  => max( 1, (int) $atts['columns_mobile'] ),
        'gap'     => $atts['gap'],
    ];

    $carousel = [
        'desktop'   => max( 1, (int) $atts['columns_desktop'] ),
        'tablet'    => max( 1, (int) $atts['columns_tablet'] ),
        'mobile'    => max( 1, (int) $atts['columns_mobile'] ),
        'autoplay'  => false,
        'loop'      => false,
        'speed'     => 600,
        'navigation'=> 'arrows-dots',
    ];

    $ajax_enabled = in_array( strtolower( $atts['ajax'] ), [ '1', 'true', 'yes', 'on' ], true );
    $prefetch     = in_array( strtolower( $atts['prefetch'] ), [ '1', 'true', 'yes', 'on' ], true );
    $cache_ttl    = $ajax_enabled ? absint( $atts['cache_ttl'] ) : 0;

    $container_attrs = [
        'class'             => 'anima-projects-tabs',
        'data-layout'       => $layout,
        'data-ajax'         => $ajax_enabled ? '1' : '0',
        'data-tabs-position'=> 'top',
        'data-tabs-align'   => 'flex-start',
        'data-columns'      => wp_json_encode( $columns ),
        'data-carousel'     => wp_json_encode( $carousel ),
        'data-cache-ttl'    => (string) $cache_ttl,
        'data-prefetch'     => $prefetch ? '1' : '0',
    ];

    if ( $ajax_enabled ) {
        $container_attrs['data-endpoint'] = rest_url( 'anima/v' . ANIMA_ENGINE_API_VERSION . '/proyectos' );
    }

    $tablist = '';
    $panels  = '';

    foreach ( $terms as $index => $term ) {
        $tab_id   = 'anima-tabs-' . $term->slug;
        $panel_id = 'anima-panel-' . $term->slug;
        $selected = 0 === $index;

        $tablist .= sprintf(
            '<button class="anima-tabs__button%1$s" id="%2$s" role="tab" aria-controls="%3$s" aria-selected="%4$s" tabindex="%5$d">%6$s</button>',
            $selected ? ' is-active' : '',
            esc_attr( $tab_id ),
            esc_attr( $panel_id ),
            $selected ? 'true' : 'false',
            $selected ? 0 : -1,
            esc_html( $term->name )
        );

        $query_args = [
            'post_type'      => 'proyecto',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'orderby'        => $orderby,
            'order'          => $order,
            'tax_query'      => [
                [
                    'taxonomy' => 'servicio',
                    'field'    => 'slug',
                    'terms'    => $term->slug,
                ],
            ],
        ];

        if ( 'meta_value' === $orderby ) {
            $query_args['meta_key'] = 'anima_anio';
        }

        $meta_query = [];
        if ( $year_min && $year_max && $year_max >= $year_min ) {
            $meta_query[] = [
                'key'     => 'anima_anio',
                'value'   => [ $year_min, $year_max ],
                'compare' => 'BETWEEN',
                'type'    => 'NUMERIC',
            ];
        } elseif ( $year_min ) {
            $meta_query[] = [
                'key'     => 'anima_anio',
                'value'   => $year_min,
                'compare' => '>=',
                'type'    => 'NUMERIC',
            ];
        } elseif ( $year_max ) {
            $meta_query[] = [
                'key'     => 'anima_anio',
                'value'   => $year_max,
                'compare' => '<=',
                'type'    => 'NUMERIC',
            ];
        }

        if ( ! empty( $meta_query ) ) {
            $query_args['meta_query'] = $meta_query;
        }

        if ( '' !== $search ) {
            $query_args['s'] = $search;
        }

        $panel_html = '';

        if ( ! $ajax_enabled || 0 === $index ) {
            $query = new WP_Query( $query_args );

            if ( $query->have_posts() ) {
                $panel_html = ProjectCardRenderer::wrap_with_layout(
                    $layout,
                    ProjectCardRenderer::render_cards( $query->posts, $card_settings ),
                    $columns,
                    $carousel
                );
            } else {
                $panel_html = '<div class="an-empty" role="status">' . esc_html__( 'No hay proyectos disponibles en este servicio.', 'anima-engine' ) . '</div>';
            }

            wp_reset_postdata();
        } else {
            $panel_html = anima_engine_projects_placeholder( $layout, $columns, $carousel, $per_page );
        }

        $panel_attrs = [
            'id'             => $panel_id,
            'class'          => 'anima-tabs__panel',
            'role'           => 'tabpanel',
            'aria-labelledby'=> $tab_id,
            'data-layout'    => $layout,
            'data-columns'   => wp_json_encode( $columns ),
            'data-carousel'  => wp_json_encode( $carousel ),
            'data-card'      => wp_json_encode( $card_settings ),
            'data-query'     => wp_json_encode(
                [
                    'servicio'    => $term->slug,
                    'per_page'    => $per_page,
                    'layout'      => $layout,
                    'orderby'     => $orderby,
                    'order'       => $order,
                    'year_min'    => $year_min,
                    'year_max'    => $year_max,
                    'search'      => $search,
                    'card'        => $card_settings,
                    'columns'     => $columns,
                    'carousel'    => $carousel,
                ]
            ),
        ];

        if ( ! $selected ) {
            $panel_attrs['hidden'] = 'hidden';
        }

        $panel  = '<div ' . anima_engine_build_attributes( $panel_attrs ) . '>';
        $panel .= '<div class="anima-tabs__panel-inner" data-layout="' . esc_attr( $layout ) . '">' . $panel_html . '</div>';
        $panel .= '</div>';

        $panels .= $panel;
    }

    $output  = '<div ' . anima_engine_build_attributes( $container_attrs ) . '>';
    $output .= '<div class="anima-tabs" role="tablist" aria-orientation="horizontal">' . $tablist . '</div>';
    $output .= '<div class="anima-tabs__panels">' . $panels . '</div>';
    $output .= '</div>';

    return $output;
}

function anima_engine_build_attributes( array $attributes ): string {
    $pairs = [];
    foreach ( $attributes as $key => $value ) {
        if ( null === $value || '' === $value ) {
            continue;
        }

        if ( true === $value ) {
            $pairs[] = esc_attr( $key );
            continue;
        }

        $pairs[] = sprintf( '%s="%s"', esc_attr( $key ), esc_attr( (string) $value ) );
    }

    return implode( ' ', $pairs );
}

function anima_engine_projects_placeholder( string $layout, array $columns, array $carousel, int $count ): string {
    $count = max( 1, min( 6, $count ) );
    $items = [];

    for ( $i = 0; $i < $count; $i++ ) {
        $items[] = '<div class="an-card an-card--skeleton"><div class="an-card__media"></div><div class="an-card__body"><span class="an-skeleton an-skeleton--title"></span><span class="an-skeleton an-skeleton--text"></span><span class="an-skeleton an-skeleton--text"></span></div></div>';
    }

    $content = implode( '', $items );

    $output = ProjectCardRenderer::wrap_with_layout( $layout, $content, $columns, $carousel );

    if ( 'carousel' === $layout ) {
        return str_replace( 'anima-carousel swiper', 'anima-carousel swiper is-loading', $output );
    }

    return preg_replace( '/class="([^"]*an-grid[^"]*)"/', 'class="$1 an-grid--skeleton"', (string) $output, 1 ) ?: $output;
}

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
