<?php get_header(); ?>

<main id="main-content" class="site-main container" role="main">
<?php if ( have_posts() ) :
    while ( have_posts() ) :
        the_post();
        ?>
        <article id="page-<?php the_ID(); ?>" <?php post_class( 'page-entry' ); ?>>
            <header class="page-header">
                <h1 class="page-title"><?php the_title(); ?></h1>
            </header>
            <div class="page-content">
                <?php the_content(); ?>
                <?php wp_link_pages( array(
                    'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Páginas de la entrada', 'animaavatar' ) . '">',
                    'after'  => '</nav>',
                ) ); ?>
            </div>
        </article>
        <?php
        if ( comments_open() || get_comments_number() ) {
            comments_template();
        }
    endwhile;
endif; ?>
</main>

<?php get_footer(); ?>
