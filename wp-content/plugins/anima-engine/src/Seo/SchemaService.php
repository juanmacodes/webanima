<?php
namespace Anima\Engine\Seo;

use Anima\Engine\Services\ServiceInterface;
use WP_Post;
use WC_Product;

use function add_action;
use function apply_filters;
use function get_bloginfo;
use function get_option;
use function get_page_by_path;
use function get_permalink;
use function get_post;
use function get_post_ancestors;
use function get_post_field;
use function get_post_meta;
use function get_post_type_archive_link;
use function get_post_type_object;
use function get_queried_object;
use function get_queried_object_id;
use function get_site_icon_url;
use function get_term_link;
use function get_the_archive_title;
use function get_the_post_thumbnail_url;
use function get_the_title;
use function get_transient;
use function get_woocommerce_currency;
use function get_author_posts_url;
use function get_search_link;
use function home_url;
use function is_admin;
use function is_archive;
use function is_author;
use function is_category;
use function is_front_page;
use function is_page;
use function is_post_type_archive;
use function is_search;
use function is_singular;
use function is_tag;
use function is_tax;
use function is_wp_error;
use function get_theme_mod;
use function set_transient;
use function wp_parse_args;
use function wp_get_attachment_image_url;
use function wp_get_post_terms;
use function wp_strip_all_tags;
use function wp_trim_words;
use function wc_get_product;
use function wc_get_products;
use function array_key_exists;

/**
 * Servicio encargado de inyectar los esquemas JSON-LD.
 */
class SchemaService implements ServiceInterface {
    /**
     * {@inheritDoc}
     */
    public function register(): void {
        add_action( 'wp_head', [ $this, 'render_schema' ], 5 );
    }

    /**
     * Imprime los esquemas disponibles.
     */
    public function render_schema(): void {
        if ( is_admin() ) {
            return;
        }

        if ( ! $this->is_schema_enabled() ) {
            return;
        }

        $organization = $this->prepare_organization_schema();
        $organization = apply_filters( 'anima_engine_schema_data', $organization, 'organization' );
        if ( ! empty( $organization ) ) {
            Schema::print( 'organization', $organization );
        }

        if ( is_singular( 'avatar' ) ) {
            $product = $this->prepare_avatar_schema( get_queried_object_id() );
            $product = apply_filters( 'anima_engine_schema_data', $product, 'avatar' );
            if ( ! empty( $product ) ) {
                Schema::print( 'product', $product );
            }
        }

        if ( is_singular( 'curso' ) ) {
            $course = $this->prepare_course_schema( get_queried_object_id() );
            $course = apply_filters( 'anima_engine_schema_data', $course, 'curso' );
            if ( ! empty( $course ) ) {
                Schema::print( 'course', $course );
            }
        }

        if ( $this->is_live_page() ) {
            $software = $this->prepare_software_schema();
            $software = apply_filters( 'anima_engine_schema_data', $software, 'software' );
            if ( ! empty( $software ) ) {
                Schema::print( 'software', $software );
            }
        }

        if ( is_singular() || is_archive() ) {
            $breadcrumb = $this->prepare_breadcrumb_schema();
            $breadcrumb = apply_filters( 'anima_engine_schema_data', $breadcrumb, 'breadcrumb' );
            if ( ! empty( $breadcrumb ) ) {
                Schema::print( 'breadcrumb', $breadcrumb );
            }
        }
    }

    /**
     * Datos del sitio como organización.
     */
    protected function prepare_organization_schema(): array {
        $name        = get_bloginfo( 'name' );
        $description = get_bloginfo( 'description' );
        $logo_id     = (int) get_theme_mod( 'custom_logo' );
        $logo_url    = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : get_site_icon_url();

        $contact_point = null;
        $email         = get_bloginfo( 'admin_email' );
        if ( ! empty( $email ) ) {
            $contact_point = [
                '@type'       => 'ContactPoint',
                'contactType' => 'customer support',
                'email'       => $email,
                'availableLanguage' => [ 'es', 'en' ],
            ];
        }

        return [
            'name'         => $name,
            'description'  => $description,
            'url'          => home_url( '/' ),
            'logo'         => $logo_url,
            'contact_point'=> $contact_point,
        ];
    }

    /**
     * Prepara datos para el CPT Avatar.
     */
    protected function prepare_avatar_schema( int $post_id ): array {
        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post ) {
            return [];
        }

        $image = get_the_post_thumbnail_url( $post, 'full' );
        $terms = wp_get_post_terms( $post_id, [ 'tecnologia' ] );
        $attributes = [];

