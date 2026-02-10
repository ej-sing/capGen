console.log('capGen.js loaded');

(() => {

  const scriptTag = document.currentScript;
  const startSize = parseInt(scriptTag.dataset.startSize || 100, 10);
  const SCALE = 3;

  const sleep = ms => new Promise(r => setTimeout(r, ms));

  async function run() {

    // wait for lazy-load / JS gallery
    await sleep(2000);

    const images = Array.from(document.images)
      .map(img => ({
        src: img.currentSrc || img.src,
        w: img.naturalWidth,
        h: img.naturalHeight,
        file: img.src.split('/').pop()
      }))
      .filter(img => img.w >= startSize && img.h > 0);

    const container = document.getElementById('result');
    container.innerHTML = '';

    if (!images.length) {
      container.innerHTML =
        '<p style="color:#888">No images found</p>';
      return;
    }

    images.forEach(img => {

      const wrapper = document.createElement('div');

      const mock = document.createElement('div');
      mock.className = 'mock';
      mock.style.width  = img.w / SCALE + 'px';
      mock.style.height = img.h / SCALE + 'px';

      const label = document.createElement('span');
      label.textContent = `${img.w} × ${img.h}`;

      mock.appendChild(label);

      const name = document.createElement('div');
      name.className = 'filename';
      name.textContent = img.file;

      wrapper.appendChild(mock);
      wrapper.appendChild(name);

      container.appendChild(wrapper);
    });
  }

  run();

})();
