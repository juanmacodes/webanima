<?php
/**
 * Funciones de comercio electrónico específicas de Anima.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'anima_core_register_license_attribute', 11 );
add_filter( 'woocommerce_product_data_tabs', 'anima_core_add_preview_tab' );
add_action( 'woocommerce_product_data_panels', 'anima_core_render_preview_panel' );
add_action( 'woocommerce_admin_process_product_object', 'anima_core_save_preview_meta' );
add_action( 'woocommerce_product_options_general_product_data', 'anima_core_avatar_flag_field' );
add_action( 'woocommerce_admin_process_product_object', 'anima_core_sync_avatar_product', 20 );
add_filter( 'woocommerce_product_tabs', 'anima_core_add_product_preview_tab' );
add_filter( 'woocommerce_checkout_fields', 'anima_core_add_eula_field' );
add_action( 'woocommerce_checkout_process', 'anima_core_validate_eula_field' );
add_action( 'woocommerce_checkout_update_order_meta', 'anima_core_save_eula_meta' );
add_action( 'woocommerce_admin_order_data_after_billing_address', 'anima_core_render_eula_admin' );
add_action( 'woocommerce_order_details_after_customer_details', 'anima_core_render_eula_front' );
add_action( 'woocommerce_order_status_completed', 'anima_core_generate_license_file' );
add_action( 'woocommerce_order_details_after_order_table', 'anima_core_render_license_link' );
add_action( 'init', 'anima_core_ensure_launch_coupon', 15 );

/**
 * Crea el atributo global "Licencia" con términos predefinidos.
 */
function anima_core_register_license_attribute(): void {
    if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
        return;
    }

    $attribute_name = 'license';
    $attribute_id   = 0;

    foreach ( wc_get_attribute_taxonomies() as $attribute ) {
        if ( $attribute->attribute_name === $attribute_name ) {
            $attribute_id = (int) $attribute->attribute_id;
            break;
        }
    }

    if ( 0 === $attribute_id ) {
        $attribute_id = wc_create_attribute(
            [
                'name'         => __( 'Licencia', 'anima' ),
                'slug'         => $attribute_name,
                'type'         => 'select',
                'order_by'     => 'menu_order',
                'has_archives' => false,
            ]
        );

        if ( is_wp_error( $attribute_id ) ) {
            return;
        }
    }

    $taxonomy = wc_attribute_taxonomy_name( $attribute_name );

    if ( ! taxonomy_exists( $taxonomy ) ) {
        register_taxonomy( $taxonomy, [ 'product' ] );
    }

    $terms = [
        'indie'      => __( 'Indie', 'anima' ),
        'studio'     => __( 'Studio', 'anima' ),
        'enterprise' => __( 'Enterprise', 'anima' ),
    ];

    foreach ( $terms as $slug => $label ) {
        if ( ! term_exists( $slug, $taxonomy ) ) {
            wp_insert_term( $label, $taxonomy, [ 'slug' => $slug ] );
        }
    }
}

/**
 * Añade una pestaña personalizada en el editor de producto.
 */
function anima_core_add_preview_tab( array $tabs ): array {
    $tabs['anima_preview'] = [
        'label'    => __( 'Preview 3D', 'anima' ),
        'target'   => 'anima_preview_product_data',
        'class'    => [ 'show_if_simple', 'show_if_variable', 'show_if_downloadable' ],
        'priority' => 75,
    ];

    return $tabs;
}

/**
 * Renderiza los campos de configuración del visor 3D.
 */
function anima_core_render_preview_panel(): void {
    global $post;

    echo '<div id="anima_preview_product_data" class="panel woocommerce_options_panel">';
    echo '<div class="options_group">';

    if ( function_exists( 'woocommerce_wp_text_input' ) ) {
        woocommerce_wp_text_input(
            [
                'id'          => '_anima_model_src',
                'label'       => __( 'URL del modelo (GLB)', 'anima' ),
                'description' => __( 'Enlace directo al archivo GLB que se mostrará en el visor.', 'anima' ),
                'type'        => 'url',
                'value'       => get_post_meta( $post->ID, '_anima_model_src', true ),
            ]
        );

        woocommerce_wp_text_input(
            [
                'id'          => '_anima_model_ios_src',
                'label'       => __( 'URL modelo iOS (USDZ opcional)', 'anima' ),
                'description' => __( 'Recomendado para experiencia AR en dispositivos iOS.', 'anima' ),
                'type'        => 'url',
                'value'       => get_post_meta( $post->ID, '_anima_model_ios_src', true ),
            ]
        );

        woocommerce_wp_text_input(
            [
                'id'          => '_anima_model_poster',
                'label'       => __( 'Imagen de poster', 'anima' ),
                'description' => __( 'Imagen que se mostrará mientras se carga el modelo.', 'anima' ),
                'type'        => 'url',
                'value'       => get_post_meta( $post->ID, '_anima_model_poster', true ),
            ]
        );
    }

    echo '</div>';
    echo '</div>';
}

