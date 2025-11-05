<?php
/**
 * Custom post types and taxonomies for Anima Core.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'anima_core_register_cpts' );
add_action( 'init', 'anima_core_register_taxonomies' );

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
    add_action( 'add_meta_boxes', 'anima_core_register_meta_boxes' );
    add_action( 'save_post', 'anima_core_save_meta_boxes', 10, 2 );
}

/**
 * Register custom post types.
 */
function anima_core_register_cpts(): void {
    register_post_type(
        'project',
        array(
            'labels' => array(
                'name'          => __( 'Projects', 'anima-core' ),
                'singular_name' => __( 'Project', 'anima-core' ),
            ),
            'public'       => true,
            'show_in_rest' => true,
            'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
            'menu_icon'    => 'dashicons-art',
            'rewrite'      => array( 'slug' => 'projects' ),
        )
    );

    register_post_type(
        'course',
        array(
            'labels' => array(
                'name'          => __( 'Courses', 'anima-core' ),
                'singular_name' => __( 'Course', 'anima-core' ),
            ),
            'public'       => true,
            'show_in_rest' => true,
            'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
            'menu_icon'    => 'dashicons-welcome-learn-more',
            'rewrite'      => array( 'slug' => 'courses' ),
        )
    );

    register_post_type(
        'slide',
        array(
            'labels' => array(
                'name'          => __( 'Slides', 'anima-core' ),
                'singular_name' => __( 'Slide', 'anima-core' ),
            ),
            'public'       => true,
            'show_in_rest' => true,
            'supports'     => array( 'title', 'excerpt', 'thumbnail', 'custom-fields' ),
            'menu_icon'    => 'dashicons-images-alt2',
            'rewrite'      => array( 'slug' => 'slides' ),
        )
    );

    register_post_type(
        'hotspot',
        array(
            'labels' => array(
                'name'          => __( 'Hotspots', 'anima-core' ),
                'singular_name' => __( 'Hotspot', 'anima-core' ),
            ),
            'public'       => true,
            'show_in_rest' => true,
            'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
            'menu_icon'    => 'dashicons-location',
            'rewrite'      => array( 'slug' => 'hotspots' ),
        )
    );
}

/**
 * Register custom taxonomies.
 */
function anima_core_register_taxonomies(): void {
    register_taxonomy(
        'project_type',
        array( 'project' ),
        array(
            'labels' => array(
                'name'          => __( 'Project Types', 'anima-core' ),
                'singular_name' => __( 'Project Type', 'anima-core' ),
            ),
            'show_in_rest' => true,
            'public'       => true,
            'hierarchical' => false,
            'rewrite'      => array( 'slug' => 'project-type' ),
        )
    );

    register_taxonomy(
        'slide_group',
        array( 'slide' ),
        array(
            'labels' => array(
                'name'          => __( 'Slide Groups', 'anima-core' ),
                'singular_name' => __( 'Slide Group', 'anima-core' ),
            ),
            'show_in_rest' => true,
            'public'       => true,
            'hierarchical' => false,
            'rewrite'      => array( 'slug' => 'slide-group' ),
        )
    );
}

/**
 * Return meta field definitions for CPTs.
 *
 * @return array
 */
