<?php
/**
 * Shortcodes equivalentes para los grids y listados.
 *
 * @package Anima\Engine
 */

defined( 'ABSPATH' ) || exit;


add_action(
    'init',
    static function () {
        add_shortcode( 'anima_avatars_grid', 'anima_engine_shortcode_avatars_grid' );
        add_shortcode( 'anima_courses_grid', 'anima_engine_shortcode_courses_grid' );
        add_shortcode( 'anima_posts_grid', 'anima_engine_shortcode_posts_grid' );
    }
);

function anima_engine_shortcode_avatars_grid( array $atts = [] ): string {
    $atts = shortcode_atts(
        [
            'categoria' => '',
            'columns'   => 4,
            'per_page'  => 12,
            'order'     => 'DESC',
        ],
        $atts,
        'anima_avatars_grid'
    );

    $tax_query = [];
    $categories = anima_parse_csv_slugs( $atts['categoria'] );
    if ( ! empty( $categories ) ) {
        $tax_query[] = [
            'taxonomy' => 'avatar_categoria',
            'field'    => 'slug',
            'terms'    => $categories,
        ];
    }

    $query = new WP_Query(
        [
            'post_type'      => 'avatar',
            'post_status'    => 'publish',
            'posts_per_page' => max( 1, (int) $atts['per_page'] ),
            'orderby'        => 'date',
            'order'          => 'ASC' === strtoupper( $atts['order'] ) ? 'ASC' : 'DESC',
            'tax_query'      => $tax_query,
        ]
    );

    if ( ! $query->have_posts() ) {
        return '<div class="an-empty" role="status">' . esc_html__( 'No se encontraron avatares.', 'anima-engine' ) . '</div>';
    }

    ob_start();
    ?>
    <div class="an-grid cols-<?php echo (int) $atts['columns']; ?>" data-cols-desktop="<?php echo (int) $atts['columns']; ?>" data-cols-tablet="2" data-cols-mobile="1">
        <?php
        while ( $query->have_posts() ) {
            $query->the_post();
            $meta       = anima_get_avatar_meta( get_the_ID() );
            $thumb_id   = $meta['thumb_id'] ?: get_post_thumbnail_id();
            $image_url  = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'an_square' ) : get_the_post_thumbnail_url( null, 'an_square' );
            $type_terms = wp_get_post_terms( get_the_ID(), 'avatar_categoria' );
            ?>
            <article class="an-avatar-card" data-anima-reveal>
                <figure class="an-avatar-card__media">
                    <?php if ( $image_url ) : ?>
                        <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" />
                    <?php endif; ?>
                </figure>
                <div class="an-avatar-card__body">
                    <h3 class="an-avatar-card__title"><?php the_title(); ?></h3>
                    <?php if ( ! empty( $type_terms ) ) : ?>
                        <div class="an-avatar-card__tags" aria-label="<?php esc_attr_e( 'Categorías', 'anima-engine' ); ?>">
                            <?php foreach ( anima_normalize_terms( $type_terms ) as $term ) : ?>
                                <span class="an-chip"><?php echo esc_html( $term['name'] ); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="an-avatar-card__actions">
                        <a class="an-button" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Ver detalle', 'anima-engine' ); ?></a>
                        <?php if ( $meta['webgl_url'] ) : ?>
                            <a class="an-button" href="#" data-anima-lightbox="<?php echo esc_url( $meta['webgl_url'] ); ?>" data-anima-lightbox-type="iframe" aria-label="<?php the_title_attribute(); ?>">
                                <?php esc_html_e( 'Abrir visor 3D', 'anima-engine' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php
        }
        ?>
    </div>
    <?php
    wp_reset_postdata();

    return ob_get_clean();
}

