
document.addEventListener('DOMContentLoaded', ()=>{
  const els = document.querySelectorAll('.card, h1, h2, .cta');
  const io = new IntersectionObserver(entries=>{
    entries.forEach(e=>{
      if(e.isIntersecting){
        e.target.style.transform='translateY(0)'; e.target.style.opacity='1';
      }
    });
  },{threshold:.15});
  els.forEach(el=>{
    el.style.transform='translateY(12px)';
    el.style.opacity='0';
    el.style.transition='all .6s ease';
    io.observe(el);
  });
});
