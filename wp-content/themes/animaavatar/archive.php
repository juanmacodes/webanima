<?php
/**
 * Plantilla para archivos
 *
 * @package AnimaAvatar
 */

global $post;
get_header();
?>
<section class="section container">
    <header class="section__header">
        <h1 class="section__title"><?php the_archive_title(); ?></h1>
        <?php if ( get_the_archive_description() ) : ?>
            <p class="muted"><?php echo wp_kses_post( get_the_archive_description() ); ?></p>
        <?php endif; ?>
    </header>
    <?php if ( have_posts() ) : ?>
        <div class="post-grid animate-on-scroll">
            <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
                    <header>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    </header>
                    <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 25 ) ); ?></p>
                    <a class="button" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Leer más', 'animaavatar' ); ?></a>
                </article>
            <?php endwhile; ?>
        </div>
        <nav class="pagination" aria-label="<?php esc_attr_e( 'Paginación de archivos', 'animaavatar' ); ?>">
            <?php
            the_posts_pagination( [
                'prev_text' => esc_html__( 'Anterior', 'animaavatar' ),
                'next_text' => esc_html__( 'Siguiente', 'animaavatar' ),
            ] );
            ?>
        </nav>
    <?php else : ?>
        <p><?php esc_html_e( 'No encontramos publicaciones en este archivo.', 'animaavatar' ); ?></p>
    <?php endif; ?>
</section>
<?php
get_footer();
