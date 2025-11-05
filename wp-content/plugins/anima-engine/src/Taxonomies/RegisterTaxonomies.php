<?php
namespace Anima\Engine\Taxonomies;

use Anima\Engine\Services\ServiceInterface;

use function __;
use function sprintf;

/**
 * Registro de taxonomías personalizadas.
 */
class RegisterTaxonomies implements ServiceInterface {
    /**
     * Taxonomías a registrar.
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $taxonomies = [];

    public function __construct() {
        $this->taxonomies = [
            'nivel'      => [
                'singular'    => __( 'Nivel', 'anima-engine' ),
                'plural'      => __( 'Niveles', 'anima-engine' ),
                'hierarchical'=> true,
                'objects'     => [ 'curso', 'experiencia' ],
            ],
            'modalidad'  => [
                'singular'    => __( 'Modalidad', 'anima-engine' ),
                'plural'      => __( 'Modalidades', 'anima-engine' ),
                'hierarchical'=> false,
                'objects'     => [ 'curso', 'experiencia' ],
            ],
            'tecnologia' => [
                'singular'    => __( 'Tecnología', 'anima-engine' ),
                'plural'      => __( 'Tecnologías', 'anima-engine' ),
                'hierarchical'=> false,
                'objects'     => [ 'curso', 'avatar', 'proyecto', 'experiencia' ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(): void {
        add_action( 'init', [ $this, 'register_taxonomies' ] );
    }

    /**
     * Registra las taxonomías.
     */
    public function register_taxonomies(): void {
        foreach ( $this->taxonomies as $taxonomy => $settings ) {
            $labels = [
                'name'              => $settings['plural'],
                'singular_name'     => $settings['singular'],
                'search_items'      => sprintf( __( 'Buscar %s', 'anima-engine' ), strtolower( $settings['plural'] ) ),
                'all_items'         => sprintf( __( 'Todas las %s', 'anima-engine' ), strtolower( $settings['plural'] ) ),
                'edit_item'         => sprintf( __( 'Editar %s', 'anima-engine' ), strtolower( $settings['singular'] ) ),
                'view_item'         => sprintf( __( 'Ver %s', 'anima-engine' ), strtolower( $settings['singular'] ) ),
                'update_item'       => sprintf( __( 'Actualizar %s', 'anima-engine' ), strtolower( $settings['singular'] ) ),
                'add_new_item'      => sprintf( __( 'Añadir nueva %s', 'anima-engine' ), strtolower( $settings['singular'] ) ),
                'new_item_name'     => sprintf( __( 'Nuevo nombre de %s', 'anima-engine' ), strtolower( $settings['singular'] ) ),
                'menu_name'         => $settings['plural'],
            ];

            $args = [
                'labels'            => $labels,
                'hierarchical'      => $settings['hierarchical'],
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_rest'      => true,
                'rewrite'           => [ 'slug' => $taxonomy ],
            ];

            if ( function_exists( 'register_taxonomy' ) ) {
                $args['show_in_graphql']    = function_exists( 'register_graphql_object_type' );
                $args['graphql_single_name'] = $settings['singular'];
                $args['graphql_plural_name'] = $settings['plural'];
                register_taxonomy( $taxonomy, $settings['objects'], apply_filters( 'anima_engine_register_taxonomy_args', $args, $taxonomy ) );
            }
        }
    }
}
