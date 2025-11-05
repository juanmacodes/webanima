<?php
/**
 * Shortcodes del plugin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function anima_register_shortcodes() {
    add_shortcode( 'anima_cursos', 'anima_render_featured_courses' );
}

function anima_render_featured_courses( $atts ) {
    $atts = shortcode_atts(
        array(
            'cantidad' => 3,
        ),
        $atts,
        'anima_cursos'
    );

    $limit = max( 1, intval( $atts['cantidad'] ) );

    $query = new WP_Query(
        array(
            'post_type'      => 'curso',
            'posts_per_page' => $limit,
            'meta_key'       => 'anima_destacado',
            'meta_value'     => '1',
            'post_status'    => 'publish',
        )
    );

    if ( ! $query->have_posts() ) {
        return '<div class="anima-cursos anima-cursos--vacio">' . esc_html__( 'No hay cursos destacados disponibles en este momento.', 'anima-core' ) . '</div>';
    }

    ob_start();
    ?>
    <div class="anima-cursos anima-cursos--grid">
        <?php
        while ( $query->have_posts() ) :
            $query->the_post();
            $demo_url = get_post_meta( get_the_ID(), 'anima_demo_url', true );
            ?>
            <article class="anima-curso">
                <header class="anima-curso__cabecera">
                    <h3 class="anima-curso__titulo"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <a class="anima-curso__imagen" href="<?php the_permalink(); ?>">
                            <?php the_post_thumbnail( 'medium' ); ?>
                        </a>
                    <?php endif; ?>
                </header>
                <div class="anima-curso__contenido">
                    <?php if ( $excerpt = get_the_excerpt() ) : ?>
                        <p class="anima-curso__extracto"><?php echo esc_html( $excerpt ); ?></p>
                    <?php endif; ?>
                    <ul class="anima-curso__detalle">
                        <?php if ( $instructores = get_post_meta( get_the_ID(), 'anima_instructores', true ) ) : ?>
                            <li><strong><?php esc_html_e( 'Instructores:', 'anima-core' ); ?></strong> <?php echo esc_html( $instructores ); ?></li>
                        <?php endif; ?>
                        <?php if ( $duracion = get_post_meta( get_the_ID(), 'anima_duracion', true ) ) : ?>
                            <li><strong><?php esc_html_e( 'Duración:', 'anima-core' ); ?></strong> <?php echo esc_html( $duracion ); ?></li>
                        <?php endif; ?>
                        <?php if ( $kpis = get_post_meta( get_the_ID(), 'anima_kpis', true ) ) : ?>
                            <li><strong><?php esc_html_e( 'KPIs:', 'anima-core' ); ?></strong> <?php echo nl2br( esc_html( $kpis ) ); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <footer class="anima-curso__acciones">
                    <a class="anima-curso__accion" href="<?php the_permalink(); ?>">
                        <?php esc_html_e( 'Ver curso', 'anima-core' ); ?>
                    </a>
                    <?php if ( $demo_url ) : ?>
                        <a class="anima-curso__accion anima-curso__accion--demo" href="<?php echo esc_url( $demo_url ); ?>" target="_blank" rel="noopener">
                            <?php esc_html_e( 'Demo inmersiva', 'anima-core' ); ?>
                        </a>
                    <?php endif; ?>
                </footer>
            </article>
            <?php
        endwhile;
        wp_reset_postdata();
        ?>
    </div>
    <?php

    return ob_get_clean();
}
