// capGen.js – Engine Core

document.addEventListener('DOMContentLoaded', () => {
  const status = document.getElementById('status');
  const canvas = document.getElementById('canvas');

  if (!status || !canvas) {
    console.error('capGen: missing DOM');
    return;
  }

  status.textContent = 'Engine loaded from Git ✔';

  canvas.innerHTML = `
    <strong>capGen Engine</strong><br>
    Source: GitHub (raw)<br>
    Mode: Internal<br>
    Status: OK
  `;
});
