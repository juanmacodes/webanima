<?php
namespace Anima\Engine\Seo;

use function esc_url_raw;
use function wp_json_encode;

/**
 * Helper para imprimir estructuras JSON-LD.
 */
class Schema {
    /**
     * Imprime la estructura correspondiente al tipo solicitado.
     *
     * @param string $type Tipo de esquema (organization, product, course, software, breadcrumb).
     * @param array  $data Datos específicos.
     */
    public static function print( string $type, array $data ): void {
        $payload = self::build_schema( $type, $data );

        if ( empty( $payload ) ) {
            return;
        }

        echo '<script type="application/ld+json">' . wp_json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Genera la estructura del esquema.
     *
     * @param string $type Tipo solicitado.
     * @param array  $data Datos de contexto.
     *
     * @return array|null
     */
    protected static function build_schema( string $type, array $data ): ?array {
        switch ( $type ) {
            case 'organization':
                return self::build_organization_schema( $data );
            case 'product':
                return self::build_product_schema( $data );
            case 'course':
                return self::build_course_schema( $data );
            case 'software':
                return self::build_software_schema( $data );
            case 'breadcrumb':
                return self::build_breadcrumb_schema( $data );
            default:
                return null;
        }
    }

    /**
     * Construye un Organization.
     *
     * @param array $data Datos del sitio.
     */
    protected static function build_organization_schema( array $data ): ?array {
        $structure = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Organization',
            'name'        => $data['name'] ?? '',
            'url'         => isset( $data['url'] ) ? esc_url_raw( $data['url'] ) : '',
            'description' => $data['description'] ?? '',
            'logo'        => isset( $data['logo'] ) ? esc_url_raw( $data['logo'] ) : '',
            'sameAs'      => array_values( array_filter( $data['same_as'] ?? [] ) ),
            'contactPoint'=> $data['contact_point'] ?? null,
        ];

        return self::clean_array( $structure );
    }

    /**
     * Construye un Product.
     *
     * @param array $data Datos del producto.
     */
    protected static function build_product_schema( array $data ): ?array {
        $structure = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'image'       => isset( $data['image'] ) ? esc_url_raw( $data['image'] ) : '',
            'url'         => isset( $data['url'] ) ? esc_url_raw( $data['url'] ) : '',
            'sku'         => $data['sku'] ?? '',
            'brand'       => isset( $data['brand'] ) ? self::clean_array( $data['brand'] ) : null,
            'offers'      => isset( $data['offers'] ) ? self::clean_array( $data['offers'] ) : null,
            'additionalProperty' => ! empty( $data['additional_property'] ) ? array_map( [ __CLASS__, 'clean_array' ], $data['additional_property'] ) : null,
        ];

        return self::clean_array( $structure );
    }

    /**
     * Construye un Course.
     *
     * @param array $data Datos del curso.
     */
    protected static function build_course_schema( array $data ): ?array {
        $structure = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Course',
            'name'        => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'provider'    => isset( $data['provider'] ) ? self::clean_array( $data['provider'] ) : null,
            'courseMode'  => $data['mode'] ?? '',
            'timeRequired'=> $data['duration'] ?? '',
            'inLanguage'  => $data['language'] ?? 'es',
            'audience'    => isset( $data['audience'] ) ? self::clean_array( $data['audience'] ) : null,
            'educationalCredentialAwarded' => $data['credential'] ?? '',
            'hasCourseInstance' => isset( $data['instance'] ) ? self::clean_array( $data['instance'] ) : null,
        ];

        return self::clean_array( $structure );
    }

    /**
     * Construye un SoftwareApplication.
     *
     * @param array $data Datos de la aplicación.
     */
    protected static function build_software_schema( array $data ): ?array {
        $structure = [
            '@context'          => 'https://schema.org',
            '@type'             => 'SoftwareApplication',
            'name'              => $data['name'] ?? '',
            'applicationCategory' => $data['category'] ?? 'BusinessApplication',
            'operatingSystem'   => $data['operating_system'] ?? 'Web',
            'description'       => $data['description'] ?? '',
            'softwareVersion'   => $data['version'] ?? '',
            'url'               => isset( $data['url'] ) ? esc_url_raw( $data['url'] ) : '',
            'image'             => isset( $data['image'] ) ? esc_url_raw( $data['image'] ) : '',
            'screenshot'        => isset( $data['screenshot'] ) ? esc_url_raw( $data['screenshot'] ) : '',
            'offers'            => isset( $data['offers'] ) ? self::clean_array( $data['offers'] ) : null,
            'publisher'         => isset( $data['publisher'] ) ? self::clean_array( $data['publisher'] ) : null,
        ];

        return self::clean_array( $structure );
    }

    /**
     * Construye un BreadcrumbList.
     *
     * @param array $data Lista de elementos.
     */
    protected static function build_breadcrumb_schema( array $data ): ?array {
        if ( empty( $data['items'] ) || ! is_array( $data['items'] ) ) {
            return null;
        }

        $position = 1;
        $items    = [];

        foreach ( $data['items'] as $item ) {
            if ( empty( $item['name'] ) || empty( $item['url'] ) ) {
                continue;
            }

            $items[] = [
                '@type'    => 'ListItem',
                'position' => $position++,
                'name'     => $item['name'],
                'item'     => esc_url_raw( $item['url'] ),
            ];
        }

        if ( empty( $items ) ) {
            return null;
        }

        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * Limpia arrays recursivamente.
     *
     * @param array $data Datos a limpiar.
     */
    protected static function clean_array( array $data ): ?array {
        $clean = [];

        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
                $value = self::clean_array( $value );
            }

            if ( null === $value || '' === $value || ( is_array( $value ) && empty( $value ) ) ) {
                continue;
            }

            $clean[ $key ] = $value;
        }

        return empty( $clean ) ? null : $clean;
    }
}
