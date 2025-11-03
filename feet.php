<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AR Shoe Try-On via Vossle</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    html, body {
      height: 100%;
      margin: 0;
      font-family: sans-serif;
      background: #f4f4f4;
      text-align: center;
    }

    iframe {
      width: 100%;
      height: 95vh;
      border: none;
    }

    h1 {
      padding: 10px;
      background: #333;
      color: white;
    }
  </style>
</head>
<body>

  <h1>Try On Shoes with Vossle AR</h1>

  <!-- Replace the link below with your actual experience link from Vossle -->
  <iframe 
    src="https://webxr.vossle.ai/ar/index.html?experienceID=YOUR_REAL_ID"
    allow="camera; gyroscope; accelerometer; fullscreen; xr-spatial-tracking"
    loading="eager"
  ></iframe>

</body>
</html>
