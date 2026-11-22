/* Taprobellelk theme interactions: reveal on scroll, sticky nav, sparkles, tilt, counters, zoom, toasts, confetti */
(function () {
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;

  // preloader (only first visit per tab)
  var pre = $('.preloader');
  if (pre) {
    if (sessionStorage.getItem('tb_seen')) pre.remove();
    else window.addEventListener('load', function () { setTimeout(function () { pre.classList.add('done'); sessionStorage.setItem('tb_seen', 1); }, 450); });
  }

  // nav shadow, scroll progress, back-to-top
  var nav = $('#siteNav'), bar = $('.scroll-progress'), top = $('.to-top');
  function onScroll() {
    var y = window.pageYOffset, h = document.documentElement.scrollHeight - innerHeight;
    if (nav) nav.classList.toggle('scrolled', y > 30);
    if (bar) bar.style.width = (h > 0 ? y / h * 100 : 0) + '%';
    if (top) top.classList.toggle('show', y > 500);
  }
  addEventListener('scroll', onScroll, {passive: true}); onScroll();
  if (top) top.addEventListener('click', function () { scrollTo({top: 0, behavior: 'smooth'}); });

  // mobile menu + user dropdown
  var mt = $('#menuToggle'), mn = $('#mainNav');
  if (mt && mn) mt.addEventListener('click', function () { mn.classList.toggle('open'); });
  var um = $('#userMenu');
  if (um) {
    $('.user-btn', um).addEventListener('click', function (e) { e.stopPropagation(); um.classList.toggle('open'); });
    document.addEventListener('click', function (e) { if (!um.contains(e.target)) um.classList.remove('open'); });
  }

  // active nav link
  var here = location.pathname.split('/').pop() || 'index.php';
  $$('#MenuItems a').forEach(function (a) { if (a.getAttribute('href').split('/').pop() === here) a.classList.add('active'); });

  // scroll reveal
  var targets = '.prod-row>div,.cat-tile,.title,.sub-title,.group-title,.row-head,.offer .col-2,.summary-box,.form-card,.checkout-grid>div,.success-box,.total-price,.feature,.cta-band,.about-grid>*,.contact-grid>*,.auth-wrap>*,.prose,.single-product .col-2';
  var els = $$(targets);
  els.forEach(function (el) {
    el.classList.add('reveal');
    if (el.matches('.offer .col-2:first-child,.about-grid>:first-child,.contact-grid>:first-child,.auth-wrap>:first-child,.single-product .col-2:first-child')) el.classList.add('left');
    else if (el.matches('.offer .col-2:last-child,.about-grid>:last-child,.contact-grid>:last-child,.auth-wrap>:last-child,.single-product .col-2:last-child')) el.classList.add('right');
    else if (el.matches('.cat-tile')) el.classList.add('zoom');
    var idx = Array.prototype.indexOf.call(el.parentNode.children, el);
    el.style.setProperty('--d', Math.min(idx % 8, 7) * 70 + 'ms');
  });
  if ('IntersectionObserver' in window && !reduce) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, {threshold: 0.12, rootMargin: '0px 0px -40px 0px'});
    els.forEach(function (el) { io.observe(el); });
  } else els.forEach(function (el) { el.classList.add('in'); });

  // sparkles
  $$('.sparkles').forEach(function (box) {
    for (var i = 0; i < 28; i++) {
      var s = document.createElement('i');
      s.style.cssText = 'left:' + Math.random() * 100 + '%;top:' + Math.random() * 100 + '%;--s:' + (2 + Math.random() * 4) + 'px;--t:' + (3 + Math.random() * 4) + 's;--dl:' + (Math.random() * 5) + 's';
      box.appendChild(s);
    }
  });

  // 3D tilt on hero image
  $$('.hero-visual').forEach(function (v) {
    var img = $('.tilt', v); if (!img || reduce) return;
    v.addEventListener('mousemove', function (e) {
      var r = v.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
      img.style.transform = 'rotateY(' + x * 14 + 'deg) rotateX(' + -y * 14 + 'deg)';
    });
    v.addEventListener('mouseleave', function () { img.style.transform = ''; });
  });

  // count-up numbers
  $$('[data-count]').forEach(function (el) {
    var end = parseFloat(el.dataset.count), suffix = el.dataset.suffix || '', done = false;
    function run() {
      if (done) return; done = true; var t0 = null;
      (function step(t) { if (!t0) t0 = t; var p = Math.min((t - t0) / 1400, 1); el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3))) + suffix; if (p < 1) requestAnimationFrame(step); })(performance.now());
    }
    if ('IntersectionObserver' in window) new IntersectionObserver(function (es, o) { if (es[0].isIntersecting) { run(); o.disconnect(); } }).observe(el); else run();
  });

  // product gallery: thumbnails + hover zoom
  var main = $('#ProductImg');
  if (main) {
    var thumbs = $$('.small-img-col');
    if (thumbs[0]) thumbs[0].classList.add('active');
    $$('.small-img').forEach(function (t) {
      t.addEventListener('click', function () {
        main.style.opacity = 0;
        setTimeout(function () { main.src = t.src; main.style.opacity = 1; }, 180);
        thumbs.forEach(function (c) { c.classList.remove('active'); }); t.parentNode.classList.add('active');
      });
    });
    main.style.transition = 'opacity .25s, transform .2s';
    var zb = main.parentNode;
    zb.addEventListener('mousemove', function (e) {
      var r = zb.getBoundingClientRect();
      main.style.transformOrigin = ((e.clientX - r.left) / r.width * 100) + '% ' + ((e.clientY - r.top) / r.height * 100) + '%';
      main.style.transform = 'scale(1.8)';
    });
    zb.addEventListener('mouseleave', function () { main.style.transform = 'scale(1)'; });
  }

  // toasts: auto-dismiss + click to close
  $$('.toasts .flash').forEach(function (f, i) {
    function out() { f.classList.add('out'); setTimeout(function () { f.remove(); }, 500); }
    f.addEventListener('click', out); setTimeout(out, 4500 + i * 500);
  });
  // cart badge bump when something was just added
  if ($('.toasts .flash-success') && /Added to cart/.test($('.toasts .flash-success').textContent)) {
    var b = $('.cart-badge'), l = $('.cart-link');
    if (b) b.classList.add('bump'); if (l) l.classList.add('bump');
  }

  // login / register tabs
  var tabs = $$('.auth-tabs button');
  tabs.forEach(function (t) {
    t.addEventListener('click', function () {
      tabs.forEach(function (x) { x.classList.toggle('on', x === t); });
      $$('.auth-card form').forEach(function (f) { f.classList.toggle('on', f.id === t.dataset.form); });
    });
  });

  // confetti (order success page)
  var cv = $('#confetti');
  if (cv && !reduce) {
    var cx = cv.getContext('2d'), W = cv.width = innerWidth, H = cv.height = innerHeight, cols = ['#d4af6a', '#f3dca3', '#2747d8', '#6a3df0', '#ff6b8a'];
    var ps = Array.apply(null, Array(140)).map(function () { return {x: Math.random() * W, y: -20 - Math.random() * H * .6, r: 4 + Math.random() * 6, vy: 2 + Math.random() * 4, vx: -1.5 + Math.random() * 3, a: Math.random() * 6, c: cols[Math.floor(Math.random() * cols.length)]}; });
    var t0 = performance.now();
    (function frame(t) {
      cx.clearRect(0, 0, W, H);
      ps.forEach(function (p) { p.y += p.vy; p.x += p.vx + Math.sin(p.a += .05); cx.save(); cx.translate(p.x, p.y); cx.rotate(p.a); cx.fillStyle = p.c; cx.fillRect(-p.r / 2, -p.r / 2, p.r, p.r * .6); cx.restore(); });
      if (t - t0 < 5500) requestAnimationFrame(frame); else cv.remove();
    })(t0);
  }
})();
