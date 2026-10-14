/* CEATPE — interações do site. Sem dependências. */
(function () {
  'use strict';
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* Cabeçalho: sombra ao rolar */
  var header = $('[data-header]');
  if (header) {
    var onScroll = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* Menu móvel */
  var menuBtn = $('[data-menu]'), drawer = $('[data-drawer]');
  if (menuBtn && drawer) {
    var setMenu = function (open) {
      drawer.classList.toggle('ce-header__drawer--open', open);
      menuBtn.setAttribute('aria-expanded', String(open));
      menuBtn.querySelector('.ce-sr').textContent = open ? 'Fechar menu' : 'Abrir menu';
    };
    menuBtn.addEventListener('click', function () { setMenu(menuBtn.getAttribute('aria-expanded') !== 'true'); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && menuBtn.getAttribute('aria-expanded') === 'true') { setMenu(false); menuBtn.focus(); } });
    window.addEventListener('resize', function () { if (window.innerWidth > 860) setMenu(false); });
  }

  /* Carrossel do topo */
  var hero = $('[data-hero]');
  if (hero) {
    var slides = $$('[data-slide]', hero), tabs = $$('[data-go]', hero);
    var interval = parseInt(hero.getAttribute('data-interval'), 10) || 7000;
    var current = 0, timer = null, paused = reduce, hovering = false;
    var heights = { red: .30, gold: .38, green: .34, blue: .42 };
    var lift = .56;
    hero.style.setProperty('--interval', interval + 'ms');

    var show = function (i, focusTab) {
      i = (i + slides.length) % slides.length;
      var color = slides[i].getAttribute('data-color') || 'blue';
      slides.forEach(function (s, k) {
        var on = k === i;
        s.hidden = !on;
        s.classList.toggle('is-entering', on && k !== current);
      });
      tabs.forEach(function (t, k) {
        t.setAttribute('aria-selected', String(k === i));
        t.tabIndex = k === i ? 0 : -1;
        t.classList.toggle('is-done', k < i);
        var f = t.querySelector('.hero__fill'); f.style.animation = 'none'; void f.offsetWidth; f.style.animation = '';
      });
      hero.setAttribute('data-active', color);
      hero.style.setProperty('--top', String(Math.max(lift, heights[color] || 0)));
      current = i;
      if (focusTab && tabs[i]) tabs[i].focus();
      schedule();
    };
    var schedule = function () {
      clearTimeout(timer);
      if (paused || hovering || slides.length < 2) return;
      timer = setTimeout(function () { show(current + 1); }, interval);
    };
    tabs.forEach(function (t, k) {
      t.addEventListener('click', function () { show(k); });
      t.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight') { e.preventDefault(); show(current + 1, true); }
        if (e.key === 'ArrowLeft') { e.preventDefault(); show(current - 1, true); }
      });
    });
    var pauseBtn = $('[data-pause]', hero);
    var setPaused = function (p) {
      paused = p;
      hero.classList.toggle('is-paused', p);
      if (pauseBtn) {
        pauseBtn.setAttribute('aria-pressed', String(p));
        $('[data-pause-icon]', pauseBtn).hidden = p;
        $('[data-play-icon]', pauseBtn).hidden = !p;
        $('[data-pause-label]', pauseBtn).textContent = p ? 'Retomar destaques' : 'Pausar destaques';
      }
      schedule();
    };
    if (pauseBtn) pauseBtn.addEventListener('click', function () { setPaused(!paused); });
    var text = $('.hero__text', hero);
    if (text) {
      text.addEventListener('mouseenter', function () { hovering = true; hero.classList.add('is-paused'); clearTimeout(timer); });
      text.addEventListener('mouseleave', function () { hovering = false; if (!paused) hero.classList.remove('is-paused'); schedule(); });
      text.addEventListener('focusin', function () { hovering = true; hero.classList.add('is-paused'); clearTimeout(timer); });
      text.addEventListener('focusout', function () { hovering = false; if (!paused) hero.classList.remove('is-paused'); schedule(); });
    }
    document.addEventListener('visibilitychange', function () { if (document.hidden) clearTimeout(timer); else schedule(); });
    setPaused(paused);
    show(0);
  }

  /* Revelação ao rolar */
  var targets = $$('[data-reveal], [data-areas]');
  if ('IntersectionObserver' in window && !reduce) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
    targets.forEach(function (t) {
      var r = t.getBoundingClientRect();
      if (r.top < window.innerHeight * 0.92) t.classList.add('is-in'); else io.observe(t);
    });
  } else {
    targets.forEach(function (t) { t.classList.add('is-in'); });
  }

  /* Carrossel de parceiros */
  $$('[data-marquee]').forEach(function (m) {
    var track = $('[data-track]', m);
    if (!track || reduce) return;
    var items = $$('li', track);
    if (!items.length) return;
    var fill = function () {
      var guard = 0;
      while (track.scrollWidth < m.clientWidth * 1.2 && guard++ < 12) items.forEach(function (li) { var c = li.cloneNode(true); c.setAttribute('aria-hidden', 'true'); $$('a', c).forEach(function (a) { a.tabIndex = -1; }); track.appendChild(c); });
      $$('li', track).forEach(function (li) { var c = li.cloneNode(true); c.setAttribute('aria-hidden', 'true'); $$('a', c).forEach(function (a) { a.tabIndex = -1; }); track.appendChild(c); });
      var w = track.scrollWidth / 2;
      m.style.setProperty('--dur', Math.max(20, w / 60) + 's');
      m.classList.add('is-running');
    };
    if (document.readyState === 'complete') fill(); else window.addEventListener('load', fill);
  });

  /* Copiar (PIX, link) */
  $$('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var src = btn.getAttribute('data-copy-text') || ($('[data-copy-src]', btn.parentNode) || {}).textContent || '';
      var label = $('[data-copy-label]', btn);
      var done = function () { if (label) { var old = label.textContent; label.textContent = 'Copiado'; setTimeout(function () { label.textContent = old; }, 1800); } };
      if (navigator.clipboard) navigator.clipboard.writeText(src.trim()).then(done, function () {});
      else { var ta = document.createElement('textarea'); ta.value = src.trim(); document.body.appendChild(ta); ta.select(); try { document.execCommand('copy'); done(); } catch (e) {} ta.remove(); }
    });
  });

  /* Filtro de projetos (sem recarregar) */
  var filterBar = $('[data-filter]');
  if (filterBar) {
    var list = $$('[data-cat]');
    var empty = $('[data-filter-empty]');
    $$('[data-filter-btn]', filterBar).forEach(function (b) {
      b.addEventListener('click', function () {
        var cat = b.getAttribute('data-filter-btn');
        $$('[data-filter-btn]', filterBar).forEach(function (o) { o.setAttribute('aria-pressed', String(o === b)); });
        var shown = 0;
        list.forEach(function (li) { var on = cat === 'todos' || li.getAttribute('data-cat') === cat; li.hidden = !on; if (on) shown++; });
        if (empty) empty.hidden = shown > 0;
      });
    });
  }
})();
