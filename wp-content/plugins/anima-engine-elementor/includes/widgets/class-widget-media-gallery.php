<?php
namespace AnimaEngine\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Media_Gallery extends Widget_Base {
  public function get_name(){ return 'anima_media_gallery'; }
  public function get_title(){ return __('Galería (Historia)', 'anima-engine'); }
  public function get_icon(){ return 'eicon-gallery-justified'; }
  public function get_categories(){ return ['anima']; }

  protected function register_controls(){
    $this->start_controls_section('content', ['label'=>__('Imágenes','anima-engine')]);

    $this->add_control('images', [
      'label' => __('Selecciona imágenes', 'anima-engine'),
      'type'  => Controls_Manager::GALLERY
    ]);

    $this->add_control('cols', [
      'label' => __('Columnas (desktop)', 'anima-engine'),
      'type'  => Controls_Manager::NUMBER,
      'default' => 3, 'min'=>1, 'max'=>6
    ]);

    $this->end_controls_section();
  }

  protected function render(){
    $imgs = $this->get_settings('images');
    $cols = max(1, intval($this->get_settings('cols')));
    if (empty($imgs)){ echo '<em>'.__('Añade imágenes.','anima-engine').'</em>'; return; }
    echo '<div class="anima-gallery" style="--cols:'.$cols.'">';
    foreach($imgs as $i){
      $url = esc_url($i['url']);
      echo '<a class="anima-gallery__item" href="'.$url.'" target="_blank" rel="noopener"><img src="'.$url.'" alt=""></a>';
    }
    echo '</div>';
  }
}