/**
 * Guarda la configuración del visor y el flag de producto avatar.
 */
function anima_core_save_preview_meta( \WC_Product $product ): void {
    $fields = [
        '_anima_model_src'      => 'url',
        '_anima_model_ios_src'  => 'url',
        '_anima_model_poster'   => 'url',
    ];

    foreach ( $fields as $field => $type ) {
        $raw = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : '';
        $value = 'url' === $type ? esc_url_raw( $raw ) : sanitize_text_field( $raw );
        $product->update_meta_data( $field, $value );
    }

    $is_avatar = isset( $_POST['_anima_is_avatar_product'] ) ? 'yes' : 'no';
    $product->update_meta_data( '_anima_is_avatar_product', $is_avatar );
}

/**
 * Checkbox para indicar productos Avatar.
 */
function anima_core_avatar_flag_field(): void {
    global $post;

    if ( ! function_exists( 'woocommerce_wp_checkbox' ) ) {
        return;
    }

    woocommerce_wp_checkbox(
        [
            'id'          => '_anima_is_avatar_product',
            'label'       => __( 'Producto Avatar descargable', 'anima' ),
            'description' => __( 'Activa la generación de variaciones por licencia y marca el producto como descargable.', 'anima' ),
            'value'       => get_post_meta( $post->ID, '_anima_is_avatar_product', true ) ?: 'no',
        ]
    );
}

/**
 * Sincroniza atributos y variaciones para productos Avatar.
 */
function anima_core_sync_avatar_product( \WC_Product $product ): void {
    $is_avatar = $product->get_meta( '_anima_is_avatar_product', true );

    if ( 'yes' !== $is_avatar ) {
        return;
    }

    if ( ! $product->is_type( 'variable' ) ) {
        return;
    }

    $taxonomy = wc_attribute_taxonomy_name( 'license' );

    if ( ! taxonomy_exists( $taxonomy ) ) {
        return;
    }

    $terms = get_terms(
        [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ]
    );

    if ( empty( $terms ) ) {
        return;
    }

    $attributes = $product->get_attributes();
    $attribute  = $attributes[ $taxonomy ] ?? new \WC_Product_Attribute();

    if ( ! $attribute->get_id() && function_exists( 'wc_attribute_taxonomy_id_by_name' ) ) {
        $attribute->set_id( wc_attribute_taxonomy_id_by_name( $taxonomy ) );
    }

    $attribute->set_name( $taxonomy );
    $attribute->set_visible( true );
    $attribute->set_variation( true );
    $attribute->set_options( wp_list_pluck( $terms, 'term_id' ) );

    $attributes[ $taxonomy ] = $attribute;
    $product->set_attributes( $attributes );

    wp_set_object_terms( $product->get_id(), wp_list_pluck( $terms, 'slug' ), $taxonomy );

    $existing = [];
    foreach ( $product->get_children() as $child_id ) {
        $variation = wc_get_product( $child_id );
        if ( $variation instanceof \WC_Product_Variation ) {
            $value = $variation->get_attribute( $taxonomy );
            if ( $value ) {
                $existing[ sanitize_title( $value ) ] = $variation;
            }
        }
    }

    $usage = [
        'indie'      => __( 'Licencia para freelancers y creadores independientes.', 'anima' ),
        'studio'     => __( 'Licencia pensada para estudios y equipos en crecimiento.', 'anima' ),
        'enterprise' => __( 'Licencia corporativa para despliegues a gran escala.', 'anima' ),
    ];

    $order = 0;

    foreach ( $terms as $term ) {
        $slug = sanitize_title( $term->slug );

        if ( isset( $existing[ $slug ] ) ) {
            $variation = $existing[ $slug ];
            $variation->set_downloadable( true );
            $variation->set_virtual( true );
            if ( isset( $usage[ $slug ] ) ) {
                $variation->set_description( $usage[ $slug ] );
            }
            $variation->save();
            continue;
        }

        $variation_post = [
            'post_title'  => $product->get_name() . ' — ' . $term->name,
            'post_name'   => sanitize_title( $product->get_name() . '-' . $term->slug ),
            'post_status' => 'publish',
            'post_parent' => $product->get_id(),
            'post_type'   => 'product_variation',
            'menu_order'  => $order++,
        ];

        $variation_id = wp_insert_post( $variation_post );

        if ( is_wp_error( $variation_id ) || 0 === $variation_id ) {
            continue;
        }

        $variation = new \WC_Product_Variation( $variation_id );
        $variation->set_regular_price( $product->get_regular_price() ?: '0' );
        $variation->set_downloadable( true );
        $variation->set_virtual( true );
        $variation->set_description( $usage[ $slug ] ?? '' );
        $variation->set_attributes( [ $taxonomy => $term->slug ] );

        $downloads = $product->get_downloads();
        if ( ! empty( $downloads ) ) {
            $variation->set_downloads( $downloads );
        }

        $variation->save();
    }

    \WC_Product_Variable::sync( $product->get_id() );
}

