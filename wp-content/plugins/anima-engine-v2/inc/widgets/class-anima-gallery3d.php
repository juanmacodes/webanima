<?php
namespace Anima_Engine\Widgets;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
if ( ! defined( 'ABSPATH' ) ) exit;
class Anima_Gallery3D extends Widget_Base {
  public function get_name(){ return 'anima_gallery3d'; }
  public function get_title(){ return __('Anima Galería 3D (QuickView)','anima-engine'); }
  public function get_icon(){ return 'eicon-carousel'; }
  public function get_categories(){ return ['anima-category']; }
  public function get_style_depends(){ return ['anima-engine']; }
  public function get_script_depends(){ return ['anima-quickview','model-viewer']; }
  protected function register_controls(){
    $this->start_controls_section('content',['label'=>__('Galería','anima-engine')]);
      $this->add_control('columns',['label'=>__('Columnas','anima-engine'),'type'=>Controls_Manager::SELECT,'default'=>'3','options'=>['2'=>'2','3'=>'3']]);
      $rep = new \Elementor\Repeater();
      $rep->add_control('poster',['label'=>__('Poster','anima-engine'),'type'=>Controls_Manager::MEDIA]);
      $rep->add_control('title',['label'=>__('Título','anima-engine'),'type'=>Controls_Manager::TEXT,'default'=>'Avatar demo']);
      $rep->add_control('model_src',['label'=>__('URL GLB/GLTF','anima-engine'),'type'=>Controls_Manager::TEXT,'default'=>'https://assets.readyplayer.me/Example.glb']);
      $this->add_control('items',['type'=>Controls_Manager::REPEATER,'fields'=>$rep->get_controls(),'default'=>[
        ['title'=>'Avatar demo 1'],['title'=>'Avatar demo 2'],['title'=>'Avatar demo 3']
      ]]);
    $this->end_controls_section();
  }
  protected function render(){
    $s = $this->get_settings_for_display();
    $cols = $s['columns'] ?? '3';
    echo '<div class="anima-grid cols-'.$cols.'">';
    foreach($s['items'] as $it){
      $poster = !empty($it['poster']['url']) ? $it['poster']['url'] : 'https://picsum.photos/1200/700?random=71';
      $src = $it['model_src'] ?? '';
      echo '<article class="anima-card">';
      echo '<img src="'.esc_url($poster).'" alt="" style="width:100%;display:block">';
      echo '<div style="padding:16px;display:flex;align-items:center;justify-content:space-between;gap:12px">';
      echo '<div><h3 style="margin:.2em 0">'.esc_html($it['title']).'</h3><p style="color:var(--anima-muted);margin:0">GLB · vista previa</p></div>';
      echo '<a href="#" class="anima-btn anima-btn--ghost" data-anima-qv data-src="'.esc_attr($src).'" data-poster="'.esc_attr($poster).'">Ver en 3D</a>';
      echo '</div></article>';
    }
    echo '</div>';
    echo '<div id="anima-qv"><div class="wrap">';
    echo '<div class="hd"><strong>Vista previa 3D</strong><button id="anima-qv-close" aria-label="Cerrar">✕</button></div>';
    echo '<model-viewer src="" crossorigin="anonymous" camera-controls auto-rotate exposure="1.0" shadow-intensity="1" reveal="interaction" style="width:100%;height:min(70vh,640px);background:#0b0b10"></model-viewer>';
    echo '<div class="ft"><button id="anima-qv-close">Cerrar</button></div>';
    echo '</div></div>';
  }
}
