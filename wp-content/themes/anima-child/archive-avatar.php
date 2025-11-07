<?php
get_header();
?>

<main id="primary" class="site-main">
    <header class="section-hero container">
        <h1 class="section-title"><?php esc_html_e( 'Avatares de Anima', 'anima-child' ); ?></h1>
        <p class="section-subtitle"><?php esc_html_e( 'Explora nuestro catálogo de avatares y las tecnologías que los potencian.', 'anima-child' ); ?></p>
    </header>

    <section class="grid-layout container">
        <?php if ( have_posts() ) : ?>
            <div class="grid">
                <?php
                while ( have_posts() ) :
                    the_post();
                    ?>
                    <article <?php post_class( 'card avatar-card' ); ?>>
                        <a class="card-link" href="<?php the_permalink(); ?>">
                            <div class="card-media">
                                <?php
                                if ( has_post_thumbnail() ) {
                                    the_post_thumbnail( 'large' );
                                }
                                ?>
                            </div>
                            <div class="card-body">
                                <h3 class="card-title"><?php the_title(); ?></h3>
                                <?php
                                $terms_list = get_the_term_list( get_the_ID(), 'avatar_tech', '', ' ', '' );
                                if ( $terms_list ) :
                                    ?>
                                    <div class="chips" aria-label="<?php esc_attr_e( 'Tecnologías', 'anima-child' ); ?>">
                                        <?php echo wp_kses_post( $terms_list ); ?>
                                    </div>
                                <?php endif; ?>
                                <p class="card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ); ?></p>
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
            <p class="container empty-state"><?php esc_html_e( 'No hay avatares disponibles en este momento.', 'anima-child' ); ?></p>
        <?php endif; ?>
    </section>
</main>

<?php
get_footer();
