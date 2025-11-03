<!DOCTYPE html>
<html>
  <head>
    <title>AR Shoe Try-On with Shoe Picker</title>
    <script src="https://cdn.jsdelivr.net/npm/three@0.151.3/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mind-ar@1.1.4/dist/mindar-image-three.prod.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.151.3/examples/js/loaders/GLTFLoader.js"></script>
    <style>
      body {
        margin: 0;
        overflow: hidden;
        font-family: Arial, sans-serif;
        background-color: #000;
        color: white;
        text-align: center;
        user-select: none;
        -webkit-user-select: none;
      }
      canvas {
        position: absolute;
        top: 0;
        left: 0;
      }
      #message {
        position: absolute;
        top: 1rem;
        left: 50%;
        transform: translateX(-50%);
        background-color: rgba(0,0,0,0.5);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 1.1rem;
        z-index: 10;
        pointer-events: none;
      }
      #shoe-picker {
        position: absolute;
        top: 60px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 20;
        background: rgba(0,0,0,0.7);
        padding: 10px;
        border-radius: 10px;
        display: flex;
        gap: 10px;
      }
      #shoe-picker button {
        background-color: #222;
        border: none;
        padding: 8px 15px;
        color: white;
        font-size: 1rem;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
      }
      #shoe-picker button:hover {
        background-color: #555;
      }
      #shoe-picker button:focus {
        outline: 2px solid #fff;
      }
    </style>
  </head>

  <body>
    <div id="message">📷 Point your camera at the Hiro marker. Use mouse wheel to scale the shoe. Drag to move.</div>

    <div id="shoe-picker">
      <button data-model="https://modelviewer.dev/shared-assets/models/shoe.glb">Shoe 1</button>
      <button data-model="https://cdn.jsdelivr.net/gh/KhronosGroup/glTF-Sample-Models/2.0/Shoe/glTF/Shoe.gltf">Shoe 2</button>
      <button data-model="https://cdn.jsdelivr.net/gh/KhronosGroup/glTF-Sample-Models/2.0/Avocado/glTF/Avocado.gltf">Avocado (fun)</button>
    </div>

    <script>
      document.addEventListener("DOMContentLoaded", async () => {
        const mindarThree = new window.MINDAR.IMAGE.MindARThree({
          container: document.body,
          imageTargetSrc: "https://cdn.jsdelivr.net/npm/mind-ar@1.1.4/examples/image-tracking/assets/card-example/card.mind",
          maxTrack: 1,
        });

        const { renderer, scene, camera } = mindarThree;
        const anchor = mindarThree.addAnchor(0);
        const loader = new THREE.GLTFLoader();

        let shoeModel = null;

        function disposeModel(model) {
          model.traverse((child) => {
            if (child.geometry) child.geometry.dispose();
            if (child.material) {
              if (Array.isArray(child.material)) {
                child.material.forEach(mat => mat.dispose());
              } else {
                child.material.dispose();
              }
            }
          });
        }

        function loadShoeModel(url) {
          if (shoeModel) {
            anchor.group.remove(shoeModel);
            disposeModel(shoeModel);
            shoeModel = null;
          }

          loader.load(url, (gltf) => {
            shoeModel = gltf.scene;
            shoeModel.scale.set(0.5, 0.5, 0.5);
            shoeModel.rotation.x = Math.PI / 2;
            shoeModel.position.set(0, 0, 0);
            anchor.group.add(shoeModel);
          }, undefined, (error) => {
            console.error("Error loading model:", error);
          });
        }

        // Load default shoe on start
        loadShoeModel("https://modelviewer.dev/shared-assets/models/shoe.glb");

        // Shoe picker buttons
        document.querySelectorAll("#shoe-picker button").forEach(button => {
          button.addEventListener("click", () => {
            const url = button.getAttribute("data-model");
            loadShoeModel(url);
          });
        });

        // Scaling with mouse wheel
        window.addEventListener("wheel", (event) => {
          if (!shoeModel) return;

          event.preventDefault();
          const scaleAmount = event.deltaY * -0.001;
          const newScale = shoeModel.scale.x + scaleAmount;
          if (newScale > 0.1 && newScale < 3) {
            shoeModel.scale.set(newScale, newScale, newScale);
          }
        }, { passive: false });

        // Drag to move shoe on the marker
        let isDragging = false;
        let previousMousePosition = { x: 0, y: 0 };

        window.addEventListener("mousedown", (event) => {
          isDragging = true;
          previousMousePosition.x = event.clientX;
          previousMousePosition.y = event.clientY;
        });

        window.addEventListener("mouseup", () => {
          isDragging = false;
        });

        window.addEventListener("mousemove", (event) => {
          if (isDragging && shoeModel) {
            const deltaMove = {
              x: event.clientX - previousMousePosition.x,
              y: event.clientY - previousMousePosition.y
            };

            shoeModel.position.x += deltaMove.x * 0.0015;
            shoeModel.position.y -= deltaMove.y * 0.0015;

            previousMousePosition.x = event.clientX;
            previousMousePosition.y = event.clientY;
          }
        });

        await mindarThree.start();
        renderer.setAnimationLoop(() => {
          renderer.render(scene, camera);
        });
      });
    </script>
  </body>
</html>
