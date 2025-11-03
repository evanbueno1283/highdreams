<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fit Scan - Shoe Sizing</title>
  <style>
    * {
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      font-family: 'Arial', sans-serif;
      background-color: #f8f9fa;
      text-align: center;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 30px;
      background-color: #000000;
      color: white;
      height: 80px;
      width: 100%;
       position: fixed;
  top: 0;
  width: 100%;
  z-index: 1000;
    }

    .logo-container {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .logo img,
    .second-logo img {
      height: 50px;
      box-shadow: 0 2px 10px rgb(255, 255, 255);
    }

    .container {
      width: 100%;
      min-height: 100vh;
      padding: 20px;
      background: #fff;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    h2 {
      font-size: 26px;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .button {
      padding: 10px 15px;
      margin: 10px;
      border: none;
      cursor: pointer;
      border-radius: 8px;
      font-size: 16px;
      background: #000;
      color: white;
    }

    .chat-box {
      margin-top: 20px;
      text-align: left;
      max-height: 300px;
      overflow-y: auto;
      width: 100%;
    }

    .chat-bubble {
      padding: 10px;
      border-radius: 10px;
      max-width: 80%;
      margin: 5px auto;
    }

    .ai-bubble {
      background-color: #e9ecef;
      color: black;
      text-align: left;
    }

    .hidden {
      display: none;
    }

    video, canvas {
      width: 100%;
      max-width: 300px;
      border-radius: 12px;
      margin-top: 10px;
      border: 4px dashed #000;
    }

    #foot-width-result {
      margin-top: 15px;
      font-size: 18px;
      font-weight: bold;
    }

  </style>
</head>
<body>


<header class="header">
  <div class="logo-container">
    <div class="logo">
      <a href="home.php">
        <img src="image/logo1.png" alt="Shoe Store Logo">
      </a>
    </div>
    <div class="second-logo">
      <img src="image/hdb2.png" alt="Second Logo">
    </div>
  </div>

  <div class="user-options">
    <a href="home.php" class="home-button" title="Home">
      <img src="image/home.png" alt="Home Icon">
    </a>
  </div>
</header>

<style>
/* Layout container for user options */
.user-options {
  display: flex;
  align-items: center;
  gap: 20px;
}

/* Home button styling */
.home-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px;
  border-radius: 20px;
  text-decoration: none;
  transition: background-color 0.3s ease;
}

.home-button:hover {
  background-color: #eeeeee;
}

/* Home icon image styling */
.home-button img {
  width: 24px;
  height: 24px;
  display: block;
}

/* Main container */
.container {
  text-align: center;
  margin-top: 40px;
}

/* Buttons */
.button {
  padding: 10px 20px;
  font-size: 16px;
  background-color: black;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.button:hover {
  background-color: #333;
}

/* Hidden elements */
.hidden {
  display: none;
}

/* Chat Box */
.chat-box {
  margin-top: 30px;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
  text-align: left;
}

/* Chat bubbles */
.chat-bubble {
  margin: 10px 0;
  padding: 10px 15px;
  border-radius: 10px;
  font-size: 15px;
  line-height: 1.5;
}

.ai-bubble {
  background-color: #f3f3f3;
  color: black;
}

.user-bubble {
  background-color: #007bff;
  color: white;
  text-align: right;
}
</style>

<div class="container">
  <h2>Fit Scan - Shoe Sizing</h2>
  <p>Take a photo of your foot to check if it fits a standard shoe size.</p>
  <button id="scan-btn" class="button">Scan My Foot</button>

  <video id="camera-preview" autoplay class="hidden"></video>
  <button id="capture-btn" class="button hidden">Capture</button>
  <canvas id="camera-canvas" class="hidden"></canvas>

  <div id="chat-box" class="chat-box"></div>
  <div id="foot-width-result"></div>
</div>

<script>
const scanBtn = document.getElementById("scan-btn");
const cameraPreview = document.getElementById("camera-preview");
const cameraCanvas = document.getElementById("camera-canvas");
const captureBtn = document.getElementById("capture-btn");
const chatBox = document.getElementById("chat-box");
const footWidthResult = document.getElementById("foot-width-result");

// Clear results each time a new scan starts
function clearPreviousResults() {
  chatBox.innerHTML = "";
  footWidthResult.textContent = "";
  const ctx = cameraCanvas.getContext("2d");
  ctx.clearRect(0, 0, cameraCanvas.width, cameraCanvas.height);
  cameraCanvas.classList.add("hidden");
}

scanBtn.addEventListener("click", async () => {
  clearPreviousResults();
  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } });
    cameraPreview.srcObject = stream;
    cameraPreview.classList.remove("hidden");
    captureBtn.classList.remove("hidden");
  } catch (error) {
    alert("Camera error: " + error.message);
  }
});

