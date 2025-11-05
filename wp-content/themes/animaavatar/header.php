<?php
/**
 * header.php - Cabecera del tema (apertura del HTML, logo, navegación)
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1"><?php 
    // Meta viewport para responsive 
?>
<?php wp_head(); // Hook obligatorio para que WordPress inserte scripts, estilos, etc. ?>
</head>
<body <?php body_class(); ?>>
<header class="site-header">
    <div class="container flex space-between">
        <!-- Branding: logo o nombre del sitio -->
        <div class="site-branding">
            <?php if ( function_exists('the_custom_logo') && has_custom_logo() ) :
                the_custom_logo();
            else: ?>
                <a href="<?php echo esc_url( home_url('/') ); ?>"><?php bloginfo('name'); ?></a>
            <?php endif; ?>
        </div>
        <!-- Menú de navegación principal -->
        <nav class="main-nav" role="navigation" aria-label="<?php esc_attr_e('Menú principal', 'animaavatar'); ?>">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'main-menu',
                'container'      => false,
                'menu_class'     => 'main-menu',
                'fallback_cb'    => 'anima_default_menu' // función fallback definida abajo
            ) );
            ?>
        </nav>
    </div>
</header>

<?php
// Función fallback: mostrar páginas si no hay menú asignado
function anima_default_menu() {
    echo '<ul class="main-menu">';
    wp_list_pages( array(
        'title_li' => '',
        'depth'    => 1
    ) );
    echo '</ul>';
}
?>
