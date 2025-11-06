<?php
/**
 * Metacampos personalizados para los CPTs de Anima.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function anima_register_meta_boxes() {
    add_action( 'add_meta_boxes', 'anima_add_content_meta_box' );
    add_action( 'save_post', 'anima_save_content_meta' );
}

function anima_add_content_meta_box() {
    $screens = array( 'curso', 'avatar', 'proyecto', 'experiencia' );

    foreach ( $screens as $screen ) {
        add_meta_box(
            'anima_content_details',
            __( 'Detalles de Anima', 'anima-core' ),
            'anima_render_content_meta_box',
            $screen,
            'normal',
            'default'
        );
    }
}

function anima_render_content_meta_box( $post ) {
    wp_nonce_field( 'anima_save_content_meta', 'anima_content_meta_nonce' );

    $instructores = get_post_meta( $post->ID, 'anima_instructores', true );
    $duracion     = get_post_meta( $post->ID, 'anima_duracion', true );
    $nivel        = get_post_meta( $post->ID, 'anima_nivel', true );
    $modalidad    = get_post_meta( $post->ID, 'anima_modalidad', true );
    $kpis         = get_post_meta( $post->ID, 'anima_kpis', true );
    $demo_url     = get_post_meta( $post->ID, 'anima_demo_url', true );
    $destacado    = get_post_meta( $post->ID, 'anima_destacado', true );
    ?>
    <p>
        <label for="anima_instructores"><strong><?php esc_html_e( 'Instructores', 'anima-core' ); ?></strong></label><br />
        <input type="text" id="anima_instructores" name="anima_instructores" value="<?php echo esc_attr( $instructores ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'Ej: Ana Vega, Juan Pérez', 'anima-core' ); ?>" />
    </p>
    <p>
        <label for="anima_duracion"><strong><?php esc_html_e( 'Duración', 'anima-core' ); ?></strong></label><br />
        <input type="text" id="anima_duracion" name="anima_duracion" value="<?php echo esc_attr( $duracion ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'Ej: 6 semanas · 24 horas', 'anima-core' ); ?>" />
    </p>
    <?php if ( 'curso' === $post->post_type ) : ?>
        <p>
            <label for="anima_nivel"><strong><?php esc_html_e( 'Nivel', 'anima-core' ); ?></strong></label><br />
            <input type="text" id="anima_nivel" name="anima_nivel" value="<?php echo esc_attr( $nivel ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'Ej: Principiante, Intermedio, Avanzado', 'anima-core' ); ?>" />
        </p>
        <p>
            <label for="anima_modalidad"><strong><?php esc_html_e( 'Modalidad', 'anima-core' ); ?></strong></label><br />
            <input type="text" id="anima_modalidad" name="anima_modalidad" value="<?php echo esc_attr( $modalidad ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'Ej: Online en vivo, On-demand, Híbrido', 'anima-core' ); ?>" />
        </p>
    <?php endif; ?>
    <p>
        <label for="anima_kpis"><strong><?php esc_html_e( 'KPIs o logros', 'anima-core' ); ?></strong></label><br />
        <textarea id="anima_kpis" name="anima_kpis" class="widefat" rows="4" placeholder="<?php esc_attr_e( "Ej: 1200 estudiantes graduados\n95% satisfacción", 'anima-core' ); ?>"><?php echo esc_textarea( $kpis ); ?></textarea>
    </p>
    <p>
        <label for="anima_demo_url"><strong><?php esc_html_e( 'URL de demo inmersiva', 'anima-core' ); ?></strong></label><br />
        <input type="url" id="anima_demo_url" name="anima_demo_url" value="<?php echo esc_attr( $demo_url ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'https://ejemplo.com/demo', 'anima-core' ); ?>" />
    </p>
    <p>
        <label>
            <input type="checkbox" name="anima_destacado" value="1" <?php checked( $destacado, '1' ); ?> />
            <?php esc_html_e( 'Marcar como curso destacado', 'anima-core' ); ?>
        </label>
    </p>
    <?php
}

function anima_save_content_meta( $post_id ) {
    if ( ! isset( $_POST['anima_content_meta_nonce'] ) || ! wp_verify_nonce( $_POST['anima_content_meta_nonce'], 'anima_save_content_meta' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['post_type'] ) && ! current_user_can( 'edit_' . sanitize_key( $_POST['post_type'] ), $post_id ) ) {
        return;
    }

    $fields = array(
        'anima_instructores' => 'sanitize_text_field',
        'anima_duracion'     => 'sanitize_text_field',
        'anima_kpis'         => 'anima_sanitize_multiline_text',
        'anima_demo_url'     => 'esc_url_raw',
        'anima_nivel'        => 'sanitize_text_field',
        'anima_modalidad'    => 'sanitize_text_field',
    );

    foreach ( $fields as $field => $sanitizer ) {
        if ( isset( $_POST[ $field ] ) ) {
            $value = call_user_func( $sanitizer, wp_unslash( $_POST[ $field ] ) );
            update_post_meta( $post_id, $field, $value );
        } else {
            delete_post_meta( $post_id, $field );
        }
    }

    $is_featured = isset( $_POST['anima_destacado'] ) && '1' === $_POST['anima_destacado'] ? '1' : '';

    if ( $is_featured ) {
        update_post_meta( $post_id, 'anima_destacado', '1' );
    } else {
        delete_post_meta( $post_id, 'anima_destacado' );
    }
}

function anima_sanitize_multiline_text( $value ) {
    return sanitize_textarea_field( $value );
}
