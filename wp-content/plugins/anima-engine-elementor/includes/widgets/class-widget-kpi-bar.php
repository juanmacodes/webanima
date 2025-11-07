<?php
namespace AnimaEngine\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class KPI_Bar extends Widget_Base {
  public function get_name(){ return 'anima_kpi_bar'; }
  public function get_title(){ return __('Barra de KPIs', 'anima-engine'); }
  public function get_icon(){ return 'eicon-counter'; }
  public function get_categories(){ return ['anima']; }

  protected function register_controls(){
    $this->start_controls_section('kpis', ['label'=>__('Campos','anima-engine')]);

    $this->add_control('items', [
      'label' => __('Campos (meta key => etiqueta)','anima-engine'),
      'type'  => Controls_Manager::TEXTAREA,
      'default' => "anima_views => Vistas\nanima_runtime => Min",
      'description' => __('Una por línea, formato: meta_key => Etiqueta','anima-engine')
    ]);

    $this->end_controls_section();
  }

  protected function render(){
    global $post;
    if ( ! $post ) return;
    $lines = array_filter(array_map('trim', explode("\n", (string)$this->get_settings('items'))));
    echo '<div class="anima-kpi-bar">';
    foreach($lines as $line){
      if (!str_contains($line,'=>')) continue;
      [$key,$label] = array_map('trim', explode('=>', $line, 2));
      $val = get_post_meta($post->ID, $key, true);
      if ($val === '') continue;
      echo '<div class="anima-kpi"><span class="anima-kpi__val">'.esc_html($val).'</span><span class="anima-kpi__label">'.esc_html($label).'</span></div>';
    }
    echo '</div>';
  }
}