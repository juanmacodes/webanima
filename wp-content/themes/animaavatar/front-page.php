<?php
/**
 * Plantilla de la página de inicio
 *
 * @package AnimaAvatar
 */

global $post;
get_header();

$services = [
    [
        'title' => __( 'Producción para streaming holográfico', 'animaavatar' ),
        'description' => __( 'Escenarios virtuales, cámaras virtuales y overlays con efectos volumétricos para streams inmersivos.', 'animaavatar' ),
    ],
    [
        'title' => __( 'Eventos XR con IA generativa', 'animaavatar' ),
        'description' => __( 'Integramos asistentes virtuales, clones digitales y traducción en tiempo real.', 'animaavatar' ),
    ],
    [
        'title' => __( 'Laboratorio de avatares 3D', 'animaavatar' ),
        'description' => __( 'Creamos personajes estilizados y realistas listos para Unreal, Unity y WebGL.', 'animaavatar' ),
    ],
    [
        'title' => __( 'Experiencias VR multiusuario', 'animaavatar' ),
        'description' => __( 'Mundos persistentes con interacción avanzada, analítica y despliegues en la nube.', 'animaavatar' ),
    ],
];

$curso_ids = get_transient( 'animaavatar_home_cursos' );
if ( false === $curso_ids ) {
    $curso_query = new WP_Query( [
        'post_type'      => 'curso',
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ] );
    $curso_ids = wp_list_pluck( $curso_query->posts, 'ID' );
    set_transient( 'animaavatar_home_cursos', $curso_ids, MINUTE_IN_SECONDS * 10 );
    wp_reset_postdata();
}

