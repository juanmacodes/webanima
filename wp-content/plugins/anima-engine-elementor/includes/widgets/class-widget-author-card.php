<?php
namespace AnimaEngine\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Author_Card extends Widget_Base {
  public function get_name(){ return 'anima_author_card'; }
  public function get_title(){ return __('Autor / Equipo', 'anima-engine'); }
  public function get_icon(){ return 'eicon-person'; }
  public function get_categories(){ return ['anima']; }

  protected function register_controls(){
    $this->start_controls_section('c', ['label'=>__('Datos','anima-engine')]);
    $this->add_control('avatar', ['label'=>__('Avatar','anima-engine'), 'type'=>Controls_Manager::MEDIA]);
    $this->add_control('name',   ['label'=>__('Nombre','anima-engine'), 'type'=>Controls_Manager::TEXT, 'default'=>'Equipo Anima']);
    $this->add_control('role',   ['label'=>__('Rol','anima-engine'), 'type'=>Controls_Manager::TEXT, 'default'=>'XR / IA']);
    $this->add_control('bio',    ['label'=>__('Bio','anima-engine'), 'type'=>Controls_Manager::TEXTAREA, 'default'=>'Creamos experiencias inmersivas.']);
    $this->end_controls_section();
  }

  protected function render(){
    $s = $this->get_settings();
    $img = !empty($s['avatar']['url']) ? esc_url($s['avatar']['url']) : '';
    echo '<div class="anima-author">';
    echo $img ? '<img class="anima-author__avatar" src="'.$img.'" alt="">' : '';
    echo '<div class="anima-author__meta">';
    echo '<strong class="anima-author__name">'.esc_html($s['name']).'</strong>';
    echo '<span class="anima-author__role">'.esc_html($s['role']).'</span>';
    echo '<p class="anima-author__bio">'.wp_kses_post($s['bio']).'</p>';
    echo '</div></div>';
  }
}