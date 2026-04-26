<?php /** @var GSMSDK\Core\Application $app */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>GSMSDK Framework</title>
<style>
body{font-family:sans-serif;background:#0a0a0f;color:#e2e8f0;margin:0;padding:2rem}
h1{font-size:3rem;margin:0 0 1rem;background:linear-gradient(135deg,#6366f1,#818cf8);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1rem;margin:2rem 0}
.card{background:#1a1a2a;border:1px solid #2a2a40;border-radius:8px;padding:1.5rem}
.card h3{margin:0 0 .5rem;color:#fff}
.card p{margin:0;color:#64748b;font-size:.9rem}
</style>
</head>
<body>
<h1>GSMSDK</h1>
<p>Full-Stack PHP Framework for Desktop & Mobile Apps</p>
<div class="grid">
<div class="card"><h3>💻 Desktop</h3><p>Electron-based desktop applications</p></div>
<div class="card"><h3>📱 Mobile</h3><p>Android & iOS native apps</p></div>
<div class="card"><h3>🌐 API</h3><p>RESTful web services</p></div>
<div class="card"><h3>🗄️ DB</h3><p>Fluent query builder</p></div>
</div>
<p>Version: <?= $app->version() ?> | Env: <?= ucfirst($app->environment()) ?></p>
</body>
</html>