function anima_core_meta_field_definitions(): array {
    return array(
        'project' => array(
            'client'    => array(
                'label'       => __( 'Client', 'anima-core' ),
                'type'        => 'text',
                'description' => __( 'Client name for the project.', 'anima-core' ),
            ),
            'year'      => array(
                'label'       => __( 'Year', 'anima-core' ),
                'type'        => 'number',
                'description' => __( 'Year the project was delivered.', 'anima-core' ),
            ),
            'cta_label' => array(
                'label'       => __( 'CTA Label', 'anima-core' ),
                'type'        => 'text',
                'description' => __( 'Call to action label.', 'anima-core' ),
            ),
            'cta_url'   => array(
                'label'       => __( 'CTA URL', 'anima-core' ),
                'type'        => 'url',
                'description' => __( 'Call to action URL.', 'anima-core' ),
            ),
            'gallery'   => array(
                'label'       => __( 'Gallery URLs', 'anima-core' ),
                'type'        => 'textarea',
                'description' => __( 'One image URL per line.', 'anima-core' ),
                'multiple'    => true,
                'sanitize'    => 'url',
            ),
        ),
        'course'  => array(
            'level'       => array(
                'label'       => __( 'Level', 'anima-core' ),
                'type'        => 'select',
                'description' => __( 'Difficulty level.', 'anima-core' ),
                'options'     => array( 'Intro', 'Intermedio', 'Avanzado' ),
            ),
            'modality'    => array(
                'label'       => __( 'Modality', 'anima-core' ),
                'type'        => 'select',
                'description' => __( 'Delivery modality.', 'anima-core' ),
                'options'     => array( 'Online', 'Presencial', 'Híbrido' ),
            ),
            'duration'    => array(
                'label'       => __( 'Duration', 'anima-core' ),
                'type'        => 'text',
                'description' => __( 'Duration description (e.g. 8h).', 'anima-core' ),
            ),
            'price'       => array(
                'label'       => __( 'Price', 'anima-core' ),
                'type'        => 'number',
                'description' => __( 'Numeric price without currency symbol.', 'anima-core' ),
            ),
            'cta_label'   => array(
                'label'       => __( 'CTA Label', 'anima-core' ),
                'type'        => 'text',
                'description' => __( 'Call to action label.', 'anima-core' ),
            ),
            'cta_url'     => array(
                'label'       => __( 'CTA URL', 'anima-core' ),
                'type'        => 'url',
                'description' => __( 'Call to action URL.', 'anima-core' ),
            ),
            'syllabus'    => array(
                'label'       => __( 'Syllabus Items', 'anima-core' ),
                'type'        => 'textarea',
                'description' => __( 'One syllabus item per line.', 'anima-core' ),
                'multiple'    => true,
                'sanitize'    => 'text',
            ),
        ),
        'slide'   => array(
            'button_label' => array(
                'label'       => __( 'Button Label', 'anima-core' ),
                'type'        => 'text',
                'description' => __( 'Optional button label.', 'anima-core' ),
            ),
            'button_url'   => array(
                'label'       => __( 'Button URL', 'anima-core' ),
                'type'        => 'url',
                'description' => __( 'Optional button link.', 'anima-core' ),
            ),
            'order'        => array(
                'label'       => __( 'Slide Order', 'anima-core' ),
                'type'        => 'number',
                'description' => __( 'Manual ordering (lower numbers first).', 'anima-core' ),
            ),
        ),
        'hotspot' => array(
            'x'            => array(
                'label'       => __( 'Position X', 'anima-core' ),
                'type'        => 'number',
                'step'        => '0.01',
                'description' => __( 'Horizontal axis.', 'anima-core' ),
            ),
            'y'            => array(
                'label'       => __( 'Position Y', 'anima-core' ),
                'type'        => 'number',
                'step'        => '0.01',
                'description' => __( 'Vertical axis.', 'anima-core' ),
            ),
            'z'            => array(
                'label'       => __( 'Position Z', 'anima-core' ),
                'type'        => 'number',
                'step'        => '0.01',
                'description' => __( 'Depth axis.', 'anima-core' ),
            ),
            'look_at_x'    => array(
                'label'       => __( 'Look At X', 'anima-core' ),
                'type'        => 'number',
                'step'        => '0.01',
                'description' => __( 'Optional look-at horizontal axis.', 'anima-core' ),
            ),
            'look_at_y'    => array(
                'label'       => __( 'Look At Y', 'anima-core' ),
                'type'        => 'number',
                'step'        => '0.01',
                'description' => __( 'Optional look-at vertical axis.', 'anima-core' ),
            ),
            'look_at_z'    => array(
                'label'       => __( 'Look At Z', 'anima-core' ),
                'type'        => 'number',
                'step'        => '0.01',
                'description' => __( 'Optional look-at depth axis.', 'anima-core' ),
            ),
            'action_label' => array(
                'label'       => __( 'Action Label', 'anima-core' ),
                'type'        => 'text',
                'description' => __( 'Call to action label.', 'anima-core' ),
            ),
            'action_href'  => array(
                'label'       => __( 'Action URL', 'anima-core' ),
                'type'        => 'url',
                'description' => __( 'Call to action target URL.', 'anima-core' ),
            ),
            'action_type'  => array(
                'label'       => __( 'Action Type', 'anima-core' ),
                'type'        => 'select',
                'options'     => array( 'link', 'modal' ),
                'description' => __( 'Defines how the action behaves.', 'anima-core' ),
            ),
            'media_image'  => array(
                'label'       => __( 'Media Image URL', 'anima-core' ),
                'type'        => 'url',
                'description' => __( 'Optional image preview URL.', 'anima-core' ),
            ),
            'media_video'  => array(
                'label'       => __( 'Media Video URL', 'anima-core' ),
                'type'        => 'url',
                'description' => __( 'Optional video URL.', 'anima-core' ),
            ),
        ),
    );
}

