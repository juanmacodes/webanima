<?php
/**
 * Pie del tema
 *
 * @package AnimaAvatar
 */
?>
</main>
<footer class="site-footer" role="contentinfo">
    <div class="container">
        <p><?php echo esc_html( sprintf( '© %1$s %2$s', date_i18n( 'Y' ), get_bloginfo( 'name' ) ) ); ?></p>
        <p class="muted"><?php esc_html_e( 'Creado para experiencias inmersivas y avatares digitales.', 'animaavatar' ); ?></p>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
