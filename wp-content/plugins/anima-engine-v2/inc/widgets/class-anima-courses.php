<?php
namespace Anima_Engine\Widgets;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use WP_Query;
if ( ! defined( 'ABSPATH' ) ) exit;
class Anima_Courses extends Widget_Base {
  public function get_name(){ return 'anima_courses'; }
  public function get_title(){ return __('Anima Cursos (CPT)','anima-engine'); }
  public function get_icon(){ return 'eicon-library-download'; }
  public function get_categories(){ return ['anima-category']; }
  public function get_style_depends(){ return ['anima-engine']; }
  protected function register_controls(){
    $this->start_controls_section('query',['label'=>__('Consulta','anima-engine')]);
      $this->add_control('posts_per_page',['label'=>__('Número','anima-engine'),'type'=>Controls_Manager::NUMBER,'default'=>6]);
      $this->add_control('columns',['label'=>__('Columnas','anima-engine'),'type'=>Controls_Manager::SELECT,'default'=>'3','options'=>['2'=>'2','3'=>'3']]);
      $this->add_control('show_price',['label'=>__('Mostrar precio','anima-engine'),'type'=>Controls_Manager::SWITCHER,'default'=>'yes']);
      $this->add_control('show_duration',['label'=>__('Mostrar duración','anima-engine'),'type'=>Controls_Manager::SWITCHER,'default'=>'yes']);
    $this->end_controls_section();
  }
  protected function render(){
    $s = $this->get_settings_for_display();
    $q = new WP_Query(['post_type'=>'curso','posts_per_page'=>intval($s['posts_per_page'] ?: 6)]);
    $cols = $s['columns'] ?? '3';
    echo '<div class="anima-grid cols-'.$cols.'">';
    if($q->have_posts()): while($q->have_posts()): $q->the_post();
      $thumb = get_the_post_thumbnail_url(get_the_ID(),'large');
      $price = get_post_meta(get_the_ID(),'anima_price', true);
      $dur = get_post_meta(get_the_ID(),'anima_duration_hours', true);
      echo '<article class="anima-card">';
      if($thumb) echo '<img src="'.esc_url($thumb).'" alt="" style="width:100%;display:block">';
      echo '<div style="padding:16px">';
      echo '<h3 style="margin:.2em 0"><a href="'.esc_url(get_permalink()).'">'.esc_html(get_the_title()).'</a></h3>';
      echo '<p style="color:var(--anima-muted);margin:6px 0">'.esc_html(wp_trim_words(strip_tags(get_the_excerpt()), 18, '…')).'</p>';
      echo '<div style="display:flex;gap:10px;align-items:center;margin:8px 0 12px">';
      if(!empty($s['show_price']) && $price!=='') echo '<span class="anima-pill" style="background:rgba(91,140,255,.12);color:#bcd1ff">€'.esc_html($price).'</span>';
      if(!empty($s['show_duration']) && $dur!=='') echo '<span class="anima-pill" style="background:rgba(49,225,247,.10);color:#9BE7FF">'.intval($dur).' h</span>';
      echo '</div>';
      echo '<a class="anima-btn anima-btn--ghost" href="'.esc_url(get_permalink()).'">Ver curso</a>';
      echo '</div></article>';
    endwhile; wp_reset_postdata(); else: echo '<p>No hay cursos publicados.</p>'; endif;
    echo '</div>';
  }
}
