<?php
namespace AnimaEngine\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Video_Embed extends Widget_Base {
  public function get_name(){ return 'anima_video_embed'; }
  public function get_title(){ return __('Vídeo (Historia)', 'anima-engine'); }
  public function get_icon(){ return 'eicon-play'; }
  public function get_categories(){ return ['anima']; }

  protected function register_controls(){
    $this->start_controls_section('content', ['label'=>__('Vídeo','anima-engine')]);
    $this->add_control('url', ['label'=>__('URL YouTube/Vimeo','anima-engine'),'type'=>Controls_Manager::URL,'placeholder'=>'https://youtu.be/...']);
    $this->add_control('ratio', ['label'=>__('Aspecto','anima-engine'),'type'=>Controls_Manager::SELECT,'default'=>'16x9','options'=>['16x9'=>'16:9','21x9'=>'21:9','1x1'=>'1:1']]);
    $this->end_controls_section();
  }

  protected function render(){
    $u = esc_url($this->get_settings('url')['url'] ?? '');
    if (!$u){ echo '<em>'.__('Configura la URL del vídeo.','anima-engine').'</em>'; return; }
    $ratio = $this->get_settings('ratio') ?? '16x9';
    echo '<div class="anima-video anima-video--'.$ratio.'">';
    echo wp_oembed_get($u) ?: '<iframe src="'.esc_url($u).'" frameborder="0" allowfullscreen></iframe>';
    echo '</div>';
  }
}