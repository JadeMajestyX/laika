// public/js/modals.js

(function(){
  if (typeof bootstrap === 'undefined') {
    console.warn('Bootstrap JS no detectado. Los modales necesitan bootstrap.bundle.js');
    return;
  }

    const CLINICAS = [
    { id: 1, name: 'Clinica FCAM' },
    { id: 2, name: 'Clinica FIE' },
    { id: 3, name: 'Clinica FACIMAR' },
    { id: 4, name: 'Veterinaria el Caballo' },
    { id: 5, name: 'Animal Care' }
  ];

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

    const MAP = {
      clientes: 'usuarios',         // clientes → usuarios (para clientes)
      mascotas: 'mascotas',        
      trabajadores: 'trabajadores', 
    };

    const base = MAP[section] || section;  
    return `/${base}${id ? '/' + id : ''}`;
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
      const skipView = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
        'imagen',
        'imagen_perfil',
        'notas',
        'clinica_id',
        'user_id'
      ];
     const rows = Object.entries(obj)
      .filter(([k, v]) => !skipView.includes(k)) // ← FILTRO
      .map(([k, v]) => `
        <div class="mb-2">
          <strong>${escapeHtml(prettyKey(k))}:</strong> 
          ${escapeHtml(String(v ?? '-'))}
        </div>
      `);
      return rows.join('');
    }

    // --- VALIDACIÓN Y MENSAJES  ---
    function clearFormErrors(container) {
      // quitar marcas previas
      container.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      container.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
      const existingAlert = container.querySelector('.js-form-alert');
      if (existingAlert) existingAlert.remove();
    }

    function showFormErrors(container, errors) {
      // errors puede ser: { field: ['msg1','msg2'], ... } o array de strings
      clearFormErrors(container);

      // Si es objeto con campos
      if (errors && typeof errors === 'object' && !Array.isArray(errors)) {
        // mostrar mensajes por campo
        Object.entries(errors).forEach(([field, msgs]) => {
          const input = container.querySelector(`[name="${field}"]`);
          if (input) {
            input.classList.add('is-invalid');
            const fb = document.createElement('div');
            fb.className = 'invalid-feedback';
            fb.innerHTML = escapeHtml((Array.isArray(msgs) ? msgs.join('<br>') : String(msgs)));
            // si el input está dentro de un .mb-3 lo anexamos después del input
            input.parentNode.appendChild(fb);
          }
        });
        // además agregamos un alert general arriba
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger js-form-alert';
        alert.innerHTML = '<strong>Errores al validar el formulario. Revisa los campos marcados.</strong>';
        container.prepend(alert);
        return;
      }

      // Si es array o string, mostrarlo en un alert general
      const alert = document.createElement('div');
      alert.className = 'alert alert-danger js-form-alert';
      if (Array.isArray(errors)) alert.innerHTML = '<strong>' + escapeHtml(errors.join('<br>')) + '</strong>';
      else alert.innerHTML = '<strong>' + escapeHtml(String(errors || 'Error en el formulario')) + '</strong>';
      container.prepend(alert);
    }

    function showSuccessMessage(container, msg = 'Datos guardados') {
      const existingAlert = container.querySelector('.js-form-alert');
      if (existingAlert) existingAlert.remove();
      const alert = document.createElement('div');
      alert.className = 'alert alert-success js-form-alert';
      alert.innerHTML = '<strong>' + escapeHtml(msg) + '</strong>';
      container.prepend(alert);
    }

  function buildEditFormFields(data, section) {
    
    const fields = [];
    const skip = ['id','created_at','updated_at', 'deleted_at', 'imagen_perfil','user_id','notas','password','imagen'];

    Object.entries(data).forEach(([k,v]) => {
      if (skip.includes(k)) return;
      if (typeof v === 'object' && v !== null) return;

      const label = prettyKey(k);
      let inputHtml = '';

      // --- SELECTS PERSONALIZADOS ---
      if (k === 'rol') {  //U / A
        inputHtml = `
          <select name="rol" class="form-select" required>
            <option value="A" ${v === 'A' ? 'selected' : ''}>Administrador</option>
            <option value="U" ${v === 'U' ? 'selected' : ''}>Usuario</option>
            <option value="V" ${v === 'V' ? 'selected' : ''}>Veterinario</option>
          </select>`;
      }
      else if (k === 'genero') { //U / A
        inputHtml = `
          <select name="genero" class="form-select" required>
            <option value="F" ${v === 'F' ? 'selected' : ''}>Femenino</option>
            <option value="M" ${v === 'M' ? 'selected' : ''}>Masculino</option>
            <option value="O" ${v === 'O' ? 'selected' : ''}>Otro</option>
          </select>`;
      }
        else if (k === 'especie') {  //mascotas
              inputHtml = `
                <select name="especie" id="select-especie" class="form-select" required>
                  <option value="">Selecciona especie</option>
                  ${Object.keys(window.RAZAS_POR_ESPECIE).map(especie => `
                    <option value="${especie}" ${v === especie ? 'selected' : ''}>${especie}</option>
                  `).join('')}
                </select>
              `;
            }
                else if (k === 'raza') { //mascotas
                  const especieActual = data.especie || "";
                  const razas = window.RAZAS_POR_ESPECIE[especieActual] || [];

                  inputHtml = `
                    <select name="raza" id="select-raza" class="form-select" required>
                      <option value="">Selecciona raza</option>
                      ${razas.map(raza => `
                        <option value="${raza}" ${v === raza ? 'selected' : ''}>${raza}</option>
                      `).join('')}
                    </select>
                  `;
                }

          else if (k === 'sexo') {
            inputHtml = `
              <select name="sexo" class="form-select" required>
                <option value="">Selecciona</option>
                <option value="M" ${v === 'M' ? 'selected' : ''}>Macho</option>
                <option value="H" ${v === 'H' ? 'selected' : ''}>Hembra</option>
              </select>
            `;
          }

      else if (k === 'is_active') { //solo para clientes y trabajadores
        inputHtml = `
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active"
              ${v == 1 ? 'checked' : ''}>
          </div>`;
      }

      // --- VALIDACIÓN TELÉFONO ---
      else if (k === 'telefono') {
        inputHtml = `<input type="text" maxlength="10" name="${k}" 
                      value="${escapeHtml(String(v ?? ''))}" 
                      class="form-control" />`;
      }

      else if (k === 'clinica_id') {
          if (section === 'trabajadores') {
            inputHtml = `
              <select name="clinica_id" class="form-select" required>
                <option value="">Selecciona clínica</option>
                ${CLINICAS.map(c => {
                  const selected = (String(v) === String(c.id) || String(v) === c.name) ? 'selected' : '';
                  return `<option value="${c.id}" ${selected}>${c.name}</option>`;
                }).join('')}
              </select>
            `;
              }else {
                return;
              }
            }

      // --- TIPOS AUTOMÁTICOS ---
      else if (typeof v === 'number') {
        inputHtml = `<input type="number" step="any" name="${k}" value="${v}" class="form-control" />`;
      }
      else if (/\b(email|correo)\b/i.test(k)) {
        inputHtml = `<input type="email" name="${k}" value="${escapeHtml(String(v ?? ''))}" class="form-control" />`;
      }
       else if (k === "fecha_nacimiento") {  //fecha de nacimiento en general (C,A,T)
            let fechaValor = "";

            if (v && typeof v === "string") {
              fechaValor = v.split("T")[0];
            }
            const hoy = new Date().toISOString().split("T")[0];
            inputHtml = `
              <input type="date"
                    name="${k}"
                    value="${escapeHtml(fechaValor)}"
                    class="form-control"
                    max="${hoy}"
                    onkeydown="return false"
              />
            `;
          }

      else {
          let maxAttr = "";

          // Limitar nombre y apellidos a 25 caracteres
          if (['nombre','apellido_paterno','apellido_materno'].includes(k)) {
              maxAttr = 'maxlength="25"';
          }

          // campos que NO queremos obligatorios (lista de excepciones)
          const optionalFields = ['imagen_perfil','imagen','notas','password','user_id', 'apellido_materno']; // añade más si hace falta

          const requiredAttr = optionalFields.includes(k) ? '' : 'required';

          inputHtml = `
            <input type="text" 
                  name="${k}" 
                  value="${escapeHtml(String(v ?? ''))}" 
                  class="form-control"
                  ${maxAttr}
                  ${requiredAttr}
            />`;
        }


          fields.push(`
            <div class="mb-3">
              <label class="form-label">${label}</label>
              ${inputHtml}
            </div>
          `);
        });

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

        const payload = await fetchJson(getApiUrl(section, id), { 
                        method: 'GET',
                        headers: { Accept: 'application/json' }
                      });
        const formFields = buildEditFormFields(payload, section);

        editModalBody.innerHTML = `
          <input type="hidden" name="id" value="${escapeHtml(id)}"/>
          <input type="hidden" name="_section" value="${escapeHtml(section)}"/>
          ${formFields}
        `;
        editModal.show();
          // Actualiza el select de RAZA cuando cambie la ESPECIE
            const especieSelect = editModalBody.querySelector('#select-especie');
            const razaSelect = editModalBody.querySelector('#select-raza');

            if (especieSelect && razaSelect) {
              especieSelect.addEventListener('change', function () {
                const especie = this.value;
                const razas = window.RAZAS_POR_ESPECIE[especie] || [];

                // Rellenar las opciones del select raza
                razaSelect.innerHTML = `
                  <option value="">Selecciona raza</option>
                  ${razas.map(r => `<option value="${r}">${r}</option>`).join('')}
                `;
              });
            }

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


        // VALIDACIÓN CLIENTE: revisar campos required del formulario visible en el modal
      const container = editModalBody; 
      clearFormErrors(container);

      // buscar todos los controls que tengan el atributo required
      const requiredControls = Array.from(container.querySelectorAll('input[required], select[required], textarea[required]'));

      const clientErrors = {};
      requiredControls.forEach(ctrl => {
        // si es checkbox tipo switch, manejar diferente
        if (ctrl.type === 'checkbox') return; // is_active no es obligatorio visualmente
        const val = ctrl.value;
        if (val === null || val === undefined || String(val).trim() === '') {
          clientErrors[ctrl.name || '(campo)'] = ['Este campo es obligatorio.'];
        }
      });

      if (Object.keys(clientErrors).length > 0) {
        showFormErrors(container, clientErrors);
        return; // detener envío
      }

      // detectar archivos
      let hasFile = false;
      for (const pair of formData.entries()) {
        const value = pair[1];
        if (value instanceof File && value.size > 0) { hasFile = true; break; }
      }
          const payload = {};
          formData.forEach((v,k) => {
            if (k === '_section' || k === 'id') return;
            if (k === 'is_active') {
            payload[k] = v === 'on' ? 1 : 0;
          } else {
            payload[k] = v;
          }

          });
          try {
            const url = getApiUrl(section, id);
              if (hasFile) {
          // enviar FormData 
          formData.set('_method','PUT');
          clearFormErrors(container);
          const res = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Accept': 'application/json' },
            body: formData
      });
      if (!res.ok) {
        // intentar parsear JSON con errores
        const txt = await res.text().catch(()=>'');
        let json;
        try { json = JSON.parse(txt); } catch(e){ json = null; }
        if (res.status === 422 && json && json.errors) {
          showFormErrors(container, json.errors);
          throw new Error('Validation error');
        } else {
          throw new Error(txt || res.statusText || 'Error en servidor');
        }
      }
    } else {
      // envío JSON
          clearFormErrors(container);
          await fetchJson(url, {
            method: 'PUT',
            body: JSON.stringify(payload),
            headers: { 'Accept': 'application/json' }
          });
        }

        // si llegamos aquí es éxito
        showSuccessMessage(container, 'Datos actualizados');
        document.dispatchEvent(new CustomEvent('entity:updated', { detail: { section, id } }));
        setTimeout(()=> { editModal.hide(); }, 900);

      } catch (err) {
        // muestra errores (Laravel devuelve 422 con errors)
        if (err && err.status === 422 && err.body) {
          try {
            const obj = JSON.parse(err.body);
            if (obj.errors) {
              showFormErrors(editModalBody, obj.errors);
              return;
            }
          } catch(e){}
        }
        console.error(err);
        // mostrará mensaje genérico si no hay detalles
        showFormErrors(editModalBody, ['Error guardando los cambios: ' + (err.message || err)]);
      }
    });
  }

})();
