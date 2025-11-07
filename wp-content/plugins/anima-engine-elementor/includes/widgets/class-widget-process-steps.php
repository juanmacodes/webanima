<?php
namespace AnimaEngine\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

class Process_Steps extends Widget_Base {
  public function get_name(){ return 'anima_process_steps'; }
  public function get_title(){ return __('Proceso (pasos)', 'anima-engine'); }
  public function get_icon(){ return 'eicon-bullet-list'; }
  public function get_categories(){ return ['anima']; }

  protected function register_controls(){
    $this->start_controls_section('content', ['label'=>__('Pasos','anima-engine')]);

    $rep = new Repeater();
    $rep->add_control('title', ['label'=>__('Título','anima-engine'), 'type'=>Controls_Manager::TEXT, 'default'=>'Paso']);
    $rep->add_control('text',  ['label'=>__('Descripción','anima-engine'), 'type'=>Controls_Manager::TEXTAREA, 'default'=>'Detalle breve.']);
    $rep->add_control('icon',  ['label'=>__('Icono','anima-engine'), 'type'=>Controls_Manager::ICONS]);

    $this->add_control('steps', [
      'type'        => Controls_Manager::REPEATER,
      'fields'      => $rep->get_controls(),
      'default'     => [
        ['title'=>'Concepto', 'text'=>'Definimos estilo y propósito.'],
        ['title'=>'Customizado', 'text'=>'Ajustes en Anima Live.'],
        ['title'=>'Integración', 'text'=>'Voz IA + lipsync.'],
        ['title'=>'Evento', 'text'=>'Publicación en streaming/VR.'],
      ],
      'title_field' => '{{{ title }}}'
    ]);

    $this->end_controls_section();
  }

  protected function render(){
    $steps = $this->get_settings('steps');
    echo '<div class="anima-steps">';
    foreach($steps as $s){
      echo '<div class="anima-step">';
      if (!empty($s['icon']['value'])){
        \Elementor\Icons_Manager::render_icon($s['icon'], ['aria-hidden' => 'true', 'class'=>'anima-step__icon']);
      }
      echo '<h4 class="anima-step__title">'.esc_html($s['title']).'</h4>';
      echo '<p class="anima-step__text">'.wp_kses_post($s['text']).'</p>';
      echo '</div>';
    }
    echo '</div>';
  }
}