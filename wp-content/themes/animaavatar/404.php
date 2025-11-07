<?php
/**
 * Plantilla para errores 404
 *
 * @package AnimaAvatar
 */

global $post;
get_header();
?>
<section class="section container text-center">
    <div class="card animate-on-scroll">
        <h1 class="section__title"><?php esc_html_e( 'Algo se ha perdido en el metaverso', 'animaavatar' ); ?></h1>
        <p><?php esc_html_e( 'La página que buscas no existe o fue trasladada a otra dimensión.', 'animaavatar' ); ?></p>
        <a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Volver al inicio', 'animaavatar' ); ?></a>
    </div>
</section>
<?php
get_footer();
