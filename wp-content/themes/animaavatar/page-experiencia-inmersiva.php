<?php
/**
 * Plantilla para la página Experiencia Inmersiva.
 *
 * @package AnimaAvatar
 */

global $post;

get_header();
?>
<section class="section container immersive-intro">
    <header class="section__header animate-on-scroll">
        <h1 class="section__title"><?php esc_html_e( 'Experiencia inmersiva', 'animaavatar' ); ?></h1>
        <p class="muted"><?php esc_html_e( 'Conoce los requisitos y explora una vista previa ligera antes de saltar a WebXR.', 'animaavatar' ); ?></p>
    </header>
    <div class="immersive-cta animate-on-scroll">
        <p><?php esc_html_e( 'Asegúrate de contar con conexión estable y un navegador con soporte WebXR para obtener el máximo rendimiento.', 'animaavatar' ); ?></p>
        <a class="button" href="#webxr-demo"><?php esc_html_e( 'Entrar a WebXR', 'animaavatar' ); ?></a>
        <p class="compatibility-note"><?php esc_html_e( 'Compatibilidad recomendada: Chrome, Edge, Quest Browser o navegadores con soporte WebXR.', 'animaavatar' ); ?></p>
    </div>
</section>

<section class="section container" id="webxr-demo">
    <header class="section__header animate-on-scroll">
        <h2 class="section__title"><?php esc_html_e( 'Vista previa 3D', 'animaavatar' ); ?></h2>
        <p class="muted"><?php esc_html_e( 'Interactúa con el modelo antes de iniciar la sesión inmersiva completa.', 'animaavatar' ); ?></p>
    </header>
    <div class="animate-on-scroll">
        <?php
        echo do_shortcode(
            '[anima_model src="https://modelviewer.dev/shared-assets/models/Astronaut.glb" poster="https://modelviewer.dev/shared-assets/models/Astronaut.webp" video="" height="400px" reveal="interaction" loading="lazy" draco_decoder="https://www.gstatic.com/draco/versioned/decoders/1.5.6/" ktx2_transcoder="https://www.gstatic.com/draco/versioned/decoders/1.5.6/"]'
        );
        ?>
    </div>
</section>
<?php
get_footer();
