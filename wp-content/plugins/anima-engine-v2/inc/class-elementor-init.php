<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class Anima_Engine_Elementor_Init {
  public function __construct() {
    add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
    add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
  }
  public function register_category( $elements_manager ) {
    $elements_manager->add_category('anima-category',[ 'title' => __( 'Anima', 'anima-engine' ), 'icon'  => 'fa fa-plug' ]);
  }
  public function register_widgets( $widgets_manager ) {
    require_once ANIMA_ENGINE_DIR . 'inc/widgets/class-anima-hero3d.php';
    require_once ANIMA_ENGINE_DIR . 'inc/widgets/class-anima-cards.php';
    require_once ANIMA_ENGINE_DIR . 'inc/widgets/class-anima-services.php';
    require_once ANIMA_ENGINE_DIR . 'inc/widgets/class-anima-courses.php';
    require_once ANIMA_ENGINE_DIR . 'inc/widgets/class-anima-projects.php';
    require_once ANIMA_ENGINE_DIR . 'inc/widgets/class-anima-gallery3d.php';
    $widgets_manager->register( new \Anima_Engine\Widgets\Anima_Hero3D() );
    $widgets_manager->register( new \Anima_Engine\Widgets\Anima_Cards() );
    $widgets_manager->register( new \Anima_Engine\Widgets\Anima_Services() );
    $widgets_manager->register( new \Anima_Engine\Widgets\Anima_Courses() );
    $widgets_manager->register( new \Anima_Engine\Widgets\Anima_Projects() );
    $widgets_manager->register( new \Anima_Engine\Widgets\Anima_Gallery3D() );
  }
}
new Anima_Engine_Elementor_Init();
