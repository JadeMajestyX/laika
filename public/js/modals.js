// public/js/modals.js
(function(){
  if (typeof bootstrap === 'undefined') {
    console.warn('Bootstrap JS no detectado. Los modales necesitan bootstrap.bundle.js');
    return;
  }

  const viewModalEl = document.getElementById('viewModal');
  const editModalEl = document.getElementById('editModal');
  const confirmModalEl = document.getElementById('confirmDeleteModal');

  const viewModal = viewModalEl ? new bootstrap.Modal(viewModalEl) : null;
  const editModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;
  const confirmModal = confirmModalEl ? new bootstrap.Modal(confirmModalEl) : null;

  const viewModalTitle = document.getElementById('viewModalTitle');
  const viewModalBody = document.getElementById('viewModalBody');
  const editModalTitle = document.getElementById('editModalTitle');
  const editModalBody = document.getElementById('editModalBody');
  const editForm = document.getElementById('editModalForm');
  const confirmDeleteBody = document.getElementById('confirmDeleteBody');
  const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function getApiUrl(section, id = '') {
    return `/${section}${id ? '/' + id : ''}`;
  }

  async function fetchJson(url, opts = {}) {
    const headers = opts.headers || {};
    if (!headers['Content-Type'] && !(opts.body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
    }
    if (!headers['X-CSRF-TOKEN']) headers['X-CSRF-TOKEN'] = getCsrfToken();
    const res = await fetch(url, { credentials: 'same-origin', ...opts, headers });
    if (!res.ok) {
      const text = await res.text().catch(()=>'' );
      const err = new Error('HTTP ' + res.status + ': ' + (text || res.statusText));
      err.status = res.status; err.body = text;
      throw err;
    }
    const contentType = res.headers.get('content-type') || '';
    if (contentType.includes('application/json')) return res.json();
    return res.text();
  }

  function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
  }
  function prettyKey(key){ return key.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()); }

  function renderObjectAsList(obj) {
    if (!obj || typeof obj !== 'object') return escapeHtml(String(obj ?? '-'));
    const rows = Object.entries(obj).map(([k,v]) => `<div class="mb-2"><strong>${escapeHtml(prettyKey(k))}:</strong> ${escapeHtml(String(v ?? '-'))}</div>`);
    return rows.join('');
  }

  function buildEditFormFields(data) {
    const fields = [];
    const skip = ['id','created_at','updated_at','password'];
    Object.entries(data).forEach(([k,v]) => {
      if (skip.includes(k)) return;
      if (typeof v === 'object' && v !== null) return;
      const label = prettyKey(k);
      let inputHtml = '';
      if (typeof v === 'number') inputHtml = `<input type="number" step="any" name="${escapeHtml(k)}" value="${escapeHtml(String(v))}" class="form-control" />`;
      else if (/\b(email|correo)\b/i.test(k)) inputHtml = `<input type="email" name="${escapeHtml(k)}" value="${escapeHtml(String(v ?? ''))}" class="form-control" />`;
      else if (/\b(fecha|date|birthday|nacimiento)\b/i.test(k)) inputHtml = `<input type="date" name="${escapeHtml(k)}" value="${escapeHtml(String(v ?? ''))}" class="form-control" />`;
      else inputHtml = `<input type="text" name="${escapeHtml(k)}" value="${escapeHtml(String(v ?? ''))}" class="form-control" />`;
      fields.push(`<div class="mb-3"><label class="form-label">${escapeHtml(label)}</label>${inputHtml}</div>`);
    });
    if (fields.length === 0) fields.push('<div class="text-body-secondary">No hay campos editables automáticos.</div>');
    return fields.join('');
  }

  document.addEventListener('click', async function(e){
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    e.preventDefault();

    const action = btn.dataset.action;
    const section = btn.dataset.section;
    const id = btn.dataset.id;
    if (!section || !id) { console.warn('Falta data-section o data-id'); return; }

    try {
      if (action === 'view') {
        if (!viewModal) return;
        viewModalTitle.textContent = `Ver ${section}`;
        viewModalBody.innerHTML = '<div class="text-center py-3"><div class="spinner-border" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
        viewModal.show();
        const payload = await fetchJson(getApiUrl(section, id), { method: 'GET', headers: { Accept: 'application/json' } });
        viewModalBody.innerHTML = renderObjectAsList(payload);
      } else if (action === 'edit') {
        if (!editModal) return;
        editModalTitle.textContent = `Editar ${section}`;
        editModalBody.innerHTML = '<div class="text-center py-3"><div class="spinner-border"></div></div>';

        const payload = await fetchJson(getApiUrl(section, id), { method: 'GET', headers: { Accept: 'application/json' } });
        const formFields = buildEditFormFields(payload);

        editModalBody.innerHTML = `
          <input type="hidden" name="id" value="${escapeHtml(id)}"/>
          <input type="hidden" name="_section" value="${escapeHtml(section)}"/>
          ${formFields}
        `;
         editModal.show();

        const first = editModalBody.querySelector('input,select,textarea'); if (first) first.focus();
      } else if (action === 'delete') {
        if (!confirmModal) return;
        confirmDeleteBody.textContent = `¿Deseas eliminar este ${section.slice(0,-1)} (ID ${id})? Esta acción no se puede deshacer.`;
        confirmModal.show();

        const oneClickHandler = async () => {
          confirmDeleteBtn.disabled = true; confirmDeleteBtn.textContent = 'Eliminando...';
          try {
            await fetchJson(getApiUrl(section, id), { method: 'DELETE' });
            confirmModal.hide();
            document.dispatchEvent(new CustomEvent('entity:deleted', { detail: { section, id } }));
          } catch (err) {
            console.error(err); alert('Error eliminando: ' + (err.message || err));
          } finally {
            confirmDeleteBtn.disabled = false; confirmDeleteBtn.textContent = 'Sí, eliminar';
            confirmDeleteBtn.removeEventListener('click', oneClickHandler);
          }
        };
        confirmDeleteBtn.addEventListener('click', oneClickHandler);
      }
    } catch (err) {
      console.error(err);
      alert('Ocurrió un error: ' + (err.message || err));
    }
  });

  if (editForm) {
    editForm.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const formData = new FormData(editForm);
      const section = formData.get('_section');
      const id = formData.get('id');
      if (!section || !id) { alert('Falta sección o id'); return; }
      const payload = {};
      formData.forEach((v,k) => {
        if (k === '_section' || k === 'id') return;
        payload[k] = v;
      });
      try {
        const url = getApiUrl(section, id);
        await fetchJson(url, { method: 'PUT', body: JSON.stringify(payload) });
        editModal.hide();
        document.dispatchEvent(new CustomEvent('entity:updated', { detail: { section, id } }));
      } catch (err) {
        console.error(err); alert('Error guardando los cambios: ' + (err.message || err));
      }
    });
  }

})();
