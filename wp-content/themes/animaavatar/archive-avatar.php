<?php
/**
 * Plantilla para el archivo de avatares
 *
 * @package AnimaAvatar
 */

global $post;
get_header();

$selected_tax       = isset( $_GET['tecnologia'] ) ? sanitize_text_field( wp_unslash( $_GET['tecnologia'] ) ) : '';
$tecnologias        = get_terms( [
    'taxonomy'   => 'tecnologia',
    'hide_empty' => false,
] );
$has_demo_viewer    = false;
?>
<section class="section container">
    <header class="section__header animate-on-scroll">
        <h1 class="section__title"><?php post_type_archive_title(); ?></h1>
        <p class="muted"><?php esc_html_e( 'Explora nuestros avatares y filtra por la tecnología empleada.', 'animaavatar' ); ?></p>
    </header>

    <form class="card animate-on-scroll avatar-filters" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'avatar' ) ); ?>">
        <label for="tecnologia" class="pill"><?php esc_html_e( 'Filtrar por tecnología', 'animaavatar' ); ?></label>
        <select id="tecnologia" name="tecnologia">
            <option value=""><?php esc_html_e( 'Todas', 'animaavatar' ); ?></option>
            <?php foreach ( $tecnologias as $term ) : ?>
                <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected_tax, $term->slug ); ?>>
                    <?php echo esc_html( $term->name ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="button"><?php esc_html_e( 'Aplicar', 'animaavatar' ); ?></button>
    </form>

    <?php if ( have_posts() ) : ?>
        <div class="post-grid animate-on-scroll">
            <?php while ( have_posts() ) : the_post();
                $template_id = 'avatar-modal-template-' . get_the_ID();
                $demo_url    = get_post_meta( get_the_ID(), 'anima_url_demo', true );
                $has_demo_viewer = $has_demo_viewer || ! empty( $demo_url );
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'card avatar-card' ); ?>>
                    <header>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    </header>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <figure>
                            <?php the_post_thumbnail( 'medium', [ 'loading' => 'lazy' ] ); ?>
                        </figure>
                    <?php endif; ?>
                    <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
                    <div class="pill">
                        <?php echo wp_kses_post( get_the_term_list( get_the_ID(), 'tecnologia', '', ', ', '' ) ); ?>
                    </div>
                    <div class="card__actions">
                        <a class="button" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Ver ficha', 'animaavatar' ); ?></a>
                        <button type="button" class="button button--ghost avatar-quick-view" data-avatar-modal-open="<?php echo esc_attr( $template_id ); ?>" aria-haspopup="dialog">
                            <?php esc_html_e( 'Vista rápida', 'animaavatar' ); ?>
                        </button>
                    </div>
                </article>
                <template id="<?php echo esc_attr( $template_id ); ?>" data-avatar-template>
                    <div class="avatar-modal__layout">
                        <header class="avatar-modal__header">
                            <span class="pill pill-gradient"><?php esc_html_e( 'Avatar', 'animaavatar' ); ?></span>
                            <h2 class="avatar-modal__title" id="avatar-modal-heading"><?php the_title(); ?></h2>
                            <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 35 ) ); ?></p>
                        </header>
                        <div class="avatar-modal__media">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <figure>
                                    <?php the_post_thumbnail( 'large', [ 'loading' => 'lazy', 'class' => 'avatar-modal__image' ] ); ?>
                                </figure>
                            <?php endif; ?>
                            <?php if ( $demo_url ) : ?>
                                <div class="avatar-modal__viewer">
                                    <?php
                                    echo do_shortcode(
                                        sprintf(
                                            '[anima_model src="%1$s" alt="%2$s" poster="%3$s" video="%4$s" ar="false" auto_rotate="false" camera_controls="false" height="320px" reveal="interaction" loading="lazy"]',
                                            esc_url( $demo_url ),
                                            esc_attr( get_the_title() ),
                                            esc_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ?: '' ),
                                            esc_url( get_post_meta( get_the_ID(), 'anima_avatar_video_fallback', true ) ?: '' )
                                        )
                                    );
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="avatar-modal__meta">
                            <?php
                            $tax_list = get_the_term_list( get_the_ID(), 'tecnologia', '', ', ', '' );
                            if ( $tax_list ) {
                                echo '<p class="avatar-modal__tax">' . wp_kses_post( $tax_list ) . '</p>';
                            }
                            ?>
                            <a class="button" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Ver', 'animaavatar' ); ?></a>
                        </div>
                    </div>
                </template>
            <?php endwhile; ?>
        </div>
        <nav class="pagination" aria-label="<?php esc_attr_e( 'Paginación de avatares', 'animaavatar' ); ?>">
            <?php the_posts_pagination(); ?>
        </nav>
    <?php else : ?>
        <p><?php esc_html_e( 'No se encontraron avatares con los filtros seleccionados.', 'animaavatar' ); ?></p>
    <?php endif; ?>
    <?php if ( $has_demo_viewer ) :
        wp_enqueue_script( 'anima-model-viewer' );
        wp_enqueue_script( 'anima-model-viewer-enhancements' );
    endif; ?>

    <div class="avatar-modal" data-avatar-modal hidden>
        <div class="avatar-modal__overlay" data-avatar-modal-close></div>
        <div class="avatar-modal__dialog" data-avatar-dialog role="dialog" aria-modal="true" aria-labelledby="avatar-modal-heading">
            <button type="button" class="avatar-modal__close" aria-label="<?php esc_attr_e( 'Cerrar vista rápida', 'animaavatar' ); ?>" data-avatar-modal-close>
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="avatar-modal__content" data-avatar-content></div>
        </div>
    </div>
</section>
<?php
get_footer();
