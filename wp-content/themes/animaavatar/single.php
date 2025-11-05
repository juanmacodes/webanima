<?php get_header(); ?>

<main id="main-content" class="site-main container" role="main">
<?php if ( have_posts() ) :
    while ( have_posts() ) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--single' ); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>

                <?php if ( 'post' === get_post_type() ) : ?>
                    <ul class="entry-meta reset-list">
                        <li class="entry-meta__item"><?php printf( esc_html__( 'Por %s', 'animaavatar' ), esc_html( get_the_author() ) ); ?></li>
                        <li class="entry-meta__item"><?php echo esc_html( get_the_date() ); ?></li>
                        <li class="entry-meta__item"><?php the_category( ', ' ); ?></li>
                    </ul>
                <?php else : ?>
                    <div class="entry-meta entry-meta--cpt">
                        <?php
                        $taxonomies = array( 'nivel', 'tecnologia', 'modalidad' );
                        foreach ( $taxonomies as $tax ) {
                            if ( taxonomy_exists( $tax ) && has_term( '', $tax ) ) {
                                echo '<p class="entry-meta__taxonomy"><strong>' . esc_html( ucfirst( $tax ) ) . ':</strong> ' . wp_kses_post( get_the_term_list( get_the_ID(), $tax, '', ', ', '' ) ) . '</p>';
                            }
                        }

                        $fields = array(
                            'anima_instructores' => __( 'Instructores', 'animaavatar' ),
                            'anima_duracion'    => __( 'Duración', 'animaavatar' ),
                            'anima_dificultad'  => __( 'Dificultad', 'animaavatar' ),
                            'anima_kpis'        => __( 'KPIs', 'animaavatar' ),
                            'anima_url_demo'    => __( 'Demo', 'animaavatar' ),
                        );

                        echo '<dl class="entry-meta__details">';
                        foreach ( $fields as $meta_key => $label ) {
                            $value = get_post_meta( get_the_ID(), $meta_key, true );

                            if ( empty( $value ) ) {
                                continue;
                            }

                            echo '<div class="entry-meta__detail">';
                            echo '<dt>' . esc_html( $label ) . '</dt>';
                            if ( 'anima_url_demo' === $meta_key ) {
                                echo '<dd><a href="' . esc_url( $value ) . '" target="_blank" rel="noopener">' . esc_html( $value ) . '</a></dd>';
                            } else {
                                echo '<dd>' . esc_html( $value ) . '</dd>';
                            }
                            echo '</div>';
                        }
                        echo '</dl>';
                        ?>
                    </div>
                <?php endif; ?>
            </header>

            <div class="entry-content">
                <?php
                if ( has_post_thumbnail() ) {
                    the_post_thumbnail( 'large', array( 'loading' => 'lazy', 'class' => 'entry-featured-image' ) );
                }

                the_content();

                wp_link_pages( array(
                    'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Páginas de la entrada', 'animaavatar' ) . '">',
                    'after'  => '</nav>',
                ) );
                ?>
            </div>

            <footer class="entry-footer">
                <?php the_post_navigation( array(
                    'prev_text' => '<span class="nav-label">&larr; ' . esc_html__( 'Anterior', 'animaavatar' ) . '</span><span class="nav-title">%title</span>',
                    'next_text' => '<span class="nav-label">' . esc_html__( 'Siguiente', 'animaavatar' ) . ' &rarr;</span><span class="nav-title">%title</span>',
                ) ); ?>
            </footer>

            <?php
            if ( comments_open() || get_comments_number() ) {
                comments_template();
            }
            ?>
        </article>
        <?php
    endwhile;
endif; ?>
</main>

<?php get_footer(); ?>
