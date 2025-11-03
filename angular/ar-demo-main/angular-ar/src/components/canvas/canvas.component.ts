declare var HandTrackerThreeHelper: any;
declare var PoseFlipFilter: any;
declare var THREE: any;
declare var WEBARROCKSHAND: any;

import { Component, Input, OnDestroy } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Component({
  selector: 'app-canvas',
  templateUrl: './canvas.component.html',
  styleUrls: ['./canvas.component.scss']
})
export class CanvasComponent implements OnDestroy {
  private three: any;
  private _isMirroredMode = false;
  private _loadedShoe: any = null;
  private resizeListener: any;

  private updateShoeTransform!: () => void;
  private prevFootPos: any = null; // ✅ for smoothing

  @Input() threshold!: number;
  @Input() shoeRightPath!: BehaviorSubject<string>;
  @Input() modeName!: string;
  @Input() scale!: number;
  @Input() translation!: number[];
  @Input() debugCube!: boolean;
  @Input() scanSettings!: any;
  @Input() NNsPaths!: string[];

  private _settings: any;
  private _state = -1; // notLoaded

  constructor() {}

  ngOnInit() {
    this._settings = {
      threshold: this.threshold,
      shoeRightPath: null,
      occluderPath: 'assets/3d-models/occluder.glb',
      scale: this.scale,
      translation: this.translation,
      debugCube: this.debugCube,
      debugDisplayLandmarks: true,
      isModelLightMapped: true
    };

    this.shoeRightPath.subscribe((s) => {
      this._settings.shoeRightPath = s;
      if (this._state !== -1) {
        this.loadShoe(s);
        this.start(this.three);
      }
    });

    this.main();

    // ✅ Responsive canvas
    this.resizeListener = () => {
      const handTrackerCanvas = document.getElementById('handTrackerCanvas');
      const VTOCanvas = document.getElementById('ARCanvas');
      this.setFullScreen(handTrackerCanvas);
      this.setFullScreen(VTOCanvas);
      if (this.three) this.three.renderer.setSize(window.innerWidth, window.innerHeight);
    };
    window.addEventListener('resize', this.resizeListener);
  }

  ngOnDestroy() {
    window.removeEventListener('resize', this.resizeListener);
  }

  setFullScreen(cv: any) {
    if (!cv) return;
    cv.width = window.innerWidth;
    cv.height = window.innerHeight;
  }

  main() {
    this._state = 0; // loading
    const handTrackerCanvas = document.getElementById('handTrackerCanvas');
    const VTOCanvas = document.getElementById('ARCanvas');

    this.setFullScreen(handTrackerCanvas);
    this.setFullScreen(VTOCanvas);

    const initParams: any = {
      poseLandmarksLabels: [
        'ankleBack', 'ankleOut', 'ankleIn', 'ankleFront',
        'heelBackOut', 'heelBackIn',
        'pinkyToeBaseTop', 'middleToeBaseTop', 'bigToeBaseTop'
      ],
      enableFlipObject: true,
      cameraZoom: 1,
      freeZRot: false,
      threshold: this._settings.threshold,
      scanSettings: this.scanSettings,
      VTOCanvas: VTOCanvas,
      handTrackerCanvas: handTrackerCanvas,
      debugDisplayLandmarks: this._settings.debugDisplayLandmarks,
      NNsPaths: this.NNsPaths,
      maxHandsDetected: 2,
    };

    if (this.modeName === 'shoes-on-vto') {
      initParams.landmarksStabilizerSpec = { minCutOff: 0.001, beta: 5 };
      initParams.enableFlipObject = this._isMirroredMode;
    } else if (this.modeName === 'barefoot-vto') {
      initParams.poseFilter = PoseFlipFilter.instance({});
    }

    HandTrackerThreeHelper.init(initParams).then((three: any) => {
      this.three = three;
      if (this._settings.shoeRightPath) this.loadShoe(this._settings.shoeRightPath);
      this.start(this.three);
    }).catch((err: any) => {
      console.error('Error initializing HandTracker:', err);
    });
  }

  start(three: any) {
    if (!three) return;
    HandTrackerThreeHelper.clear_threeObjects(true);

    three.renderer.toneMapping = THREE.ACESFilmicToneMapping;
    three.renderer.outputEncoding = THREE.sRGBEncoding;

    const pointLight = new THREE.PointLight(0xffffff, 2);
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
    three.scene.add(pointLight, ambientLight);

    // ✅ Mirror support
    three.camera.scale.x = this._isMirroredMode ? -1 : 1;

    if (this._settings.debugCube) {
      const cube = new THREE.Mesh(
        new THREE.BoxGeometry(1, 1, 1),
        new THREE.MeshNormalMaterial()
      );
      HandTrackerThreeHelper.add_threeObject(cube);
    }

    if (this._loadedShoe) this.updateShoeTransform();

    this._state = 1; // running
  }

