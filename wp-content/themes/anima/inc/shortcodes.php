<?php
/**
 * Shortcodes personalizados.
 *
 * @package Anima
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_shortcode(
    'anima_world_loft',
    static function (): string {
        ob_start();
        ?>
        <div class="anima-world-embed" data-anima-world>
            <div id="anima-loft" class="anima-world-canvas" role="application" aria-label="<?php echo esc_attr__( 'Visor 3D Anima World', 'anima' ); ?>">
                <p class="anima-world-fallback"><?php esc_html_e( 'Tu navegador no soporta WebGL. Mira el recorrido en vídeo.', 'anima' ); ?>
                    <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" target="_blank" rel="noopener">YouTube</a>
                </p>
            </div>
        </div>
        <?php
        return trim( ob_get_clean() );
    }
);

add_shortcode(
    'waitlist_form',
    static function (): string {
        $nonce = wp_create_nonce( 'anima_waitlist' );
        ob_start();
        ?>
        <form class="anima-waitlist" data-anima-waitlist novalidate>
            <div class="anima-waitlist__field">
                <label for="anima-waitlist-name"><?php esc_html_e( 'Nombre', 'anima' ); ?></label>
                <input type="text" id="anima-waitlist-name" name="name" required autocomplete="name" />
            </div>
            <div class="anima-waitlist__field">
                <label for="anima-waitlist-email"><?php esc_html_e( 'Email', 'anima' ); ?></label>
                <input type="email" id="anima-waitlist-email" name="email" required autocomplete="email" />
            </div>
            <div class="anima-waitlist__field">
                <label for="anima-waitlist-network"><?php esc_html_e( 'Red principal', 'anima' ); ?></label>
                <select id="anima-waitlist-network" name="network" required>
                    <option value="">--</option>
                    <option value="twitch">Twitch</option>
                    <option value="tiktok">TikTok</option>
                    <option value="youtube">YouTube</option>
                    <option value="instagram">Instagram</option>
                    <option value="otro"><?php esc_html_e( 'Otra', 'anima' ); ?></option>
                </select>
            </div>
            <div class="anima-waitlist__field">
                <label for="anima-waitlist-country"><?php esc_html_e( 'País', 'anima' ); ?></label>
                <input type="text" id="anima-waitlist-country" name="country" required />
            </div>
            <div class="anima-waitlist__field anima-waitlist__field--checkbox">
                <input type="checkbox" id="anima-waitlist-beta" name="beta" value="1" />
                <label for="anima-waitlist-beta"><?php esc_html_e( 'Quiero acceso beta', 'anima' ); ?></label>
            </div>
            <div class="anima-waitlist__actions">
                <button type="submit" class="wp-block-button__link" data-text-default="<?php echo esc_attr__( 'Unirme a la lista de espera', 'anima' ); ?>">
                    <?php esc_html_e( 'Unirme a la lista de espera', 'anima' ); ?>
                </button>
            </div>
            <div class="anima-waitlist__feedback" role="status" aria-live="polite"></div>
            <input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>" />
        </form>
        <?php
        return trim( ob_get_clean() );
    }
);

add_shortcode(
    'app_badges',
    static function (): string {
        ob_start();
        ?>
        <div class="anima-app-badges">
            <a class="anima-app-badge" href="#" rel="nofollow noopener" aria-label="App Store">
                <span class="anima-app-badge__label">App Store</span>
            </a>
            <a class="anima-app-badge" href="#" rel="nofollow noopener" aria-label="Google Play">
                <span class="anima-app-badge__label">Google Play</span>
            </a>
        </div>
        <?php
        return trim( ob_get_clean() );
    }
);
add_shortcode(
    'anima_year',
    static function (): string {
        return (string) wp_date( 'Y' );
    }
);
add_shortcode(
    'anima_meta',
    static function ( $atts ) {
        $atts = shortcode_atts( [ 'key' => '' ], $atts, 'anima_meta' );
        $key  = sanitize_key( $atts['key'] );
        if ( empty( $key ) ) {
            return '';
        }
        $value = get_post_meta( get_the_ID(), $key, true );
        if ( is_array( $value ) ) {
            return esc_html( wp_json_encode( $value ) );
        }
        return esc_html( (string) $value );
    }
);

add_shortcode(
    'anima_kpis',
    static function (): string {
        $items = get_post_meta( get_the_ID(), 'anima_kpis', true );
        if ( empty( $items ) || ! is_array( $items ) ) {
            return '';
        }
        $html = '<ul class="anima-kpis">';
        foreach ( $items as $item ) {
            $label = isset( $item['label'] ) ? esc_html( $item['label'] ) : '';
            $value = isset( $item['value'] ) ? esc_html( $item['value'] ) : '';
            $html .= '<li><strong>' . $value . '</strong> ' . $label . '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
);

add_shortcode(
    'anima_gallery',
    static function (): string {
        $ids = get_post_meta( get_the_ID(), 'anima_galeria', true );
        if ( empty( $ids ) || ! is_array( $ids ) ) {
            return '';
        }
        $html = '<div class="anima-gallery" role="list">';
        foreach ( $ids as $id ) {
            $html .= wp_get_attachment_image( (int) $id, 'large', false, [ 'loading' => 'lazy', 'class' => 'anima-gallery__item' ] );
        }
        $html .= '</div>';
        return $html;
    }
);

add_shortcode(
    'anima_course_ficha',
    static function (): string {
        $id        = get_the_ID();
        $nivel     = wp_get_post_terms( $id, 'nivel', [ 'fields' => 'names' ] );
        $modalidad = wp_get_post_terms( $id, 'modalidad', [ 'fields' => 'names' ] );
        $duracion  = get_post_meta( $id, 'anima_duracion', true );
        $precio    = get_post_meta( $id, 'anima_precio', true );
        $html      = '<dl class="anima-course-ficha">';
        if ( ! empty( $nivel ) ) {
            $html .= '<dt>' . esc_html__( 'Nivel', 'anima' ) . '</dt><dd>' . esc_html( implode( ', ', $nivel ) ) . '</dd>';
        }
        if ( $duracion ) {
            $html .= '<dt>' . esc_html__( 'Duración', 'anima' ) . '</dt><dd>' . esc_html( $duracion ) . 'h</dd>';
        }
        if ( ! empty( $modalidad ) ) {
            $html .= '<dt>' . esc_html__( 'Modalidad', 'anima' ) . '</dt><dd>' . esc_html( implode( ', ', $modalidad ) ) . '</dd>';
        }
        if ( $precio ) {
            $html .= '<dt>' . esc_html__( 'Precio', 'anima' ) . '</dt><dd>€' . esc_html( $precio ) . '</dd>';
        }
        $html .= '</dl>';
        return $html;
    }
);

add_shortcode(
    'anima_course_instructors',
    static function (): string {
        $items = get_post_meta( get_the_ID(), 'anima_instructores', true );
        if ( empty( $items ) || ! is_array( $items ) ) {
            return '';
        }
        $html = '<div class="anima-instructors">';
        foreach ( $items as $item ) {
            $name = isset( $item['nombre'] ) ? esc_html( $item['nombre'] ) : '';
            $bio  = isset( $item['bio'] ) ? wp_kses_post( $item['bio'] ) : '';
            $avatar = '';
            if ( ! empty( $item['avatar'] ) ) {
                $avatar = wp_get_attachment_image( (int) $item['avatar'], 'thumbnail', false, [ 'class' => 'anima-instructors__avatar' ] );
            }
            $html .= '<article class="anima-instructor">' . $avatar . '<div><h3>' . $name . '</h3><p>' . $bio . '</p></div></article>';
        }
        $html .= '</div>';
        return $html;
    }
);

add_shortcode(
    'anima_course_dates',
    static function (): string {
        $dates = get_post_meta( get_the_ID(), 'anima_fechas', true );
        if ( empty( $dates ) || ! is_array( $dates ) ) {
            return '';
        }
        $html = '<ul class="anima-course-dates">';
        foreach ( $dates as $date ) {
            $html .= '<li>' . esc_html( $date ) . '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
);
add_shortcode(
    'anima_course_curriculum',
    static function (): string {
        $items = get_post_meta( get_the_ID(), 'anima_temario', true );
        if ( empty( $items ) || ! is_array( $items ) ) {
            return '';
        }
        $html = '<div class="anima-curriculum">';
        foreach ( $items as $item ) {
            $title = isset( $item['titulo'] ) ? esc_html( $item['titulo'] ) : '';
            $lessons = isset( $item['lecciones'] ) && is_array( $item['lecciones'] ) ? $item['lecciones'] : [];
            $html .= '<details><summary>' . $title . '</summary>';
            if ( $lessons ) {
                $html .= '<ul>';
                foreach ( $lessons as $lesson ) {
                    $html .= '<li>' . esc_html( $lesson ) . '</li>';
                }
                $html .= '</ul>';
            }
            $html .= '</details>';
        }
        $html .= '</div>';
        return $html;
    }
);
add_shortcode(
    'anima_taxonomy_links',
    static function ( $atts ): string {
        $atts = shortcode_atts( [ 'taxonomy' => '' ], $atts, 'anima_taxonomy_links' );
        $taxonomy = sanitize_key( $atts['taxonomy'] );
        if ( empty( $taxonomy ) ) {
            return '';
        }
        $terms = get_terms( [ 'taxonomy' => $taxonomy, 'hide_empty' => true ] );
        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }
        $html = '<ul class="anima-taxonomy-links">';
        foreach ( $terms as $term ) {
            $html .= '<li><a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }
);

