<?php
/**
 * Cabecera del tema: define la estructura semántica del encabezado y navegación principal.
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'theme-animaavatar' ); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main-content"><?php esc_html_e( 'Saltar al contenido principal', 'animaavatar' ); ?></a>

<header class="site-header" role="banner">
    <div class="container header-inner">
        <div class="site-branding">
            <?php if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <?php bloginfo( 'name' ); ?>
                </a>
                <p class="site-description"><?php bloginfo( 'description' ); ?></p>
            <?php endif; ?>
        </div>

        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation">
            <span class="menu-toggle__icon" aria-hidden="true"></span>
            <span class="menu-toggle__label"><?php esc_html_e( 'Menú', 'animaavatar' ); ?></span>
        </button>

        <nav id="primary-navigation" class="main-nav" aria-label="<?php esc_attr_e( 'Menú principal', 'animaavatar' ); ?>">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'main-menu',
                'container'      => false,
                'menu_class'     => 'menu menu--primary reset-list',
                'fallback_cb'    => 'animaavatar_fallback_menu',
                'depth'          => 2,
            ) );
            ?>
        </nav>
    </div>
</header>
