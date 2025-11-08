
<?php
// Setup
add_action('after_setup_theme', function(){
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  register_nav_menu('menu-app', 'Menú App');
});

// Enqueue styles/scripts
add_action('wp_enqueue_scripts', function(){
  wp_enqueue_style('anima-style', get_stylesheet_uri(), [], '2.0.0');
  wp_enqueue_script('anima-app', get_template_directory_uri().'/assets/js/app.js', [], '2.0.0', true);
  // Model Viewer (para GLB/GLTF sin plugins)
  wp_enqueue_script('model-viewer','https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js',[], null, true);

  if (is_page('galeria-de-avatares')) {
    wp_enqueue_style('avatars-modal', get_template_directory_uri() . '/assets/css/avatars-modal.css', [], '1.0.0');
    wp_enqueue_script('avatars-modal', get_template_directory_uri() . '/assets/js/avatars-modal.js', [], '1.0.0', true);
  }
});

// === Customizer (panel de personalización) ===
add_action('customize_register', function($c){
  $c->add_panel('anima_app', ['title'=>'Anima App — Apariencia','priority'=>10]);

  // Colores
  $c->add_section('anima_app_colors', ['title'=>'Colores','panel'=>'anima_app']);
  $fields = [
    'bg'=>'#0b0b10','surface'=>'#0f121a','line'=>'#1e2330',
    'text'=>'#efefef','muted'=>'#b8b8b8','primary'=>'#5B8CFF','accent'=>'#31E1F7'
  ];
  foreach($fields as $key=>$default){
    $id = "anima_color_$key";
    $c->add_setting($id,['default'=>$default,'transport'=>'refresh','sanitize_callback'=>'sanitize_hex_color']);
    $c->add_control(new WP_Customize_Color_Control($c,$id,['label'=>ucfirst($key),'section'=>'anima_app_colors']));
  }

  // Layout
  $c->add_section('anima_app_layout', ['title'=>'Layout','panel'=>'anima_app']);
  $c->add_setting('anima_radius',['default'=>16,'sanitize_callback'=>'absint']);
  $c->add_control('anima_radius',['label'=>'Radio de bordes (px)','type'=>'number','section'=>'anima_app_layout']);
  $c->add_setting('anima_header_mode',['default'=>'top','sanitize_callback'=>'sanitize_text_field']);
  $c->add_control('anima_header_mode',['label'=>'Header','type'=>'select','choices'=>['top'=>'Solo barra superior','topbottom'=>'Barra + Bottom Nav'],'section'=>'anima_app_layout']);
  $c->add_setting('anima_logo',['sanitize_callback'=>'absint']);
  $c->add_control(new WP_Customize_Media_Control($c,'anima_logo',['label'=>'Logo (SVG/PNG)','mime_type'=>'image','section'=>'anima_app_layout']));

  // Bottom nav links
  $c->add_section('anima_app_nav', ['title'=>'Bottom Nav','panel'=>'anima_app']);
  $navs = ['inicio'=>'/','explorar'=>'/avatares','cursos'=>'/cursos','proyectos'=>'/proyectos','perfil'=>'/mi-perfil'];
  foreach($navs as $k=>$v){
    $sid = "anima_nav_$k";
    $c->add_setting($sid,['default'=>$v,'sanitize_callback'=>'esc_url_raw']);
    $c->add_control($sid,['label'=>ucfirst($k).' URL','type'=>'url','section'=>'anima_app_nav']);
  }
});

// Pintar tokens como CSS variables globales
add_action('wp_head', function(){
  $vars = [
    '--color-bg'=>get_theme_mod('anima_color_bg','#0b0b10'),
    '--color-surface'=>get_theme_mod('anima_color_surface','#0f121a'),
    '--color-line'=>get_theme_mod('anima_color_line','#1e2330'),
    '--color-text'=>get_theme_mod('anima_color_text','#efefef'),
    '--color-muted'=>get_theme_mod('anima_color_muted','#b8b8b8'),
    '--color-primary'=>get_theme_mod('anima_color_primary','#5B8CFF'),
    '--color-accent'=>get_theme_mod('anima_color_accent','#31E1F7'),
    '--radius'=>get_theme_mod('anima_radius',16).'px',
  ];
  echo '<style>:root{';
  foreach($vars as $k=>$v){ echo $k.':'.esc_html($v).';'; }
  echo '}</style>';
});