$avatar_ids = get_transient( 'animaavatar_home_avatares' );
if ( false === $avatar_ids ) {
    $avatar_query = new WP_Query( [
        'post_type'      => 'avatar',
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );
    $avatar_ids = wp_list_pluck( $avatar_query->posts, 'ID' );
    set_transient( 'animaavatar_home_avatares', $avatar_ids, MINUTE_IN_SECONDS * 10 );
    wp_reset_postdata();
}
?>
<section class="hero dark-bg">
    <div class="container">
        <span class="badge pill-gradient animate-on-scroll"><?php esc_html_e( 'Laboratorio inmersivo', 'animaavatar' ); ?></span>
        <h1 class="hero__title animate-on-scroll"><?php esc_html_e( 'Diseñamos avatares que brillan en cualquier realidad', 'animaavatar' ); ?></h1>
        <p class="hero__subtitle animate-on-scroll"><?php esc_html_e( 'Tecnología XR, pipelines realtime y storytelling volumétrico para marcas que buscan impacto en streaming, VR y metaverso.', 'animaavatar' ); ?></p>
        <div class="flex flex-center animate-on-scroll">
            <a class="button" href="<?php echo esc_url( home_url( '/contacto' ) ); ?>"><?php esc_html_e( 'Agenda una demo', 'animaavatar' ); ?></a>
            <a class="button" href="<?php echo esc_url( home_url( '/experiencia-inmersiva' ) ); ?>"><?php esc_html_e( 'Explora la experiencia 3D', 'animaavatar' ); ?></a>
        </div>
    </div>
</section>

<section class="section container" aria-labelledby="slider-title">
    <div class="flex flex-between animate-on-scroll">
        <h2 id="slider-title" class="section__title"><?php esc_html_e( 'Experiencias recientes', 'animaavatar' ); ?></h2>
        <p class="muted"><?php esc_html_e( 'Explora nuestros proyectos inmersivos destacados.', 'animaavatar' ); ?></p>
    </div>
    <div class="swiper-container overflow-hidden animate-on-scroll">
        <div class="swiper" id="home-slider" aria-live="polite" aria-label="<?php esc_attr_e( 'Slider de experiencias recientes', 'animaavatar' ); ?>">
            <div class="swiper-wrapper">
                <?php
                $slides = new WP_Query( [
                    'post_type'      => 'slide',
                    'posts_per_page' => 5,
                    'post_status'    => 'publish',
                    'orderby'        => 'menu_order',
                    'order'          => 'ASC',
                ] );

                if ( $slides->have_posts() ) :
                    $slide_index = 0;
                    while ( $slides->have_posts() ) :
                        $slides->the_post();
                        $slide_index++;
                        $is_first = 1 === $slide_index;
                        $slide_url = get_post_meta( get_the_ID(), 'anima_slide_url', true );
                        ?>
                        <article class="swiper-slide card">
                            <figure>
                                <?php
                                if ( has_post_thumbnail() ) {
                                    $thumb_args = [
                                        'loading'       => $is_first ? 'eager' : 'lazy',
                                        'class'         => 'slide-image',
                                        'fetchpriority' => $is_first ? 'high' : 'auto',
                                    ];

                                    echo wp_get_attachment_image( get_post_thumbnail_id(), 'large', false, $thumb_args );
                                } else {
                                    $placeholder = get_post_meta( get_the_ID(), 'anima_slide_placeholder', true );
                                    if ( $placeholder ) {
                                        $dimensions = wp_getimagesize( $placeholder );
                                        $width      = $dimensions ? (int) $dimensions[0] : '';
                                        $height     = $dimensions ? (int) $dimensions[1] : '';
                                        $size_attr  = '';

                                        if ( $width && $height ) {
                                            $size_attr = ' width="' . esc_attr( (string) $width ) . '" height="' . esc_attr( (string) $height ) . '"';
                                        }

                                        echo '<img class="slide-image" loading="' . ( $is_first ? 'eager' : 'lazy' ) . '"' . ( $is_first ? ' fetchpriority="high"' : '' ) . ' src="' . esc_url( $placeholder ) . '" alt="' . esc_attr( get_the_title() ) . '"' . $size_attr . ' />';
                                    }
                                }
                                ?>
                            </figure>
                            <header>
                                <h3><?php the_title(); ?></h3>
                            </header>
                            <p><?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
                            <?php if ( $slide_url ) : ?>
                                <a class="button" href="<?php echo esc_url( $slide_url ); ?>">
                                    <?php esc_html_e( 'Descubrir', 'animaavatar' ); ?>
                                </a>
                            <?php endif; ?>
                        </article>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <p><?php esc_html_e( 'Añade diapositivas desde el panel de administración para alimentar este slider.', 'animaavatar' ); ?></p>
                    <?php
                endif;
                ?>
            </div>
            <div class="swiper-pagination" aria-hidden="true"></div>
            <div class="swiper-navigation" aria-controls="home-slider">
                <button type="button" class="swiper-button-prev" aria-label="<?php esc_attr_e( 'Ver diapositiva anterior', 'animaavatar' ); ?>" aria-controls="home-slider">
                    <span class="screen-reader-text"><?php esc_html_e( 'Anterior', 'animaavatar' ); ?></span>
                </button>
                <button type="button" class="swiper-button-next" aria-label="<?php esc_attr_e( 'Ver diapositiva siguiente', 'animaavatar' ); ?>" aria-controls="home-slider">
                    <span class="screen-reader-text"><?php esc_html_e( 'Siguiente', 'animaavatar' ); ?></span>
                </button>
            </div>
        </div>
    </div>
</section>

<section class="section container" id="servicios">
    <h2 class="section__title animate-on-scroll"><?php esc_html_e( 'Servicios', 'animaavatar' ); ?></h2>
    <div class="grid-2">
        <?php foreach ( $services as $service ) : ?>
            <article class="card animate-on-scroll">
                <h3><?php echo esc_html( $service['title'] ); ?></h3>
                <p><?php echo esc_html( $service['description'] ); ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section container" id="cursos-destacados">
    <h2 class="section__title animate-on-scroll"><?php esc_html_e( 'Cursos destacados', 'animaavatar' ); ?></h2>
    <div class="post-grid">
        <?php
        if ( ! empty( $curso_ids ) ) {
            foreach ( $curso_ids as $curso_id ) {
                $curso = get_post( $curso_id );
                if ( ! $curso ) {
                    continue;
                }
                ?>
                <article class="card animate-on-scroll">
                    <header>
                        <h3><a href="<?php echo esc_url( get_permalink( $curso ) ); ?>"><?php echo esc_html( get_the_title( $curso ) ); ?></a></h3>
                    </header>
                    <p><?php echo esc_html( wp_trim_words( get_the_excerpt( $curso ), 20 ) ); ?></p>
                    <a class="button" href="<?php echo esc_url( get_permalink( $curso ) ); ?>"><?php esc_html_e( 'Ver curso', 'animaavatar' ); ?></a>
                </article>
                <?php
            }
        } else {
            ?>
            <p><?php esc_html_e( 'Crea cursos con el plugin Anima Engine para mostrarlos aquí.', 'animaavatar' ); ?></p>
            <?php
        }
        ?>
    </div>
</section>

<section class="section container" id="avatares-populares">
    <h2 class="section__title animate-on-scroll"><?php esc_html_e( 'Avatares populares', 'animaavatar' ); ?></h2>
    <div class="post-grid">
        <?php
        if ( ! empty( $avatar_ids ) ) {
            foreach ( $avatar_ids as $avatar_id ) {
                $avatar = get_post( $avatar_id );
                if ( ! $avatar ) {
                    continue;
                }
                ?>
                <article class="card animate-on-scroll">
                    <?php if ( has_post_thumbnail( $avatar ) ) : ?>
                        <figure>
                            <?php echo get_the_post_thumbnail( $avatar, 'medium', [ 'loading' => 'lazy' ] ); ?>
                        </figure>
                    <?php endif; ?>
                    <header>
                        <h3><a href="<?php echo esc_url( get_permalink( $avatar ) ); ?>"><?php echo esc_html( get_the_title( $avatar ) ); ?></a></h3>
                    </header>
                    <p><?php echo esc_html( wp_trim_words( get_the_excerpt( $avatar ), 18 ) ); ?></p>
                    <a class="button" href="<?php echo esc_url( get_permalink( $avatar ) ); ?>"><?php esc_html_e( 'Ver avatar', 'animaavatar' ); ?></a>
                </article>
                <?php
            }
        } else {
            ?>
            <p><?php esc_html_e( 'Publica avatares para activar esta galería.', 'animaavatar' ); ?></p>
            <?php
        }
        ?>
    </div>
</section>

<section class="section dark-bg">
    <div class="container text-center animate-on-scroll">
        <h2 class="section__title"><?php esc_html_e( '¿Listo para tu experiencia inmersiva?', 'animaavatar' ); ?></h2>
        <p><?php esc_html_e( 'Agenda una consultoría express o sumérgete en nuestra experiencia preparada con Unreal Engine.', 'animaavatar' ); ?></p>
        <a class="button" href="<?php echo esc_url( home_url( '/experiencia-inmersiva' ) ); ?>"><?php esc_html_e( 'Ir a Experiencia Inmersiva', 'animaavatar' ); ?></a>
    </div>
</section>
<?php
get_footer();
