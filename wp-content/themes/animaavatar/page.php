<?php get_header(); ?>

<main id="main" class="site-main container">
<?php if ( have_posts() ) : the_post(); ?>
    <article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
        <h1><?php the_title(); ?></h1>
        <div class="page-content">
            <?php the_content(); ?>
        </div>
    </article>
<?php endif; ?>
</main>

<?php get_footer(); ?>
