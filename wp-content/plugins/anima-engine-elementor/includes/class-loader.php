<?php
namespace AnimaEngine\Elementor;

if ( ! defined('ABSPATH') ) exit;

class Loader {
  public function __construct() {
    add_action('elementor/widgets/register', [$this, 'register_widgets']);
    add_action('elementor/elements/categories_registered', [$this, 'register_category']);
  }

  public function register_category($elements_manager) {
    $elements_manager->add_category('anima', [
      'title' => __('Anima', 'anima-engine'),
      'icon'  => 'fa fa-star',
    ]);
  }

  public function register_widgets($widgets_manager) {
    require_once ANIMA_EW_PATH.'includes/widgets/class-widget-chips-tax.php';
    require_once ANIMA_EW_PATH.'includes/widgets/class-widget-kpi-bar.php';
    require_once ANIMA_EW_PATH.'includes/widgets/class-widget-process-steps.php';
    require_once ANIMA_EW_PATH.'includes/widgets/class-widget-model-viewer.php';
    require_once ANIMA_EW_PATH.'includes/widgets/class-widget-media-gallery.php';
    require_once ANIMA_EW_PATH.'includes/widgets/class-widget-video-embed.php';
    require_once ANIMA_EW_PATH.'includes/widgets/class-widget-author-card.php';
    require_once ANIMA_EW_PATH.'includes/widgets/class-widget-share.php';
    require_once ANIMA_EW_PATH.'includes/widgets/class-widget-related-stories.php';

    $widgets_manager->register( new Widgets\Chips_Tax() );
    $widgets_manager->register( new Widgets\KPI_Bar() );
    $widgets_manager->register( new Widgets\Process_Steps() );
    $widgets_manager->register( new Widgets\Model_Viewer() );
    $widgets_manager->register( new Widgets\Media_Gallery() );
    $widgets_manager->register( new Widgets\Video_Embed() );
    $widgets_manager->register( new Widgets\Author_Card() );
    $widgets_manager->register( new Widgets\Share() );
    $widgets_manager->register( new Widgets\Related_Stories() );

    $avatar_exists = false;

    if ( method_exists($widgets_manager, 'is_widget_type_registered') && $widgets_manager->is_widget_type_registered('anima_avatars_grid') ) {
      $avatar_exists = true;
    }

    if ( ! $avatar_exists && method_exists($widgets_manager, 'get_widget_types') ) {
      $avatar_exists = isset($widgets_manager->get_widget_types()['anima_avatars_grid']);
    }

    if (
      ! $avatar_exists
      && class_exists('\\Elementor\\Plugin')
      && isset(\Elementor\Plugin::$instance->widgets_manager)
      && method_exists(\Elementor\Plugin::$instance->widgets_manager, 'is_widget_type_registered')
    ) {
      $avatar_exists = \Elementor\Plugin::$instance->widgets_manager->is_widget_type_registered('anima_avatars_grid');
    }

    if ( $avatar_exists ) {
      return;
    }

    require_once ANIMA_EW_PATH.'includes/widgets/class-widget-avatars-grid.php';
    $widgets_manager->register( new Widgets\Anima_Avatares_Grid() );
  }
}

new Loader();