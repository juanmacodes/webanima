<?php
namespace Anima_Engine\Widgets;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
if ( ! defined( 'ABSPATH' ) ) exit;
class Anima_Services extends Widget_Base {
  public function get_name(){ return 'anima_services'; }
  public function get_title(){ return __('Anima Servicios','anima-engine'); }
  public function get_icon(){ return 'eicon-apps'; }
  public function get_categories(){ return ['anima-category']; }
  public function get_style_depends(){ return ['anima-engine']; }
  protected function register_controls(){
    $this->start_controls_section('content',['label'=>__('Servicios','anima-engine')]);
      $this->add_control('columns',['label'=>__('Columnas','anima-engine'),'type'=>Controls_Manager::SELECT,'default'=>'3','options'=>['2'=>'2','3'=>'3']]);
      $rep = new \Elementor\Repeater();
      $rep->add_control('icon',['label'=>__('Icono (emoji o URL SVG)','anima-engine'),'type'=>Controls_Manager::TEXT,'default'=>'🕶️']);
      $rep->add_control('title',['label'=>__('Título','anima-engine'),'type'=>Controls_Manager::TEXT,'default'=>'AVATARES VR']);
      $rep->add_control('text',['label'=>__('Texto','anima-engine'),'type'=>Controls_Manager::TEXT,'default'=>'Aulas multiusuario y showrooms 3D.']);
      $this->add_control('items',['type'=>Controls_Manager::REPEATER,'fields'=>$rep->get_controls(),'default'=>[
        ['icon'=>'🕶️','title'=>'AVATARES VR','text'=>'Aulas multiusuario y showrooms 3D.'],
        ['icon'=>'🔷','title'=>'AVATARES HOLOGRÁFICOS','text'=>'Cabinas volumétricas para eventos.'],
        ['icon'=>'📡','title'=>'STREAMING','text'=>'Presentadores virtuales en directo.'],
      ]]);
    $this->end_controls_section();
  }
  protected function render(){
    $s = $this->get_settings_for_display();
    $cols = $s['columns'] ?? '3';
    echo '<div class="anima-grid cols-'.$cols.'">';
    foreach($s['items'] as $it){
      $icon = esc_html($it['icon']);
      if ( filter_var($icon, FILTER_VALIDATE_URL) ) {
        $icon_html = '<img src="'.esc_url($icon).'" alt="" style="width:28px;height:28px">';
      } else {
        $icon_html = '<div class="svc-ico" style="font-size:28px">'.$icon.'</div>';
      }
      echo '<article class="anima-card" style="padding:24px">';
      echo $icon_html;
      echo '<h3>'.esc_html($it['title']).'</h3>';
      echo '<p style="color:var(--anima-muted)">'.esc_html($it['text']).'</p>';
      echo '</article>';
    }
    echo '</div>';
  }
}
