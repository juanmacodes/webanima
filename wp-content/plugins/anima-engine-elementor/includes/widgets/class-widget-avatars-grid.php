<?php
namespace AnimaEngine\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Repeater;
use Elementor\Widget_Base;

if ( ! defined('ABSPATH') ) exit;

if ( ! class_exists('Anima_Avatares_Grid') ) {
class Anima_Avatares_Grid extends Widget_Base {
  public function get_name(){ return 'anima_avatars_grid'; }
  public function get_title(){ return __('Avatares — Grid', 'anima-engine'); }
  public function get_icon(){ return 'eicon-person'; }
  public function get_categories(){ return ['anima']; }

  public function get_style_depends(){
    return ['anima-avatars-modal'];
  }

  public function get_script_depends(){
    return ['anima-avatars-modal'];
  }

  protected function register_controls(){
    $this->start_controls_section('section_avatars', [
      'label' => __('Avatares', 'anima-engine'),
    ]);

    $repeater = new Repeater();

    $repeater->add_control('name', [
      'label' => __('Nombre', 'anima-engine'),
      'type'  => Controls_Manager::TEXT,
      'default' => __('Avatar Anima', 'anima-engine'),
    ]);

    $repeater->add_control('image', [
      'label' => __('Imagen', 'anima-engine'),
      'type'  => Controls_Manager::MEDIA,
    ]);

    $repeater->add_control('desc', [
      'label' => __('Descripción', 'anima-engine'),
      'type'  => Controls_Manager::TEXTAREA,
    ]);

    $repeater->add_control('twitter', [
      'label' => __('Twitter', 'anima-engine'),
      'type'  => Controls_Manager::URL,
    ]);

    $repeater->add_control('instagram', [
      'label' => __('Instagram', 'anima-engine'),
      'type'  => Controls_Manager::URL,
    ]);

    $repeater->add_control('tiktok', [
      'label' => __('TikTok', 'anima-engine'),
      'type'  => Controls_Manager::URL,
    ]);

    $this->add_control('avatars', [
      'label' => __('Lista de avatares', 'anima-engine'),
      'type'  => Controls_Manager::REPEATER,
      'fields'=> $repeater->get_controls(),
      'title_field' => '{{{ name }}}',
    ]);

    $this->end_controls_section();
  }

  protected function render(){
    $settings = $this->get_settings_for_display();
    $avatars  = $settings['avatars'] ?? [];
    static $modal_rendered = false;

    if ( empty($avatars) ) {
      return;
    }

    echo '<div class="avatar-grid">';

    foreach ( $avatars as $item ) {
      $title   = isset($item['name']) ? wp_strip_all_tags($item['name']) : '';
      $image   = ! empty($item['image']['url']) ? $item['image']['url'] : '';
      $desc    = isset($item['desc']) ? wp_strip_all_tags($item['desc']) : '';
      $twitter = ! empty($item['twitter']['url']) ? $item['twitter']['url'] : '';
      $instagram = ! empty($item['instagram']['url']) ? $item['instagram']['url'] : '';
      $tiktok = ! empty($item['tiktok']['url']) ? $item['tiktok']['url'] : '';

      echo '<article class="avatar-card">';

      if ( $image ) {
        echo '<div class="avatar-thumb"><img src="' . esc_url($image) . '" alt="' . esc_attr($title) . '"></div>';
      }

      if ( $title ) {
        echo '<h3 class="avatar-title">' . esc_html($title) . '</h3>';
      }

      echo '<button type="button" class="btn avatar-detail"'
        . ' data-title="' . esc_attr($title) . '"'
        . ' data-img="' . esc_url($image) . '"'
        . ' data-desc="' . esc_attr($desc) . '"'
        . ' data-twitter="' . esc_url($twitter) . '"'
        . ' data-instagram="' . esc_url($instagram) . '"'
        . ' data-tiktok="' . esc_url($tiktok) . '">' . esc_html__('Ver detalles', 'anima-engine') . '</button>';

      echo '</article>';
    }

    echo '</div>';

    $is_editor_mode = false;

    if ( class_exists('\\Elementor\\Plugin') && isset(Plugin::$instance->editor) ) {
      $is_editor_mode = Plugin::$instance->editor->is_edit_mode();
    }

    if ( ! $modal_rendered && ! $is_editor_mode ) {
      echo '<div id="avatar-modal" class="avatar-modal" aria-hidden="true">';
      echo '  <div class="avatar-modal__backdrop" data-close></div>';
      echo '  <div class="avatar-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="am-title" aria-describedby="am-desc">';
      echo '    <button type="button" class="avatar-modal__close" data-close aria-label="' . esc_attr__('Cerrar', 'anima-engine') . '">&times;</button>';
      echo '    <div class="avatar-modal__content">';
      echo '      <img id="am-img" src="" alt="">';
      echo '      <h2 id="am-title"></h2>';
      echo '      <p id="am-desc"></p>';
      echo '      <div id="am-socials" class="avatar-modal__socials" aria-live="polite"></div>';
      echo '    </div>';
      echo '  </div>';
      echo '</div>';
      $modal_rendered = true;
    }
  }
}
}
