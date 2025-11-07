<?php
namespace Anima\Engine\Metaboxes;

use Anima\Engine\Services\ServiceInterface;

use function __;
use function current_user_can;
use function delete_post_meta;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_textarea;
use function esc_url;
use function esc_url_raw;
use function get_post_meta;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function update_post_meta;
use function wp_nonce_field;
use function wp_unslash;

/**
 * Registro de metaboxes personalizados.
 */
class RegisterMetaboxes implements ServiceInterface {
    /**
     * {@inheritDoc}
     */
    public function register(): void {
        add_action( 'add_meta_boxes', [ $this, 'register_boxes' ] );
        add_action( 'save_post', [ $this, 'save_meta' ], 10, 2 );
    }

    /**
     * Añade las cajas meta.
     */
    public function register_boxes( string $post_type ): void {
        $general_post_types = [ 'avatar', 'proyecto', 'experiencia' ];

        if ( in_array( $post_type, $general_post_types, true ) ) {
            add_meta_box(
                'anima_engine_general_meta',
                __( 'Detalles de la experiencia', 'anima-engine' ),
                [ $this, 'render_general_meta' ],
                $post_type,
                'normal',
                'default'
            );
        }

        if ( 'slide' === $post_type ) {
            add_meta_box(
                'anima_engine_slide_meta',
                __( 'Enlace del botón', 'anima-engine' ),
                [ $this, 'render_slide_meta' ],
                $post_type,
                'normal',
                'default'
            );
        }
    }

    /**
     * Renderiza la metabox general.
     */
    public function render_general_meta( \WP_Post $post ): void { // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_post_class
        wp_nonce_field( 'anima_engine_general_meta', 'anima_engine_general_nonce' );

        $fields = [
            'anima_instructores' => [
                'label' => __( 'Instructores', 'anima-engine' ),
                'type'  => 'text',
            ],
            'anima_duracion'     => [
                'label' => __( 'Duración estimada', 'anima-engine' ),
                'type'  => 'text',
            ],
            'anima_dificultad'   => [
                'label' => __( 'Nivel de dificultad', 'anima-engine' ),
                'type'  => 'text',
            ],
            'anima_kpis'         => [
                'label' => __( 'KPIs o resultados clave', 'anima-engine' ),
                'type'  => 'textarea',
            ],
            'anima_url_demo'     => [
                'label' => __( 'URL de demo o streaming', 'anima-engine' ),
                'type'  => 'url',
            ],
        ];

        echo '<div class="anima-engine-meta">';

        foreach ( $fields as $key => $field ) {
            $value = get_post_meta( $post->ID, $key, true );
            echo '<p>';
            echo '<label for="' . esc_attr( $key ) . '"><strong>' . esc_html( $field['label'] ) . '</strong></label><br />';

            if ( 'textarea' === $field['type'] ) {
                echo '<textarea style="width:100%;min-height:120px" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">' . esc_textarea( (string) $value ) . '</textarea>';
            } else {
                $type = 'url' === $field['type'] ? 'url' : 'text';
                echo '<input style="width:100%" type="' . esc_attr( $type ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( (string) $value ) . '" />';
            }

            echo '</p>';
        }

        echo '</div>';
    }

    /**
     * Renderiza metabox para slides.
     */
    public function render_slide_meta( \WP_Post $post ): void { // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_post_class
        wp_nonce_field( 'anima_engine_slide_meta', 'anima_engine_slide_nonce' );

        $value = get_post_meta( $post->ID, 'anima_slide_url', true );
        echo '<p>';
        echo '<label for="anima_slide_url"><strong>' . esc_html__( 'URL del botón del slider', 'anima-engine' ) . '</strong></label><br />';
        echo '<input style="width:100%" type="url" id="anima_slide_url" name="anima_slide_url" value="' . esc_attr( (string) $value ) . '" />';
        echo '</p>';
    }

    /**
     * Guarda la información enviada.
     */
    public function save_meta( int $post_id, \WP_Post $post ): void { // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_post_class
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['anima_engine_general_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anima_engine_general_nonce'] ) ), 'anima_engine_general_meta' ) ) {
            $this->save_general_meta( $post_id );
        }

        if ( isset( $_POST['anima_engine_slide_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anima_engine_slide_nonce'] ) ), 'anima_engine_slide_meta' ) ) {
            $this->save_slide_meta( $post_id );
        }
    }

    /**
     * Guarda los campos generales.
     */
    protected function save_general_meta( int $post_id ): void {
        $map = [
            'anima_instructores' => 'sanitize_text_field',
            'anima_duracion'     => 'sanitize_text_field',
            'anima_dificultad'   => 'sanitize_text_field',
            'anima_kpis'         => 'sanitize_textarea_field',
            'anima_url_demo'     => 'esc_url_raw',
        ];

        foreach ( $map as $key => $callback ) {
            if ( isset( $_POST[ $key ] ) ) {
                $raw = wp_unslash( $_POST[ $key ] );
                if ( 'sanitize_textarea_field' === $callback ) {
                    $value = sanitize_textarea_field( $raw );
                } elseif ( 'esc_url_raw' === $callback ) {
                    $value = esc_url_raw( $raw );
                } else {
                    $value = sanitize_text_field( $raw );
                }

                if ( '' === $value ) {
                    delete_post_meta( $post_id, $key );
                } else {
                    update_post_meta( $post_id, $key, $value );
                }
            }
        }
    }

    /**
     * Guarda el campo de slide.
     */
    protected function save_slide_meta( int $post_id ): void {
        if ( isset( $_POST['anima_slide_url'] ) ) {
            $value = esc_url_raw( wp_unslash( $_POST['anima_slide_url'] ) );
            if ( '' === $value ) {
                delete_post_meta( $post_id, 'anima_slide_url' );
            } else {
                update_post_meta( $post_id, 'anima_slide_url', $value );
            }
        }
    }
}
