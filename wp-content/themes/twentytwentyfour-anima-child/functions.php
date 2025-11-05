<?php
/**
 * Functions for the Twenty Twenty-Four — Anima Child theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'after_setup_theme', 'anima_child_setup' );
add_action( 'wp_enqueue_scripts', 'anima_child_enqueue_assets' );
add_action( 'wp_body_open', 'anima_child_wrapper_open' );
add_action( 'wp_footer', 'anima_child_wrapper_close', 0 );
add_shortcode( 'anima_slider', 'anima_child_slider_shortcode' );

/**
 * Theme supports and menu registration.
 */
function anima_child_setup(): void {
    register_nav_menus(
        array(
            'primary' => __( 'Primary Menu', 'twentytwentyfour-anima-child' ),
            'footer'  => __( 'Footer Menu', 'twentytwentyfour-anima-child' ),
        )
    );
}

/**
 * Enqueue child theme styles and scripts.
 */
function anima_child_enqueue_assets(): void {
    $theme      = wp_get_theme();
    $parent     = wp_get_theme( get_template() );
    $theme_ver  = $theme->get( 'Version' ) ?: '1.0.0';
    $parent_ver = $parent ? $parent->get( 'Version' ) : '1.0.0';

    wp_enqueue_style( 'twentytwentyfour-style', get_template_directory_uri() . '/style.css', array(), $parent_ver );
    wp_enqueue_style( 'twentytwentyfour-anima-child-style', get_stylesheet_uri(), array( 'twentytwentyfour-style' ), $theme_ver );

    wp_register_style( 'swiper', 'https://unpkg.com/swiper@10/swiper-bundle.min.css', array(), '10.3.1' );
    wp_register_script( 'swiper', 'https://unpkg.com/swiper@10/swiper-bundle.min.js', array(), '10.3.1', true );
    wp_register_script(
        'anima-slider',
        get_stylesheet_directory_uri() . '/assets/js/anima-slider.js',
        array( 'swiper' ),
        $theme_ver,
        true
    );
}

/**
 * Output wrapper opening markup for wider layouts.
 */
function anima_child_wrapper_open(): void {
    get_template_part( 'parts/wrapper', 'top' );
}

/**
 * Output wrapper closing markup.
 */
function anima_child_wrapper_close(): void {
    get_template_part( 'parts/wrapper', 'bottom' );
}

/**
 * Slider shortcode powered by Swiper.
 *
 * Usage: [anima_slider id="home"]
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function anima_child_slider_shortcode( array $atts ): string {
    $atts = shortcode_atts(
        array(
            'id' => '',
        ),
        $atts,
        'anima_slider'
    );

    $group = sanitize_title( $atts['id'] );

    $query_args = array(
        'post_type'      => 'slide',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        'orderby'        => 'meta_value_num',
        'meta_key'       => 'order',
        'order'          => 'ASC',
    );

    if ( $group ) {
        $query_args['tax_query'] = array(
            array(
                'taxonomy' => 'slide_group',
                'field'    => 'slug',
                'terms'    => $group,
            ),
        );
    }

    $slides = new WP_Query( $query_args );

    if ( ! $slides->have_posts() ) {
        wp_reset_postdata();
        return '';
    }

    wp_enqueue_style( 'swiper' );
    wp_enqueue_script( 'swiper' );
    wp_enqueue_script( 'anima-slider' );

    ob_start();
    ?>
    <div class="anima-slider" data-slider-group="<?php echo esc_attr( $group ); ?>">
        <div class="swiper" aria-roledescription="carousel">
            <div class="swiper-wrapper">
                <?php
                while ( $slides->have_posts() ) :
                    $slides->the_post();
                    $title     = get_the_title();
                    $excerpt   = get_the_excerpt();
                    $button    = anima_child_get_cta( get_the_ID() );
                    $thumbnail = get_post_thumbnail_id();
                    $image     = $thumbnail ? wp_get_attachment_image_src( $thumbnail, 'anima-hero-2x' ) : false;
                    $alt       = $thumbnail ? get_post_meta( $thumbnail, '_wp_attachment_image_alt', true ) : '';
                    ?>
                    <div class="swiper-slide">
                        <?php if ( $image ) : ?>
                            <img src="<?php echo esc_url( $image[0] ); ?>" width="<?php echo esc_attr( $image[1] ); ?>" height="<?php echo esc_attr( $image[2] ); ?>" loading="lazy" alt="<?php echo esc_attr( $alt ); ?>" />
                        <?php endif; ?>
                        <div class="slide-content">
                            <h3><?php echo esc_html( $title ); ?></h3>
                            <?php if ( $excerpt ) : ?>
                                <p><?php echo esc_html( wp_strip_all_tags( $excerpt ) ); ?></p>
                            <?php endif; ?>
                            <?php if ( $button ) : ?>
                                <a class="button-primary" href="<?php echo esc_url( $button['url'] ); ?>">
                                    <?php echo esc_html( $button['label'] ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="swiper-button-prev" aria-label="<?php esc_attr_e( 'Previous slide', 'twentytwentyfour-anima-child' ); ?>"></div>
            <div class="swiper-button-next" aria-label="<?php esc_attr_e( 'Next slide', 'twentytwentyfour-anima-child' ); ?>"></div>
        </div>
    </div>
    <?php
    wp_reset_postdata();

    return trim( ob_get_clean() );
}

/**
 * Helper to retrieve CTA data for slides.
 *
 * @param int $post_id Slide ID.
 * @return array|null
 */
function anima_child_get_cta( int $post_id ): ?array {
    $label = get_post_meta( $post_id, 'button_label', true );
    $url   = get_post_meta( $post_id, 'button_url', true );

    $label = trim( wp_strip_all_tags( (string) $label ) );
    $url   = esc_url_raw( (string) $url );

    if ( '' === $label || '' === $url ) {
        return null;
    }

    return array(
        'label' => $label,
        'url'   => $url,
    );
}
