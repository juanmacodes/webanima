<?php
/**
 * Demo content importer admin page.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Anima_Core_Demo_Importer {

    /**
     * Import demo content from the bundled XML file or a provided path.
     *
     * @param string|null $import_file Optional absolute path to the XML file to import.
     * @return array<string, mixed> Result array with success flag and stats.
     */
    public function import( ?string $import_file = null ): array {
        if ( null === $import_file ) {
            $import_file = apply_filters(
                'anima_core_demo_import_file',
                trailingslashit( dirname( WP_CONTENT_DIR ) ) . 'content/demo-content.xml'
            );
        }

        if ( ! $import_file || ! file_exists( $import_file ) ) {
            return [
                'success' => false,
                'message' => sprintf(
                    /* translators: %s: File path. */
                    __( 'El archivo de contenido demo no se encontró en %s.', 'anima-core' ),
                    (string) $import_file
                ),
            ];
        }

        if ( ! is_readable( $import_file ) ) {
            return [
                'success' => false,
                'message' => sprintf(
                    /* translators: %s: File path. */
                    __( 'El archivo %s no tiene permisos de lectura.', 'anima-core' ),
                    $import_file
                ),
            ];
        }

        $previous = libxml_use_internal_errors( true );
        $xml      = simplexml_load_file( $import_file );

        if ( false === $xml ) {
            libxml_use_internal_errors( $previous );

            return [
                'success' => false,
                'message' => __( 'No se pudo procesar el XML de demo.', 'anima-core' ),
            ];
        }

        $xml->registerXPathNamespace( 'wp', 'http://wordpress.org/export/1.2/' );
        $xml->registerXPathNamespace( 'content', 'http://purl.org/rss/1.0/modules/content/' );
        $xml->registerXPathNamespace( 'excerpt', 'http://wordpress.org/export/1.2/excerpt/' );

        $imported = 0;
        $skipped  = [];
        $errors   = [];

        if ( empty( $xml->channel->item ) ) {
            libxml_use_internal_errors( $previous );

            return [
                'success' => false,
                'message' => __( 'El archivo XML no contiene elementos para importar.', 'anima-core' ),
            ];
        }

        foreach ( $xml->channel->item as $item ) {
            $result = $this->import_item( $item );

            if ( is_wp_error( $result ) ) {
                $errors[] = $result->get_error_message();
                continue;
            }

            if ( true === $result ) {
                $imported++;
            } else {
                $skipped[] = $result;
            }
        }

        libxml_use_internal_errors( $previous );

        $message = sprintf(
            /* translators: 1: Imported count, 2: skipped count. */
            __( 'Importación completada: %1$d elementos creados, %2$d elementos omitidos.', 'anima-core' ),
            $imported,
            count( $skipped )
        );

        if ( ! empty( $errors ) ) {
            $message .= ' ' . __( 'Algunos elementos no se pudieron importar.', 'anima-core' );
        }

        return [
            'success' => empty( $errors ),
            'message' => $message,
            'skipped' => $skipped,
            'errors'  => $errors,
        ];
    }

    /**
     * Import a single XML item.
     *
     * @param SimpleXMLElement $item Item node.
     * @return true|string|WP_Error True on success, slug on skip, WP_Error on failure.
     */
    protected function import_item( SimpleXMLElement $item ) {
        $wp      = $item->children( 'wp', true );
        $content = $item->children( 'content', true );
        $excerpt = $item->children( 'excerpt', true );

        $post_type = sanitize_key( (string) $wp->post_type );

        if ( empty( $post_type ) || ! post_type_exists( $post_type ) ) {
            return new WP_Error( 'invalid_post_type', __( 'Tipo de contenido no válido en el XML.', 'anima-core' ) );
        }

        $post_name = sanitize_title( (string) $wp->post_name );
        $existing  = get_page_by_path( $post_name, OBJECT, $post_type );

        if ( $existing ) {
            return $post_name;
        }

        $post_status = in_array( (string) $wp->post_status, [ 'publish', 'draft', 'pending' ], true )
            ? (string) $wp->post_status
            : 'draft';

        $post_date     = (string) $wp->post_date;
        $post_date_gmt = (string) $wp->post_date_gmt;

        if ( empty( $post_date_gmt ) && ! empty( $post_date ) ) {
            $post_date_gmt = get_gmt_from_date( $post_date );
        }

        $post_data = [
            'post_type'      => $post_type,
            'post_name'      => $post_name,
            'post_title'     => sanitize_text_field( (string) $item->title ),
            'post_content'   => wp_kses_post( (string) $content->encoded ),
            'post_excerpt'   => wp_kses_post( (string) $excerpt->encoded ),
            'post_status'    => $post_status,
            'post_author'    => get_current_user_id(),
            'post_date'      => $post_date,
            'post_date_gmt'  => $post_date_gmt,
            'post_modified'     => $post_date,
            'post_modified_gmt' => $post_date_gmt,
        ];

        $post_id = wp_insert_post( $post_data, true );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        $this->import_meta( $post_id, $wp );
        $this->import_terms( $post_id, $item );

        return true;
    }

    /**
     * Import post meta values.
     *
     * @param int               $post_id Post ID.
     * @param SimpleXMLElement  $wp      Namespaced wp node.
     * @return void
     */
    protected function import_meta( int $post_id, SimpleXMLElement $wp ) {
        if ( empty( $wp->postmeta ) ) {
            return;
        }

        foreach ( $wp->postmeta as $meta ) {
            $key   = sanitize_key( (string) $meta->meta_key );
            $value = (string) $meta->meta_value;

            if ( '' === $key ) {
                continue;
            }

            update_post_meta( $post_id, $key, wp_kses_post( $value ) );
        }
    }

    /**
     * Import taxonomy terms for a post.
     *
     * @param int              $post_id Post ID.
     * @param SimpleXMLElement $item    Item node.
     * @return void
     */
    protected function import_terms( int $post_id, SimpleXMLElement $item ) {
        if ( empty( $item->category ) ) {
            return;
        }

        $terms = [];

        foreach ( $item->category as $category ) {
            $taxonomy = sanitize_key( (string) $category['domain'] );
            $name     = sanitize_text_field( (string) $category );

            if ( '' === $taxonomy || '' === $name || ! taxonomy_exists( $taxonomy ) ) {
                continue;
            }

            $terms[ $taxonomy ][] = $name;
        }

        foreach ( $terms as $taxonomy => $names ) {
            wp_set_post_terms( $post_id, $names, $taxonomy, false );
        }
    }
}

