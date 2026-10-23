(function () {
 'use strict';
 var btn=document.querySelector('.mm-menu-toggle');
 var nav=document.getElementById('mm-navigation');
 if(btn && nav){
  function close(){btn.setAttribute('aria-expanded','false');btn.setAttribute('aria-label','Mở menu');nav.classList.remove('mm-nav-open');document.body.classList.remove('mm-menu-open');}
  btn.addEventListener('click',function(){var open=btn.getAttribute('aria-expanded')!=='true';btn.setAttribute('aria-expanded',open?'true':'false');btn.setAttribute('aria-label',open?'Đóng menu':'Mở menu');nav.classList.toggle('mm-nav-open',open);document.body.classList.toggle('mm-menu-open',open);});
  document.addEventListener('keydown',function(e){if(e.key==='Escape')close();});
  nav.addEventListener('click',function(e){if(e.target.closest('a'))close();});
  document.addEventListener('click',function(e){if(!nav.contains(e.target)&&!btn.contains(e.target))close();});
 }
 var reveals=[].slice.call(document.querySelectorAll('.mm-category-card,.mm-product-card,.mm-section-heading,.mm-story-grid'));
 if('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches){
  var observer=new IntersectionObserver(function(entries){entries.forEach(function(entry){if(entry.isIntersecting){entry.target.classList.add('mm-in-view');observer.unobserve(entry.target);}});},{threshold:.12});
  reveals.forEach(function(el){el.classList.add('mm-reveal');observer.observe(el);});
 }
})();
