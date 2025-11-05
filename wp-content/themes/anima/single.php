<?php
/**
 * Plantilla para entradas individuales.
 */

get_header();
?>
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
  <article <?php post_class(); ?>>
    <h1 class="animate-on-scroll"><?php the_title(); ?></h1>
    <p class="post-meta"><?php echo esc_html( get_the_date() ); ?> &middot; <?php the_author(); ?></p>
    <?php if ( has_post_thumbnail() ) : ?>
      <div class="post-thumbnail animate-on-scroll">
        <?php the_post_thumbnail( 'large' ); ?>
      </div>
    <?php endif; ?>
    <div class="post-content animate-on-scroll">
      <?php the_content(); ?>
    </div>
    <div class="post-navigation">
      <span class="prev"><?php previous_post_link( '%link', '&larr; %title' ); ?></span>
      <span class="next"><?php next_post_link( '%link', '%title &rarr;' ); ?></span>
    </div>
  </article>
<?php endwhile; endif; ?>
<?php
get_footer();
