<?php get_header(); ?>

<main id="main-content" class="site-main container" role="main">
    <header class="archive-header">
        <h1 class="archive-title"><?php the_archive_title(); ?></h1>
        <?php
        $archive_description = get_the_archive_description();
        if ( $archive_description ) :
            ?>
            <div class="archive-description"><?php echo wp_kses_post( wpautop( $archive_description ) ); ?></div>
        <?php endif; ?>
    </header>

    <?php if ( have_posts() ) : ?>
        <div class="archive-posts">
            <?php
            while ( have_posts() ) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'archive-card animate-on-scroll' ); ?>>
                    <div class="archive-card__media">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="archive-card__body">
                        <h2 class="archive-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <p class="archive-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt() ? get_the_excerpt() : get_the_content(), 25, '…' ) ); ?></p>
                        <a class="archive-card__link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Leer más', 'animaavatar' ); ?></a>
                    </div>
                </article>
                <?php
            endwhile;
            ?>
        </div>

        <?php the_posts_pagination( array(
            'prev_text' => esc_html__( 'Entradas anteriores', 'animaavatar' ),
            'next_text' => esc_html__( 'Entradas siguientes', 'animaavatar' ),
            'mid_size'  => 1,
        ) ); ?>
    <?php else : ?>
        <p><?php esc_html_e( 'No hay entradas disponibles en esta sección.', 'animaavatar' ); ?></p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
