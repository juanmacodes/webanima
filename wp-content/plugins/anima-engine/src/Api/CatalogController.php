<?php
namespace Anima\Engine\Api;

use Anima\Engine\Models\Asset;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

use const MINUTE_IN_SECONDS;

use function absint;
use function array_map;
use function ceil;
use function __;
use function get_option;
use function get_transient;
use function in_array;
use function is_array;
use function register_rest_route;
use function rest_ensure_response;
use function sanitize_text_field;
use function set_transient;
use function strtolower;
use function wp_json_encode;

/**
 * Controlador REST para exponer el catálogo de assets disponibles.
 */
class CatalogController
{
    protected Asset $assets;

    public function __construct(?Asset $assets = null)
    {
        $this->assets = $assets ?? new Asset();
    }

    /**
     * Registra las rutas del controlador.
     */
    public function register_routes(): void
    {
        register_rest_route(
            'anima/v' . ANIMA_ENGINE_API_VERSION,
            '/catalog',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_catalog' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'type'     => [
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'search'   => [
                        'required'          => false,
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'page'     => [
                        'required'          => false,
                        'sanitize_callback' => 'absint',
                    ],
                    'per_page' => [
                        'required'          => false,
                        'sanitize_callback' => 'absint',
                    ],
                ],
            ]
        );
    }

    /**
     * Devuelve el catálogo filtrado.
     */
    public function get_catalog(WP_REST_Request $request)
    {
        $type      = sanitize_text_field($request->get_param('type') ?? '');
        $search    = sanitize_text_field($request->get_param('search') ?? '');
        $page      = absint($request->get_param('page') ?? 1);
        $perPage   = absint($request->get_param('per_page') ?? 24);

        if ('' !== $type) {
            $type = strtolower($type);
            if (! in_array($type, [ 'skin', 'environment' ], true)) {
                return new WP_Error('anima_engine_invalid_type', __('El tipo solicitado no es válido.', 'anima-engine'), [ 'status' => 400 ]);
            }
        }

        if ($perPage <= 0) {
            $perPage = 24;
        }

        $queryArgs = [
            'type'     => '' !== $type ? $type : null,
            'search'   => '' !== $search ? $search : null,
            'page'     => max(1, $page),
            'per_page' => $perPage,
        ];

        $cacheKey = $this->build_cache_key($queryArgs);
        if ($this->is_cache_enabled()) {
            $cached = get_transient($cacheKey);
            if (false !== $cached && is_array($cached)) {
                return rest_ensure_response($cached);
            }
        }

        $result = $this->assets->getCatalog($queryArgs);

        $total       = (int) ($result['total'] ?? 0);
        $items       = $result['items'] ?? [];
        $totalPages  = (int) ceil($total / $perPage);

        $items = array_map(
            static function (array $item): array {
                return [
                    'id'        => (int) ($item['id'] ?? 0),
                    'slug'      => (string) ($item['slug'] ?? ''),
                    'title'     => (string) ($item['title'] ?? ''),
                    'media_url' => (string) ($item['media_url'] ?? ''),
                    'price'     => isset($item['price']) ? (float) $item['price'] : 0.0,
                    'version'   => (string) ($item['version'] ?? ''),
                ];
            },
            is_array($items) ? $items : []
        );

        $response = [
            'items'      => $items,
            'pagination' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'page'         => max(1, $page),
                'total_pages'  => $totalPages > 0 ? $totalPages : 1,
            ],
        ];

        if ($this->is_cache_enabled()) {
            set_transient($cacheKey, $response, $this->get_cache_ttl());
        }

        return rest_ensure_response($response);
    }

    protected function build_cache_key(array $args): string
    {
        return 'anima_engine_catalog_' . md5(wp_json_encode($args));
    }

    protected function is_cache_enabled(): bool
    {
        $options = get_option('anima_engine_options', []);
        if (array_key_exists('cache_catalog', $options)) {
            return (bool) $options['cache_catalog'];
        }

        return true;
    }

    protected function get_cache_ttl(): int
    {
        return MINUTE_IN_SECONDS * 5;
    }
}
