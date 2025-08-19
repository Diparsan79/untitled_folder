// Global theme toggle (iOS-style) for Shiksha Mitra
document.addEventListener('DOMContentLoaded', () => {
  const root = document.documentElement;
  const saved = localStorage.getItem('theme');
  if (saved === 'dark') {
    root.setAttribute('data-theme', 'dark');
    root.classList.add('dark');
  }

  // Build iOS-style switch
  const wrapper = document.createElement('div');
  wrapper.className = 'theme-switch';
  wrapper.style.position = 'fixed';
  wrapper.style.right = '16px';
  wrapper.style.bottom = '16px';
  wrapper.style.zIndex = '9999';

  wrapper.innerHTML = `
    <label class="ios-switch" title="Toggle dark mode">
      <input type="checkbox" aria-label="Toggle dark mode">
      <span class="slider"></span>
    </label>
  `;

  document.body.appendChild(wrapper);

  const checkbox = wrapper.querySelector('input');
  checkbox.checked = root.getAttribute('data-theme') === 'dark';

  checkbox.addEventListener('change', () => {
    const dark = checkbox.checked;
    if (dark) {
      root.setAttribute('data-theme', 'dark');
      root.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    } else {
      root.removeAttribute('data-theme');
      root.classList.remove('dark');
      localStorage.setItem('theme', 'light');
    }
  });
});


