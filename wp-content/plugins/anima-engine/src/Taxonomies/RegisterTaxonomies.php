<?php
namespace Anima\Engine\Taxonomies;

use Anima\Engine\Services\ServiceInterface;

use function __;
use function apply_filters;
use function class_exists;
use function preg_replace;
use function register_taxonomy_for_object_type;
use function sprintf;
use function strtolower;
use function str_replace;
use function ucwords;
use function remove_accents;
use function taxonomy_exists;

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
        $graphql_enabled = class_exists( '\\WPGraphQL' );

        foreach ( $this->taxonomies as $taxonomy => $settings ) {
            if ( taxonomy_exists( $taxonomy ) ) {
                foreach ( $settings['objects'] as $object_type ) {
                    register_taxonomy_for_object_type( $taxonomy, $object_type );
                }
                continue;
            }

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
                $args['show_in_graphql']     = $graphql_enabled;
                $args['graphql_single_name'] = $this->format_graphql_name( $settings['singular'] );
                $args['graphql_plural_name'] = $this->format_graphql_name( $settings['plural'] );
                register_taxonomy( $taxonomy, $settings['objects'], apply_filters( 'anima_engine_register_taxonomy_args', $args, $taxonomy ) );
            }
        }
    }

    /**
     * Convierte etiquetas a nombres válidos para GraphQL.
     */
    protected function format_graphql_name( string $name ): string {
        $normalized = remove_accents( $name );
        $normalized = strtolower( $normalized );
        $normalized = preg_replace( '/[^a-z0-9\s]/', '', $normalized ) ?? '';
        $normalized = ucwords( $normalized );
        $normalized = str_replace( ' ', '', $normalized );

        return $normalized ?: 'Taxonomy';
    }
}
