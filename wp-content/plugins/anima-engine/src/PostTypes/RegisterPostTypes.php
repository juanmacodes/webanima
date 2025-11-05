<?php
namespace Anima\Engine\PostTypes;

use Anima\Engine\Services\ServiceInterface;

use function __;
use function sprintf;

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
            'curso'      => [
                'singular' => __( 'Curso', 'anima-engine' ),
                'plural'   => __( 'Cursos', 'anima-engine' ),
                'args'     => [
                    'menu_icon' => 'dashicons-welcome-learn-more',
                    'rewrite'   => [ 'slug' => 'cursos' ],
                    'taxonomies'=> [ 'nivel', 'modalidad', 'tecnologia' ],
                ],
            ],
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
    }

    /**
     * Registra los tipos.
     */
    public function register_post_types(): void {
        foreach ( $this->post_types as $post_type => $settings ) {
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
                $args['show_in_graphql']    = function_exists( 'register_graphql_object_type' );
                $args['graphql_single_name'] = $settings['singular'];
                $args['graphql_plural_name'] = $settings['plural'];
                register_post_type( $post_type, apply_filters( 'anima_engine_register_post_type_args', $args, $post_type ) );
            }
        }
    }
}
