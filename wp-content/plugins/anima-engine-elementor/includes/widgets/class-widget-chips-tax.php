<?php
namespace AnimaEngine\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined('ABSPATH') ) exit;

class Chips_Tax extends Widget_Base {
  public function get_name(){ return 'anima_chips_tax'; }
  public function get_title(){ return __('Chips de Taxonomía', 'anima-engine'); }
  public function get_icon(){ return 'eicon-filter'; }
  public function get_categories(){ return ['anima']; }

  protected function register_controls(){
    $this->start_controls_section('content', ['label'=>__('Contenido','anima-engine')]);

    $this->add_control('taxonomy', [
      'label' => __('Taxonomía','anima-engine'),
      'type'  => Controls_Manager::SELECT,
      'default' => 'servicio',
      'options' => [
        'category'       => __('Categoría', 'anima-engine'),
        'post_tag'       => __('Etiqueta', 'anima-engine'),
        'servicio'       => __('Servicio (CPT Proyecto)', 'anima-engine'),
        'tipo-historia'  => __('Tipo de historia', 'anima-engine'),
      ],
    ]);

    $this->add_control('style', [
      'label' => __('Estilo','anima-engine'),
      'type'  => Controls_Manager::SELECT,
      'default' => 'chip',
      'options' => ['chip'=>'Chip','pill'=>'Píldora','link'=>'Link'],
    ]);

    $this->add_control('base_url', [
      'label' => __('URL base (lista de historias)','anima-engine'),
      'type'  => Controls_Manager::TEXT,
      'placeholder' => '/historias/',
      'default' => '/historias/',
    ]);

    $this->end_controls_section();
  }

  protected function render(){
    $tax = $this->get_settings('taxonomy');
    $style = $this->get_settings('style');
    $base  = trailingslashit($this->get_settings('base_url'));

    $terms = get_terms(['taxonomy'=>$tax,'hide_empty'=>true]);
    if ( is_wp_error($terms) || empty($terms) ) {
      echo '<div class="anima-chips empty">'.esc_html__('No hay términos.','anima-engine').'</div>';
      return;
    }
    echo '<div class="anima-chips anima-chips--'.$style.'">';
    foreach ($terms as $t){
      $url = esc_url( add_query_arg([$tax=>$t->slug], $base) );
      echo '<a class="anima-chip" href="'.$url.'">'.esc_html($t->name).'</a>';
    }
    echo '</div>';
  }
}