(function(){
  var btn = document.getElementById('themeToggle');
  function setTheme(isDark){
    document.documentElement.classList.toggle('dark', !!isDark);
    try{ localStorage.setItem('theme', isDark ? 'dark' : 'light'); }catch(e){}
    if(btn){ btn.setAttribute('aria-pressed', String(!!isDark)); }
  }
  var stored = null; try{ stored = localStorage.getItem('theme'); }catch(e){}
  var isDark = (stored ? stored === 'dark' : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches));
  if(isDark){ document.documentElement.classList.add('dark'); }
  if(btn){ setTheme(isDark); btn.addEventListener('click', function(){ setTheme(!document.documentElement.classList.contains('dark')); }); }

  var burger = document.getElementById('sidebarToggle');
  var sidebar = document.querySelector('aside.sidebar');
  if(burger && sidebar){ burger.addEventListener('click', function(){ sidebar.classList.toggle('open'); }); }
})();


