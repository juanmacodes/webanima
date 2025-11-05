<?php

/**
 * Return the list of editable meta fields for the project CPT.
 *
 * @return array<string, string>
 */
function anima_core_meta_fields(): array {
    return [
        'client'   => __( 'Cliente', 'anima-core' ),
        'date'     => __( 'Fecha', 'anima-core' ),
        'services' => __( 'Servicios', 'anima-core' ),
        'kpis'     => __( 'KPIs', 'anima-core' ),
    ];
}

add_action(
    'add_meta_boxes',
    static function ( string $post_type ): void {
        if ( 'project' !== $post_type ) {
            return;
        }

        add_meta_box(
            'anima-project-details',
            __( 'Detalles del proyecto', 'anima-core' ),
            'anima_core_render_meta_box',
            'project',
            'normal',
            'default'
        );
    },
    10,
    1
);

/**
 * Render the project meta box.
 */
function anima_core_render_meta_box( \WP_Post $post ): void {
    wp_nonce_field( 'anima_core_save_meta', 'anima_core_nonce' );
    echo '<div class="anima-core-meta">';
    foreach ( anima_core_meta_fields() as $key => $label ) {
        $value = get_post_meta( $post->ID, "anima_{$key}", true );
        printf(
            '<p><label for="anima_%1$s"><strong>%2$s</strong></label><br/><input type="text" id="anima_%1$s" name="anima_%1$s" value="%3$s" class="widefat"/></p>',
            esc_attr( $key ),
            esc_html( $label ),
            esc_attr( $value )
        );
    }
    echo '</div>';
}

add_action(
    'save_post_project',
    static function ( int $post_id ): void {
        if ( ! isset( $_POST['anima_core_nonce'] ) ) {
            return;
        }

        $nonce = sanitize_text_field( wp_unslash( $_POST['anima_core_nonce'] ) );

        if ( ! wp_verify_nonce( $nonce, 'anima_core_save_meta' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        foreach ( anima_core_meta_fields() as $key => $label ) {
            if ( isset( $_POST[ "anima_{$key}" ] ) ) {
                update_post_meta(
                    $post_id,
                    "anima_{$key}",
                    sanitize_text_field( wp_unslash( $_POST[ "anima_{$key}" ] ) )
                );
            }
        }
    }
);
