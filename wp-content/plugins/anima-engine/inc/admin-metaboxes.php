<?php
/**
 * Metabox para gestionar los detalles del curso.
 */

defined('ABSPATH') || exit;

add_action(
    'add_meta_boxes',
    static function () {
        add_meta_box(
            'anima_engine_curso_details',
            __( 'Detalles del curso', 'anima-engine' ),
            static function ( \WP_Post $post ) {
                wp_nonce_field( 'anima_engine_save_curso_details', 'anima_engine_curso_nonce' );

                $price        = get_post_meta( $post->ID, 'anima_price', true );
                $hours        = get_post_meta( $post->ID, 'anima_duration_hours', true );
                $requirements = get_post_meta( $post->ID, 'anima_requirements', true );
                $dates        = get_post_meta( $post->ID, 'anima_upcoming_dates', true );
                $syllabus     = get_post_meta( $post->ID, 'anima_syllabus', true );
                $instructors  = get_post_meta( $post->ID, 'anima_instructors', true );

                $dates_json       = is_array( $dates ) ? wp_json_encode( $dates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) : (string) $dates;
                $syllabus_json    = is_array( $syllabus ) ? wp_json_encode( $syllabus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) : (string) $syllabus;
                $instructors_json = is_array( $instructors ) ? wp_json_encode( $instructors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) : (string) $instructors;
                ?>
                <p>
                    <label for="anima_price"><strong><?php esc_html_e( 'Precio (€)', 'anima-engine' ); ?></strong></label><br />
                    <input type="number" step="0.01" min="0" id="anima_price" name="anima_price" value="<?php echo esc_attr( $price ); ?>" class="widefat" />
                </p>
                <p>
                    <label for="anima_duration_hours"><strong><?php esc_html_e( 'Duración (horas)', 'anima-engine' ); ?></strong></label><br />
                    <input type="number" step="1" min="0" id="anima_duration_hours" name="anima_duration_hours" value="<?php echo esc_attr( $hours ); ?>" class="widefat" />
                </p>
                <p>
                    <label for="anima_requirements"><strong><?php esc_html_e( 'Requisitos', 'anima-engine' ); ?></strong></label><br />
                    <textarea id="anima_requirements" name="anima_requirements" class="widefat" rows="4"><?php echo esc_textarea( (string) $requirements ); ?></textarea>
                    <span class="description"><?php esc_html_e( 'Puedes usar formato HTML básico para listas o énfasis.', 'anima-engine' ); ?></span>
                </p>
                <p>
                    <label for="anima_upcoming_dates"><strong><?php esc_html_e( 'Próximas fechas (JSON)', 'anima-engine' ); ?></strong></label><br />
                    <textarea id="anima_upcoming_dates" name="anima_upcoming_dates" class="widefat code" rows="4"><?php echo esc_textarea( $dates_json ); ?></textarea>
                    <span class="description"><?php esc_html_e( 'Ejemplo: ["2024-09-10","2024-11-05"]', 'anima-engine' ); ?></span>
                </p>
                <p>
                    <label for="anima_syllabus"><strong><?php esc_html_e( 'Temario (JSON)', 'anima-engine' ); ?></strong></label><br />
                    <textarea id="anima_syllabus" name="anima_syllabus" class="widefat code" rows="5"><?php echo esc_textarea( $syllabus_json ); ?></textarea>
                    <span class="description"><?php esc_html_e( 'Ejemplo: [{"title":"Módulo 1","lessons":["Introducción","Setup"]}]', 'anima-engine' ); ?></span>
                </p>
                <p>
                    <label for="anima_instructors"><strong><?php esc_html_e( 'Instructores (JSON)', 'anima-engine' ); ?></strong></label><br />
                    <textarea id="anima_instructors" name="anima_instructors" class="widefat code" rows="5"><?php echo esc_textarea( $instructors_json ); ?></textarea>
                    <span class="description"><?php esc_html_e( 'Ejemplo: [{"name":"Ana","bio":"Productora XR","avatar_url":"https://..."}]', 'anima-engine' ); ?></span>
                </p>
                <?php
            },
            'curso',
            'normal',
            'default'
        );
    }
);

