// capGen.js – Engine Core (DOM SAFE)

function capGenInit() {
  const status = document.getElementById('status');
  const canvas = document.getElementById('canvas');

  console.log('capGen init', { status, canvas });

  if (!status || !canvas) {
    console.error('capGen: missing DOM element');
    return;
  }

  status.textContent = 'Engine loaded from Git ✔';

  canvas.innerHTML = `
    <strong>capGen Engine</strong><br>
    Status: OK<br>
    Loaded at: ${new Date().toLocaleTimeString()}
  `;
}

// DOM-safe bootstrap
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', capGenInit);
} else {
  capGenInit();
}
