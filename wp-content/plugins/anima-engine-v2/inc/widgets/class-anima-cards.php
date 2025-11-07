<?php
namespace Anima_Engine\Widgets;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
if ( ! defined( 'ABSPATH' ) ) exit;
class Anima_Cards extends Widget_Base {
  public function get_name(){ return 'anima_cards'; }
  public function get_title(){ return __('Anima Cards','anima-engine'); }
  public function get_icon(){ return 'eicon-posts-grid'; }
  public function get_categories(){ return ['anima-category']; }
  public function get_style_depends(){ return ['anima-engine']; }
  protected function register_controls(){
    $this->start_controls_section('content',['label'=>__('Contenido','anima-engine')]);
      $this->add_control('columns',['label'=>__('Columnas','anima-engine'),'type'=>Controls_Manager::SELECT,'default'=>'3','options'=>['2'=>'2','3'=>'3']]);
      $rep = new \Elementor\Repeater();
      $rep->add_control('image',['label'=>__('Imagen','anima-engine'),'type'=>Controls_Manager::MEDIA]);
      $rep->add_control('title',['label'=>__('Título','anima-engine'),'type'=>Controls_Manager::TEXT,'default'=>'Proyecto inmersivo']);
      $rep->add_control('text',['label'=>__('Texto','anima-engine'),'type'=>Controls_Manager::TEXT,'default'=>'Evento con avatar IA y WebXR.']);
      $rep->add_control('link',['label'=>__('Enlace','anima-engine'),'type'=>Controls_Manager::URL,'default'=>['url'=>'#']]);
      $this->add_control('items',['type'=>Controls_Manager::REPEATER,'fields'=>$rep->get_controls(),'default'=>[
        ['title'=>'Proyecto inmersivo','text'=>'Evento con avatar IA y WebXR.'],
        ['title'=>'Showroom 3D','text'=>'WebXR de producto en tiempo real.'],
        ['title'=>'Aula VR','text'=>'Capacitación multiusuario.']
      ]]);
    $this->end_controls_section();
  }
  protected function render(){
    $s = $this->get_settings_for_display();
    $cols = $s['columns'] ?? '3';
    echo '<div class="anima-grid cols-'.$cols.'">';
    foreach($s['items'] as $it){
      $img = !empty($it['image']['url']) ? '<img src="'.esc_url($it['image']['url']).'" alt="" style="width:100%;display:block">' : '';
      $url = $it['link']['url'] ?? '#';
      echo '<article class="anima-card">';
      echo $img;
      echo '<div style="padding:16px">';
      echo '<h3 style="margin:.2em 0">'.esc_html($it['title']).'</h3>';
      echo '<p style="color:var(--anima-muted)">'.esc_html($it['text']).'</p>';
      echo '<a class="anima-btn anima-btn--ghost" href="'.esc_url($url).'">Ver más</a>';
      echo '</div></article>';
    }
    echo '</div>';
  }
}
