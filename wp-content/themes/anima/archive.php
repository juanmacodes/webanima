<?php
/**
 * Plantilla de archivos (categorías, CPT, etc.).
 */

get_header();
?>
<header class="archive-header animate-on-scroll">
  <h1><?php the_archive_title(); ?></h1>
  <?php if ( get_the_archive_description() ) : ?>
    <div class="archive-description"><?php the_archive_description(); ?></div>
  <?php endif; ?>
</header>
<?php if ( have_posts() ) : ?>
  <div class="post-list">
    <?php while ( have_posts() ) : the_post(); ?>
      <article <?php post_class(); ?>>
        <h2 class="animate-on-scroll"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <p class="post-meta"><?php echo esc_html( get_the_date() ); ?></p>
        <div class="post-excerpt animate-on-scroll"><?php the_excerpt(); ?></div>
      </article>
    <?php endwhile; ?>
    <div class="pagination">
      <div class="newer"><?php previous_posts_link( __( 'Entradas más recientes', 'anima' ) ); ?></div>
      <div class="older"><?php next_posts_link( __( 'Entradas anteriores', 'anima' ) ); ?></div>
    </div>
  </div>
<?php else : ?>
  <p><?php esc_html_e( 'No se encontraron resultados.', 'anima' ); ?></p>
<?php endif; ?>
<?php
get_footer();