add_action( 'admin_menu', function () {
    add_management_page(
        __( 'Importar contenido demo de Anima', 'anima-core' ),
        __( 'Importar demo Anima', 'anima-core' ),
        'import',
        'anima-demo-import',
        function () {
            if ( ! current_user_can( 'import' ) ) {
                wp_die( __( 'No tienes permisos para importar contenido.', 'anima-core' ) );
            }

            $result = null;

            if ( isset( $_POST['anima_demo_import'] ) ) {
                check_admin_referer( 'anima_demo_import' );

                $importer = new Anima_Core_Demo_Importer();
                $file     = null;

                if ( isset( $_FILES['anima_demo_xml'] ) && ! empty( $_FILES['anima_demo_xml']['name'] ) ) {
                    $upload      = wp_unslash( $_FILES['anima_demo_xml'] );
                    $upload_code = isset( $upload['error'] ) ? (int) $upload['error'] : UPLOAD_ERR_NO_FILE;

                    if ( UPLOAD_ERR_OK !== $upload_code ) {
                        switch ( $upload_code ) {
                            case UPLOAD_ERR_INI_SIZE:
                            case UPLOAD_ERR_FORM_SIZE:
                                $message = __( 'El archivo seleccionado excede el tamaño permitido.', 'anima-core' );
                                break;
                            case UPLOAD_ERR_PARTIAL:
                                $message = __( 'La subida del archivo XML no se completó.', 'anima-core' );
                                break;
                            default:
                                $message = __( 'No se pudo subir el archivo XML seleccionado.', 'anima-core' );
                                break;
                        }

                        $result = [
                            'success' => false,
                            'message' => $message,
                            'skipped' => [],
                            'errors'  => [],
                        ];
                    } elseif ( empty( $upload['tmp_name'] ) || ! is_uploaded_file( $upload['tmp_name'] ) ) {
                        $result = [
                            'success' => false,
                            'message' => __( 'El archivo XML subido no es válido.', 'anima-core' ),
                            'skipped' => [],
                            'errors'  => [],
                        ];
                    } else {
                        $checked = wp_check_filetype_and_ext( $upload['tmp_name'], $upload['name'], [ 'xml' => 'text/xml' ] );

                        if ( empty( $checked['ext'] ) || 'xml' !== $checked['ext'] ) {
                            $result = [
                                'success' => false,
                                'message' => __( 'El archivo seleccionado debe ser un XML de exportación de WordPress.', 'anima-core' ),
                                'skipped' => [],
                                'errors'  => [],
                            ];
                        } else {
                            $file = $upload['tmp_name'];
                        }
                    }
                }

                if ( null === $result ) {
                    $result = $importer->import( $file );
                }
            }
            ?>
            <div class="wrap">
                <h1><?php esc_html_e( 'Importar contenido demo de Anima', 'anima-core' ); ?></h1>
                <p><?php esc_html_e( 'Esta herramienta importa proyectos, entradas y taxonomías de ejemplo. Puedes usar el archivo incluido en content/demo-content.xml o subir tu propio XML de exportación.', 'anima-core' ); ?></p>
                <?php if ( is_array( $result ) ) : ?>
                    <div class="notice notice-<?php echo esc_attr( $result['success'] ? 'success' : 'error' ); ?>"><p><?php echo esc_html( $result['message'] ); ?></p></div>
                    <?php if ( ! empty( $result['skipped'] ) ) : ?>
                        <p><?php esc_html_e( 'Elementos omitidos (ya existían):', 'anima-core' ); ?></p>
                        <ul>
                            <?php foreach ( $result['skipped'] as $slug ) : ?>
                                <li><code><?php echo esc_html( $slug ); ?></code></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if ( ! empty( $result['errors'] ) ) : ?>
                        <p><?php esc_html_e( 'Errores encontrados:', 'anima-core' ); ?></p>
                        <ul class="anima-demo-import-errors">
                            <?php foreach ( $result['errors'] as $error ) : ?>
                                <li><?php echo esc_html( $error ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field( 'anima_demo_import' ); ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="anima_demo_xml"><?php esc_html_e( 'Archivo XML', 'anima-core' ); ?></label></th>
                            <td>
                                <input type="file" id="anima_demo_xml" name="anima_demo_xml" accept=".xml" />
                                <p class="description"><?php esc_html_e( 'Selecciona un archivo XML exportado desde WordPress para importar su contenido.', 'anima-core' ); ?></p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary" name="anima_demo_import" value="1">
                            <?php esc_html_e( 'Importar contenido demo', 'anima-core' ); ?>
                        </button>
                    </p>
                </form>
                <p><?php esc_html_e( 'Si necesitas personalizar el archivo puedes sobrescribir la ruta usando el filtro anima_core_demo_import_file.', 'anima-core' ); ?></p>
            </div>
            <?php
        }
    );
} );