/**
 * Añade la pestaña de vista previa en la ficha pública del producto.
 */
function anima_core_add_product_preview_tab( array $tabs ): array {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
        return $tabs;
    }

    global $product;

    if ( ! $product instanceof \WC_Product ) {
        return $tabs;
    }

    $model_src = $product->get_meta( '_anima_model_src', true );

    if ( empty( $model_src ) ) {
        return $tabs;
    }

    $tabs['anima_preview'] = [
        'title'    => __( 'Preview 3D', 'anima' ),
        'priority' => 25,
        'callback' => 'anima_core_render_product_preview_tab_content',
    ];

    return $tabs;
}

/**
 * Contenido del tab de preview.
 */
function anima_core_render_product_preview_tab_content(): void {
    global $product;

    if ( ! $product instanceof \WC_Product ) {
        return;
    }

    $model_src = $product->get_meta( '_anima_model_src', true );

    if ( empty( $model_src ) ) {
        echo '<p>' . esc_html__( 'No hay un modelo 3D configurado para este producto.', 'anima' ) . '</p>';
        return;
    }

    $poster  = $product->get_meta( '_anima_model_poster', true );
    $ios_src = $product->get_meta( '_anima_model_ios_src', true );

    wp_enqueue_script( 'anima-model-viewer' );
    wp_enqueue_script( 'anima-model-viewer-enhancements' );

    $attributes = [
        'src'             => esc_url( $model_src ),
        'alt'             => esc_attr( $product->get_name() ),
        'ar'              => true,
        'ar-modes'        => 'webxr scene-viewer quick-look',
        'camera-controls' => true,
        'tone-mapping'    => 'neutral',
        'exposure'        => '1',
        'shadow-intensity'=> '1',
        'style'           => 'width:100%;min-height:420px;border-radius:16px;overflow:hidden;',
    ];

    if ( ! empty( $poster ) ) {
        $attributes['poster'] = esc_url( $poster );
    }

    if ( ! empty( $ios_src ) ) {
        $attributes['ios-src'] = esc_url( $ios_src );
    }

    echo '<div class="anima-product-preview">';
    echo '<model-viewer';
    foreach ( $attributes as $attr => $value ) {
        if ( true === $value ) {
            echo ' ' . esc_attr( $attr );
        } else {
            echo ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
        }
    }
    echo ' autoplay>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo '<button slot="ar-button" class="button anima-product-preview__ar-button">' . esc_html__( 'Probar en AR', 'anima' ) . '</button>';
    echo '</model-viewer>';
    echo '</div>';
}

/**
 * Añade el checkbox del EULA en el checkout.
 */
function anima_core_add_eula_field( array $fields ): array {
    $fields['billing']['anima_accept_eula'] = [
        'type'        => 'checkbox',
        'label'       => __( 'Acepto el Acuerdo de Licencia de Usuario Final (EULA)', 'anima' ),
        'required'    => true,
        'class'       => [ 'form-row-wide' ],
        'priority'    => 999,
    ];

    return $fields;
}

/**
 * Valida el EULA.
 */
function anima_core_validate_eula_field(): void {
    if ( empty( $_POST['anima_accept_eula'] ) ) {
        wc_add_notice( __( 'Debes aceptar el EULA para completar el pedido.', 'anima' ), 'error' );
    }
}

/**
 * Guarda la aceptación en los metadatos del pedido.
 */
function anima_core_save_eula_meta( int $order_id ): void {
    update_post_meta( $order_id, '_anima_accept_eula', ! empty( $_POST['anima_accept_eula'] ) ? 'yes' : 'no' );
}

/**
 * Muestra la aceptación en el panel de administración.
 */
