<?php
/**
 * Cabecera del tema
 *
 * @package AnimaAvatar
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Saltar al contenido', 'animaavatar' ); ?></a>
<header class="site-header" role="banner">
    <div class="container flex flex-between">
        <div class="site-branding">
            <?php if ( has_custom_logo() ) : ?>
                <div class="logo"><?php the_custom_logo(); ?></div>
            <?php else : ?>
                <a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php bloginfo( 'name' ); ?>
                </a>
                <p class="site-description"><?php bloginfo( 'description' ); ?></p>
            <?php endif; ?>
        </div>
        <nav class="site-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Menú principal', 'animaavatar' ); ?>">
            <button class="nav-toggle" aria-expanded="false" aria-controls="primary-menu">
                <span class="screen-reader-text"><?php esc_html_e( 'Abrir menú', 'animaavatar' ); ?></span>
                &#9776;
            </button>
            <div id="primary-menu-container" class="primary-menu-container" aria-hidden="true">
                <?php
                wp_nav_menu( [
                    'theme_location' => 'main-menu',
                    'menu_id'        => 'primary-menu',
                    'menu_class'     => 'primary-menu',
                    'container'      => false,
                    'fallback_cb'    => 'animaavatar_default_menu',
                ] );
                ?>
            </div>
        </nav>
        <div class="header-cta">
            <a class="button" href="<?php echo esc_url( home_url( '/experiencia-inmersiva' ) ); ?>">
                <?php esc_html_e( 'Experiencia 3D', 'animaavatar' ); ?>
            </a>
        </div>
    </div>
</header>
<main id="content" class="site-main" role="main">
