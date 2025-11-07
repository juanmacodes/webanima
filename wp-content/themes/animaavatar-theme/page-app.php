
<?php
/* Template Name: App (Inicio) */
get_header();
?>
<section class="section hero-wrap">
  <div class="container">
    <span class="pill">LABORATORIO INMERSIVO</span>
    <h1 style="font-size:clamp(36px,6vw,64px);letter-spacing:.05em;margin:.3em 0">Diseñamos avatares que brillan en cualquier realidad</h1>
    <p style="color:var(--color-muted);max-width:760px">Tecnología XR, IA conversacional y WebXR para experiencias memorables en streaming, hologramas y VR.</p>
    <div style="display:flex;gap:12px;margin:18px 0 26px">
      <a class="app-actions cta" href="<?php echo esc_url( home_url('/contacto') ); ?>" style="display:inline-block">Agenda una demo</a>
      <a class="menu" href="<?php echo esc_url( home_url('/experiencia-inmersiva') ); ?>" style="display:inline-block;padding:10px 16px;border:1px solid var(--color-line);border-radius:12px;font-weight:700">Explora la experiencia 3D</a>
    </div>
    <div class="anima-3d-hero card">
      <model-viewer
        src="https://assets.readyplayer.me/Example.glb"
        alt="Avatar Anima"
        camera-controls auto-rotate ar ar-modes="webxr scene-viewer quick-look"
        exposure="1.0" shadow-intensity="1" reveal="interaction" loading="lazy">
      </model-viewer>
    </div>
  </div>
</section>
<section class="section"><div class="container"><h2>Sección libre</h2><p style="color:var(--color-muted)">Añade aquí bloques/shortcodes o Elementor.</p></div></section>
<?php get_footer(); ?>
