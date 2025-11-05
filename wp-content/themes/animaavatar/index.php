<?php
/**
 * Plantilla principal
 *
 * @package AnimaAvatar
 */

global $wp_query;
get_header();
?>
<section class="section container">
    <header class="section__header">
        <h1 class="section__title">
            <?php echo esc_html( get_the_archive_title() ? get_the_archive_title() : get_bloginfo( 'name' ) ); ?>
        </h1>
        <?php if ( get_the_archive_description() ) : ?>
            <p class="section__description"><?php echo wp_kses_post( get_the_archive_description() ); ?></p>
        <?php endif; ?>
    </header>
    <?php if ( have_posts() ) : ?>
        <div class="post-grid animate-on-scroll">
            <?php
            while ( have_posts() ) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
                    <header>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    </header>
                    <div class="entry-summary">
                        <?php the_excerpt(); ?>
                    </div>
                    <footer>
                        <a class="button" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Ver más', 'animaavatar' ); ?></a>
                    </footer>
                </article>
                <?php
            endwhile;
            ?>
        </div>
        <nav class="pagination" aria-label="<?php esc_attr_e( 'Paginación', 'animaavatar' ); ?>">
            <?php
            the_posts_pagination( [
                'prev_text' => esc_html__( 'Anterior', 'animaavatar' ),
                'next_text' => esc_html__( 'Siguiente', 'animaavatar' ),
            ] );
            ?>
        </nav>
    <?php else : ?>
        <p><?php esc_html_e( 'No se encontraron contenidos.', 'animaavatar' ); ?></p>
    <?php endif; ?>
</section>
<?php
get_footer();
