/* =========================================================
   AutoBook — front-end glue
   - Toast notifications (react-hot-toast replacement)
   - Modal open/close (Modal component replacement)
   - Lucide icon rendering
   - Scroll-aware navbar (matches Navbar.tsx)
   - Reveal-on-scroll for sections (replaces framer-motion)
   ========================================================= */

(function () {
  'use strict';

  // ── Lucide ────────────────────────────────────────────
  function renderIcons() {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  }

  // ── Toasts (replaces react-hot-toast) ────────────────
  function toast(message, type) {
    type = type || 'info';
    var container = document.getElementById('toast-container');
    if (!container) return;
    var el = document.createElement('div');
    el.className = 'toast ' + type;
    var iconName = type === 'success' ? 'check-circle-2'
                 : type === 'error'   ? 'alert-circle'
                 : 'info';
    el.innerHTML =
      '<i data-lucide="' + iconName + '" style="width:16px;height:16px;flex-shrink:0;margin-top:1px;color:' +
        (type === 'success' ? '#047857' : type === 'error' ? '#b91c1c' : '#525252') + '"></i>' +
      '<span>' + escapeHtml(message) + '</span>';
    container.appendChild(el);
    renderIcons();
    setTimeout(function () {
      el.classList.add('leaving');
      setTimeout(function () { el.remove(); }, 200);
    }, 3500);
  }
  window.toast = toast;

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function (c) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
    });
  }

  // Initial flash from PHP session
  if (window.__INITIAL_FLASH__) {
    toast(window.__INITIAL_FLASH__.message, window.__INITIAL_FLASH__.type);
    delete window.__INITIAL_FLASH__;
  }

  // ── Modal (Modal.tsx replacement) ────────────────────
  // Usage:
  //   <button data-modal-open="bookingModal">…</button>
  //   <div id="bookingModal" class="modal" hidden> … </div>
  //   <button data-modal-close>×</button>
  //
  // Or programmatically: window.openModal('bookingModal')
  function openModal(id, opts) {
    var el = typeof id === 'string' ? document.getElementById(id) : id;
    if (!el) return;
    el.hidden = false;
    document.body.classList.add('modal-open');
    if (opts && typeof opts.onOpen === 'function') opts.onOpen(el);
    renderIcons();
  }
  function closeModal(id) {
    var el = typeof id === 'string' ? document.getElementById(id) : id;
    if (!el) return;
    el.hidden = true;
    document.body.classList.remove('modal-open');
  }
  window.openModal = openModal;
  window.closeModal = closeModal;

  document.addEventListener('click', function (ev) {
    var trigger = ev.target.closest('[data-modal-open]');
    if (trigger) {
      ev.preventDefault();
      openModal(trigger.getAttribute('data-modal-open'));
      return;
    }
    var closer = ev.target.closest('[data-modal-close]');
    if (closer) {
      ev.preventDefault();
      var panel = closer.closest('.modal-backdrop') || closer.closest('[id]');
      var id = panel && panel.id;
      if (id) closeModal(id);
      return;
    }
    // Click outside modal-panel closes
    if (ev.target.classList && ev.target.classList.contains('modal-backdrop')) {
      closeModal(ev.target);
    }
  });
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape') {
      var open = document.querySelectorAll('.modal-backdrop:not([hidden])');
      open.forEach(function (m) { closeModal(m); });
    }
  });

  // ── Navbar transparency on scroll ────────────────────
  function setupNavbarScroll() {
    var nav = document.getElementById('site-nav');
    if (!nav) return;
    var transparentDefault = nav.getAttribute('data-transparent-default') === '1';
    if (!transparentDefault) return;
    var scrolled = false;
    function update() {
      var s = window.scrollY > 20;
      if (s === scrolled) return;
      scrolled = s;
      if (s) {
        nav.classList.remove('bg-transparent', 'border-transparent', 'py-4');
        nav.classList.add('bg-white/95', 'border-b', 'border-stone-200', 'backdrop-blur-md', 'py-0', 'shadow-sm');
        applyOnLightNav(nav);
      } else {
        nav.classList.add('bg-transparent', 'border-transparent', 'py-4');
        nav.classList.remove('bg-white/95', 'border-b', 'border-stone-200', 'backdrop-blur-md', 'py-0', 'shadow-sm');
        applyOnDarkNav(nav);
      }
    }
    window.addEventListener('scroll', update, { passive: true });
    update();
  }

  function applyOnDarkNav(nav) {
    var logoBg = nav.querySelector('.nav-logo-bg');
    var logoText = nav.querySelector('.nav-logo-text');
    var cta = nav.querySelector('.nav-cta');
    var username = nav.querySelector('.nav-username');
    var logout = nav.querySelector('.nav-logout');
    if (logoBg)   { logoBg.classList.remove('bg-stone-900', 'text-white'); logoBg.classList.add('bg-white', 'text-stone-900'); }
    if (logoText) { logoText.classList.remove('text-stone-900', 'group-hover:text-stone-700'); logoText.classList.add('text-white'); }
    if (cta)      { cta.classList.remove('bg-stone-900', 'text-white', 'hover:bg-stone-800'); cta.classList.add('bg-white', 'text-stone-900', 'hover:bg-stone-100'); }
    if (username) { username.classList.remove('text-stone-700'); username.classList.add('text-white'); }
    if (logout)   { logout.classList.remove('hover:text-stone-700'); logout.classList.add('hover:text-white'); }
    // Nav links
    nav.querySelectorAll('a[data-nav-active]').forEach(function (a) {
      var active = a.getAttribute('data-nav-active') === '1';
      a.classList.remove('bg-stone-100', 'text-stone-900', 'text-stone-500', 'hover:text-stone-900', 'hover:bg-stone-50');
      if (active) {
        a.classList.add('bg-white/10', 'text-white');
      } else {
        a.classList.add('text-stone-300', 'hover:text-white', 'hover:bg-white/10');
      }
    });
  }
  function applyOnLightNav(nav) {
    var logoBg = nav.querySelector('.nav-logo-bg');
    var logoText = nav.querySelector('.nav-logo-text');
    var cta = nav.querySelector('.nav-cta');
    var username = nav.querySelector('.nav-username');
    var logout = nav.querySelector('.nav-logout');
    if (logoBg)   { logoBg.classList.remove('bg-white', 'text-stone-900'); logoBg.classList.add('bg-stone-900', 'text-white'); }
    if (logoText) { logoText.classList.remove('text-white'); logoText.classList.add('text-stone-900', 'group-hover:text-stone-700'); }
    if (cta)      { cta.classList.remove('bg-white', 'text-stone-900', 'hover:bg-stone-100'); cta.classList.add('bg-stone-900', 'text-white', 'hover:bg-stone-800'); }
    if (username) { username.classList.remove('text-white'); username.classList.add('text-stone-700'); }
    if (logout)   { logout.classList.remove('hover:text-white'); logout.classList.add('hover:text-stone-700'); }
    nav.querySelectorAll('a[data-nav-active]').forEach(function (a) {
      var active = a.getAttribute('data-nav-active') === '1';
      a.classList.remove('bg-white/10', 'text-white', 'text-stone-300', 'hover:text-white', 'hover:bg-white/10');
      if (active) {
        a.classList.add('bg-stone-100', 'text-stone-900');
      } else {
        a.classList.add('text-stone-500', 'hover:text-stone-900', 'hover:bg-stone-50');
      }
    });
  }

  // ── Reveal on scroll (all variants) ─────────────────
  function setupReveal() {
    var sel = '.reveal, .reveal-left, .reveal-right, .reveal-scale, .reveal-fade, .line-draw, .heading-underline';
    var els = document.querySelectorAll(sel);
    if (!els.length) return;
    if (!('IntersectionObserver' in window)) {
      els.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -32px 0px' });
    els.forEach(function (el) { io.observe(el); });
  }

  // ── Stagger: auto-assign --delay to children of [data-stagger] ──
  function setupStagger() {
    document.querySelectorAll('[data-stagger]').forEach(function (parent) {
      var base = parseInt(parent.getAttribute('data-stagger') || '60', 10);
      var children = parent.querySelectorAll(':scope > *');
      children.forEach(function (child, i) {
        child.style.setProperty('--delay', (i * base) + 'ms');
      });
    });
  }

  // ── Counter: animate numbers up when visible ─────────
  function setupCounters() {
    var els = document.querySelectorAll('[data-count]');
    if (!els.length) return;
    if (!('IntersectionObserver' in window)) {
      els.forEach(function (el) {
        el.textContent = el.getAttribute('data-count');
      });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        var target = parseInt(el.getAttribute('data-count'), 10);
        var duration = 2400;
        var start = null;
        function step(ts) {
          if (!start) start = ts;
          var progress = Math.min((ts - start) / duration, 1);
          var eased = 1 - Math.pow(1 - progress, 3); // cubic ease-out
          el.textContent = Math.floor(eased * target);
          if (progress < 1) requestAnimationFrame(step);
          else el.textContent = target;
        }
        requestAnimationFrame(step);
        el.classList.add('count-pop');
        io.unobserve(el);
      });
    }, { threshold: 0.5 });
    els.forEach(function (el) { io.observe(el); });
  }

  // ── Hero parallax (subtle, 0.25x) ────────────────────
  function setupParallax() {
    var hero = document.getElementById('hero-parallax');
    if (!hero) return;
    var last = 0;
    var ticking = false;
    function update() {
      hero.style.transform = 'translateY(' + (last * 0.25) + 'px)';
      ticking = false;
    }
    window.addEventListener('scroll', function () {
      last = window.scrollY;
      if (!ticking) { requestAnimationFrame(update); ticking = true; }
    }, { passive: true });
  }

  // ── Magnetic CTA buttons ──────────────────────────────
  function setupMagnetic() {
    document.querySelectorAll('[data-magnetic]').forEach(function (btn) {
      btn.addEventListener('mousemove', function (e) {
        var r = btn.getBoundingClientRect();
        var x = ((e.clientX - r.left) / r.width  - 0.5) * 10;
        var y = ((e.clientY - r.top)  / r.height - 0.5) * 8;
        btn.style.transform = 'translate(' + x + 'px,' + y + 'px)';
      });
      btn.addEventListener('mouseleave', function () {
        btn.style.transform = '';
      });
    });
  }

  // ── Page entrance ─────────────────────────────────────
  function setupPageEnter() {
    var main = document.querySelector('main');
    if (main) main.classList.add('page-enter');
  }


  // ── Confirm via form data attribute ──────────────────
  // <form data-confirm="Delete?"> blocks submit unless user confirms.
  document.addEventListener('submit', function (ev) {
    var form = ev.target;
    if (!form.matches || !form.matches('[data-confirm]')) return;
    var msg = form.getAttribute('data-confirm') || 'Are you sure?';
    if (!window.confirm(msg)) {
      ev.preventDefault();
    }
  });

  // ── Init ─────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', function () {
    renderIcons();
    setupNavbarScroll();
    setupStagger();
    setupReveal();
    setupCounters();
    setupParallax();
    setupMagnetic();
    setupPageEnter();
  });
})();
