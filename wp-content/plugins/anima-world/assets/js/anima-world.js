(function () {
  function hasWebGL() {
    try {
      const canvas = document.createElement('canvas');
      return !!window.WebGLRenderingContext && !!(canvas.getContext('webgl') || canvas.getContext('experimental-webgl'));
    } catch (e) {
      return false;
    }
  }

  function init(sceneContainer) {
    const canvas = sceneContainer.querySelector('canvas[data-anima-world]');
    if (!canvas) {
      return;
    }

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setSize(sceneContainer.clientWidth, sceneContainer.clientHeight);

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(60, sceneContainer.clientWidth / sceneContainer.clientHeight, 0.1, 100);
    camera.position.set(2.4, 1.8, 3.2);

    const ambient = new THREE.AmbientLight(0x7df9ff, 0.45);
    const directional = new THREE.DirectionalLight(0xffffff, 0.8);
    directional.position.set(4, 3, 5);
    scene.add(ambient, directional);

    const controls = new THREE.OrbitControls(camera, canvas);
    controls.enableDamping = true;
    controls.maxDistance = 12;

    const pmrem = new THREE.PMREMGenerator(renderer);
    scene.environment = pmrem.fromScene(new THREE.Scene()).texture;

    const loader = new THREE.GLTFLoader();

    if (THREE.DRACOLoader) {
      const dracoLoader = new THREE.DRACOLoader();
      dracoLoader.setDecoderPath('https://cdn.jsdelivr.net/npm/three@0.164/examples/jsm/libs/draco/');
      loader.setDRACOLoader(dracoLoader);
    }

    if (THREE.MeshoptDecoder) {
      loader.setMeshoptDecoder(THREE.MeshoptDecoder);
    }

    if (THREE.KTX2Loader) {
      const ktx2Loader = new THREE.KTX2Loader();
      ktx2Loader.setTranscoderPath('https://cdn.jsdelivr.net/npm/three@0.164/examples/jsm/libs/basis/');
      ktx2Loader.detectSupport(renderer);
      loader.setKTX2Loader(ktx2Loader);
    }

    loader.load(
      animaWorld.gltf,
      function (gltf) {
        gltf.scene.traverse(function (child) {
          if (child.isMesh) {
            child.castShadow = true;
            child.receiveShadow = true;
          }
        });
        scene.add(gltf.scene);
      },
      undefined,
      function () {
        console.warn('No se pudo cargar la escena GLTF');
      }
    );

    const hotspotContainer = sceneContainer.querySelector('.anima-world__hotspots');

    function renderHotspots() {
      hotspotContainer.innerHTML = '';
      animaWorld.hotspots.forEach(function (hotspot) {
        const button = document.createElement('button');
        button.className = 'anima-world__hotspot';
        button.type = 'button';
        button.setAttribute('data-target', hotspot.id);
        button.innerHTML = '<span></span><strong>' + hotspot.label + '</strong>';
        const position = hotspot.position || {};
        if (position.top) {
          button.style.top = position.top;
        }
        if (position.left) {
          button.style.left = position.left;
        }
        button.addEventListener('click', function () {
          window.dispatchEvent(new CustomEvent('anima-world:hotspot', { detail: hotspot }));
          window.open(hotspot.url, '_self');
        });
        hotspotContainer.appendChild(button);
      });
    }

    renderHotspots();

    let lowSpecMode = false;
    let rafId = null;

    function tick() {
      controls.update();
      renderer.render(scene, camera);
      if (!lowSpecMode) {
        rafId = requestAnimationFrame(tick);
      }
    }

    function startLoop() {
      cancelAnimationFrame(rafId);
      lowSpecMode = false;
      rafId = requestAnimationFrame(tick);
    }

    function stopLoop() {
      lowSpecMode = true;
      cancelAnimationFrame(rafId);
    }

    startLoop();

    const observer = new ResizeObserver(function (entries) {
      for (const entry of entries) {
        const { width, height } = entry.contentRect;
        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height, false);
      }
    });

    observer.observe(sceneContainer);

    document.addEventListener('visibilitychange', function () {
      if (document.hidden) {
        cancelAnimationFrame(rafId);
      } else if (!lowSpecMode) {
        startLoop();
      }
    });

    const lowSpecButton = sceneContainer.parentElement.querySelector('[data-low-spec]');
    if (lowSpecButton) {
      lowSpecButton.addEventListener('click', function () {
        if (lowSpecMode) {
          startLoop();
          lowSpecButton.classList.remove('is-active');
          lowSpecButton.textContent = lowSpecButton.dataset.lowSpecLabel || 'Modo low-spec';
        } else {
          stopLoop();
          lowSpecButton.classList.add('is-active');
          lowSpecButton.textContent = lowSpecButton.dataset.perfLabel || 'Modo performance';
        }
      });
    }

    sceneContainer.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowRight') {
        controls.rotateLeft(-0.1);
      } else if (event.key === 'ArrowLeft') {
        controls.rotateLeft(0.1);
      } else if (event.key === 'Home') {
        controls.reset();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.anima-world__canvas').forEach(function (container) {
      if (hasWebGL()) {
        init(container);
      } else {
        container.querySelector('.anima-world__fallback').hidden = false;
      }
    });
  });
})();
