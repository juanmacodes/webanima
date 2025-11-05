<?php get_header(); ?>

<main id="main" class="site-main">
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1 class="animate-on-scroll"><?php bloginfo('name'); ?></h1>
            <p class="animate-on-scroll">Bienvenido a nuestra plataforma. Explora cursos, avatares y experiencias 3D inmersivas desarrolladas para potenciar tu aprendizaje.</p>
            <a href="<?php echo esc_url( home_url('/experiencia-inmersiva') ); ?>" class="btn animate-on-scroll">👓 Experiencia Inmersiva</a>
        </div>
    </section>

    <!-- Slider Section (Swiper slider placeholder) -->
    <section class="slider-section">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <div class="swiper-slide">Slide 1 - Contenido visual o testimonial</div>
                <div class="swiper-slide">Slide 2 - Contenido visual o testimonial</div>
                <div class="swiper-slide">Slide 3 - Contenido visual o testimonial</div>
            </div>
            <!-- Puedes agregar navegación del slider:
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-pagination"></div>
            -->
        </div>
    </section>

    <!-- Services/Features Section -->
    <section class="services-section">
        <div class="container">
            <h2>Servicios y Características</h2>
            <div class="card-grid">
                <div class="card animate-on-scroll">
                    <h3>Curso Interactivo</h3>
                    <p>Aprende con cursos en línea gamificados y experiencias 3D.</p>
                </div>
                <div class="card animate-on-scroll">
                    <h3>Avatares Personalizados</h3>
                    <p>Crea y utiliza avatares 3D en tus proyectos educativos.</p>
                </div>
                <div class="card animate-on-scroll">
                    <h3>Proyectos Inmersivos</h3>
                    <p>Desarrolla proyectos con realidad virtual y aumentada integrados.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Courses Section -->
    <section class="courses-section">
        <div class="container">
            <h2>Cursos Destacados</h2>
            <div class="post-grid">
                <?php
                $curso_query = new WP_Query( array(
                    'post_type' => 'curso',
                    'posts_per_page' => 3,
                    'post_status' => 'publish',
                ) );
                if ( $curso_query->have_posts() ) :
                    while ( $curso_query->have_posts() ) : $curso_query->the_post(); ?>
                        <article class="post-card animate-on-scroll">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium'); ?></a>
                            <?php endif; ?>
                            <div class="content">
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <p><?php echo wp_trim_words( get_the_content(), 15, '...' ); ?></p>
                            </div>
                        </article>
                    <?php endwhile;
                    wp_reset_postdata();
                else: ?>
                    <p class="animate-on-scroll">Próximamente añadiremos cursos increíbles. ¡Mantente atento!</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Popular Avatars Section -->
    <section class="avatars-section">
        <div class="container">
            <h2>Avatares Populares</h2>
            <div class="post-grid">
                <?php
                $avatar_query = new WP_Query( array(
                    'post_type' => 'avatar',
                    'posts_per_page' => 3,
                    'post_status' => 'publish',
                ) );
                if ( $avatar_query->have_posts() ) :
                    while ( $avatar_query->have_posts() ) : $avatar_query->the_post(); ?>
                        <article class="post-card animate-on-scroll">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium'); ?></a>
                            <?php endif; ?>
                            <div class="content">
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <p><?php echo wp_strip_all_tags( get_the_excerpt() ); ?></p>
                            </div>
                        </article>
                    <?php endwhile;
                    wp_reset_postdata();
                else: ?>
                    <p class="animate-on-scroll">Muy pronto podrás descubrir nuestros avatares más populares aquí.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Immersive Experience Call-To-Action Section -->
    <section class="immersive-section">
        <div class="container">
            <h2>¿Listo para una experiencia inmersiva?</h2>
            <p>Sumérgete en un mundo virtual y comprueba cómo el aprendizaje puede ser diferente.</p>
            <a href="<?php echo esc_url( home_url('/experiencia-inmersiva') ); ?>" class="btn">Ingresar a la Experiencia VR</a>
        </div>
    </section>
</main>

<?php get_footer(); ?>
