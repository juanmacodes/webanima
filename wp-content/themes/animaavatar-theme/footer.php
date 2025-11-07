
</main>
<?php if( get_theme_mod('anima_header_mode','top') === 'topbottom'): ?>
  <nav class="bottom-nav">
    <ul class="container">
      <?php
        $items = [
          'Inicio'   => get_theme_mod('anima_nav_inicio','/'),
          'Explorar' => get_theme_mod('anima_nav_explorar','/avatares'),
          'Cursos'   => get_theme_mod('anima_nav_cursos','/cursos'),
          'Proyectos'=> get_theme_mod('anima_nav_proyectos','/proyectos'),
          'Perfil'   => get_theme_mod('anima_nav_perfil','/mi-perfil'),
        ];
        foreach($items as $label=>$url){
          printf('<li><a href="%s">%s</a></li>', esc_url($url), esc_html($label));
        }
      ?>
    </ul>
  </nav>
<?php endif; ?>
<footer class="container" style="padding:40px 16px;color:var(--color-muted)">
  © <?php echo date('Y');?> Anima Avatar Agency
</footer>
<?php wp_footer();?>
</body></html>
