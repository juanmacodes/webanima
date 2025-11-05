<?php get_header(); ?>

<main id="main" class="site-main container">
    <section class="error-404">
        <h1>404 - Página no encontrada</h1>
        <p>Lo sentimos, no podemos encontrar la página que buscas.</p>
        <p><a href="<?php echo esc_url( home_url('/') ); ?>" class="btn">Volver al inicio</a></p>
        <p>O intenta buscar lo que necesitas:</p>
        <?php get_search_form(); // muestra el formulario de búsqueda ?>
    </section>
</main>

<?php get_footer(); ?>
