(async () => {

  const sleep = ms => new Promise(r => setTimeout(r, ms));

  // wait for lazy content
  await sleep(2000);

  const imgs = Array.from(document.images)
    .map(img => ({
      src: img.currentSrc || img.src,
      w: img.naturalWidth,
      h: img.naturalHeight
    }))
    .filter(img => img.w >= window.CAPGEN_CONFIG.startSize);

  const container = document.getElementById('result');
  container.innerHTML = '';

  imgs.forEach(img => {
    const box = document.createElement('div');
    box.style.border = '2px solid red';
    box.style.width = img.w / 3 + 'px';
    box.style.height = img.h / 3 + 'px';
    box.style.marginBottom = '10px';
    box.textContent = `${img.w} × ${img.h}`;
    container.appendChild(box);
  });

})();
