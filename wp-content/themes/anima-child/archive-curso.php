<?php
get_header();
?>

<main id="primary" class="site-main">
    <header class="section-hero container">
        <h1 class="section-title"><?php esc_html_e( 'Formación en Anima', 'anima-child' ); ?></h1>
        <p class="section-subtitle"><?php esc_html_e( 'Cursos de IA, VR y experiencias inmersivas diseñados por nuestro equipo.', 'anima-child' ); ?></p>
    </header>

    <section class="courses-grid container">
        <?php if ( have_posts() ) : ?>
            <div class="grid">
                <?php
                while ( have_posts() ) :
                    the_post();
                    ?>
                    <article <?php post_class( 'card course-card' ); ?>>
                        <a class="card-link" href="<?php the_permalink(); ?>">
                            <div class="card-media">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'large' ); ?>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h3 class="card-title"><?php the_title(); ?></h3>
                                <p class="card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?></p>
                                <span class="cta-link"><?php esc_html_e( 'Ver curso', 'anima-child' ); ?></span>
                            </div>
                        </a>
                    </article>
                    <?php
                endwhile;
                ?>
            </div>

            <div class="pagination-wrapper">
                <?php
                the_posts_pagination(
                    [
                        'prev_text' => __( 'Anterior', 'anima-child' ),
                        'next_text' => __( 'Siguiente', 'anima-child' ),
                    ]
                );
                ?>
            </div>
        <?php else : ?>
            <p class="container empty-state"><?php esc_html_e( 'No hay cursos disponibles actualmente.', 'anima-child' ); ?></p>
        <?php endif; ?>
    </section>
</main>

<?php
get_footer();
