<?php
namespace Anima\Engine\PostTypes;

use Anima\Engine\Services\ServiceInterface;

use function __;
use function array_key_exists;
use function class_exists;
use function get_option;
use function get_post_meta;
use function is_object;
use function method_exists;
use function preg_replace;
use function register_graphql_field;
use function sprintf;
use function strtolower;
use function str_replace;
use function ucwords;
use function wp_parse_args;
use function remove_accents;
use function lcfirst;

/**
 * Registro de tipos de contenido personalizados.
 */
class RegisterPostTypes implements ServiceInterface {
    /**
     * Mapea los tipos y sus configuraciones.
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $post_types = [];

    public function __construct() {
        $this->post_types = [
            'avatar'     => [
                'singular' => __( 'Avatar', 'anima-engine' ),
                'plural'   => __( 'Avatares', 'anima-engine' ),
                'args'     => [
                    'menu_icon' => 'dashicons-id',
                    'rewrite'   => [ 'slug' => 'avatares' ],
                    'taxonomies'=> [ 'tecnologia' ],
                ],
            ],
            'proyecto'   => [
                'singular' => __( 'Proyecto', 'anima-engine' ),
                'plural'   => __( 'Proyectos', 'anima-engine' ),
                'args'     => [
                    'menu_icon' => 'dashicons-portfolio',
                    'rewrite'   => [ 'slug' => 'proyectos' ],
                    'taxonomies'=> [ 'tecnologia' ],
                ],
            ],
            'experiencia'=> [
                'singular' => __( 'Experiencia', 'anima-engine' ),
                'plural'   => __( 'Experiencias', 'anima-engine' ),
                'args'     => [
                    'menu_icon' => 'dashicons-visibility',
                    'rewrite'   => [ 'slug' => 'experiencias' ],
                    'taxonomies'=> [ 'nivel', 'modalidad', 'tecnologia' ],
                ],
            ],
            'slide'      => [
                'singular' => __( 'Slide', 'anima-engine' ),
                'plural'   => __( 'Slides', 'anima-engine' ),
                'args'     => [
                    'menu_icon' => 'dashicons-images-alt2',
                    'rewrite'   => [ 'slug' => 'slides' ],
                    'supports'  => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(): void {
        add_action( 'init', [ $this, 'register_post_types' ] );

        if ( class_exists( '\\WPGraphQL' ) ) {
            add_action( 'graphql_register_types', [ $this, 'register_graphql_fields' ] );
        }
    }

    /**
     * Registra los tipos.
     */
    public function register_post_types(): void {
        foreach ( $this->post_types as $post_type => $settings ) {
            if ( 'slide' === $post_type && ! $this->is_feature_enabled( 'enable_slider', true ) ) {
                continue;
            }

            $labels = [
                'name'                     => $settings['plural'],
                'singular_name'            => $settings['singular'],
                'add_new'                  => __( 'Añadir nuevo', 'anima-engine' ),
                'add_new_item'             => sprintf( __( 'Añadir %s', 'anima-engine' ), strtolower( $settings['singular'] ) ),
                'edit_item'                => sprintf( __( 'Editar %s', 'anima-engine' ), strtolower( $settings['singular'] ) ),
                'new_item'                 => sprintf( __( 'Nuevo %s', 'anima-engine' ), strtolower( $settings['singular'] ) ),
                'view_item'                => sprintf( __( 'Ver %s', 'anima-engine' ), strtolower( $settings['singular'] ) ),
                'search_items'             => sprintf( __( 'Buscar %s', 'anima-engine' ), strtolower( $settings['plural'] ) ),
                'not_found'                => __( 'No se encontraron resultados.', 'anima-engine' ),
                'not_found_in_trash'       => __( 'No se encontraron elementos en la papelera.', 'anima-engine' ),
                'all_items'                => sprintf( __( 'Todos los %s', 'anima-engine' ), strtolower( $settings['plural'] ) ),
                'archives'                 => sprintf( __( 'Archivo de %s', 'anima-engine' ), strtolower( $settings['plural'] ) ),
                'attributes'               => __( 'Atributos', 'anima-engine' ),
                'insert_into_item'         => __( 'Insertar en el elemento', 'anima-engine' ),
                'uploaded_to_this_item'    => __( 'Subido a este elemento', 'anima-engine' ),
                'menu_name'                => $settings['plural'],
                'filter_items_list'        => __( 'Filtrar lista', 'anima-engine' ),
                'items_list_navigation'    => __( 'Navegación de lista', 'anima-engine' ),
                'items_list'               => __( 'Listado', 'anima-engine' ),
            ];

            $supports = $settings['args']['supports'] ?? [ 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions' ];

            $graphql_enabled = class_exists( '\\WPGraphQL' );
            $graphql_single  = $this->format_graphql_name( $settings['singular'] ?? $post_type );
            $graphql_plural  = $this->format_graphql_name( $settings['plural'] ?? $post_type . 's' );

            $args = [
                'labels'             => $labels,
                'public'             => true,
                'show_in_rest'       => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'has_archive'        => true,
                'hierarchical'       => false,
                'supports'           => $supports,
                'menu_icon'          => $settings['args']['menu_icon'] ?? 'dashicons-admin-post',
                'rewrite'            => $settings['args']['rewrite'] ?? true,
                'taxonomies'         => $settings['args']['taxonomies'] ?? [],
                'capability_type'    => $post_type,
                'map_meta_cap'       => true,
                'publicly_queryable' => true,
            ];

            if ( function_exists( 'register_post_type' ) ) {
                $args['show_in_graphql']     = $graphql_enabled;
                $args['graphql_single_name'] = $graphql_single;
                $args['graphql_plural_name'] = $graphql_plural;
                register_post_type( $post_type, apply_filters( 'anima_engine_register_post_type_args', $args, $post_type ) );
            }
        }
    }

    /**
     * Registra campos adicionales en WPGraphQL.
     */
    public function register_graphql_fields(): void {
        if ( ! function_exists( 'register_graphql_field' ) ) {
            return;
        }

        $meta_fields = [
            'anima_instructores' => __( 'Instructores principales asociados.', 'anima-engine' ),
            'anima_duracion'     => __( 'Duración estimada del contenido.', 'anima-engine' ),
            'anima_dificultad'   => __( 'Nivel de dificultad o audiencia.', 'anima-engine' ),
            'anima_kpis'         => __( 'KPIs o resultados destacados.', 'anima-engine' ),
            'anima_url_demo'     => __( 'URL de demo o streaming vinculada.', 'anima-engine' ),
        ];

        if ( $this->is_feature_enabled( 'enable_slider', true ) ) {
            $meta_fields['anima_slide_url'] = __( 'Enlace del botón del slide.', 'anima-engine' );
        }

        foreach ( $this->post_types as $post_type => $settings ) {
            if ( 'slide' === $post_type && ! $this->is_feature_enabled( 'enable_slider', true ) ) {
                continue;
            }

            $type_name = $this->format_graphql_name( $settings['singular'] ?? $post_type );

            foreach ( $meta_fields as $meta_key => $description ) {
                if ( 'slide' !== $post_type && 'anima_slide_url' === $meta_key ) {
                    continue;
                }

                register_graphql_field(
                    $type_name,
                    $this->format_graphql_field_name( $meta_key ),
                    [
                        'type'        => 'String',
                        'description' => $description,
                        'resolve'     => static function ( $post ) use ( $meta_key ) {
                            $post_id = 0;

                            if ( is_object( $post ) ) {
                                if ( isset( $post->ID ) ) {
                                    $post_id = (int) $post->ID;
                                } elseif ( isset( $post->databaseId ) ) {
                                    $post_id = (int) $post->databaseId;
                                } elseif ( method_exists( $post, 'source' ) && isset( $post->source->ID ) ) {
                                    $post_id = (int) $post->source->ID;
                                }
                            }

                            if ( $post_id <= 0 ) {
                                return null;
                            }

                            $value = get_post_meta( $post_id, $meta_key, true );

                            if ( '' === $value ) {
                                return null;
                            }

                            return $value;
                        },
                    ]
                );
            }
        }
    }

    /**
     * Determina si una funcionalidad está habilitada.
     */
    protected function is_feature_enabled( string $flag, bool $default = true ): bool {
        $options = wp_parse_args( get_option( 'anima_engine_options', [] ), [ $flag => $default ] );

        if ( array_key_exists( $flag, $options ) ) {
            return (bool) $options[ $flag ];
        }

        return $default;
    }

    /**
     * Formatea nombres compatibles con GraphQL.
     */
    protected function format_graphql_name( string $name ): string {
        $normalized = remove_accents( $name );
        $normalized = strtolower( $normalized );
        $normalized = preg_replace( '/[^a-z0-9\s]/', '', $normalized ) ?? '';
        $normalized = ucwords( $normalized );
        $normalized = str_replace( ' ', '', $normalized );

        return $normalized ?: 'Content';
    }

    /**
     * Convierte claves meta en nombres de campos GraphQL.
     */
    protected function format_graphql_field_name( string $meta_key ): string {
        $normalized = remove_accents( str_replace( '_', ' ', $meta_key ) );
        $normalized = strtolower( $normalized );
        $normalized = preg_replace( '/[^a-z0-9\s]/', '', $normalized ) ?? '';
        $normalized = ucwords( $normalized );
        $normalized = str_replace( ' ', '', $normalized );

        return lcfirst( $normalized ?: $meta_key );
    }
}
