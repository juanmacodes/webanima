<?php
/**
 * Plantilla para páginas
 *
 * @package AnimaAvatar
 */

global $post;
get_header();
?>
<section class="section container">
    <article id="post-<?php the_ID(); ?>" <?php post_class( 'card animate-on-scroll' ); ?>>
        <header>
            <h1 class="section__title"><?php the_title(); ?></h1>
        </header>
        <div class="entry-content">
            <?php the_content(); ?>
        </div>
    </article>
</section>
<?php
get_footer();
