<?php
/**
 * Plantilla individual para el CPT Avatares.
 *
 * @package Anima
 */

get_header();

the_post();
?>

<main id="primary" class="site-main">
    <article <?php post_class( 'single-avatar' ); ?>>
        <section class="avatar-hero container">
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="hero-media">
                    <?php the_post_thumbnail( 'large' ); ?>
                </div>
            <?php endif; ?>

            <div class="hero-content">
                <h1 class="hero-title"><?php the_title(); ?></h1>
                <?php
                $terms_list = get_the_term_list( get_the_ID(), 'avatar_tech', '', ' ', '' );
                if ( $terms_list ) :
                    ?>
                    <div class="chips" aria-label="<?php esc_attr_e( 'Tecnologías', 'anima' ); ?>">
                        <?php echo wp_kses_post( $terms_list ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="avatar-body container">
            <div class="prose">
                <?php the_content(); ?>
            </div>
        </section>
    </article>
</main>

<?php
get_footer();