  // ✅ FIXED: Stable shoe transform (anti-floating)
  loadShoe(path: string) {
    if (!path) return;
    console.log('Loading shoe:', path);

    const self = this;
    const transform = (obj: any) => {
      let scaleValue = self._settings.scale;
      let translationVec = new THREE.Vector3().fromArray(self._settings.translation);

      if (self._isMirroredMode) {
        scaleValue *= 1.1;
        translationVec.x *= -1;
        translationVec.z += 0.3;
      }

      try {
        const lm = HandTrackerThreeHelper.get_Landmarks();
        if (lm && lm.ankleFront && lm.heelBackIn) {
          const ankle = new THREE.Vector3().fromArray(lm.ankleFront);
          const heel = new THREE.Vector3().fromArray(lm.heelBackIn);

          // ✅ Average foot center (more stable)
          const footCenter = new THREE.Vector3().addVectors(ankle, heel).multiplyScalar(0.5);

          // ✅ Direction from heel → ankle
          const dir = new THREE.Vector3().subVectors(ankle, heel).normalize();

          // ✅ Lock Y height to reduce floating
          const minY = Math.min(ankle.y, heel.y);
          footCenter.y = minY - 0.02;

          // ✅ Smooth motion (anti-jitter)
          if (!self.prevFootPos) self.prevFootPos = footCenter.clone();
          self.prevFootPos.lerp(footCenter, 0.25); // smoothing factor

          // ✅ Rotation alignment
          const forward = new THREE.Vector3(0, 0, 1);
          const quat = new THREE.Quaternion().setFromUnitVectors(forward, dir);
          obj.quaternion.copy(quat);
          obj.rotateX(-Math.PI / 2);

          // ✅ Apply stabilized position
          obj.position.copy(self.prevFootPos);

          // ✅ Scale dynamically based on real foot length
          const length = ankle.distanceTo(heel);
          if (length > 0.01) scaleValue *= (length * 9);
        }
      } catch (e) {
        console.warn('Transform error:', e);
      }

      obj.scale.multiplyScalar(scaleValue);
      obj.position.add(translationVec);
    };

    if (self._loadedShoe) {
      HandTrackerThreeHelper.clear_threeObjects(true);
      self._loadedShoe = null;
    }

    new THREE.GLTFLoader().load(
      path,
      (gltf: any) => {
        self._loadedShoe = gltf.scene;

        self._loadedShoe.traverse((child: any) => {
          if (child.isMesh) {
            child.castShadow = true;
            child.receiveShadow = true;
            if (child.material.map) child.material.map.encoding = THREE.sRGBEncoding;
          }
        });

        self.updateShoeTransform = () => {
          if (!self._loadedShoe) return;
          const shoeClone = self._loadedShoe.clone(true);
          transform(shoeClone);
          HandTrackerThreeHelper.add_threeObject(shoeClone);
        };

        self.updateShoeTransform();
        console.log('✅ Shoe fully loaded with stable foot tracking:', path);
      },
      undefined,
      (err: any) => console.error('❌ Shoe load error:', path, err)
    );

    // ✅ Load occluder once
    new THREE.GLTFLoader().load(this._settings.occluderPath, (gltf: any) => {
      const occ = gltf.scene.children[0];
      transform(occ);
      HandTrackerThreeHelper.add_threeOccluder(occ);
    });
  }

  flip_camera() {
    if (this._state !== 1) return;
    this._state = 2; // busy

    WEBARROCKSHAND.update_videoSettings({
      facingMode: this._isMirroredMode ? 'environment' : 'user'
    }).then(() => {
      this._isMirroredMode = !this._isMirroredMode;
      this._state = 1;

      const canvases = document.getElementById('canvases')!;
      canvases.style.transition = 'transform 0.3s';
      canvases.style.transform = this._isMirroredMode ? 'rotateY(180deg)' : '';

      this.start(this.three);
      console.log('Camera flipped → Mirror mode:', this._isMirroredMode);
    }).catch((err: any) => {
      console.error('Flip camera error:', err);
    });
  }
}
