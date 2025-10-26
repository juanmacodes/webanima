<?php
/**
 * Plugin Name: Anima Swiper Slider
 * Description: Shortcode [anima_slider ids="1,2,3"] con autoplay, loop y controles accesibles.
 * Version: 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ANIMA_SLIDER_VERSION', '0.1.0' );
define( 'ANIMA_SLIDER_URL', plugin_dir_url( __FILE__ ) );

add_action( 'wp_enqueue_scripts', function () {
    wp_register_script( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11.1.0', true );
    wp_register_style( 'swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11.1.0' );
    wp_register_script( 'anima-slider', ANIMA_SLIDER_URL . 'assets/js/slider.js', [ 'swiper' ], ANIMA_SLIDER_VERSION, true );
    wp_register_style( 'anima-slider', ANIMA_SLIDER_URL . 'assets/css/slider.css', [ 'swiper' ], ANIMA_SLIDER_VERSION );
} );

add_shortcode( 'anima_slider', function ( $atts ) {
    $atts = shortcode_atts(
        [
            'ids'        => '',
            'autoplay'   => '5000',
            'loop'       => 'true',
            'pagination' => 'true',
        ],
        $atts,
        'anima_slider'
    );

    if ( empty( $atts['ids'] ) ) {
        return '';
    }

    wp_enqueue_style( 'anima-slider' );
    wp_enqueue_script( 'anima-slider' );

    $ids = array_map( 'absint', explode( ',', $atts['ids'] ) );

    $slides = array_filter( array_map( 'get_post', $ids ) );
    if ( empty( $slides ) ) {
        return '';
    }

    ob_start();
    ?>
    <div class="anima-slider" data-autoplay="<?php echo esc_attr( $atts['autoplay'] ); ?>" data-loop="<?php echo esc_attr( $atts['loop'] ); ?>" data-pagination="<?php echo esc_attr( $atts['pagination'] ); ?>">
        <div class="swiper" role="region" aria-label="<?php esc_attr_e( 'Slider destacado', 'anima-slider' ); ?>">
            <div class="swiper-wrapper">
                <?php foreach ( $slides as $slide ) : ?>
                    <?php $image = get_the_post_thumbnail_url( $slide, 'full' ); ?>
                    <div class="swiper-slide">
                        <article class="anima-slide">
                            <?php if ( $image ) : ?>
                                <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title( $slide ) ); ?>" loading="lazy" />
                            <?php endif; ?>
                            <div class="anima-slide__overlay">
                                <h3><?php echo esc_html( get_the_title( $slide ) ); ?></h3>
                                <p><?php echo esc_html( wp_trim_words( $slide->post_content, 32 ) ); ?></p>
                                <a class="anima-cta" href="<?php echo esc_url( get_permalink( $slide ) ); ?>">
                                    <?php esc_html_e( 'Explorar proyecto', 'anima-slider' ); ?>
                                </a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination" aria-hidden="true"></div>
            <button class="swiper-button-prev" aria-label="<?php esc_attr_e( 'Anterior', 'anima-slider' ); ?>"></button>
            <button class="swiper-button-next" aria-label="<?php esc_attr_e( 'Siguiente', 'anima-slider' ); ?>"></button>
        </div>
    </div>
    <?php
    return ob_get_clean();
} );
