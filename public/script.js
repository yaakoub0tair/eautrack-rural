let scene, camera, renderer;
let drop, splash, heart;
let mouseX = 0, mouseY = 0;

const container = document.getElementById("three-container");

// Wait for libraries to load
function waitForLibraries() {
  console.log('Checking libraries...');
  console.log('THREE:', typeof THREE);
  console.log('GLTFLoader:', typeof THREE?.GLTFLoader);
  console.log('GSAP:', typeof gsap);
  
  if (typeof THREE !== 'undefined' && typeof THREE.GLTFLoader !== 'undefined' && typeof gsap !== 'undefined') {
    console.log('All libraries loaded! Starting initialization...');
    init();
    animate();
  } else {
    console.log('Waiting for libraries to load...');
    setTimeout(waitForLibraries, 500);
  }
}

waitForLibraries();

/* ================= INIT ================= */
function init() {
  scene = new THREE.Scene();

  camera = new THREE.PerspectiveCamera(
    60,
    window.innerWidth / window.innerHeight,
    0.1,
    100
  );
  camera.position.set(0, 1.5, 5);

  renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setPixelRatio(window.devicePixelRatio);
  container.appendChild(renderer.domElement);

  // Lights
  scene.add(new THREE.AmbientLight(0xffffff, 0.4));

  const dir = new THREE.DirectionalLight(0x00d4ff, 1.2);
  dir.position.set(5, 10, 5);
  scene.add(dir);

  loadModels();
  window.addEventListener("resize", onResize);
  window.addEventListener("mousemove", onMouseMove);
}

/* ================= LOAD MODELS ================= */
function loadModels() {
  console.log('Loading models...');
  const loader = new THREE.GLTFLoader();

  loader.load("models/water.glb", g => {
    console.log('Water drop loaded successfully!');
    drop = g.scene;
    prepareModel(drop);
    drop.position.y = 4;
    drop.scale.set(0.3,0.3,0.3);
    scene.add(drop);

    loader.load("models/water2.glb", g2 => {
      console.log('Water splash loaded successfully!');
      splash = g2.scene;
      prepareModel(splash);
      splash.visible = false;
      scene.add(splash);

      loader.load("models/water 3.glb", g3 => {
        console.log('Water heart loaded successfully!');
        heart = g3.scene;
        prepareModel(heart);
        heart.visible = false;
        scene.add(heart);

        console.log('All models loaded! Starting story...');
        startStory();
      }, undefined, (error) => {
        console.error('Error loading water 3.glb:', error);
      });
    }, undefined, (error) => {
      console.error('Error loading water2.glb:', error);
    });
  }, undefined, (error) => {
    console.error('Error loading water.glb:', error);
  });
}

function prepareModel(model) {
  model.traverse(m => {
    if (m.isMesh) {
      m.material.transparent = true;
      m.material.opacity = 1;
    }
  });
}

/* ================= STORY ================= */
function startStory() {
  gsap.to(drop.position, {
    y: 0,
    duration: 2.5,
    ease: "power2.in",
    onUpdate: () => {
      drop.rotation.x = Math.sin(Date.now()*0.005)*0.1;
    },
    onComplete: impactPhase
  });
}

function impactPhase() {
  splash.visible = true;
  splash.scale.set(0.1,0.1,0.1);

  gsap.to(drop.scale, { x:2,y:0.2,z:2, duration:0.6 });
  fade(drop, 0, 1);

  gsap.to(splash.scale, {
    x:2,y:0.3,z:2,
    duration:1.5,
    ease:"power3.out",
    onComplete: heartPhase
  });
}

function heartPhase() {
  heart.visible = true;
  heart.scale.set(0.01,0.01,0.01);
  fade(splash,0,1.5);

  gsap.to(heart.scale,{
    x:1.2,y:1.2,z:1.2,
    duration:2,
    ease:"elastic.out(1,0.4)"
  });
}

/* ================= HELPERS ================= */
function fade(model,target,duration){
  model.traverse(m=>{
    if(m.isMesh){
      gsap.to(m.material,{
        opacity:target,
        duration,
        ease:"power2.inOut",
        onComplete:()=>{ if(target===0) model.visible=false; }
      });
    }
  });
}

function onMouseMove(e){
  mouseX = (e.clientX / window.innerWidth - 0.5) * 0.6;
  mouseY = (e.clientY / window.innerHeight - 0.5) * 0.6;
}

function animate() {
  requestAnimationFrame(animate);

  if(heart && heart.visible){
    heart.rotation.y += (mouseX - heart.rotation.y)*0.05;
    heart.rotation.x += (mouseY - heart.rotation.x)*0.05;
    heart.position.y = 0.2 + Math.sin(Date.now()*0.002)*0.15;
  }

  renderer.render(scene,camera);
}

function onResize(){
  camera.aspect = window.innerWidth/window.innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth,window.innerHeight);
}

/* ================= CHARTS ================= */
// Initialize Chart.js analytics
function initCharts() {
    console.log('Initializing charts...');
    
    // Daily Usage Line Chart
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Water Usage (Liters)',
                data: [245, 289, 267, 301, 278, 312, 295],
                borderColor: '#00d4ff',
                backgroundColor: 'rgba(0, 212, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#00d4ff',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.8)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.8)'
                    }
                }
            }
        }
    });
    
    // Monthly Comparison Bar Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Water Usage (m³)',
                data: [8.2, 7.8, 9.1, 8.5, 7.9, 8.7],
                backgroundColor: [
                    'rgba(0, 212, 255, 0.8)',
                    'rgba(0, 168, 204, 0.8)',
                    'rgba(0, 119, 190, 0.8)',
                    'rgba(0, 212, 255, 0.8)',
                    'rgba(0, 168, 204, 0.8)',
                    'rgba(0, 119, 190, 0.8)'
                ],
                borderColor: '#00d4ff',
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.8)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.8)'
                    }
                }
            }
        }
    });
    
    // Usage Distribution Pie Chart
    const distributionCtx = document.getElementById('distributionChart').getContext('2d');
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Kitchen', 'Bathroom', 'Garden', 'Laundry', 'Other'],
            datasets: [{
                data: [35, 28, 20, 12, 5],
                backgroundColor: [
                    'rgba(0, 212, 255, 0.8)',
                    'rgba(0, 168, 204, 0.8)',
                    'rgba(0, 119, 190, 0.8)',
                    'rgba(102, 126, 234, 0.8)',
                    'rgba(0, 61, 91, 0.8)'
                ],
                borderColor: '#ffffff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: 'rgba(255, 255, 255, 0.8)',
                        padding: 20
                    }
                }
            }
        }
    });
}

// Initialize charts when analytics section is visible
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            initCharts();
            observer.disconnect();
        }
    });
});

const analyticsSection = document.getElementById('analytics');
if (analyticsSection) {
    observer.observe(analyticsSection);
}
