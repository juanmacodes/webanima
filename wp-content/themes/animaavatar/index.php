<?php get_header(); ?>

<main id="main" class="site-main container">
<?php if ( have_posts() ) : ?>
    <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <div class="entry-content">
                <?php if ( is_singular() ) {
                    the_content();
                } else {
                    the_excerpt();
                } ?>
            </div>
        </article>
    <?php endwhile; ?>

    <?php // Paginación básica
    the_posts_pagination( array(
        'prev_text' => '&laquo; Anteriores',
        'next_text' => 'Siguientes &raquo;',
    ) ); ?>

<?php else : ?>
    <p><?php _e('No se encontraron contenidos.', 'animaavatar'); ?></p>
<?php endif; ?>
</main>

<?php get_footer(); ?>
