<?php
namespace AnimaEngine\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Model_Viewer extends Widget_Base {
  public function get_name(){ return 'anima_model_viewer'; }
  public function get_title(){ return __('Visor 3D (GLB/GLTF)', 'anima-engine'); }
  public function get_icon(){ return 'eicon-gallery-grid'; }
  public function get_categories(){ return ['anima']; }

  protected function register_controls(){
    $this->start_controls_section('media', ['label'=>__('3D','anima-engine')]);

    $this->add_control('src', ['label'=>__('URL GLB/GLTF','anima-engine'), 'type'=>Controls_Manager::URL, 'show_external'=>true, 'placeholder'=>'https://.../avatar.glb']);
    $this->add_control('poster', ['label'=>__('Poster','anima-engine'), 'type'=>Controls_Manager::MEDIA]);
    $this->add_control('auto_rotate', ['label'=>__('Auto rotar','anima-engine'), 'type'=>Controls_Manager::SWITCHER, 'return_value'=>'yes', 'default'=>'yes']);
    $this->add_control('camera_controls', ['label'=>__('Controles de cámara','anima-engine'), 'type'=>Controls_Manager::SWITCHER, 'return_value'=>'yes', 'default'=>'yes']);
    $this->add_control('exposure', ['label'=>__('Exposición','anima-engine'),'type'=>Controls_Manager::SLIDER,'range'=>['px'=>['min'=>0,'max'=>2,'step'=>0.1]],'default'=>['size'=>0.9]]);
    $this->add_control('height', ['label'=>__('Alto (px)','anima-engine'),'type'=>Controls_Manager::NUMBER,'default'=>420]);

    $this->end_controls_section();
  }

  protected function render(){
    wp_enqueue_script('model-viewer');
    $s   = $this->get_settings();
    $src = esc_url($s['src']['url'] ?? '');
    if (!$src){ echo '<em>'.__('Configura la URL del modelo.','anima-engine').'</em>'; return; }
    $poster = !empty($s['poster']['url']) ? esc_url($s['poster']['url']) : '';
    $auto   = $s['auto_rotate'] ? 'auto-rotate' : '';
    $cam    = $s['camera_controls'] ? 'camera-controls' : '';
    $exp    = isset($s['exposure']['size']) ? floatval($s['exposure']['size']) : 1.0;
    $h      = intval($s['height']);
    echo '<div class="anima-3d" style="height:'.$h.'px">';
    echo '<model-viewer src="'.$src.'" '.($poster?'poster="'.$poster.'"':'').' '.$auto.' '.$cam.' exposure="'.$exp.'" style="width:100%;height:100%;border-radius:16px;background:#0b111a;" interaction-prompt="none" ar ar-modes="webxr scene-viewer quick-look"></model-viewer>';
    echo '</div>';
  }
}