function anima_core_render_eula_admin( \WC_Order $order ): void {
    if ( 'yes' === $order->get_meta( '_anima_accept_eula' ) ) {
        echo '<p><strong>' . esc_html__( 'EULA aceptado:', 'anima' ) . '</strong> ' . esc_html__( 'Sí', 'anima' ) . '</p>';
    }
}

/**
 * Muestra la aceptación en el frontend.
 */
function anima_core_render_eula_front( \WC_Order $order ): void {
    if ( 'yes' === $order->get_meta( '_anima_accept_eula' ) ) {
        echo '<p class="anima-order-eula">' . esc_html__( 'Confirmaste la aceptación del EULA para este pedido.', 'anima' ) . '</p>';
    }
}

/**
 * Genera un archivo de licencia cuando el pedido se completa.
 */
function anima_core_generate_license_file( int $order_id ): void {
    if ( empty( $order_id ) || get_post_meta( $order_id, '_anima_license_file', true ) ) {
        return;
    }

    $order = wc_get_order( $order_id );

    if ( ! $order instanceof \WC_Order ) {
        return;
    }

    $uploads = wp_upload_dir();

    if ( empty( $uploads['basedir'] ) || empty( $uploads['baseurl'] ) ) {
        return;
    }

    $directory = trailingslashit( $uploads['basedir'] ) . 'anima-licenses';
    wp_mkdir_p( $directory );

    $customer   = $order->get_formatted_billing_full_name() ?: trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
    $date       = current_time( 'Y-m-d' );
    $order_name = $order->get_order_number();

    $lines = [];
    $lines[] = 'Anima Avatar License';
    $lines[] = '----------------------';
    $lines[] = 'Order: #' . $order_name;
    $lines[] = 'Customer: ' . $customer;
    $lines[] = 'Email: ' . $order->get_billing_email();
    $lines[] = 'Date: ' . $date;
    $lines[] = 'Items:';

    foreach ( $order->get_items() as $item ) {
        $license  = '';
        $variation_attributes = $item->get_variation_attributes();

        foreach ( $variation_attributes as $attribute_key => $attribute_value ) {
            if ( false !== strpos( $attribute_key, 'pa_license' ) ) {
                $term = get_term_by( 'slug', $attribute_value, wc_attribute_taxonomy_name( 'license' ) );
                if ( $term && ! is_wp_error( $term ) ) {
                    $license = $term->name;
                }
            }
        }

        $line = sprintf( ' - %s x%d', $item->get_name(), $item->get_quantity() );
        if ( $license ) {
            $line .= ' [' . $license . ']';
        }

        $lines[] = $line;
    }

    $lines[] = '';
    $lines[] = 'Gracias por confiar en Anima.';

    $filename   = sanitize_file_name( 'anima-license-' . $order_name . '-' . $date . '.txt' );
    $file_path  = trailingslashit( $directory ) . $filename;
    $file_url   = trailingslashit( $uploads['baseurl'] ) . 'anima-licenses/' . $filename;

    file_put_contents( $file_path, implode( PHP_EOL, $lines ) );

    update_post_meta( $order_id, '_anima_license_file', esc_url_raw( $file_url ) );
    $order->add_order_note( sprintf( __( 'Licencia generada: %s', 'anima' ), $file_url ), true, true );
}

/**
 * Muestra el enlace de descarga al cliente.
 */
function anima_core_render_license_link( \WC_Order $order ): void {
    $link = $order->get_meta( '_anima_license_file' );

    if ( empty( $link ) ) {
        return;
    }

    echo '<section class="anima-order-license">';
    echo '<h2>' . esc_html__( 'Licencia de uso', 'anima' ) . '</h2>';
    echo '<p><a class="button" href="' . esc_url( $link ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Descargar licencia', 'anima' ) . '</a></p>';
    echo '</section>';
}

/**
 * Crea el cupón de lanzamiento si no existe.
 */
function anima_core_ensure_launch_coupon(): void {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    if ( get_option( 'anima_launch_coupon_created' ) ) {
        return;
    }

    if ( wc_get_coupon_id_by_code( 'LAUNCH10' ) ) {
        update_option( 'anima_launch_coupon_created', 1 );
        return;
    }

    $coupon = new \WC_Coupon();
    $coupon->set_code( 'LAUNCH10' );
    $coupon->set_description( __( 'Cupón de lanzamiento 10% de descuento.', 'anima' ) );
    $coupon->set_amount( 10 );
    $coupon->set_discount_type( 'percent' );
    $coupon->set_usage_limit( 500 );
    $coupon->save();

    update_option( 'anima_launch_coupon_created', 1 );
}