/**
 * Register classic meta boxes when ACF is not active.
 */
function anima_core_register_meta_boxes(): void {
    $definitions = anima_core_meta_field_definitions();

    foreach ( $definitions as $post_type => $fields ) {
        add_meta_box(
            'anima_core_' . $post_type . '_meta',
            __( 'Anima Details', 'anima-core' ),
            'anima_core_render_meta_box',
            $post_type,
            'normal',
            'default',
            array(
                'fields'     => $fields,
                'post_type'  => $post_type,
            )
        );
    }
}

/**
 * Render meta box fields.
 *
 * @param WP_Post $post Current post.
 * @param array   $args Callback arguments.
 */
function anima_core_render_meta_box( WP_Post $post, array $args ): void {
    $fields = $args['args']['fields'] ?? array();

    wp_nonce_field( 'anima_core_meta_' . $post->post_type, 'anima_core_meta_nonce' );

    echo '<div class="anima-core-meta-fields">';

    foreach ( $fields as $key => $field ) {
        $value = get_post_meta( $post->ID, $key, true );
        $label = esc_html( $field['label'] );
        $description = isset( $field['description'] ) ? wp_kses_post( $field['description'] ) : '';

        echo '<p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<label for="' . esc_attr( $key ) . '"><strong>' . $label . '</strong></label><br />';

        $type = $field['type'];
        $attributes = '';
        if ( isset( $field['step'] ) ) {
            $attributes .= ' step="' . esc_attr( $field['step'] ) . '"';
        }

        switch ( $type ) {
            case 'textarea':
                $text_value = is_array( $value ) ? implode( "\n", array_map( 'sanitize_text_field', $value ) ) : (string) $value;
                echo '<textarea class="widefat" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="4">' . esc_textarea( $text_value ) . '</textarea>';
                break;
            case 'select':
                echo '<select class="widefat" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
                echo '<option value="">' . esc_html__( 'Select…', 'anima-core' ) . '</option>';
                foreach ( $field['options'] as $option ) {
                    $selected = selected( $value, $option, false );
                    echo '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( $option ) . '</option>';
                }
                echo '</select>';
                break;
            case 'number':
                echo '<input type="number" class="widefat" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '"' . $attributes . ' />';
                break;
            case 'url':
                echo '<input type="url" class="widefat" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
                break;
            default:
                echo '<input type="text" class="widefat" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
                break;
        }

        if ( $description ) {
            echo '<span class="description">' . $description . '</span>';
        }

        echo '</p>';
    }

    echo '</div>';
}

/**
 * Save meta box values.
 *
 * @param int     $post_id Saved post ID.
 * @param WP_Post $post    Post object.
 */
function anima_core_save_meta_boxes( int $post_id, WP_Post $post ): void {
    if ( ! isset( $_POST['anima_core_meta_nonce'] ) ) {
        return;
    }

    $nonce = sanitize_text_field( wp_unslash( $_POST['anima_core_meta_nonce'] ) );
    if ( ! wp_verify_nonce( $nonce, 'anima_core_meta_' . $post->post_type ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $definitions = anima_core_meta_field_definitions();

    if ( ! isset( $definitions[ $post->post_type ] ) ) {
        return;
    }

    foreach ( $definitions[ $post->post_type ] as $key => $field ) {
        $raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';

        switch ( $field['type'] ) {
            case 'number':
                $value = is_numeric( $raw ) ? $raw + 0 : '';
                break;
            case 'url':
                $value = esc_url_raw( $raw );
                break;
            case 'textarea':
                if ( ! empty( $field['multiple'] ) ) {
                    $lines = array_filter( array_map( 'trim', explode( "\n", (string) $raw ) ) );
                    $sanitize = $field['sanitize'] ?? 'text';
                    if ( 'url' === $sanitize ) {
                        $value = array_map( 'esc_url_raw', $lines );
                    } else {
                        $value = array_map( 'sanitize_text_field', $lines );
                    }
                } else {
                    $value = wp_kses_post( $raw );
                }
                break;
            case 'select':
                $allowed = $field['options'];
                $value   = in_array( $raw, $allowed, true ) ? $raw : '';
                break;
            default:
                $value = sanitize_text_field( $raw );
                break;
        }

        if ( '' === $value || ( is_array( $value ) && empty( $value ) ) ) {
            delete_post_meta( $post_id, $key );
            continue;
        }

        update_post_meta( $post_id, $key, $value );
    }
}
