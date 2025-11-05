<?php
/**
 * Pie de página del tema con marcado semántico y navegación secundaria.
 */
?>
    <footer class="site-footer" role="contentinfo">
        <div class="container footer-inner">
            <nav class="footer-nav" aria-label="<?php esc_attr_e( 'Menú del pie de página', 'animaavatar' ); ?>">
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'footer-menu',
                    'container'      => false,
                    'menu_class'     => 'menu menu--footer reset-list',
                    'fallback_cb'    => 'animaavatar_fallback_menu',
                    'depth'          => 1,
                ) );
                ?>
            </nav>

            <div class="site-info">
                <p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Todos los derechos reservados.', 'animaavatar' ); ?></p>
                <p><?php esc_html_e( 'Tema animaavatar preparado para experiencias inmersivas.', 'animaavatar' ); ?></p>
            </div>
        </div>
    </footer>
    <?php wp_footer(); ?>
</body>
</html>
