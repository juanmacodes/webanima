<?php
/**
 * Plugin Name:       Anima Engine – Elementor Widgets
 * Description:       Widgets de Elementor para Historias, Avatares y 3D (model-viewer) de Anima.
 * Version:           1.0.0
 * Requires PHP:      8.0
 * Requires at least: 6.0
 * Author:            Anima Avatar Agency
 * Text Domain:       anima-engine
 */

if ( ! defined('ABSPATH') ) exit;

define('ANIMA_EW_PATH', plugin_dir_path(__FILE__));
define('ANIMA_EW_URL',  plugin_dir_url(__FILE__));
define('ANIMA_EW_VER',  '1.0.0');

add_action('plugins_loaded', function () {
  // Requiere Elementor activo
  if ( ! did_action('elementor/loaded') ) return;
  require_once ANIMA_EW_PATH . 'includes/class-loader.php';
});

/** Carga estilos y model-viewer (para el widget 3D) */
add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style('anima-ew', ANIMA_EW_URL.'assets/css/anima-elementor-widgets.css', [], ANIMA_EW_VER);
  wp_register_style('anima-avatars-modal', ANIMA_EW_URL.'assets/css/anima-avatars-modal.css', [], ANIMA_EW_VER);
  wp_register_script('anima-avatars-modal', ANIMA_EW_URL.'assets/js/anima-avatars-modal.js', [], ANIMA_EW_VER, true);
  // model-viewer (solo si no existe)
  if ( ! wp_script_is('model-viewer', 'registered') ) {
    wp_register_script('model-viewer', 'https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js', [], null, true);
  }
});