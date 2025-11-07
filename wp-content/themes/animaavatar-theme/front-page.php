
<?php get_header(); ?>

<section class="section hero-wrap">
  <div class="container">
    <span class="pill">LABORATORIO INMERSIVO</span>
    <h1 style="font-size:clamp(36px,6vw,64px);letter-spacing:.05em;margin:.3em 0">Diseñamos avatares que brillan en cualquier realidad</h1>
    <p style="color:var(--color-muted);max-width:760px">Tecnología XR, IA conversacional y WebXR para experiencias memorables en streaming, hologramas y VR.</p>
    <div style="display:flex;gap:12px;margin:18px 0 26px">
      <a class="app-actions cta" href="<?php echo esc_url( home_url('/contacto') ); ?>" style="display:inline-block">Agenda una demo</a>
      <a class="menu" href="<?php echo esc_url( home_url('/experiencia-inmersiva') ); ?>" style="display:inline-block;padding:10px 16px;border-radius:12px;border:1px solid var(--color-line);font-weight:700">Explora la experiencia 3D</a>
    </div>

    <!-- Banner 3D (model-viewer) -->
    <div class="anima-3d-hero card">
      <model-viewer
        src="https://assets.readyplayer.me/Example.glb"
        alt="Avatar Anima"
        camera-controls
        auto-rotate
        ar ar-modes="webxr scene-viewer quick-look"
        exposure="1.0" shadow-intensity="1"
        reveal="interaction" loading="lazy">
      </model-viewer>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <h2>Experiencias recientes</h2>
    <div class="grid cols-3">
      <?php for($i=61;$i<=63;$i++): ?>
        <article class="card">
          <img src="https://picsum.photos/1200/700?random=<?php echo $i;?>" alt="" style="width:100%;display:block">
          <div style="padding:16px">
            <h3 style="margin:.2em 0">Caso XR <?php echo $i-60;?></h3>
            <p style="color:var(--color-muted)">Evento interactivo con avatar IA, KPIs y WebXR.</p>
            <a href="<?php echo esc_url( home_url('/proyectos') ); ?>" style="display:inline-block;padding:10px 16px;border:1px solid var(--color-line);border-radius:12px;color:#efefef">Ver caso</a>
          </div>
        </article>
      <?php endfor; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <h2>Servicios</h2>
    <div class="grid cols-2">
      <?php
        $services = [
          ['Avatares 3D para streaming','Presentadores virtuales y platós en tiempo real.'],
          ['Asistentes con IA','Conversación natural, multicanal y conocimiento de marca.'],
          ['Avatares holográficos','Cabinas y escenarios volumétricos para eventos.'],
          ['Avatares VR / WebXR','Aulas multiusuario, showrooms 3D y métricas.']
        ];
        foreach($services as $s): ?>
          <div class="card" style="padding:24px"><h3><?php echo esc_html($s[0]);?></h3><p style="color:var(--color-muted)"><?php echo esc_html($s[1]);?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="background:linear-gradient(180deg,rgba(49,225,247,.04),transparent)">
  <div class="container">
    <h2>Anima Live</h2>
    <p style="color:var(--color-muted);max-width:720px">App en Unreal para crear avatares listos para streaming y VR.</p>
    <div style="display:flex;gap:12px;margin:18px 0">
      <a class="app-actions cta" href="<?php echo esc_url( home_url('/anima-live') ); ?>" style="display:inline-block">Ver más</a>
      <a class="menu" href="<?php echo esc_url( home_url('/contacto') ); ?>" style="display:inline-block;padding:10px 16px;border:1px solid var(--color-line);border-radius:12px;font-weight:700">Solicitar acceso</a>
    </div>
  </div>
</section>

<?php get_footer(); ?>
