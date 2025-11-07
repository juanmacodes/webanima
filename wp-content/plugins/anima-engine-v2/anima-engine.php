<?php
/**
 * Plugin Name:       Anima Engine
 * Description:       Widgets personalizados de Elementor para la web/app de Anima. Incluye Hero 3D (GLB/SKetchfab), Cards, Servicios, Cursos (CPT), Proyectos (CPT) y Galería 3D con QuickView.
 * Version:           2.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Anima Avatar Agency
 * Text Domain:       anima-engine
 */
if ( ! defined( 'ABSPATH' ) ) exit;
define( 'ANIMA_ENGINE_VERSION', '2.0.0' );
define( 'ANIMA_ENGINE_DIR', plugin_dir_path( __FILE__ ) );
define( 'ANIMA_ENGINE_URL', plugin_dir_url( __FILE__ ) );
add_action('admin_notices', function(){
  if ( ! did_action( 'elementor/loaded' ) ) {
    echo '<div class="notice notice-warning"><p><strong>Anima Engine</strong> requiere <em>Elementor</em> activo.</p></div>';
  }
});
add_filter('upload_mimes', function($m){
  $m['glb']='model/gltf-binary';
  $m['gltf']='model/gltf+json';
  $m['bin']='application/octet-stream';
  $m['hdr']='image/vnd.radiance';
  return $m;
});
add_action('wp_enqueue_scripts', function(){
  wp_register_style('anima-engine', ANIMA_ENGINE_URL.'assets/css/anima-elementor.css', [], ANIMA_ENGINE_VERSION);
  wp_enqueue_style('anima-engine');
  wp_register_script('model-viewer', 'https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js', [], null, true);
  wp_register_script('io-polyfill', 'https://polyfill.io/v3/polyfill.min.js?features=IntersectionObserver', [], null, true);
  wp_register_script('anima-quickview', ANIMA_ENGINE_URL.'assets/js/quickview.js', ['model-viewer','io-polyfill'], ANIMA_ENGINE_VERSION, true);
});
require_once ANIMA_ENGINE_DIR . 'inc/class-elementor-init.php';
