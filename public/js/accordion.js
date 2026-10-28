// Lightweight accordion initializer — no external libraries required
(function(){
  function updateCenter(ac){
    const items = Array.from(ac.querySelectorAll('.accordion-item'));
    if(!items.length) return;
    // prefer opened item
    const opened = items.find(i => i.hasAttribute('open') || i.querySelector('.accordion-panel.open'));
    items.forEach(i=> i.classList.remove('center'));
    if(opened){ opened.classList.add('center'); return; }
    const mid = Math.floor(items.length/2);
    if(items[mid]) items[mid].classList.add('center');
  }

  function initDetailsAccordion(ac){
    // Use native <details> toggle event
    ac.querySelectorAll('details.accordion-item').forEach(d=>{
      d.addEventListener('toggle', ()=> updateCenter(ac));
      // ensure keyboard enter/space on summary works (native) but ensure summary focusable
      const s = d.querySelector('summary'); if(s && !s.hasAttribute('tabindex')) s.setAttribute('tabindex', '0');
    });
    updateCenter(ac);
  }

  function initFallbackAccordion(ac){
    // support markup: .accordion-toggle + .accordion-panel
    ac.querySelectorAll('.accordion-toggle').forEach(toggle=>{
      const panel = toggle.nextElementSibling; if(!panel) return;
      toggle.setAttribute('role','button'); toggle.setAttribute('tabindex', '0');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.addEventListener('click', ()=>{
        const open = panel.classList.toggle('open');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        updateCenter(ac);
      });
      toggle.addEventListener('keydown', (e)=>{ if(e.key==='Enter' || e.key===' ') { e.preventDefault(); toggle.click(); } });
    });
    updateCenter(ac);
  }

  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.accordion').forEach(ac=>{
      if(ac.querySelector('details.accordion-item')) initDetailsAccordion(ac);
      else initFallbackAccordion(ac);

      // observe DOM mutations (items added/removed) and recompute
      const mo = new MutationObserver(()=> updateCenter(ac));
      mo.observe(ac, { childList:true, subtree:true });
      window.addEventListener('resize', ()=> updateCenter(ac));
    });
  });
})();
