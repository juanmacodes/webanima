<?php get_header(); ?>

<main id="main" class="site-main container">
<?php if ( have_posts() ) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <h1><?php the_title(); ?></h1>

        <?php if ( get_post_type() == 'post' ) : ?>
            <!-- Meta info para entradas de blog -->
            <div class="post-meta">
                <span class="author">Por <?php the_author(); ?></span> |
                <span class="date"><?php the_time( get_option('date_format') ); ?></span> |
                <span class="categories"><?php the_category(', '); ?></span>
            </div>
        <?php else : ?>
            <!-- Meta info para Custom Post Types (curso, avatar, proyecto, experiencia) -->
            <div class="cpt-meta">
                <?php 
                // Mostrar taxonomías relevantes si existen
                $taxonomies = array('nivel', 'tecnologia', 'modalidad');
                foreach( $taxonomies as $tax ) {
                    if ( taxonomy_exists($tax) && has_term('', $tax) ) {
                        echo '<div class="tax-' . esc_attr($tax) . '">';
                        echo '<strong>' . esc_html( ucfirst($tax) ) . ':</strong> ';
                        echo get_the_term_list( get_the_ID(), $tax, '', ', ', '' );
                        echo '</div>';
                    }
                }
                ?>
                <?php 
                // Mostrar campos personalizados (meta) si existen
                $fields = array(
                    'anima_instructores' => 'Instructores',
                    'anima_duracion'    => 'Duración',
                    'anima_dificultad'  => 'Dificultad',
                    'anima_kpis'        => 'KPIs',
                    'anima_url_demo'    => 'Demo URL'
                );
                echo '<ul class="cpt-fields">';
                foreach( $fields as $meta_key => $label ) {
                    $value = get_post_meta( get_the_ID(), $meta_key, true );
                    if ( !empty($value) ) {
                        // Si es URL de demo, mostrar como enlace
                        if ( $meta_key == 'anima_url_demo' ) {
                            echo '<li><strong>' . esc_html($label) . ':</strong> <a href="' . esc_url($value) . '" target="_blank" rel="noopener">' . esc_html($value) . '</a></li>';
                        } else {
                            echo '<li><strong>' . esc_html($label) . ':</strong> ' . esc_html($value) . '</li>';
                        }
                    }
                }
                echo '</ul>';
                ?>
            </div>
        <?php endif; ?>

        <div class="entry-content">
            <?php the_content(); ?>
        </div>

        <?php
        // Enlaces de navegación a siguiente/anterior post (para posts de blog o CPT que estén en algún orden cronológico)
        the_post_navigation( array(
            'prev_text' => '&larr; %title',
            'next_text' => '%title &rarr;',
        ) );
        ?>

        <?php 
        // Si los comentarios están abiertos o hay comentarios, cargar la plantilla de comentarios
        if ( comments_open() || get_comments_number() ) {
            comments_template();
        }
        ?>
    </article>
<?php endif; ?>
</main>

<?php get_footer(); ?>
