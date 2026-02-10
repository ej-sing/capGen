// capGen.js – Engine Core (Safe DOM)

async function capGenInit() {
  const status = document.getElementById('status');
  const canvas = document.getElementById('canvas');

  if (!status || !canvas) {
    console.error('capGen: DOM not ready', { status, canvas });
    return;
  }

  status.textContent = 'Engine loaded from Git ✔';

  canvas.innerHTML = `
    <strong>capGen Engine</strong><br>
    Status: OK<br>
    Time: ${new Date().toLocaleTimeString()}
  `;
}

// 🔒 DOM-safe init
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', capGenInit);
} else {
  capGenInit();
}
