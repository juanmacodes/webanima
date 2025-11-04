(function () {
    'use strict';

    if (typeof THREE === 'undefined') {
        return;
    }

    const container = document.getElementById('anima-loft');
    if (!container) {
        return;
    }

    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x05070d);

    const camera = new THREE.PerspectiveCamera(50, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.set(0, 1.2, 4);

    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.innerHTML = '';
    container.appendChild(renderer.domElement);

    const light = new THREE.PointLight(0x7df9ff, 2, 20);
    light.position.set(2, 3, 4);
    scene.add(light);

    const ambient = new THREE.AmbientLight(0x20283b, 1.5);
    scene.add(ambient);

    const geometry = new THREE.TorusKnotGeometry(1, 0.35, 100, 16);
    const material = new THREE.MeshStandardMaterial({
        color: 0x8b5cf6,
        emissive: 0x121827,
        metalness: 0.6,
        roughness: 0.2,
    });
    const knot = new THREE.Mesh(geometry, material);
    scene.add(knot);

    const floorGeometry = new THREE.CircleGeometry(3.5, 64);
    const floorMaterial = new THREE.MeshStandardMaterial({ color: 0x0b0f17, roughness: 0.8 });
    const floor = new THREE.Mesh(floorGeometry, floorMaterial);
    floor.rotation.x = -Math.PI / 2;
    floor.position.y = -1.2;
    scene.add(floor);

    const animate = () => {
        requestAnimationFrame(animate);
        knot.rotation.y += 0.01;
        knot.rotation.x += 0.005;
        renderer.render(scene, camera);
    };

    animate();

    window.addEventListener('resize', () => {
        const { clientWidth, clientHeight } = container;
        camera.aspect = clientWidth / clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(clientWidth, clientHeight);
    });
})();