        foreach ( $terms as $term ) {
            $attributes[] = [
                '@type' => 'PropertyValue',
                'name'  => 'Tecnología',
                'value' => $term->name,
            ];
        }

        $summary = $post->post_excerpt ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( $post->post_content ), 40 );

        $offers = $this->extract_product_offers( $post_id );

        return [
            'name'                => get_the_title( $post ),
            'description'         => $summary,
            'image'               => $image,
            'url'                 => get_permalink( $post ),
            'sku'                 => 'avatar-' . $post_id,
            'brand'               => [
                '@type' => 'Brand',
                'name'  => get_bloginfo( 'name' ),
            ],
            'offers'              => $offers,
            'additional_property' => $attributes,
        ];
    }

    /**
     * Prepara datos para cursos.
     */
    protected function prepare_course_schema( int $post_id ): array {
        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post ) {
            return [];
        }

        $duration   = get_post_meta( $post_id, 'anima_duracion', true );
        $difficulty = get_post_meta( $post_id, 'anima_dificultad', true );
        $instructor = get_post_meta( $post_id, 'anima_instructores', true );
        $summary    = $post->post_excerpt ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( $post->post_content ), 40 );

        $instance = [
            '@type'     => 'CourseInstance',
            'name'      => get_the_title( $post ),
            'startDate' => get_post_field( 'post_date', $post_id ),
            'location'  => [
                '@type'   => 'VirtualLocation',
                'url'     => get_permalink( $post ),
            ],
        ];

        if ( strlen( (string) $duration ) > 0 ) {
            $instance['timeRequired'] = $duration;
        }

        return [
            'name'        => get_the_title( $post ),
            'description' => $summary,
            'provider'    => [
                '@type' => 'Organization',
                'name'  => get_bloginfo( 'name' ),
                'url'   => home_url( '/' ),
            ],
            'mode'        => 'online',
            'duration'    => $duration,
            'audience'    => $difficulty ? [
                '@type' => 'EducationalAudience',
                'educationalRole' => $difficulty,
            ] : null,
            'credential'  => $instructor,
            'instance'    => $instance,
        ];
    }

    /**
     * Datos para la landing de Anima Live.
     */
    protected function prepare_software_schema(): array {
        $page_id = $this->get_live_page_id();
        if ( ! $page_id ) {
            return [];
        }

        $post = get_post( $page_id );
        if ( ! $post instanceof WP_Post ) {
            return [];
        }

        $image     = get_the_post_thumbnail_url( $post, 'full' );
        $summary   = $post->post_excerpt ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( $post->post_content ), 40 );
        $publisher = [
            '@type' => 'Organization',
            'name'  => get_bloginfo( 'name' ),
            'url'   => home_url( '/' ),
        ];

        $offers = [
            '@type'           => 'Offer',
            'availability'    => 'https://schema.org/PreOrder',
            'price'           => '0',
            'priceCurrency'   => 'USD',
            'url'             => get_permalink( $post ),
        ];

        return [
            'name'             => get_the_title( $post ),
            'description'      => $summary,
            'operating_system' => 'Web',
            'category'         => 'Application',
            'version'          => 'beta',
            'url'              => get_permalink( $post ),
            'image'            => $image,
            'screenshot'       => $image,
            'offers'           => $offers,
            'publisher'        => $publisher,
        ];
    }

    /**
     * Construye el breadcrumb.
     */
    protected function prepare_breadcrumb_schema(): array {
        if ( is_front_page() ) {
            return [];
        }

        $items = [
            [
                'name' => get_bloginfo( 'name' ),
                'url'  => home_url( '/' ),
            ],
        ];

        if ( is_archive() ) {
            $archive_name = get_the_archive_title();
            $archive_link = $this->resolve_archive_link();

            if ( $archive_link ) {
                $items[] = [
                    'name' => $archive_name,
                    'url'  => $archive_link,
                ];
            }

            if ( count( $items ) < 2 ) {
                return [];
            }

            return [ 'items' => $items ];
        }

        if ( ! is_singular() ) {
            return [];
        }

        $post = get_queried_object();
        if ( ! $post instanceof WP_Post ) {
            return [];
        }

        $post_type = get_post_type_object( $post->post_type );
        if ( $post_type && $post_type->has_archive ) {
            $archive_link = get_post_type_archive_link( $post->post_type );
            if ( $archive_link ) {
                $items[] = [
                    'name' => $post_type->labels->name ?? $post_type->label,
                    'url'  => $archive_link,
                ];
            }
        } elseif ( 'post' === $post->post_type ) {
            $blog_id = (int) get_option( 'page_for_posts' );
            if ( $blog_id ) {
                $items[] = [
                    'name' => get_the_title( $blog_id ),
                    'url'  => get_permalink( $blog_id ),
                ];
            }
        }

        $ancestors = array_reverse( (array) get_post_ancestors( $post ) );
        foreach ( $ancestors as $ancestor_id ) {
            $items[] = [
                'name' => get_the_title( $ancestor_id ),
                'url'  => get_permalink( $ancestor_id ),
            ];
        }

        $items[] = [
            'name' => get_the_title( $post ),
            'url'  => get_permalink( $post ),
        ];

        return [ 'items' => $items ];
    }

    /**
     * Devuelve el enlace de la página de archivo actual.
     */
    protected function resolve_archive_link(): ?string {
        if ( is_post_type_archive() ) {
            $object = get_queried_object();
            if ( $object && isset( $object->name ) ) {
                $link = get_post_type_archive_link( $object->name );
                if ( $link ) {
                    return $link;
                }
            }
        }

        if ( is_category() || is_tag() || is_tax() ) {
            $term = get_queried_object();
            if ( $term ) {
                $link = get_term_link( $term );
                if ( ! is_wp_error( $link ) ) {
                    return $link;
                }
            }
        }

        if ( is_author() ) {
            $author = get_queried_object();
            if ( $author && isset( $author->ID ) ) {
                return get_author_posts_url( (int) $author->ID );
            }
        }

        if ( is_search() ) {
            return get_search_link();
        }

        return null;
    }

    /**
     * Comprueba si estamos en la landing configurada.
     */
    protected function is_live_page(): bool {
        $page_id = $this->get_live_page_id();
        return $page_id ? is_page( $page_id ) : false;
    }

    /**
     * Obtiene el ID configurado para la landing.
     */
    protected function get_live_page_id(): ?int {
        $options = get_option( 'anima_engine_options', [] );
        $page    = $options['page_live'] ?? '';

        if ( empty( $page ) ) {
            return null;
        }

        if ( is_numeric( $page ) ) {
            return (int) $page;
        }

        $page_object = get_page_by_path( $page );
        if ( $page_object ) {
            return (int) $page_object->ID;
        }

        return null;
    }

    /**
     * Extrae datos de oferta desde WooCommerce si existe producto vinculado.
     */
    protected function extract_product_offers( int $post_id ): ?array {
        if ( ! class_exists( '\\WooCommerce' ) ) {
            return null;
        }

        $product = $this->locate_product_from_avatar( $post_id );
        if ( ! $product instanceof WC_Product ) {
            return null;
        }

        $price = $product->get_price();

        if ( '' === $price ) {
            return null;
        }

        $currency = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD';

        return [
            '@type'           => 'Offer',
            'price'           => (string) $price,
            'priceCurrency'   => $currency,
            'availability'    => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url'             => get_permalink( $product->get_id() ),
        ];
    }

    /**
     * Intenta localizar un producto que represente el avatar.
     */
    protected function locate_product_from_avatar( int $post_id ): ?WC_Product {
        if ( ! class_exists( '\\WooCommerce' ) ) {
            return null;
        }

        $cached = get_transient( 'anima_avatar_product_' . $post_id );
        if ( $cached ) {
            $product = wc_get_product( (int) $cached );
            if ( $product instanceof WC_Product ) {
                return $product;
            }
        }

        $linked_product_id = (int) get_post_meta( $post_id, 'linked_product_id', true );
        if ( $linked_product_id > 0 ) {
            $product = wc_get_product( $linked_product_id );
            if ( $product instanceof WC_Product ) {
                set_transient( 'anima_avatar_product_' . $post_id, $product->get_id(), DAY_IN_SECONDS );
                return $product;
            }
        }

        $product = wc_get_product( $post_id );
        if ( $product instanceof WC_Product ) {
            set_transient( 'anima_avatar_product_' . $post_id, $product->get_id(), DAY_IN_SECONDS );
            return $product;
        }

        $by_slug = wc_get_products(
            [
                'limit' => 1,
                'status'=> 'publish',
                'slug'  => get_post_field( 'post_name', $post_id ),
            ]
        );

        if ( ! empty( $by_slug ) && $by_slug[0] instanceof WC_Product ) {
            set_transient( 'anima_avatar_product_' . $post_id, $by_slug[0]->get_id(), DAY_IN_SECONDS );
            return $by_slug[0];
        }

        return null;
    }

    /**
     * Comprueba si el esquema está activo.
     */
    protected function is_schema_enabled(): bool {
        $options = wp_parse_args( get_option( 'anima_engine_options', [] ), [ 'enable_schema' => true ] );

        if ( array_key_exists( 'enable_schema', $options ) ) {
            return (bool) $options['enable_schema'];
        }

        return true;
    }
}
