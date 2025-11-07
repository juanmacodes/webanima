<?php
namespace AnimaEngine\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use WP_Query;

class Related_Stories extends Widget_Base {
  public function get_name(){ return 'anima_related_stories'; }
  public function get_title(){ return __('Historias relacionadas', 'anima-engine'); }
  public function get_icon(){ return 'eicon-posts-grid'; }
  public function get_categories(){ return ['anima']; }

  protected function register_controls(){
    $this->start_controls_section('q', ['label'=>__('Consulta','anima-engine')]);
    $this->add_control('taxonomy', ['label'=>__('Taxonomía base','anima-engine'),'type'=>Controls_Manager::TEXT,'default'=>'servicio']);
    $this->add_control('count',    ['label'=>__('Número','anima-engine'),'type'=>Controls_Manager::NUMBER,'default'=>3,'min'=>1,'max'=>12]);
    $this->end_controls_section();
  }

  protected function render(){
    global $post;
    if (! $post) return;
    $tax   = sanitize_key($this->get_settings('taxonomy'));
    $count = intval($this->get_settings('count'));

    $terms = wp_get_post_terms($post->ID, $tax, ['fields'=>'ids']);
    if ( empty($terms) ) { echo '<em>'.__('Sin términos para relacionar.','anima-engine').'</em>'; return; }

    $q = new WP_Query([
      'post_type'      => $post->post_type,
      'posts_per_page' => $count,
      'post__not_in'   => [$post->ID],
      'tax_query'      => [[ 'taxonomy'=>$tax, 'terms'=>$terms ]]
    ]);

    if ( ! $q->have_posts() ){
      echo '<em>'.__('No hay historias relacionadas.','anima-engine').'</em>'; return;
    }

    echo '<div class="anima-related">';
    while ( $q->have_posts() ){ $q->the_post();
      echo '<article class="anima-related__item">';
      if ( has_post_thumbnail() ) {
        echo '<a href="'.get_permalink().'">'.get_the_post_thumbnail(get_the_ID(),'medium_large', ['class'=>'anima-related__thumb']).'</a>';
      }
      echo '<h4 class="anima-related__title"><a href="'.get_permalink().'">'.get_the_title().'</a></h4>';
      echo '</article>';
    }
    echo '</div>';
    wp_reset_postdata();
  }
}