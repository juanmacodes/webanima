
<?php ?><!doctype html>
<html <?php language_attributes();?>>
<head>
<meta charset="<?php bloginfo('charset');?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head();?>
</head>
<body <?php body_class();?>>
<header class="app-header">
  <div class="bar container">
    <div class="brand">
      <?php
        $logo = get_theme_mod('anima_logo');
        if($logo){ echo wp_get_attachment_image($logo, 'full'); }
        else{ echo '<span>Anima</span>'; }
      ?>
    </div>
    <nav class="app-actions">
      <?php wp_nav_menu(['theme_location'=>'menu-app','container'=>false,'menu_class'=>'menu','fallback_cb'=>'__return_false']); ?>
      <a class="cta" href="<?php echo esc_url( home_url('/experiencia-inmersiva') ); ?>">Experiencia 3D</a>
    </nav>
  </div>
</header>
<main id="app" class="app-content">
