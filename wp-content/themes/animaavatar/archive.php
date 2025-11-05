<?php get_header(); ?>

<main id="main" class="site-main container">
    <header class="archive-header">
        <h1>
            <?php the_archive_title(); // Título genérico del archivo (ej: "Categoría: Diseño" o "Cursos") ?>
        </h1>
        <?php if ( the_archive_description() ) : ?>
            <p><?php the_archive_description(); // Descripción de la taxonomía o introducción ?></p>
        <?php endif; ?>
    </header>

    <?php if ( have_posts() ) : ?>
        <div class="archive-posts flex">
            <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('card animate-on-scroll'); ?> style="width:300px; margin:1rem;">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium'); ?></a>
                    <?php endif; ?>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <div class="entry-excerpt">
                        <p><?php echo wp_trim_words( get_the_content(), 20, '...' ); ?></p>
                        <a href="<?php the_permalink(); ?>" class="read-more">Leer más &raquo;</a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <?php the_posts_pagination( array(
            'prev_text' => '&laquo; Anteriores',
            'next_text' => 'Siguientes &raquo;',
        ) ); ?>

    <?php else : ?>
        <p><?php _e('No hay entradas disponibles en esta sección.', 'animaavatar'); ?></p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
