<?php
/**
 * Plantilla para entradas individuales
 *
 * @package AnimaAvatar
 */

global $post;
get_header();
?>
<section class="section container">
    <?php if ( have_posts() ) : ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'card animate-on-scroll' ); ?>>
                <header>
                    <h1 class="section__title"><?php the_title(); ?></h1>
                    <p class="muted"><?php echo esc_html( get_the_date() ); ?></p>
                </header>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
                <footer>
                    <?php the_tags( '<p class="pill">', '</p><p class="pill">', '</p>' ); ?>
                </footer>
            </article>
        <?php endwhile; ?>
    <?php endif; ?>
</section>
<?php
get_footer();
