<?php
/*
Template Name: Galería de Avatares
*/
get_header();

if (have_posts()) {
  the_post();
}
?>

<main id="site-content" class="avatars-gallery">
  <section class="section">
    <div class="container">
      <header class="section__header" style="margin-bottom:24px;">
        <h1 class="section__title"><?php the_title(); ?></h1>
        <?php if (get_the_content()) : ?>
          <div class="section__intro" style="color:var(--color-muted);max-width:720px;">
            <?php the_content(); ?>
          </div>
        <?php endif; ?>
      </header>

      <?php
      $avatars = new WP_Query([
        'post_type'      => 'avatar',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
      ]);
      ?>

      <?php if ($avatars->have_posts()) : ?>
        <div class="avatars-grid" style="display:grid;gap:24px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
          <?php while ($avatars->have_posts()) : $avatars->the_post();
            $image = get_field('avatar_imagen');
            $description = get_field('avatar_descripcion');
            $twitter = get_field('avatar_twitter');
            $instagram = get_field('avatar_instagram');
            $tiktok = get_field('avatar_tiktok');

            $image_url = '';
            $image_alt = '';
            if (is_array($image)) {
              $image_url = !empty($image['url']) ? $image['url'] : '';
              $image_alt = !empty($image['alt']) ? $image['alt'] : get_the_title();
            }
            if (!$image_url) {
              $image_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
            }
            if (!$image_alt) {
              $image_alt = get_the_title();
            }

            $description_text = $description ? wp_strip_all_tags($description) : '';
          ?>
            <article class="avatar-card">
              <?php if ($image_url) : ?>
                <img class="avatar-thumb" src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>">
              <?php endif; ?>
              <h3 class="avatar-title"><?php the_title(); ?></h3>
              <button
                type="button"
                class="btn avatar-detail"
                aria-haspopup="dialog"
                aria-controls="avatar-modal"
                data-title="<?php echo esc_attr(get_the_title()); ?>"
                data-img="<?php echo esc_url($image_url ?: ''); ?>"
                data-desc="<?php echo esc_attr($description_text); ?>"
                data-twitter="<?php echo esc_url($twitter ?: ''); ?>"
                data-instagram="<?php echo esc_url($instagram ?: ''); ?>"
                data-tiktok="<?php echo esc_url($tiktok ?: ''); ?>"
              ><?php esc_html_e('Ver detalle', 'animaavatar'); ?></button>
            </article>
          <?php endwhile; ?>
        </div>
      <?php else : ?>
        <p style="color:var(--color-muted);"><?php esc_html_e('Pronto sumaremos más avatares a la galería.', 'animaavatar'); ?></p>
      <?php endif; ?>

      <?php wp_reset_postdata(); ?>
    </div>
  </section>
</main>

<div
  id="avatar-modal"
  class="avatar-modal"
  aria-hidden="true"
  role="dialog"
  aria-modal="true"
  aria-labelledby="am-title"
  aria-describedby="am-desc"
>
  <div class="avatar-modal__backdrop" data-close></div>
  <div class="avatar-modal__dialog" role="document" tabindex="-1">
    <button class="avatar-modal__close" type="button" aria-label="<?php esc_attr_e('Cerrar', 'animaavatar'); ?>" data-close>&times;</button>
    <img id="am-img" alt="">
    <h3 id="am-title"></h3>
    <p id="am-desc"></p>
    <div id="am-socials" class="avatar-modal__socials"></div>
  </div>
</div>

<?php get_footer(); ?>
