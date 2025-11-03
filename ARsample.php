<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Shoe AR Try-On</title>

    <!-- A-Frame & MindAR Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/aframe@1.2.0/dist/aframe.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mind-ar@1.1.4/dist/aframe/mindar-aframe.prod.js"></script>

    <!-- Styles -->
    <style>
      body, html {
        margin: 0;
        overflow: hidden;
        width: 100%;
        height: 100%;
        background: black;
      }

      #loader {
        position: absolute;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.85);
        color: white;
        font-size: 1.5em;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 999;
      }
    </style>
  </head>
  <body>

    <!-- Loading Screen -->
    <div id="loader">🔄 Loading AR... Please wait</div>

    <!-- AR Scene -->
    <a-scene 
      mindar-face 
      embedded 
      color-space="sRGB" 
      renderer="colorManagement: true;" 
      vr-mode-ui="enabled: false" 
      device-orientation-permission-ui="enabled: true">

      <!-- Assets -->
      <a-assets>
        <a-asset-item id="shoeModel" src="assets/shoe.glb"></a-asset-item>
      </a-assets>

      <!-- Camera -->
      <a-camera position="0 0 0" active="true" look-controls="enabled: false"></a-camera>

      <!-- 3D Shoe Model Attached to Face (for demo) -->
      <a-entity mindar-face-target="anchorIndex: 10">
        <a-gltf-model 
          src="#shoeModel" 
          scale="0.05 0.05 0.05" 
          position="0 -0.3 0" 
          rotation="90 0 0">
        </a-gltf-model>
      </a-entity>

    </a-scene>

    <!-- JavaScript -->
    <script>
      // iOS camera & orientation permission (required on Safari iOS 13+)
      window.addEventListener('click', () => {
        if (
          typeof DeviceOrientationEvent !== 'undefined' &&
          typeof DeviceOrientationEvent.requestPermission === 'function'
        ) {
          DeviceOrientationEvent.requestPermission()
            .then(response => {
              if (response === 'granted') {
                console.log('Device orientation permission granted');
              }
            })
            .catch(console.error);
        }
      });

      // Hide loader when scene is fully loaded
      window.addEventListener('DOMContentLoaded', () => {
        const scene = document.querySelector('a-scene');
        scene.addEventListener('loaded', () => {
          document.getElementById('loader').style.display = 'none';
        });
      });
    </script>

  </body>
</html>
