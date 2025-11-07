<?php
namespace Anima_Engine\Widgets;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
if ( ! defined( 'ABSPATH' ) ) exit;
class Anima_Hero3D extends Widget_Base {
  public function get_name(){ return 'anima_hero3d'; }
  public function get_title(){ return __('Anima Hero 3D','anima-engine'); }
  public function get_icon(){ return 'eicon-gallery-grid'; }
  public function get_categories(){ return ['anima-category']; }
  public function get_style_depends(){ return ['anima-engine']; }
  public function get_script_depends(){ return ['model-viewer']; }
  protected function register_controls(){
    $this->start_controls_section('content',['label'=>__('Contenido','anima-engine')]);
      $this->add_control('pill',['label'=>__('Etiqueta','anima-engine'),'type'=>Controls_Manager::TEXT,'default'=>'LABORATORIO INMERSIVO']);
      $this->add_control('title',['label'=>__('Título','anima-engine'),'type'=>Controls_Manager::TEXTAREA,'default'=>"DISEÑAMOS AVATARES QUE BRILLAN\nEN CUALQUIER REALIDAD"]);
      $this->add_control('subtitle',['label'=>__('Descripción','anima-engine'),'type'=>Controls_Manager::TEXTAREA,'default'=>'Tecnología XR, IA conversacional y WebXR para experiencias memorables en streaming, hologramas y VR.']);
      $this->add_control('btn1_text',['label'=>__('Botón primario','anima-engine'),'type'=>Controls_Manager::TEXT,'default'=>'AGENDA UNA DEMO']);
      $this->add_control('btn1_link',['label'=>__('Enlace primario','anima-engine'),'type'=>Controls_Manager::URL,'default'=>['url'=>'/contacto']]);
      $this->add_control('btn2_text',['label'=>__('Botón secundario','anima-engine'),'type'=>Controls_Manager::TEXT,'default'=>'EXPLORA LA EXPERIENCIA 3D']);
      $this->add_control('btn2_link',['label'=>__('Enlace secundario','anima-engine'),'type'=>Controls_Manager::URL,'default'=>['url'=>'/experiencia-inmersiva']]);
    $this->end_controls_section();
    $this->start_controls_section('media',['label'=>__('3D / Media','anima-engine')]);
      $this->add_control('mode',['label'=>__('Modo visor','anima-engine'),'type'=>Controls_Manager::SELECT,'default'=>'modelviewer','options'=>['modelviewer'=>'GLB/GLTF (model-viewer)','iframe'=>'Embed (Sketchfab/Spline)']]);
      $this->add_control('model_src',['label'=>__('URL GLB/GLTF','anima-engine'),'type'=>Controls_Manager::TEXT,'default'=>'https://assets.readyplayer.me/Example.glb','condition'=>['mode'=>'modelviewer']]);
      $this->add_control('poster',['label'=>__('Poster (opcional)','anima-engine'),'type'=>Controls_Manager::MEDIA,'condition'=>['mode'=>'modelviewer']]);
      $this->add_control('autoplay',['label'=>__('Cargar automáticamente','anima-engine'),'type'=>Controls_Manager::SWITCHER,'default'=>'yes','condition'=>['mode'=>'modelviewer']]);
      $this->add_control('iframe_src',['label'=>__('URL iframe','anima-engine'),'type'=>Controls_Manager::TEXT,'placeholder'=>'https://sketchfab.com/models/.../embed?...','condition'=>['mode'=>'iframe']]);
      $this->add_control('height',['label'=>__('Alto (px)','anima-engine'),'type'=>Controls_Manager::NUMBER,'default'=>520]);
    $this->end_controls_section();
    $this->start_controls_section('style',['label'=>__('Estilos','anima-engine'),'tab'=>Controls_Manager::TAB_STYLE]);
      $this->add_control('title_color',['label'=>__('Color título','anima-engine'),'type'=>Controls_Manager::COLOR,'default'=>'#ffffff','selectors'=>['{{WRAPPER}} .anima-hero3d-title'=>'color: {{VALUE}}']]);
      $this->add_group_control(Group_Control_Typography::get_type(),['name'=>'title_typo','selector'=>'{{WRAPPER}} .anima-hero3d-title']);
    $this->end_controls_section();
  }
  protected function render(){
    $s = $this->get_settings_for_display();
    $h = intval($s['height'] ?: 520);
    $poster_url = $s['poster']['url'] ?? '';
    $poster_attr = $poster_url ? ' poster="'.esc_url($poster_url).'"' : '';
    $reveal = (!empty($s['autoplay'])) ? 'auto' : 'interaction';
    echo '<div class="anima-hero3d-wrap">';
      if(!empty($s['pill'])) echo '<span class="anima-pill">'.esc_html($s['pill']).'</span>';
      if(!empty($s['title'])){ $title = nl2br(esc_html($s['title'])); echo '<h1 class="anima-hero3d-title" style="font-size:clamp(40px,6vw,68px);line-height:1.1;letter-spacing:.08em;margin:.45em 0">'.$title.'</h1>'; }
      if(!empty($s['subtitle'])) echo '<p style="color:var(--anima-muted);max-width:760px">'.esc_html($s['subtitle']).'</p>';
      echo '<div style="display:flex;gap:14px;flex-wrap:wrap;margin:22px 0 28px">';
        if(!empty($s['btn1_text'])){ $url = $s['btn1_link']['url'] ?? '#'; echo '<a class="anima-btn anima-btn--grad" href="'.esc_url($url).'">'.esc_html($s['btn1_text']).'</a>'; }
        if(!empty($s['btn2_text'])){ $url = $s['btn2_link']['url'] ?? '#'; echo '<a class="anima-btn anima-btn--ghost" href="'.esc_url($url).'">'.esc_html($s['btn2_text']).'</a>'; }
      echo '</div>';
      echo '<div class="anima-card" style="padding:0">';
      if( $s['mode']==='iframe' && !empty($s['iframe_src']) ){
        printf('<iframe title="Anima 3D" src="%s" style="width:100%%;height:%dpx;border:0;border-radius:20px" allow="autoplay; fullscreen; xr-spatial-tracking" allowfullscreen loading="lazy"></iframe>', esc_url($s['iframe_src']), $h);
      } else {
        wp_enqueue_script('model-viewer');
        printf('<model-viewer src="%s"%s crossorigin="anonymous" alt="Avatar Anima" camera-controls auto-rotate exposure="1.0" shadow-intensity="1" reveal="%s" loading="lazy" style="width:100%%;height:%dpx;border-radius:20px"></model-viewer>', esc_url($s['model_src']), $poster_attr, esc_attr($reveal), $h);
        // Fallback visual si algo falla: muestra poster como imagen
        if($poster_url){
          printf('<div class="anima-hero3d-fallback" style="display:none"><img src="%s" alt="" style="width:100%%;display:block;border-radius:20px"></div>', esc_url($poster_url));
        }
      }
      echo '</div>';
    echo '</div>';
  }
}
