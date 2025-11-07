<?php
namespace Anima\Engine;

use Anima\Engine\Admin\OptionsPage;
use Anima\Engine\Api\RestApi;
use Anima\Engine\Cache\CacheManager;
use Anima\Engine\Metaboxes\RegisterMetaboxes;
use Anima\Engine\PostTypes\RegisterPostTypes;
use Anima\Engine\Commerce\Orders;
use Anima\Engine\Commerce\SubscriptionService;
use Anima\Engine\Services\CacheInvalidator;
use Anima\Engine\Services\Cli\MigrateCoursesMetaCommand;
use Anima\Engine\Services\Cli\SeedCommand;
use Anima\Engine\Services\ElementorTokens;
use Anima\Engine\Services\ServiceInterface;
use Anima\Engine\Seo\SchemaService;
use Anima\Engine\Shortcodes\Shortcodes;
use Anima\Engine\Taxonomies\RegisterTaxonomies;
use Anima\Engine\Config\Settings;

/**
 * Clase principal del plugin.
 */
class Plugin {
    /**
     * Lista de servicios registrados.
     *
     * @var array<int, ServiceInterface>
     */
    protected array $services = [];

    /**
     * Inicializa el plugin.
     */
    public function init(): void {
        $this->services = [
            new RegisterPostTypes(),
            new RegisterTaxonomies(),
            new RegisterMetaboxes(),
            new Shortcodes(),
            new RestApi(),
            new OptionsPage(),
            new CacheInvalidator(),
            new ElementorTokens(),
            new SchemaService(),
            new CacheManager(),
            new Orders(),
            new SubscriptionService(),
            new Settings(),
            new SeedCommand(),
            new MigrateCoursesMetaCommand(),
        ];

        foreach ( $this->services as $service ) {
            if ( $service instanceof ServiceInterface ) {
                $service->register();
            }
        }

        do_action( 'anima_engine_initialized', $this );
    }
}
