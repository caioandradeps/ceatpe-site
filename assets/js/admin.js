/* CEATPE — painel administrativo */
(function () {
  'use strict';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* Menu lateral no celular */
  var side = $('[data-side]'), toggle = $('[data-side-toggle]'), scrim = null;
  var setSide = function (open) {
    side.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
    if (open && !scrim) { scrim = document.createElement('div'); scrim.className = 'adm-scrim'; scrim.addEventListener('click', function () { setSide(false); }); document.body.appendChild(scrim); }
    if (!open && scrim) { scrim.remove(); scrim = null; }
  };
  if (side && toggle) {
    toggle.addEventListener('click', function () { setSide(!side.classList.contains('is-open')); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && side.classList.contains('is-open')) { setSide(false); toggle.focus(); } });
  }

  /* Confirmação antes de excluir */
  var dlg = $('#confirmar'), pending = null;
  $$('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (f.dataset.ok) return;
      e.preventDefault();
      pending = f;
      if (dlg && dlg.showModal) { $('[data-confirm-text]', dlg).textContent = f.getAttribute('data-confirm'); dlg.showModal(); $('[data-confirm-cancel]', dlg).focus(); }
      else if (window.confirm(f.getAttribute('data-confirm'))) { f.dataset.ok = '1'; f.submit(); }
    });
  });
  if (dlg) {
    $('[data-confirm-cancel]', dlg).addEventListener('click', function () { dlg.close(); pending = null; });
    $('[data-confirm-ok]', dlg).addEventListener('click', function () { dlg.close(); if (pending) { pending.dataset.ok = '1'; pending.submit(); } });
  }

  /* Contador de caracteres */
  $$('[data-max]').forEach(function (el) {
    var c = $('[data-counter-for="' + el.id + '"]');
    if (!c) return;
    var max = parseInt(el.getAttribute('data-max'), 10);
    var upd = function () { var n = el.value.length; c.textContent = n + ' / ' + max; c.classList.toggle('is-over', n > max); };
    el.addEventListener('input', upd); upd();
  });

  /* Prévia da imagem escolhida */
  $$('[data-image]').forEach(function (box) {
    var input = $('[data-file]', box), prev = $('[data-preview]', box), name = $('[data-filename]', box);
    if (!input) return;
    input.addEventListener('change', function () {
      var f = input.files && input.files[0];
      if (!f) return;
      if (f.size > 8 * 1024 * 1024) { alert('Essa imagem tem mais de 8 MB. Reduza o tamanho e envie de novo.'); input.value = ''; return; }
      prev.style.backgroundImage = 'url(' + URL.createObjectURL(f) + ')'; prev.innerHTML = '';
      name.textContent = f.name;
    });
  });

  /* Prévia do endereço (slug) */
  var slugIn = $('input[name="slug"]'), slugOut = $('[data-slug-preview]');
  var slugify = function (s) { return s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, ''); };
  if (slugIn && slugOut) {
    var titleIn = $('[data-editform] input[name="titulo"]');
    var upd = function () { slugOut.textContent = slugify(slugIn.value || (titleIn ? titleIn.value : '')); };
    slugIn.addEventListener('input', upd); if (titleIn) titleIn.addEventListener('input', upd); upd();
  }

  /* Editor de texto */
  if (window.Quill) {
    $$('[data-rich]').forEach(function (el) {
      var name = el.getAttribute('data-rich');
      var hidden = $('input[type="hidden"][name="' + name + '"]');
      var q = new Quill(el, {
        theme: 'snow',
        placeholder: 'Escreva aqui…',
        modules: { toolbar: { container: [[{ header: [2, 3, false] }], ['bold', 'italic', 'underline'], ['blockquote', { list: 'ordered' }, { list: 'bullet' }], ['link', 'image', 'video'], ['clean']], handlers: { image: function () { pickImage(q); } } } }
      });
      q.root.setAttribute('aria-labelledby', el.getAttribute('aria-labelledby'));
      var sync = function () { hidden.value = q.root.innerHTML === '<p><br></p>' ? '' : q.root.innerHTML; };
      q.on('text-change', sync);
      el.closest('form').addEventListener('submit', sync);
    });
  }
  function pickImage(q) {
    var inp = document.createElement('input'); inp.type = 'file'; inp.accept = 'image/jpeg,image/png,image/webp';
    inp.onchange = function () {
      var f = inp.files[0]; if (!f || !window.CE_UPLOAD) return;
      var fd = new FormData(); fd.append('imagem', f); fd.append('_csrf', window.CE_UPLOAD.csrf);
      var range = q.getSelection(true);
      q.insertText(range.index, 'Enviando imagem…', { italic: true });
      fetch(window.CE_UPLOAD.url, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (j) {
          q.deleteText(range.index, 'Enviando imagem…'.length);
          if (j.url) q.insertEmbed(range.index, 'image', j.url); else alert(j.erro || 'Não foi possível enviar a imagem.');
        })
        .catch(function () { q.deleteText(range.index, 'Enviando imagem…'.length); alert('Falha de conexão ao enviar a imagem.'); });
    };
    inp.click();
  }

  /* Avisos somem sozinhos */
  var fl = $('[data-flash]');
  if (fl && fl.classList.contains('adm-alert--success')) setTimeout(function () { fl.style.transition = 'opacity 200ms'; fl.style.opacity = '0'; setTimeout(function () { fl.remove(); }, 220); }, 4000);

  /* Aviso de alterações não salvas */
  var form = $('[data-editform]');
  if (form) {
    var dirty = false;
    form.addEventListener('input', function () { dirty = true; });
    form.addEventListener('submit', function () { dirty = false; });
    window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
  }
})();
