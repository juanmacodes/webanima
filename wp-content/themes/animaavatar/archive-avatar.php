<?php
/**
 * Plantilla para el archivo de avatares
 *
 * @package AnimaAvatar
 */

global $post;
get_header();

$selected_tax = isset( $_GET['tecnologia'] ) ? sanitize_text_field( wp_unslash( $_GET['tecnologia'] ) ) : '';
$tecnologias  = get_terms( [
    'taxonomy'   => 'tecnologia',
    'hide_empty' => false,
] );
?>
<section class="section container">
    <header class="section__header animate-on-scroll">
        <h1 class="section__title"><?php post_type_archive_title(); ?></h1>
        <p class="muted"><?php esc_html_e( 'Explora nuestros avatares y filtra por la tecnología empleada.', 'animaavatar' ); ?></p>
    </header>

    <form class="card animate-on-scroll" method="get" action="<?php echo esc_url( get_post_type_archive_link( 'avatar' ) ); ?>">
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
            <?php while ( have_posts() ) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
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
                </article>
            <?php endwhile; ?>
        </div>
        <nav class="pagination" aria-label="<?php esc_attr_e( 'Paginación de avatares', 'animaavatar' ); ?>">
            <?php the_posts_pagination(); ?>
        </nav>
    <?php else : ?>
        <p><?php esc_html_e( 'No se encontraron avatares con los filtros seleccionados.', 'animaavatar' ); ?></p>
    <?php endif; ?>
</section>
<?php
get_footer();
