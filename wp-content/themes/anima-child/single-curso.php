<?php
get_header();

the_post();

$meta_source = function ( string $field, string $fallback_key ) {
    if ( function_exists( 'get_field' ) ) {
        $value = get_field( $field );
        if ( '' !== (string) $value && null !== $value ) {
            return $value;
        }
    }

    return get_post_meta( get_the_ID(), $fallback_key, true );
};

$meta_values = [
    'precio'    => $meta_source( 'precio', '_precio' ),
    'duracion'  => $meta_source( 'duracion', '_duracion' ),
    'nivel'     => $meta_source( 'nivel', '_nivel' ),
    'modalidad' => $meta_source( 'modalidad', '_modalidad' ),
];

$detail_sections = [
    'temario'     => $meta_source( 'temario', '_temario' ),
    'requisitos'  => $meta_source( 'requisitos', '_requisitos' ),
    'instructor'  => $meta_source( 'instructor', '_instructor' ),
];

$form_shortcode = apply_filters(
    'anima_child_curso_form_shortcode',
    '[contact-form-7 id="REEMPLAZA_ID" title="Inscripción curso"]',
    get_the_ID()
);
?>

<main id="primary" class="site-main">
    <article <?php post_class( 'single-curso' ); ?>>
        <section class="course-hero container">
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="hero-media">
                    <?php the_post_thumbnail( 'full' ); ?>
                </div>
            <?php endif; ?>

            <div class="hero-content">
                <h1 class="hero-title"><?php the_title(); ?></h1>
                <?php if ( has_excerpt() ) : ?>
                    <p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
                <?php endif; ?>

                <?php
                $meta_items = [
                    'precio'    => __( 'Precio', 'anima-child' ),
                    'duracion'  => __( 'Duración', 'anima-child' ),
                    'nivel'     => __( 'Nivel', 'anima-child' ),
                    'modalidad' => __( 'Modalidad', 'anima-child' ),
                ];
                $filtered_meta = array_filter(
                    $meta_values,
                    static function ( $value ) {
                        return '' !== trim( (string) $value );
                    }
                );

                if ( ! empty( $filtered_meta ) ) :
                    ?>
                    <ul class="course-meta">
                        <?php foreach ( $filtered_meta as $key => $value ) : ?>
                            <li>
                                <strong><?php echo esc_html( $meta_items[ $key ] ); ?>:</strong>
                                <span><?php echo esc_html( wp_strip_all_tags( (string) $value ) ); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if ( ! empty( $form_shortcode ) ) : ?>
                    <div class="course-cta">
                        <?php echo do_shortcode( $form_shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="course-body container">
            <h2><?php esc_html_e( 'Descripción', 'anima-child' ); ?></h2>
            <div class="prose">
                <?php the_content(); ?>
            </div>

            <?php foreach ( $detail_sections as $slug => $value ) :
                $value = is_array( $value ) ? implode( "\n", $value ) : $value;
                if ( '' === trim( (string) $value ) ) {
                    continue;
                }

                $titles = [
                    'temario'    => __( 'Temario', 'anima-child' ),
                    'requisitos' => __( 'Requisitos', 'anima-child' ),
                    'instructor' => __( 'Instructor', 'anima-child' ),
                ];
                ?>
                <h2><?php echo esc_html( $titles[ $slug ] ); ?></h2>
                <div class="prose">
                    <?php echo wp_kses_post( wpautop( (string) $value ) ); ?>
                </div>
            <?php endforeach; ?>
        </section>
    </article>
</main>

<?php
get_footer();
?>
