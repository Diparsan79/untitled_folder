<?php if (!headers_sent()) { header('X-Content-Type-Options: nosniff'); } ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Community Voice</title>
  <link rel="stylesheet" href="/untitled_folder/public/css/app.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    (function(){
      try {
        var t = localStorage.getItem('theme');
        if(!t && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches){ t='dark'; }
        if(t==='dark'){ document.documentElement.classList.add('dark'); }
      } catch(e){}
    })();
  </script>
</head>
<body>
  <header class="site-header">
    <div style="display:flex;align-items:center;gap:8px;">
      <button id="sidebarToggle" class="burger" aria-label="Toggle sidebar" aria-controls="primary-sidebar" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
      <h1 class="site-title">Community Voice</h1>
    </div>
    <div class="site-actions">
      <label class="toggle" title="Toggle dark mode">
        <input id="themeToggleCheckbox" type="checkbox" aria-pressed="false" aria-label="Toggle dark mode" />
        <span class="slider"></span>
      </label>
    </div>
  </header>
  <script defer src="/untitled_folder/public/js/theme.js"></script>


