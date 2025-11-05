<?php get_header(); ?>

<main id="main-content" class="site-main">
    <section class="hero" aria-labelledby="hero-title">
        <div class="hero__background" aria-hidden="true">
            <span class="hero__orb hero__orb--one"></span>
            <span class="hero__orb hero__orb--two"></span>
            <span class="hero__orb hero__orb--three"></span>
        </div>
        <div class="container hero__inner">
            <div class="hero__content">
                <p class="hero__kicker animate-on-scroll"><?php esc_html_e( 'Experiencias inmersivas en tiempo real', 'animaavatar' ); ?></p>
                <h1 id="hero-title" class="hero__title animate-on-scroll"><?php bloginfo( 'name' ); ?></h1>
                <p class="hero__subtitle animate-on-scroll"><?php esc_html_e( 'Construimos aulas virtuales, avatares hiperrealistas y recorridos WebXR listos para tu próxima experiencia formativa.', 'animaavatar' ); ?></p>
                <a class="cta-3d animate-on-scroll" href="<?php echo esc_url( home_url( '/experiencia-inmersiva' ) ); ?>">
                    <?php esc_html_e( 'Iniciar la misión 3D', 'animaavatar' ); ?>
                </a>
            </div>
            <div class="hero__visual" aria-hidden="true">
                <div class="hero__card hero__card--primary">
                    <span><?php esc_html_e( 'Elementor Ready', 'animaavatar' ); ?></span>
                </div>
                <div class="hero__card hero__card--secondary">
                    <span><?php esc_html_e( 'WooCommerce XR', 'animaavatar' ); ?></span>
                </div>
                <div class="hero__card hero__card--tertiary">
                    <span><?php esc_html_e( 'BuddyPress Social Hub', 'animaavatar' ); ?></span>
                </div>
            </div>
        </div>
    </section>

    <section class="slider-section" aria-label="<?php esc_attr_e( 'Historias inmersivas destacadas', 'animaavatar' ); ?>">
        <div class="container">
            <div class="swiper hero-slider">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <article class="slider-card">
                            <h3><?php esc_html_e( 'Campus virtual de realidades mixtas', 'animaavatar' ); ?></h3>
                            <p><?php esc_html_e( 'Integra aulas holográficas y flujos colaborativos con un rendimiento impecable.', 'animaavatar' ); ?></p>
                        </article>
                    </div>
                    <div class="swiper-slide">
                        <article class="slider-card">
                            <h3><?php esc_html_e( 'Avatares hiperrealistas con IA', 'animaavatar' ); ?></h3>
                            <p><?php esc_html_e( 'Personaliza identidades digitales con animaciones responsivas en dark mode.', 'animaavatar' ); ?></p>
                        </article>
                    </div>
                    <div class="swiper-slide">
                        <article class="slider-card">
                            <h3><?php esc_html_e( 'E-commerce 3D listo para VR', 'animaavatar' ); ?></h3>
                            <p><?php esc_html_e( 'Combina WooCommerce con WebXR para lanzar catálogos navegables en 360°.', 'animaavatar' ); ?></p>
                        </article>
                    </div>
                </div>
                <div class="swiper-pagination" aria-hidden="true"></div>
                <button class="swiper-button-prev" type="button" aria-label="<?php esc_attr_e( 'Ver elemento anterior', 'animaavatar' ); ?>"></button>
                <button class="swiper-button-next" type="button" aria-label="<?php esc_attr_e( 'Ver siguiente elemento', 'animaavatar' ); ?>"></button>
            </div>
        </div>
    </section>

    <section class="services-section" aria-labelledby="services-title">
        <div class="container">
            <h2 id="services-title" class="section-title animate-on-scroll"><?php esc_html_e( 'Servicios y características', 'animaavatar' ); ?></h2>
            <div class="card-grid">
                <article class="card animate-on-scroll">
                    <h3><?php esc_html_e( 'Cursos interactivos', 'animaavatar' ); ?></h3>
                    <p><?php esc_html_e( 'Despliega itinerarios WebXR, evaluaciones gamificadas y paneles de progreso en vivo.', 'animaavatar' ); ?></p>
                </article>
                <article class="card animate-on-scroll">
                    <h3><?php esc_html_e( 'Avatares personalizados', 'animaavatar' ); ?></h3>
                    <p><?php esc_html_e( 'Crea identidades digitales con animaciones faciales y sincronización labial inteligente.', 'animaavatar' ); ?></p>
                </article>
                <article class="card animate-on-scroll">
                    <h3><?php esc_html_e( 'Proyectos inmersivos', 'animaavatar' ); ?></h3>
                    <p><?php esc_html_e( 'Integra mundos 3D con experiencias sociales BuddyPress y monetización WooCommerce.', 'animaavatar' ); ?></p>
                </article>
            </div>
        </div>
    </section>

    <section class="courses-section" aria-labelledby="courses-title">
        <div class="container">
            <h2 id="courses-title" class="section-title animate-on-scroll"><?php esc_html_e( 'Cursos destacados', 'animaavatar' ); ?></h2>
            <div class="post-grid">
                <?php
                $curso_query = new WP_Query( array(
                    'post_type'      => 'curso',
                    'posts_per_page' => 3,
                    'post_status'    => 'publish',
                ) );

                if ( $curso_query->have_posts() ) :
                    while ( $curso_query->have_posts() ) :
                        $curso_query->the_post();
                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card animate-on-scroll' ); ?>>
                            <?php if ( has_post_thumbnail() ) : ?>
                                <a href="<?php the_permalink(); ?>" class="post-card__image-link">
                                    <?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
                                </a>
                            <?php endif; ?>
                            <div class="content">
                                <h3 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <p><?php echo esc_html( wp_trim_words( get_the_content(), 18, '…' ) ); ?></p>
                            </div>
                        </article>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <p class="animate-on-scroll"><?php esc_html_e( 'Próximamente añadiremos cursos increíbles. ¡Mantente atento!', 'animaavatar' ); ?></p>
                    <?php
                endif;
                ?>
            </div>
        </div>
    </section>

    <section class="avatars-section" aria-labelledby="avatars-title">
        <div class="container">
            <h2 id="avatars-title" class="section-title animate-on-scroll"><?php esc_html_e( 'Avatares populares', 'animaavatar' ); ?></h2>
            <div class="post-grid">
                <?php
                $avatar_query = new WP_Query( array(
                    'post_type'      => 'avatar',
                    'posts_per_page' => 3,
                    'post_status'    => 'publish',
                ) );

                if ( $avatar_query->have_posts() ) :
                    while ( $avatar_query->have_posts() ) :
                        $avatar_query->the_post();
                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card animate-on-scroll' ); ?>>
                            <?php if ( has_post_thumbnail() ) : ?>
                                <a href="<?php the_permalink(); ?>" class="post-card__image-link">
                                    <?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
                                </a>
                            <?php endif; ?>
                            <div class="content">
                                <h3 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '…' ) ); ?></p>
                            </div>
                        </article>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <p class="animate-on-scroll"><?php esc_html_e( 'Muy pronto podrás descubrir nuestros avatares más populares aquí.', 'animaavatar' ); ?></p>
                    <?php
                endif;
                ?>
            </div>
        </div>
    </section>

    <section class="immersive-section" aria-labelledby="immersive-title">
        <div class="container">
            <h2 id="immersive-title" class="section-title animate-on-scroll"><?php esc_html_e( '¿Listo para una experiencia inmersiva?', 'animaavatar' ); ?></h2>
            <p class="animate-on-scroll"><?php esc_html_e( 'Sumérgete en un mundo virtual y comprueba cómo el aprendizaje puede ser diferente.', 'animaavatar' ); ?></p>
            <a class="cta-3d animate-on-scroll" href="<?php echo esc_url( home_url( '/experiencia-inmersiva' ) ); ?>"><?php esc_html_e( 'Ingresar a la experiencia VR', 'animaavatar' ); ?></a>
        </div>
    </section>
</main>

<?php get_footer(); ?>
