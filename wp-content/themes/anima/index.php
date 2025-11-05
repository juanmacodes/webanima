<?php
/**
 * Plantilla índice / blog.
 */

get_header();
?>
<?php if ( have_posts() ) : ?>
  <div class="post-list">
    <?php while ( have_posts() ) : the_post(); ?>
      <article <?php post_class(); ?>>
        <h2 class="animate-on-scroll"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <p class="post-meta"><?php echo esc_html( get_the_date() ); ?></p>
        <div class="post-excerpt animate-on-scroll">
          <?php the_excerpt(); ?>
        </div>
        <p><a class="read-more" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Leer más', 'anima' ); ?> &raquo;</a></p>
      </article>
    <?php endwhile; ?>

    <div class="pagination">
      <div class="newer">
        <?php previous_posts_link( __( 'Entradas más recientes', 'anima' ) ); ?>
      </div>
      <div class="older">
        <?php next_posts_link( __( 'Entradas anteriores', 'anima' ) ); ?>
      </div>
    </div>
  </div>
<?php else : ?>
  <p><?php esc_html_e( 'No se encontraron entradas.', 'anima' ); ?></p>
<?php endif; ?>
<?php
get_footer();
