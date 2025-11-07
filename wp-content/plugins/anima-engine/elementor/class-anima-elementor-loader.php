<?php
namespace Anima\Engine\Elementor;

defined( 'ABSPATH' ) || exit;

use Elementor\Elements_Manager;
use Elementor\Widgets_Manager;
use function __;
use function add_action;
use function class_exists;
use function did_action;

/**
 * Registra la categoría personalizada de Elementor y widgets.
 */
class Loader {
    public function __construct() {
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
    }

    /**
     * Agrega la categoría "Anima" en Elementor.
     */
    public function register_category( Elements_Manager $elements_manager ): void {
        $elements_manager->add_category(
            'anima',
            [
                'title' => __( 'Anima', 'anima-engine' ),
                'icon'  => 'fa fa-bolt',
            ],
            1
        );
    }

    /**
     * Registra los widgets personalizados.
     */
    public function register_widgets( Widgets_Manager $widgets_manager ): void {
        require_once __DIR__ . '/widget-cursos-grid.php';
        require_once __DIR__ . '/widget-curso-card.php';
        require_once __DIR__ . '/widget-curso-meta.php';
        require_once __DIR__ . '/widget-curso-syllabus.php';
        require_once __DIR__ . '/widget-avatares-grid.php';
        require_once __DIR__ . '/widgets/widget-proyectos-tabs.php';

        $widgets_manager->register( new Widget_Cursos_Grid() );
        $widgets_manager->register( new Widget_Curso_Card() );
        $widgets_manager->register( new Widget_Curso_Meta() );
        $widgets_manager->register( new Widget_Curso_Syllabus() );
        $widgets_manager->register( new Widget_Avatares_Grid() );
        $widgets_manager->register( new Widget_Proyectos_Tabs() );
    }
}

if ( ! function_exists( '\\Anima\\Engine\\Elementor\\anima_engine_bootstrap_elementor_loader' ) ) {
    /**
     * Inicializa el cargador cuando Elementor esté disponible.
     */
    function anima_engine_bootstrap_elementor_loader(): void {
        $bootstrap = static function () {
            if ( class_exists( '\\Elementor\\Plugin' ) ) {
                new Loader();
            }
        };

        if ( did_action( 'elementor/loaded' ) ) {
            $bootstrap();
        } else {
            add_action( 'elementor/loaded', $bootstrap );
        }
    }
}

anima_engine_bootstrap_elementor_loader();
