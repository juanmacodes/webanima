<?php get_header(); ?>

<main id="main-content" class="site-main container" role="main">
    <section class="error-404" aria-labelledby="error-title">
        <h1 id="error-title"><?php esc_html_e( '404 - Página no encontrada', 'animaavatar' ); ?></h1>
        <p><?php esc_html_e( 'Lo sentimos, no podemos encontrar la página que buscas.', 'animaavatar' ); ?></p>
        <p><a class="cta-3d" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Volver al inicio', 'animaavatar' ); ?></a></p>
        <p><?php esc_html_e( 'O intenta buscar lo que necesitas:', 'animaavatar' ); ?></p>
        <?php get_search_form(); ?>
    </section>
</main>

<?php get_footer(); ?>
