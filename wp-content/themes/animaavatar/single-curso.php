<?php
/**
 * Plantilla individual para el CPT Curso.
 *
 * @package AnimaAvatar
 */

global $post;
get_header();
?>
<section class="section container">
    <?php if ( have_posts() ) : ?>
        <?php
        while ( have_posts() ) :
            the_post();
            $curso_id     = get_the_ID();
            $duracion     = get_post_meta( $curso_id, 'anima_duracion', true );
            $nivel        = get_post_meta( $curso_id, 'anima_nivel', true );
            $modalidad    = get_post_meta( $curso_id, 'anima_modalidad', true );
            $instructores = get_post_meta( $curso_id, 'anima_instructores', true );
            $kpis         = get_post_meta( $curso_id, 'anima_kpis', true );

            $meta_items = array();

            if ( ! empty( $duracion ) ) {
                $meta_items[] = array(
                    'label' => __( 'Duración', 'animaavatar' ),
                    'value' => $duracion,
                );
            }

            if ( ! empty( $nivel ) ) {
                $meta_items[] = array(
                    'label' => __( 'Nivel', 'animaavatar' ),
                    'value' => $nivel,
                );
            }

            if ( ! empty( $modalidad ) ) {
                $meta_items[] = array(
                    'label' => __( 'Modalidad', 'animaavatar' ),
                    'value' => $modalidad,
                );
            }

            if ( ! empty( $instructores ) ) {
                $meta_items[] = array(
                    'label' => __( 'Instructores', 'animaavatar' ),
                    'value' => $instructores,
                );
            }
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'card animate-on-scroll anima-curso-detalle' ); ?>>
                <header class="anima-curso-detalle__cabecera">
                    <h1 class="section__title"><?php the_title(); ?></h1>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <figure class="anima-curso-detalle__imagen">
                            <?php the_post_thumbnail( 'large', array( 'loading' => 'lazy' ) ); ?>
                        </figure>
                    <?php endif; ?>
                    <?php if ( $meta_items ) : ?>
                        <ul class="anima-curso-detalle__meta">
                            <?php foreach ( $meta_items as $item ) : ?>
                                <li><strong><?php echo esc_html( $item['label'] ); ?>:</strong> <?php echo esc_html( $item['value'] ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </header>
                <div class="entry-content anima-curso-detalle__contenido">
                    <?php the_content(); ?>
                    <?php
                    if ( ! empty( $kpis ) ) :
                        $kpis_lines = array_filter( array_map( 'trim', explode( "\n", (string) $kpis ) ) );
                        if ( $kpis_lines ) :
                            ?>
                            <div class="anima-curso-detalle__kpis">
                                <h2><?php esc_html_e( 'Resultados destacados', 'animaavatar' ); ?></h2>
                                <ul>
                                    <?php foreach ( $kpis_lines as $line ) : ?>
                                        <li><?php echo esc_html( $line ); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php
                        endif;
                    endif;
                    ?>
                </div>
                <footer class="anima-curso-detalle__footer">
                    <?php
                    $demo_url = get_post_meta( $curso_id, 'anima_demo_url', true );
                    if ( ! empty( $demo_url ) ) :
                        ?>
                        <a class="button" href="<?php echo esc_url( $demo_url ); ?>" target="_blank" rel="noopener">
                            <?php esc_html_e( 'Abrir demo inmersiva', 'animaavatar' ); ?>
                        </a>
                        <?php
                    endif;
                    ?>
                </footer>
            </article>
        <?php endwhile; ?>
    <?php endif; ?>
</section>
<?php
get_footer();