add_action(
    'save_post_curso',
    static function ( int $post_id, \WP_Post $post ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( ! isset( $_POST['anima_engine_curso_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['anima_engine_curso_nonce'] ) ), 'anima_engine_save_curso_details' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $price  = isset( $_POST['anima_price'] ) ? floatval( str_replace( ',', '.', sanitize_text_field( wp_unslash( $_POST['anima_price'] ) ) ) ) : null;
        $hours  = isset( $_POST['anima_duration_hours'] ) ? absint( sanitize_text_field( wp_unslash( $_POST['anima_duration_hours'] ) ) ) : null;
        $req    = isset( $_POST['anima_requirements'] ) ? wp_kses_post( wp_unslash( $_POST['anima_requirements'] ) ) : null;
        $dates  = isset( $_POST['anima_upcoming_dates'] ) ? anima_engine_sanitize_json_array( wp_unslash( $_POST['anima_upcoming_dates'] ), 'date' ) : null;
        $syll   = isset( $_POST['anima_syllabus'] ) ? anima_engine_sanitize_syllabus( wp_unslash( $_POST['anima_syllabus'] ) ) : null;
        $instr  = isset( $_POST['anima_instructors'] ) ? anima_engine_sanitize_instructors( wp_unslash( $_POST['anima_instructors'] ) ) : null;

        anima_engine_update_meta_value( $post_id, 'anima_price', $price );
        anima_engine_update_meta_value( $post_id, 'anima_duration_hours', $hours );
        anima_engine_update_meta_value( $post_id, 'anima_requirements', $req );
        anima_engine_update_meta_value( $post_id, 'anima_upcoming_dates', $dates );
        anima_engine_update_meta_value( $post_id, 'anima_syllabus', $syll );
        anima_engine_update_meta_value( $post_id, 'anima_instructors', $instr );
    },
    10,
    2
);

if ( ! function_exists( 'anima_engine_update_meta_value' ) ) {
    /**
     * Actualiza un meta eliminándolo si está vacío.
     *
     * @param int         $post_id ID del post.
     * @param string      $key     Meta key.
     * @param mixed|null  $value   Valor a guardar.
     */
    function anima_engine_update_meta_value( int $post_id, string $key, $value ): void {
        if ( null === $value || '' === $value || ( is_array( $value ) && [] === $value ) ) {
            delete_post_meta( $post_id, $key );
            return;
        }

        update_post_meta( $post_id, $key, $value );
    }
}

if ( ! function_exists( 'anima_engine_sanitize_json_array' ) ) {
    /**
     * Sanea un JSON simple a array de strings.
     */
    function anima_engine_sanitize_json_array( string $raw, string $type = 'text' ): array {
        $decoded = json_decode( $raw, true );
        if ( ! is_array( $decoded ) ) {
            return [];
        }

        $sanitized = [];
        foreach ( $decoded as $item ) {
            if ( 'date' === $type ) {
                $item = sanitize_text_field( (string) $item );
                if ( preg_match( '/^\\d{4}-\\d{2}-\\d{2}$/', $item ) ) {
                    $sanitized[] = $item;
                }
            } else {
                $sanitized[] = sanitize_text_field( (string) $item );
            }
        }

        return $sanitized;
    }
}

if ( ! function_exists( 'anima_engine_sanitize_syllabus' ) ) {
    /**
     * Sanea la estructura del temario.
     */
    function anima_engine_sanitize_syllabus( string $raw ): array {
        $decoded = json_decode( $raw, true );
        if ( ! is_array( $decoded ) ) {
            return [];
        }

        $sanitized = [];
        foreach ( $decoded as $module ) {
            if ( ! is_array( $module ) ) {
                continue;
            }

            $title   = sanitize_text_field( $module['title'] ?? '' );
            $lessons = [];

            if ( isset( $module['lessons'] ) && is_array( $module['lessons'] ) ) {
                foreach ( $module['lessons'] as $lesson ) {
                    $lessons[] = sanitize_text_field( (string) $lesson );
                }
            }

            if ( '' === $title && [] === $lessons ) {
                continue;
            }

            $sanitized[] = [
                'title'   => $title,
                'lessons' => $lessons,
            ];
        }

        return $sanitized;
    }
}

if ( ! function_exists( 'anima_engine_sanitize_instructors' ) ) {
    /**
     * Sanea la estructura de instructores.
     */
    function anima_engine_sanitize_instructors( string $raw ): array {
        $decoded = json_decode( $raw, true );
        if ( ! is_array( $decoded ) ) {
            return [];
        }

        $sanitized = [];
        foreach ( $decoded as $instructor ) {
            if ( ! is_array( $instructor ) ) {
                continue;
            }

            $name  = sanitize_text_field( $instructor['name'] ?? '' );
            $bio   = wp_kses_post( $instructor['bio'] ?? '' );
            $avatar = esc_url_raw( $instructor['avatar_url'] ?? '' );

            if ( '' === $name && '' === $bio && '' === $avatar ) {
                continue;
            }

            $sanitized[] = [
                'name'       => $name,
                'bio'        => $bio,
                'avatar_url' => $avatar,
            ];
        }

        return $sanitized;
    }
}