captureBtn.addEventListener("click", async () => {
  const context = cameraCanvas.getContext("2d");
  cameraCanvas.width = cameraPreview.videoWidth;
  cameraCanvas.height = cameraPreview.videoHeight;

  // Draw the video frame
  context.drawImage(cameraPreview, 0, 0, cameraCanvas.width, cameraCanvas.height);

  // Stop camera stream
  cameraPreview.srcObject.getTracks().forEach(track => track.stop());
  cameraPreview.classList.add("hidden");
  captureBtn.classList.add("hidden");

  const base64Image = cameraCanvas.toDataURL("image/jpeg").split(',')[1];
  const detectionResult = await detectFootOrShoe(base64Image);

  if (detectionResult === "none") {
    addMessage("⚠️ No foot detected in the image. Please try again.", "ai");
    return;
  }

  if (detectionResult === "foot" || detectionResult === "shoe") {
    processImage(base64Image);
  }
});

async function detectFootOrShoe(base64Image) {
  try {
   const response = await fetch("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=AIzaSyAm1ySnJnZbMkKPSvv6MFSyXKMRdapjJag", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        contents: [
          {
            parts: [
              { text: "Does this image contain a human foot, a shoe, or neither? Reply only with one word: foot, shoe, or none." },
              {
                inlineData: { mimeType: "image/jpeg", data: base64Image }
              }
            ]
          }
        ]
      })
    });

    const data = await response.json();
    const reply = data.candidates?.[0]?.content?.parts?.[0]?.text.trim().toLowerCase() || "none";
    if (reply.includes("foot")) return "foot";
    if (reply.includes("shoe")) return "shoe";
    return "none";
  } catch (error) {
    console.error("Detection failed:", error);
    return "none";
  }
}

async function processImage(base64Image) {
  const prompt = `
Analyze the following image of a human foot and determine:
1. The approximate foot length in centimeters (cm).
2. The estimated US shoe size (specify if it's Male or Female), EU size, and CM size.
3. Whether the foot is bulky/wide, slim/narrow, or regular width.
4. Shoe fit recommendations.

Provide the result in this format:
<b>Fit Scan Result</b><br>
Foot Length: [cm]<br>
Estimated Size: [sizes]<br>
Foot Width Type: [Slim / Regular / Bulky]
`;

  try {
   const response = await fetch("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=AIzaSyAm1ySnJnZbMkKPSvv6MFSyXKMRdapjJag", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        contents: [
          {
            parts: [
              { text: prompt },
              { inlineData: { mimeType: "image/jpeg", data: base64Image } }
            ]
          }
        ]
      })
    });

    const data = await response.json();
    const aiReply = data.candidates?.[0]?.content?.parts?.[0]?.text || "⚠️ No response from AI.";
    addMessage(aiReply, "ai");

    const widthMatch = aiReply.match(/Foot Width Type:\s*(Slim|Regular|Bulky)/i);
    if (widthMatch) {
      const width = widthMatch[1];
      footWidthResult.textContent = `👣 Foot Width Detected: ${width}`;
      footWidthResult.style.color = width === "Bulky" ? "red" : width === "Slim" ? "blue" : "green";
    }

  } catch (error) {
    addMessage("⚠️ Error: " + error.message, "ai");
  }
}

function addMessage(text, type) {
  const bubble = document.createElement("div");
  bubble.classList.add("chat-bubble", type === "user" ? "user-bubble" : "ai-bubble");
  bubble.innerHTML = text;
  chatBox.appendChild(bubble);
  chatBox.scrollTop = chatBox.scrollHeight;
}
</script>


</body>
</html>
