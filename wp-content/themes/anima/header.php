<?php
/**
 * Cabecera del tema Anima.
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
  <div class="container header-inner">
    <div class="site-branding">
      <?php if ( has_custom_logo() ) : ?>
        <?php the_custom_logo(); ?>
      <?php else : ?>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-title"><?php bloginfo( 'name' ); ?></a>
      <?php endif; ?>
    </div>
    <nav class="main-navigation" aria-label="<?php esc_attr_e( 'Menú principal', 'anima' ); ?>">
      <?php
      wp_nav_menu( [
        'theme_location' => 'menu-principal',
        'container'      => false,
        'menu_class'     => 'menu',
        'fallback_cb'    => '__return_false',
      ] );
      ?>
    </nav>
  </div>
</header>
<main class="site-content">
  <div class="container">