function anima_engine_shortcode_courses_grid( array $atts = [] ): string {
    $atts = shortcode_atts(
        [
            'nivel'      => '',
            'modalidad'  => '',
            'tecnologia' => '',
            'columns'    => 3,
            'per_page'   => 9,
        ],
        $atts,
        'anima_courses_grid'
    );

    $tax_query = [];
    foreach ( [ 'nivel', 'modalidad', 'tecnologia' ] as $taxonomy ) {
        $slugs = anima_parse_csv_slugs( $atts[ $taxonomy ] );
        if ( ! empty( $slugs ) ) {
            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $slugs,
            ];
        }
    }

    $query = new WP_Query(
        [
            'post_type'      => 'curso',
            'post_status'    => 'publish',
            'posts_per_page' => max( 1, (int) $atts['per_page'] ),
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'tax_query'      => $tax_query,
        ]
    );

    if ( ! $query->have_posts() ) {
        return '<div class="an-empty" role="status">' . esc_html__( 'No hay cursos disponibles.', 'anima-engine' ) . '</div>';
    }

    ob_start();
    ?>
    <div class="an-grid cols-<?php echo (int) $atts['columns']; ?>" data-cols-desktop="<?php echo (int) $atts['columns']; ?>" data-cols-tablet="2" data-cols-mobile="1">
        <?php
        while ( $query->have_posts() ) {
            $query->the_post();
            $meta = anima_get_course_meta( get_the_ID() );
            ?>
            <article class="an-card" data-anima-reveal>
                <?php if ( has_post_thumbnail() ) : ?>
                    <figure class="an-card__media">
                        <?php the_post_thumbnail( 'an_card_16x10', [ 'alt' => get_the_title(), 'loading' => 'lazy' ] ); ?>
                    </figure>
                <?php endif; ?>
                <div class="an-card__body">
                    <div class="an-card__chips">
                        <?php
                        foreach ( [ 'nivel', 'modalidad' ] as $taxonomy ) {
                            $terms = anima_normalize_terms( wp_get_post_terms( get_the_ID(), $taxonomy ) );
                            foreach ( $terms as $term ) {
                                echo '<span class="an-chip">' . esc_html( $term['name'] ) . '</span>';
                            }
                        }
                        ?>
                    </div>
                    <h3 class="an-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <?php if ( $meta['price'] || $meta['hours'] ) : ?>
                        <p class="an-card__meta">
                            <?php echo esc_html( trim( implode( ' · ', array_filter( [ $meta['price'], $meta['hours'] ] ) ) ) ); ?>
                        </p>
                    <?php endif; ?>
                    <p class="an-card__excerpt"><?php echo esc_html( anima_get_trimmed_excerpt( get_the_ID(), 24 ) ); ?></p>
                    <p class="an-card__cta"><a class="an-button" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Ver curso', 'anima-engine' ); ?></a></p>
                </div>
            </article>
            <?php
        }
        ?>
    </div>
    <?php
    wp_reset_postdata();

    return ob_get_clean();
}

function anima_engine_shortcode_posts_grid( array $atts = [] ): string {
    $atts = shortcode_atts(
        [
            'categoria' => '',
            'per_page'  => 6,
        ],
        $atts,
        'anima_posts_grid'
    );

    $tax_query = [];
    $categories = anima_parse_csv_slugs( $atts['categoria'] );
    if ( ! empty( $categories ) ) {
        $tax_query[] = [
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => $categories,
        ];
    }

    $query = new WP_Query(
        [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => max( 1, (int) $atts['per_page'] ),
            'tax_query'      => $tax_query,
        ]
    );

    if ( ! $query->have_posts() ) {
        return '<div class="an-empty" role="status">' . esc_html__( 'Sin entradas por ahora.', 'anima-engine' ) . '</div>';
    }

    ob_start();
    ?>
    <div class="an-grid cols-3" data-cols-desktop="3" data-cols-tablet="2" data-cols-mobile="1">
        <?php
        while ( $query->have_posts() ) {
            $query->the_post();
            ?>
            <article class="an-post-card" data-anima-reveal>
                <?php if ( has_post_thumbnail() ) : ?>
                    <figure class="an-post-card__media">
                        <?php the_post_thumbnail( 'an_card_16x10', [ 'alt' => get_the_title(), 'loading' => 'lazy' ] ); ?>
                    </figure>
                <?php endif; ?>
                <div class="an-post-card__body">
                    <h3 class="an-post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <p class="an-post-card__meta"><?php echo esc_html( get_the_date() ); ?></p>
                    <p class="an-card__excerpt"><?php echo esc_html( anima_get_trimmed_excerpt( get_the_ID(), 26 ) ); ?></p>
                    <p class="an-post-card__cta"><a href="<?php the_permalink(); ?>"><?php esc_html_e( 'Leer', 'anima-engine' ); ?></a></p>
                </div>
            </article>
            <?php
        }
        ?>
    </div>
    <?php
    wp_reset_postdata();

    return ob_get_clean();
}
