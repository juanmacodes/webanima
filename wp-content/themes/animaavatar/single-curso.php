<?php
/**
 * Plantilla individual para el CPT Curso.
 *
 * @package AnimaAvatar
 */

get_header();

if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();
        echo '<main class="anima-course-single">';
        the_content();
        echo '</main>';
    }
}

get_footer();
