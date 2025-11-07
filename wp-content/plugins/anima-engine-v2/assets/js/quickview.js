(function(){
  const modal = document.getElementById('anima-qv');
  if(!modal) return;
  const viewer = modal.querySelector('model-viewer');
  const closeBtns = modal.querySelectorAll('#anima-qv-close');
  function open(url, poster){
    if(viewer){
      if(poster){ viewer.setAttribute('poster', poster); }
      viewer.setAttribute('src', url);
    }
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
  function close(){
    modal.style.display = 'none';
    if(viewer){ viewer.removeAttribute('src'); }
    document.body.style.overflow = '';
  }
  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('[data-anima-qv]');
    if(btn){
      e.preventDefault();
      const url = btn.getAttribute('data-src');
      const poster = btn.getAttribute('data-poster') || '';
      if(url) open(url, poster);
    }
  });
  closeBtns.forEach(b=>b.addEventListener('click', close));
  modal.addEventListener('click', (e)=>{ if(e.target === modal) close(); });
})();