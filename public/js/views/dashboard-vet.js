// Módulo: Vista detallada de cita para veterinarios
// Abre un modal con información de la mascota, citas pasadas atendidas,
// permite escribir diagnóstico, recetar medicamentos y exportar a PDF.

(function(){
  const API = {
    // Nueva ruta vía web (session auth) para ficha completa
    citaDetalle: (id) => `/vet/citas/${id}/ficha`,
    guardarDiagnostico: (id) => `/vet/citas/${id}/diagnostico`,
    guardarReceta: (id) => `/vet/citas/${id}/receta`,
  };

  // Utilidad para crear elementos
  function el(tag, attrs={}, children=[]) {
    const node = document.createElement(tag);
    Object.entries(attrs||{}).forEach(([k,v]) => {
      if (k === 'class') node.className = v;
      else if (k === 'style') node.style.cssText = v;
      else node.setAttribute(k, v);
    });
    (Array.isArray(children) ? children : [children]).forEach(c => {
      if (c == null) return;
      if (typeof c === 'string') node.appendChild(document.createTextNode(c));
      else node.appendChild(c);
    });
    return node;
  }

  // Inyección ligera de estilos del modal
  const styleId = 'vet-detail-modal-style';
  if (!document.getElementById(styleId)) {
    const style = el('style', { id: styleId }, `
      .vet-modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9998}
      .vet-modal{position:fixed;inset:auto auto auto 50%;transform:translateX(-50%);
        top:2%;width:min(1400px,96%);max-height:96%;overflow:auto;background:var(--bs-body-bg,#fff);
        color:var(--bs-body-color,#222);border-radius:14px;box-shadow:0 18px 50px rgba(0,0,0,.55);z-index:9999}
      .vet-modal header{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--bs-border-color,#444)}
      .vet-modal .content{display:grid;grid-template-columns:380px 1fr;gap:26px;padding:26px}
      .vet-card{border:1px solid var(--bs-border-color,#444);border-radius:10px;padding:12px;background:var(--bs-tertiary-bg,#f7f7f7)}
      .vet-list{margin:0;padding:0;list-style:none}
      .vet-list li{padding:6px 4px;border-bottom:1px dashed var(--bs-border-color,#555)}
      .vet-actions{display:flex;gap:8px;justify-content:flex-end;padding:12px 16px;border-top:1px solid var(--bs-border-color,#444);background:var(--bs-tertiary-bg,#f7f7f7)}
      .vet-btn{appearance:none;border:1px solid var(--bs-border-color,#555);background:var(--bs-body-bg,#fff);color:var(--bs-body-color,#222);border-radius:8px;padding:8px 12px;cursor:pointer;transition:.15s}
      .vet-btn:hover{filter:brightness(1.08)}
      .vet-btn.primary{background:#2563eb;color:#fff;border-color:#2563eb}
      .vet-input, .vet-textarea{width:100%;border:1px solid var(--bs-border-color,#555);background:var(--bs-body-bg,#fff);color:var(--bs-body-color,#222);border-radius:8px;padding:8px}
      .vet-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:8px}
      .vet-photo{width:100%;height:200px;object-fit:cover;border-radius:10px;border:1px solid var(--bs-border-color,#555);background:#2a2a2d}
      .pill{display:inline-block;padding:4px 8px;border-radius:999px;background:#334e89;color:#e5e9f2;font-size:.75rem}
      @media (prefers-color-scheme: dark){
        .vet-card{background:#26262b}
        .vet-modal{background:#1f1f24;color:#e5e5e8}
        .vet-btn{background:#303038;color:#e5e5e8}
        .vet-input,.vet-textarea{background:#26262b;color:#e5e5e8}
        .vet-list li{border-bottom-color:#3a3a42}
        .vet-actions{background:#26262b}
      }
    `);
    document.head.appendChild(style);
  }

  // Render del modal
  async function openVetDetailModal(citaId) {
    // Backdrop + contenedor
    const backdrop = el('div', { class: 'vet-modal-backdrop' });
    const modal = el('div', { class: 'vet-modal', role: 'dialog', 'aria-modal': 'true' });

    // Header
    const title = el('h3', {}, 'Ficha de consulta');
    const closeBtn = el('button', { class: 'vet-btn', 'aria-label': 'Cerrar' }, 'Cerrar');
    const header = el('header', {}, [title, closeBtn]);

    // Columnas
    const leftCol = el('div', { class: 'vet-card' });
    const rightCol = el('div', {});
    const content = el('div', { class: 'content' }, [leftCol, rightCol]);

    // Acciones
    const printBtn = el('button', { class: 'vet-btn', title:'Imprimir' }, 'Imprimir');
    const pdfBtn = el('button', { class: 'vet-btn primary', title:'Exportar PDF' }, 'Exportar PDF');
    const saveDiagBtn = el('button', { class: 'vet-btn primary', title:'Guardar diagnóstico' }, 'Guardar diagnóstico');
    const saveRecetaBtn = el('button', { class: 'vet-btn', title:'Guardar receta' }, 'Guardar receta');
    const actions = el('div', { class: 'vet-actions' }, [printBtn, pdfBtn, saveDiagBtn, saveRecetaBtn]);

    modal.appendChild(header);
    modal.appendChild(content);
    modal.appendChild(actions);
    document.body.appendChild(backdrop);
    document.body.appendChild(modal);

    function close() { backdrop.remove(); modal.remove(); }
    closeBtn.addEventListener('click', close);
    backdrop.addEventListener('click', close);

    // Cargar datos del nuevo endpoint que devuelve todo en una sola llamada
    let resp, citaObj, mascotaInfo, citasPasadas = [], recetaObj;
    try {
      const citaRes = await fetch(API.citaDetalle(citaId), { credentials: 'same-origin' });
      resp = await citaRes.json();
      
      if (!resp.success) {
        console.error('Error obteniendo ficha:', resp.message);
        alert('Error al cargar la ficha: ' + (resp.message || 'Error desconocido'));
        close();
        return;
      }
      
      citaObj = resp?.cita || {};
      recetaObj = resp?.receta || null;
      mascotaInfo = resp?.mascota || null;
      citasPasadas = resp?.historial || [];
      
      console.log('Datos cargados:', { mascotaInfo, citasPasadas, recetaObj });
    } catch (e) {
      console.error('Error cargando datos de ficha:', e);
      alert('Error al cargar la ficha de la cita');
      close();
      return;
    }

    // Izquierda: perfil mascota
    const fotoUrl = mascotaInfo?.imagen_url || mascotaInfo?.imagen || null;
    leftCol.appendChild(el('img', { class: 'vet-photo', src: fotoUrl || '/images/pet-placeholder.png', alt: 'Foto de la mascota' }));

    leftCol.appendChild(el('div', { style: 'margin-top:10px' }, [
      el('div', {}, [el('strong', {}, 'Mascota: '), (mascotaInfo?.nombre || 'Desconocida')]),
      el('div', {}, [el('strong', {}, 'Especie: '), (mascotaInfo?.especie || '-')]),
      el('div', {}, [el('strong', {}, 'Raza: '), (mascotaInfo?.raza || '-')]),
      el('div', {}, [el('strong', {}, 'Sexo: '), (mascotaInfo?.sexo || '-')]),
      el('div', {}, [el('strong', {}, 'Peso: '), (mascotaInfo?.peso || '-')]),
    ]));

    // Historial: citas atendidas
    const histCard = el('div', { class: 'vet-card', style: 'margin-top:12px' }, [
      el('div', { style: 'display:flex;justify-content:space-between;align-items:center' }, [
        el('strong', {}, 'Citas atendidas'),
        el('span', { class: 'pill' }, `${citasPasadas.length}`)
      ]),
      el('ul', { class: 'vet-list' }, (citasPasadas.length ? citasPasadas.map(c => el('li', {}, `${c.fecha} · ${c.servicio || 'Consulta'} ${c.diagnostico ? '· ' + c.diagnostico.substring(0, 50) + '...' : ''}`)) : [el('li', {}, 'Sin antecedentes registrados')]))
    ]);
    leftCol.appendChild(histCard);

    // Derecha: diagnóstico y receta
    const diagTitle = el('h4', {}, 'Diagnóstico');
    const diagInput = el('textarea', { class: 'vet-textarea', rows: '6', placeholder: 'Escriba el diagnóstico clínico, hallazgos y recomendaciones...' });
    if (citaObj?.diagnostico) diagInput.value = citaObj.diagnostico;
    const recetaTitle = el('h4', { style: 'margin-top:16px' }, 'Receta');
    const recetaList = el('div', { class: 'vet-card' });
    const recetaItems = [];

    function addRecetaItem(initial={}){
      const row = el('div', { class: 'vet-grid-2', style: 'margin-bottom:8px' }, [
        el('input', { class: 'vet-input', placeholder: 'Medicamento (ej. Amoxicilina 250mg)', value: initial.nombre||'' }),
        el('input', { class: 'vet-input', placeholder: 'Dosis y frecuencia (ej. 1 tableta cada 12h por 7 días)', value: initial.dosis||'' }),
      ]);
      const notes = el('input', { class: 'vet-input', placeholder: 'Notas (vía de administración, advertencias, etc.)', value: initial.notas||'' });
      const wrapper = el('div', { class: 'vet-card' }, [row, notes, el('div', { style:'display:flex;gap:8px;justify-content:flex-end;margin-top:6px' }, [
        el('button', { class:'vet-btn' }, 'Eliminar')
      ])]);
      wrapper.querySelector('button').addEventListener('click', () => {
        recetaList.removeChild(wrapper);
        const idx = recetaItems.indexOf(wrapper);
        if (idx >= 0) recetaItems.splice(idx,1);
      });
      recetaItems.push(wrapper);
      recetaList.appendChild(wrapper);
    }

    const addMedBtn = el('button', { class:'vet-btn', title:'Agregar medicamento' }, 'Agregar medicamento');
        // Pre-cargar items de receta si existen
        if (recetaObj?.items && Array.isArray(recetaObj.items)) {
          recetaObj.items.forEach(it => addRecetaItem({ nombre: it.medicamento, dosis: it.dosis, notas: it.notas }));
        }
    addMedBtn.addEventListener('click', () => addRecetaItem());

    rightCol.appendChild(el('div', { class:'vet-card' }, [diagTitle, diagInput]));
    rightCol.appendChild(recetaTitle);
    rightCol.appendChild(recetaList);
    rightCol.appendChild(el('div', { class:'vet-actions' }, [addMedBtn]));

    // Guardar diagnóstico
    saveDiagBtn.addEventListener('click', async () => {
      const body = { diagnostico: diagInput.value };
      try {
        const res = await fetch(API.guardarDiagnostico(citaId), {
          method: 'PATCH',
          headers: { 
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          credentials: 'same-origin',
          body: JSON.stringify(body)
        });
        const json = await res.json();
        if (!res.ok || !json.success) throw new Error(json.message || 'Error al guardar diagnóstico');
        alert('Diagnóstico guardado correctamente');
      } catch(e) {
        console.error('Error guardando diagnóstico:', e);
        alert('No se pudo guardar el diagnóstico: ' + e.message);
      }
    });

    // Guardar receta
    saveRecetaBtn.addEventListener('click', async () => {
      // Construir items desde recetaItems
      const items = recetaItems.map(wrapper => {
        const inputs = wrapper.querySelectorAll('input');
        return {
          medicamento: inputs[0].value.trim(),
          dosis: inputs[1].value.trim(),
          notas: inputs[2].value.trim() || null,
        };
      }).filter(it => it.medicamento && it.dosis);

      const payload = {
        diagnostico: diagInput.value.trim() || null,
        notas: null,
        items
      };
      try {
        const res = await fetch(API.guardarReceta(citaId), {
          method: 'POST',
          headers: { 
            'Content-Type':'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          credentials: 'same-origin',
          body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (!res.ok || !json.success) throw new Error(json.message || 'Error guardando receta');
        alert('Receta guardada correctamente');
      } catch(e) {
        console.error('Error guardando receta:', e);
        alert('No se pudo guardar la receta: ' + e.message);
      }
    });

    // Exportar PDF
    pdfBtn.addEventListener('click', async () => {
      // Cargar librería html2pdf si no está
      if (!window.html2pdf) {
        await new Promise((resolve) => {
          const s = el('script', { src:'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js' });
          s.onload = resolve;
          document.body.appendChild(s);
        });
      }
      const opt = {
        margin:       10,
        filename:     `Ficha-${(mascota?.data?.nombre||mascota?.mascota?.nombre||'Mascota')}-${Date.now()}.pdf`,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
      };
      window.html2pdf().set(opt).from(modal).save();
    });

    // Imprimir
    printBtn.addEventListener('click', () => {
      const w = window.open('', 'PRINT', 'height=800,width=1100');
      if (!w) return;
      const html = `<!doctype html><html><head><title>Imprimir ficha</title></head><body>${modal.outerHTML}</body></html>`;
      w.document.write(html);
      w.document.close();
      w.focus();
      w.print();
      w.close();
    });
  }

  // Delegar clicks en tarjetas/lista de citas para abrir el modal
  // Ajusta el selector según tu DOM real (ej. .cita-item[data-id])
  document.addEventListener('click', (ev) => {
    const btn = ev.target.closest('button.atender-cita-btn[data-cita-id]');
    if (btn) {
      const id = btn.getAttribute('data-cita-id');
      if (id) { ev.preventDefault(); openVetDetailModal(id); return; }
    }
    const row = ev.target.closest('tr[data-cita-id]');
    if (row && row.getAttribute('data-cita-id')) {
      ev.preventDefault();
      openVetDetailModal(row.getAttribute('data-cita-id'));
    }
  });
  // Exponer función global por si se requiere manualmente
  window.openVetDetailModal = openVetDetailModal;
})();

// public/js/views/dashboard-vet.js
// Prevenir duplicación
if (window.dashboardVetLoaded) {
    console.warn(' dashboard-vet.js ya estaba cargado');
    throw new Error('dashboard-vet.js already loaded');
}
window.dashboardVetLoaded = true;

let chartInstance = null;

function buildChartSeries(citasPorDia) {
  const diasOrdenados = [
    { en: 'Monday', es: 'Lun' },
    { en: 'Tuesday', es: 'Mar' },
    { en: 'Wednesday', es: 'Mié' },
    { en: 'Thursday', es: 'Jue' },
    { en: 'Friday', es: 'Vie' },
    { en: 'Saturday', es: 'Sáb' },
    { en: 'Sunday', es: 'Dom' },
  ];
  const map = {};
  (citasPorDia || []).forEach((item) => {
    map[item.dia] = item.total;
  });
  return {
    labels: diasOrdenados.map((d) => d.es),
    data: diasOrdenados.map((d) => map[d.en] || 0),
  };
}

function renderChart(labels, data) {
  const ctx = document.getElementById('appointmentsChart');
  if (!ctx || typeof Chart === 'undefined') return;
  if (chartInstance) chartInstance.destroy();
  const themeRoot = document.querySelector('[data-bs-theme]') || document.documentElement;
  const styles = getComputedStyle(themeRoot);
  const gridColor = styles.getPropertyValue('--bs-border-color').trim() || '#e9ecef';
  const barColor = getComputedStyle(document.documentElement).getPropertyValue('--brand').trim() || '#3A7CA5';
  const textColor = (window.getTextColor && window.getTextColor()) || '#6c757d';
  chartInstance = new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Citas', data, backgroundColor: barColor, borderRadius: 8 }] },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: { grid: { display: false }, ticks: { color: textColor } },
        y: { grid: { color: gridColor }, ticks: { color: textColor }, beginAtZero: true },
      },
      plugins: { legend: { display: false } },
    },
  });
}

function updateDashboardMetrics(d) {
  const setVal = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  };
  const setPct = (id, pct) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = (pct >= 0 ? '+' : '') + pct + '%';
    el.className = 'small ' + (pct >= 0 ? 'text-success' : 'text-danger');
  };
  setVal('citasHoy', d.citasHoy);
  setPct('porcentajeCitas', d.comparacionporcentaje?.citasHoy ?? 0);
  setVal('citasCompletadas', d.citasCompletadas);
  setPct('porcentajeCitasCompletadas', d.comparacionporcentaje?.citasCompletadas ?? 0);
  setVal('consultasRealizadas', d.consultasRealizadas || 0);
  setPct('porcentajeConsultas', d.comparacionporcentaje?.consultasRealizadas ?? 0);
  setVal('mascotasAtendidas', d.mascotasAtendidas || 0);
  setPct('porcentajeMascotas', d.comparacionporcentaje?.mascotasAtendidas ?? 0);
}

function renderActividades() {
  const container = document.getElementById('actividadReciente');
  if(!container) return;

  // Mostrar loading
  container.innerHTML = `
    <div class="text-center text-body-secondary py-4">
      <div class="spinner-border spinner-border-sm me-2" role="status"></div>
      Cargando citas disponibles...
    </div>
  `;

  fetch('/vet-dashboard/citas-disponibles')
    .then(async (res) => {
      const text = await res.text();
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return JSON.parse(text);
    })
    .then(data => {
      container.innerHTML = '';

      if (!data.citas || data.citas.length === 0) {
        container.innerHTML = `
          <div class="text-center text-body-secondary py-4">
            <i class="bi bi-calendar-x fs-4 d-block mb-2 opacity-50"></i>
            <div class="small">No hay citas disponibles</div>
            <small class="text-muted">Las nuevas citas aparecerán aquí automáticamente</small>
          </div>
        `;
        return;
      }

      // Limitar el número de citas visibles sin scroll excesivo
      const citasToShow = data.citas.slice(0, 5); // Máximo 5 citas para evitar overflow
      
      citasToShow.forEach((cita) => {
        const row = document.createElement('div');
        row.className = 'cita-item border rounded p-3 bg-light';
        
        const statusClass = cita.status === 'confirmada' ? 'bg-success' : 'bg-warning';
        const statusText = cita.status === 'confirmada' ? 'Confirmada' : 'Pendiente';

        row.innerHTML = `
          <div class="d-flex justify-content-between align-items-start mb-2">
            <div class="flex-grow-1">
              <strong class="h6 mb-1 text-primary">${cita.mascota?.nombre || 'N/A'}</strong>
              <div class="small text-body-secondary">${cita.mascota?.raza || 'N/A'} • ${cita.mascota?.especie || 'N/A'}</div>
            </div>
            <div class="text-end">
              <span class="badge ${statusClass}">${statusText}</span>
            </div>
          </div>
          
          <div class="small text-body-secondary mb-2">
            <i class="bi bi-person me-1"></i><strong>Dueño:</strong> ${cita.propietario || 'Cliente'}
          </div>
          
          <div class="small text-body-secondary mb-2">
            <i class="bi bi-clipboard-check me-1"></i><strong>Servicio:</strong> ${cita.servicio?.nombre || 'Consulta'}
          </div>
          
          <div class="small text-body-secondary mb-3">
            <i class="bi bi-clock me-1"></i><strong>Hora:</strong> ${cita.hora || 'N/A'} 
            <i class="bi bi-calendar ms-2 me-1"></i><strong>Fecha:</strong> ${cita.fecha || 'N/A'}
          </div>
          
          <button 
            class="btn btn-primary btn-sm w-100" 
            onclick="asignarCita(${cita.id})"
          >
            <i class="bi bi-plus-circle me-1"></i>Tomar esta cita
          </button>
        `;
        container.appendChild(row);
      });

      // Mostrar indicador si hay más citas
      if (data.citas.length > 5) {
        const moreIndicator = document.createElement('div');
        moreIndicator.className = 'text-center text-body-secondary small mt-2';
        moreIndicator.innerHTML = `<i class="bi bi-chevron-down"></i> ${data.citas.length - 5} más citas disponibles`;
        container.appendChild(moreIndicator);
      }
    })
    .catch(error => {
      console.error('Error al cargar citas disponibles:', error);
      container.innerHTML = `
        <div class="text-center text-danger py-3">
          <i class="bi bi-exclamation-triangle fs-4 d-block mb-2"></i>
          <div class="small">Error al cargar citas</div>
          <small class="text-muted">${error.message}</small>
        </div>
      `;
    });
}

// Función para abrir el modal de consulta manual
function abrirModalConsultaManual() {
    // Establecer hora actual automáticamente
    const now = new Date();
    const horaActual = now.toTimeString().slice(0, 5); // Formato HH:MM
    document.getElementById('horaConsulta').value = horaActual;
    
    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById('modalConsultaManual'));
    modal.show();
}

// Función para crear consulta manual
function crearConsultaManual() {
    const form = document.getElementById('formConsultaManual');
    const formData = new FormData(form);
    
    //  VALIDAR CAMPOS REQUERIDOS
    const nombreMascota = formData.get('nombre_mascota');
    const nombreCliente = formData.get('nombre_cliente');
    const apellidoCliente = formData.get('apellido_cliente');
    const telefonoCliente = formData.get('telefono_cliente');
    const especie = formData.get('especie');
    
    if (!nombreMascota || !nombreCliente || !apellidoCliente || !telefonoCliente || !especie) {
        Swal.fire({
            icon: 'error',
            title: 'Campos requeridos',
            text: 'Por favor completa todos los campos obligatorios',
            timer: 3000,
            showConfirmButton: false
        });
        return;
    }

    // Validar formato de teléfono (10 dígitos)
    const telefonoRegex = /^[0-9]{10}$/;
    if (!telefonoRegex.test(telefonoCliente)) {
        Swal.fire({
            icon: 'error',
            title: 'Teléfono inválido',
            text: 'El teléfono debe tener exactamente 10 dígitos',
            timer: 3000,
            showConfirmButton: false
        });
        return;
    }

    // Mostrar loading
    const submitBtn = document.getElementById('btnCrearConsulta');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creando...';
    submitBtn.disabled = true;

    fetch('/vet-dashboard/crear-consulta-manual', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            nombre_mascota: nombreMascota,
            nombre_cliente: nombreCliente,
            apellido_cliente: apellidoCliente,
            telefono_cliente: telefonoCliente,
            especie: especie,
            raza: formData.get('raza'),
            tipo_servicio: formData.get('tipo_servicio'),
            procedimiento: formData.get('procedimiento'),
            hora: formData.get('hora'),
            estado: formData.get('estado')
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalConsultaManual'));
            modal.hide();
            
            // Mostrar mensaje de éxito
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: 'Consulta manual creada correctamente',
                timer: 3000,
                showConfirmButton: false
            });
            
            // Limpiar formulario
            form.reset();
            
            // Actualizar la tabla de actividades
            actualizarTablaActividades();
            
        } else {
            throw new Error(data.message || 'Error al crear la consulta');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'No se pudo crear la consulta manual',
            timer: 3000,
            showConfirmButton: false
        });
    })
    .finally(() => {
        // Restaurar botón
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Función para limpiar el formulario cuando se cierra el modal
function limpiarFormularioConsulta() {
    const form = document.getElementById('formConsultaManual');
    if (form) {
        form.reset();
    }
    
    // Restablecer hora actual de manera segura
    const horaInput = document.getElementById('horaConsulta');
    if (horaInput) {
        const now = new Date();
        const horaActual = now.toTimeString().slice(0, 5);
        horaInput.value = horaActual;
    }
}
// Función para asignar una cita al veterinario actual
function asignarCita(citaId) {
  fetch('/vet-dashboard/asignar-cita', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ cita_id: citaId })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      // Mostrar mensaje de éxito
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });
      
      Toast.fire({
        icon: 'success',
        title: 'Cita asignada correctamente'
      });

      // Recargar las citas disponibles
      renderActividades();
      
      // Si estamos en la sección de actividades, actualizarla
      const currentSection = location.pathname.split('/').pop();
      if (currentSection === 'actividad') {
        actualizarTablaActividades();
      }
    } else {
      throw new Error(data.message || 'Error al asignar la cita');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'No se pudo asignar la cita',
      timer: 3000,
      showConfirmButton: false
    });
  });
}

// Función para cambiar estado de actividades
function cambiarEstadoActividadSimple(citaId, nuevoEstado, linkElement) {
    const dropdown = linkElement.closest('.dropdown');
    const button = dropdown.querySelector('.dropdown-toggle');
    const estadoAnterior = button.textContent.trim();
    
    // Actualizar visualmente inmediatamente
    button.textContent = nuevoEstado;
    button.className = `btn btn-sm ${getEstadoClase(nuevoEstado)} dropdown-toggle position-relative`;
    
    // Cerrar dropdown
    const dropdownInstance = bootstrap.Dropdown.getInstance(button);
    if (dropdownInstance) {
        dropdownInstance.hide();
    }
    
    // Hacer la petición al servidor
    fetch('/vet-dashboard/actualizar-estado-actividad', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            cita_id: citaId,
            estado: nuevoEstado
        })
    })
    .then(async (res) => {
        const data = await res.json();
        if (!res.ok || !data.success) {
            throw new Error(data.message || 'Error del servidor');
        }
        return data;
    })
    .then(data => {
        // Éxito - mostrar notificación
        Swal.fire({
            icon: 'success',
            title: '✅ Estado actualizado',
            text: 'El estado se ha cambiado correctamente',
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
        
        // Actualizar estadísticas
        actualizarEstadisticasActividades();
    })
    .catch(error => {
        console.error('❌ Error cambiando estado:', error);
        
        // Revertir cambios visuales
        button.textContent = estadoAnterior;
        button.className = `btn btn-sm ${getEstadoClase(estadoAnterior)} dropdown-toggle position-relative`;
        
        // Mostrar error
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'No se pudo actualizar el estado',
            timer: 3000,
            showConfirmButton: false
        });
    });
}

// Función para obtener la clase CSS según el estado
function getEstadoClase(estado) {
    switch(estado?.toLowerCase()) {
        case 'completada':
            return 'bg-success';
        case 'en_progreso':
            return 'bg-warning';
        case 'pendiente':
            return 'bg-secondary';
        case 'confirmada':
            return 'bg-info';
        case 'cancelada':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

// Función para actualizar estadísticas de actividades
function actualizarEstadisticasActividades() {
    console.log('Actualizando estadísticas...');
    
    const actividades = document.querySelectorAll('#actividadHoyBody tr');
    let total = 0;
    let canceladas = 0;
    let completadas = 0;
    
    actividades.forEach((tr, index) => {
        // Saltar filas vacías o de "no hay actividades"
        if (tr.querySelector('td[colspan]')) {
            console.log(`    Saltando fila ${index + 1}: tiene colspan`);
            return;
        }
        
        if (tr.cells.length < 8) {
            console.log(`    Saltando fila ${index + 1}: solo tiene ${tr.cells.length} celdas`);
            return;
        }
        
        total++;
        
        // BUSCAR EL ESTADO EN EL DROPDOWN BUTTON (columna 8)
        const dropdownButton = tr.querySelector('td:nth-child(8) .dropdown-toggle');
        if (dropdownButton) {
            const estado = dropdownButton.textContent.trim().toLowerCase();
            console.log(`    Fila ${index + 1}: Estado = "${estado}"`);
            
            if (estado === 'cancelada') {
                canceladas++;
                console.log(`    Contabilizada como CANCELADA`);
            } else if (estado === 'completada') {
                completadas++;
                console.log(`    Contabilizada como COMPLETADA`);
            }
        } else {
            console.log(`    Fila ${index + 1}: No se encontró dropdown button`);
            
            // Intentar buscar el estado directamente en la celda
            const celdaEstado = tr.querySelector('td:nth-child(8)');
            if (celdaEstado) {
                console.log(`    Contenido celda estado: "${celdaEstado.textContent.trim()}"`);
            }
        }
    });
    
    // Actualizar la interfaz
    const totalEl = document.querySelector('.row.text-center .col-4:nth-child(1) .h5');
    const canceladasEl = document.querySelector('.row.text-center .col-4:nth-child(2) .h5');
    const completadasEl = document.querySelector('.row.text-center .col-4:nth-child(3) .h5');
    
    if (totalEl) {
        totalEl.textContent = total;
        console.log(`    Total actualizado: ${total}`);
    }
    if (canceladasEl) {
        canceladasEl.textContent = canceladas;
        canceladasEl.className = 'h5 mb-0 text-danger';
        console.log(`    Canceladas actualizadas: ${canceladas}`);
    }
    if (completadasEl) {
        completadasEl.textContent = completadas;
        completadasEl.className = 'h5 mb-0 text-success';
        console.log(`    Completadas actualizadas: ${completadas}`);
    }

    console.log(` ESTADÍSTICAS FINALES: Total: ${total}, Canceladas: ${canceladas}, Completadas: ${completadas}`);
}

function actualizarTablaActividades() {
  const tbody = document.getElementById('actividadHoyBody');

  if (!tbody) {
    console.error(' No se encontró actividadHoyBody');
    return;
  }

  fetch('/vet-dashboard/actividades-hoy')
    .then(async (res) => {
      const text = await res.text();
      if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<!doctype')) {
        throw new Error('El servidor devolvió HTML en lugar de JSON');
      }
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return JSON.parse(text);
    })
    .then(data => {
      if (!data.actividades || data.actividades.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="9" class="text-center text-body-secondary py-5">
              <div class="py-3">
                <i class="bi bi-calendar-check fs-1 text-body-secondary opacity-50 d-block mb-3"></i>
                <h6 class="text-body-secondary">No hay actividades registradas para hoy</h6>
                <small class="text-body-secondary">Las actividades aparecerán aquí conforme se realicen procedimientos</small>
              </div>
            </td>
          </tr>
        `;
      } else {
        tbody.innerHTML = data.actividades.map(actividad => {
          const estadoClase = getEstadoClase(actividad.estado);
          
          // Determinar el texto para la columna "TIPO DE ACTIVIDAD"
          let tipoTexto = actividad.tipo_actividad || 'N/A';
          if (actividad.tipo === 'consulta') {
            tipoTexto = 'Consulta';
          } else if (actividad.tipo === 'cita') {
            tipoTexto = 'Cita';
          }
          
          // Mostrar botón lápiz para cualquier registro atendible (cita o consulta)
          // Si quieres limitar por estado, añade condición sobre actividad.estado
          const atendible = (actividad.tipo === 'cita' || actividad.tipo === 'consulta');
          const citaId = actividad.id;
          // Mostrar botón si es atendible y estado es confirmada o pendiente (amplía según necesidad)
          const mostrarBotonAtender = atendible && ['confirmada','pendiente','en_progreso'].includes(actividad.estado);
          return `
            <tr ${atendible ? `data-cita-id="${citaId}"` : ''}>
              <td>${actividad.hora || 'N/A'}</td>
              <td>${actividad.paciente || 'N/A'}</td>
              <td>
                <div class="fw-semibold">${actividad.propietario || 'N/A'} ${actividad.apellido || ''}</div>
                <small class="text-body-secondary">${actividad.telefono || 'No disponible'}</small>
              </td>
              <td>${actividad.especie || 'N/A'}</td>
              <td>${actividad.raza || 'N/A'}</td>
              <td>
                ${tipoTexto}
                ${actividad.tipo === 'consulta' ? '<span class="badge bg-info ms-1">Manual</span>' : ''}
              </td>
              <td>${actividad.procedimiento || 'N/A'}</td>
              <td>
                <div class="dropdown">
                  <button class="btn btn-sm ${estadoClase} dropdown-toggle position-relative" 
                          type="button" data-bs-toggle="dropdown" 
                          aria-expanded="false"
                          style="min-width: 120px;">
                    ${actividad.estado || 'N/A'}
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item ${actividad.estado === 'pendiente' ? 'active' : ''}" 
                         href="#" onclick="cambiarEstadoActividadSimple(${actividad.id}, 'pendiente', this)">
                        <span class="badge bg-secondary me-2">●</span>Pendiente
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item ${actividad.estado === 'confirmada' ? 'active' : ''}" 
                         href="#" onclick="cambiarEstadoActividadSimple(${actividad.id}, 'confirmada', this)">
                        <span class="badge bg-info me-2">●</span>Confirmada
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item ${actividad.estado === 'completada' ? 'active' : ''}" 
                         href="#" onclick="cambiarEstadoActividadSimple(${actividad.id}, 'completada', this)">
                        <span class="badge bg-success me-2">●</span>Completada
                      </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <a class="dropdown-item ${actividad.estado === 'cancelada' ? 'active' : ''}" 
                         href="#" onclick="cambiarEstadoActividadSimple(${actividad.id}, 'cancelada', this)">
                        <span class="badge bg-danger me-2">●</span>Cancelada
                      </a>
                    </li>
                  </ul>
                </div>
              </td>
              <td>
                ${mostrarBotonAtender ? `
                  <button class="btn btn-outline-secondary btn-sm atender-cita-btn" type="button" data-cita-id="${citaId}" title="Atender ${actividad.tipo === 'consulta' ? 'consulta' : 'cita'}">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                ` : ''}
              </td>
            </tr>
          `;
        }).join('');
        
        // Actualizar estadísticas
        actualizarEstadisticasActividades();
      }
    })
    .catch(error => {
      console.error(' Error cargando actividades:', error);
      tbody.innerHTML = `
        <tr>
          <td colspan="9" class="text-center text-danger py-4">
            Error al cargar actividades: ${error.message}
          </td>
        </tr>
      `;
    });
}

// Función para abrir el modal de atención de cita
function abrirModalAtenderCita(citaId) {
    // Mostrar loading
    document.getElementById('modalAtenderCitaBody').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-body-secondary">Cargando información de la cita...</p>
        </div>
    `;
    
    // Obtener datos de la cita e historial
    fetch(`/vet-dashboard/get-cita-detalle/${citaId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderModalAtenderCita(data.cita, data.historial);
            } else {
                throw new Error(data.message || 'Error al cargar la cita');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('modalAtenderCitaBody').innerHTML = `
                <div class="text-center text-danger py-4">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <p class="mt-2">Error al cargar la cita: ${error.message}</p>
                </div>
            `;
        });
    
    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById('modalAtenderCita'));
    modal.show();
}

// Función para renderizar el contenido del modal
function renderModalAtenderCita(cita, historial) {
    const modalBody = document.getElementById('modalAtenderCitaBody');
    
    modalBody.innerHTML = `
        <div class="row">
            <!-- Columna izquierda - Información de la mascota -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información de la Mascota</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-4"><strong>Nombre:</strong></div>
                            <div class="col-8">${cita.mascota_nombre || 'N/A'}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Especie:</strong></div>
                            <div class="col-8">${cita.mascota_especie || 'N/A'}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Raza:</strong></div>
                            <div class="col-8">${cita.mascota_raza || 'N/A'}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Propietario:</strong></div>
                            <div class="col-8">${cita.propietario_nombre || 'N/A'}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><strong>Teléfono:</strong></div>
                            <div class="col-8">${cita.propietario_telefono || 'No disponible'}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Procedimiento -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-clipboard-check me-2"></i>Procedimiento</h6>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" id="procedimientoCita" rows="6" 
                                  placeholder="Describa el procedimiento realizado, diagnóstico, tratamiento, etc.">${cita.notas || ''}</textarea>
                        <small class="text-muted">Puede editar el procedimiento antes de finalizar la cita.</small>
                    </div>
                </div>
            </div>
            
            <!-- Columna derecha - Historial -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Historial de la Mascota</h6>
                    </div>
                    <div class="card-body">
                        ${historial.length > 0 ? `
                            <div style="max-height: 400px; overflow-y: auto;">
                                ${historial.map(registro => `
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <strong class="text-primary">${registro.fecha}</strong>
                                            <span class="badge ${getEstadoClase(registro.estado)}">${registro.estado}</span>
                                        </div>
                                        <div class="small text-body-secondary mb-1">${registro.tipo_actividad}</div>
                                        <div class="small">${registro.procedimiento || 'Sin notas'}</div>
                                    </div>
                                `).join('')}
                            </div>
                        ` : `
                            <div class="text-center text-body-secondary py-4">
                                <i class="bi bi-inbox fs-1 opacity-50"></i>
                                <p class="mt-2">No hay historial previo</p>
                            </div>
                        `}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botones de acción -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cerrar
                    </button>
                    <button type="button" class="btn btn-success" onclick="finalizarCita(${cita.id})">
                        <i class="bi bi-check-circle me-1"></i>Finalizar Cita
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Función para finalizar la cita
function finalizarCita(citaId) {
    const procedimiento = document.getElementById('procedimientoCita').value;
    
    // Mostrar loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Finalizando...';
    btn.disabled = true;

    fetch('/vet-dashboard/finalizar-cita', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            cita_id: citaId,
            procedimiento: procedimiento
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalAtenderCita'));
            modal.hide();
            
            // Mostrar notificación
            Swal.fire({
                icon: 'success',
                title: '¡Cita Finalizada!',
                text: 'La cita se ha marcado como completada',
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            
            // Actualizar la tabla
            actualizarTablaActividades();
            
        } else {
            throw new Error(data.message || 'Error al finalizar la cita');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'No se pudo finalizar la cita',
            timer: 3000,
            showConfirmButton: false
        });
    })
    .finally(() => {
        // Restaurar botón
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Función auxiliar para cambiar estado desde el dropdown
function cambiarEstadoDesdeDropdown(citaId, nuevoEstado, linkElement) {
  const dropdown = linkElement.closest('.dropdown');
  const button = dropdown.querySelector('.dropdown-toggle');
  
  // Guardar el estado anterior
  const estadoAnterior = button.textContent.trim();
  
  // Actualizar visualmente inmediatamente
  button.textContent = nuevoEstado;
  button.className = `btn btn-sm ${getEstadoClase(nuevoEstado)} dropdown-toggle position-relative`;
  
  // Cerrar el dropdown
  const dropdownInstance = bootstrap.Dropdown.getInstance(button);
  if (dropdownInstance) {
    dropdownInstance.hide();
  }
  
  // Llamar a la función principal de cambio de estado
  cambiarEstadoActividad(citaId, nuevoEstado, estadoAnterior, button);
}

// Aplicar filtros del historial
function aplicarFiltrosHistorial() {
  historialCurrentPage = 1; // Reset a la primera página
  fetchHistorial();
}

// Cambiar página del historial
function cambiarPaginaHistorial(delta) {
  const nuevaPagina = historialCurrentPage + delta;
  fetchHistorial({ pagina: nuevaPagina });
}

// Función para actualizar el estado de una actividad
function actualizarEstadoActividad(selectElement) {
  const citaId = selectElement.dataset.citaId;
  const nuevoEstado = selectElement.value;

  fetch('/vet-dashboard/actualizar-actividad', {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      cita_id: citaId,
      estado: nuevoEstado
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      // Actualizar la tabla de actividades
      actualizarTablaActividades();
    } else {
      throw new Error(data.message || 'Error al actualizar el estado');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'No se pudo actualizar el estado',
      timer: 3000,
      showConfirmButton: false
    });
    // Revertir el cambio en el select
    selectElement.value = selectElement.dataset.lastValue;
  });
}

// Variables para el estado de la paginación del historial
let historialCurrentPage = 1;
const HISTORIAL_PER_PAGE = 8;

// Obtener historial (actividades pasadas) y renderizar en la tabla
function fetchHistorial(params = {}) {
  const tbody = document.getElementById('historialBody');
  if (!tbody) return;

  // Asignar valores por defecto a la paginación
  params.pagina = params.pagina || historialCurrentPage;
  params.por_pagina = params.por_pagina || HISTORIAL_PER_PAGE;

  // Obtener valores de los filtros de fecha si existen
  const desdeInput = document.getElementById('historial-desde');
  const hastaInput = document.getElementById('historial-hasta');
  
  if (desdeInput?.value) params.desde = desdeInput.value;
  if (hastaInput?.value) params.hasta = hastaInput.value;

  // Construir query string
  const query = new URLSearchParams(params).toString();
  // Llamar al endpoint API que devuelve historial
  const url = '/vet-dashboard/historial/data' + (query ? `?${query}` : '');

  fetch(url)
    .then(res => res.json())
    .then(data => {
      const actividades = data.actividades || [];
      if (actividades.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="5" class="text-center text-body-secondary py-4">No hay historial disponible</td>
          </tr>
        `;
        actualizarEstadoPaginacion(data.meta);
        return;
      }

      tbody.innerHTML = '';
      actividades.forEach(act => {
        const tr = document.createElement('tr');
        const estadoClase = getEstadoClase(act.estado);
        tr.innerHTML = `
          <td>${act.fecha} ${act.hora}</td>
          <td>${act.paciente}</td>
          <td>${act.propietario || ''}</td>
          <td>${act.tipo_actividad || ''}</td>
          <td><span class="badge ${estadoClase}">${act.estado || ''}</span></td>
        `;
        tbody.appendChild(tr);
      });

      // Actualizar controles de paginación
      actualizarEstadoPaginacion(data.meta);
    })
    .catch(err => {
      console.error('Error al obtener historial:', err);
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-3">Error al cargar historial</td></tr>`;
    });
}

// Función auxiliar para obtener la clase CSS según el estado
function getEstadoClase(estado) {
  switch(estado?.toLowerCase()) {
    case 'completada':
      return 'bg-success';
    case 'cancelada':
      return 'bg-danger';
    case 'confirmada':
      return 'bg-info';
    case 'pendiente':
      return 'bg-secondary';
    default:
      return 'bg-secondary';
  }
}

// Actualizar estado y controles de paginación
function actualizarEstadoPaginacion(meta) {
  if (!meta) return;

  const desde = ((meta.current_page - 1) * meta.per_page) + 1;
  const hasta = Math.min(meta.current_page * meta.per_page, meta.total || 0);
  const total = meta.total || 0;

  // Actualizar textos de paginación
  document.getElementById('historial-desde-registro').textContent = desde;
  document.getElementById('historial-hasta-registro').textContent = hasta;
  document.getElementById('historial-total').textContent = total;

  // Actualizar estado de botones
  const prevBtn = document.getElementById('historial-prev');
  const nextBtn = document.getElementById('historial-next');
  
  if (prevBtn) prevBtn.disabled = meta.current_page <= 1;
  if (nextBtn) nextBtn.disabled = meta.current_page >= meta.last_page;

  // Actualizar página actual
  historialCurrentPage = meta.current_page;
}


function setTodayTexts() {
  const today = new Date();
  const pad = (n) => String(n).padStart(2, '0');
  const dateStr = `${pad(today.getDate())}/${pad(today.getMonth() + 1)}/${today.getFullYear()}`;
  const dateEl = document.getElementById('todayDate');
  if (dateEl) dateEl.textContent = dateStr;
  const textEl = document.getElementById('todayText');
  if (textEl) textEl.textContent = 'Aquí está el resumen de hoy';
}

// Construir parámetros de filtro para exportar o llamadas al backend
function getReportFilters() {
  const periodo = document.getElementById('filtro-periodo')?.value || 'este-mes';
  const desde = document.getElementById('filtro-desde')?.value || '';
  const hasta = document.getElementById('filtro-hasta')?.value || '';
  const params = new URLSearchParams();
  params.set('periodo', periodo);
  if (desde) params.set('desde', desde);
  if (hasta) params.set('hasta', hasta);
  return params.toString();
}

// Disparar exportación: navega a la ruta de descarga con los filtros actuales
function exportResumen(format) {
  // format: 'pdf'
  const qs = getReportFilters();
  const base = '/vet-reportes/export';
  const url = `${base}/${format}` + (qs ? `?${qs}` : '');
  // Usar window.location para forzar la descarga (GET)
  window.location.href = url;
}

function attachReportHandlers() {
  const exportPdf = document.getElementById('export-pdf');
  const exportExcel = document.getElementById('export-excel');
  const exportMain = document.getElementById('btn-exportar-main');

  if (exportPdf) exportPdf.addEventListener('click', (e) => { e.preventDefault(); exportResumen('pdf'); });
  if (exportExcel) exportExcel.addEventListener('click', (e) => { e.preventDefault(); exportResumen('excel'); });

  // If user clicks main export button, open dropdown menu (for accessibility fallback)
  if (exportMain) exportMain.addEventListener('click', (e) => {
    // trigger the PDF export by default when clicking main button
    exportResumen('pdf');
  });
}

function renderSection(section, data) {
  const mainContent = document.getElementById('mainContent');
  if (!mainContent) return;
  mainContent.innerHTML = '';
  
  if (section === 'home') {
    const userName = mainContent.dataset.usuarioNombre || 'Usuario';
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">¡Bienvenido, ${userName}! 👋</h1>
        <p class="text-body-secondary small" id="todayText">Aquí está el resumen de hoy</p>
      </div>
      <div class="row g-3 g-lg-4 mb-4">
        <!-- Métricas -->
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-soft p-4">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="text-body-secondary small mb-1">Citas de Hoy</div>
                <div class="h4 mb-1" id="citasHoy">0</div>
                <div class="small text-success" id="porcentajeCitas">+0%</div>
              </div>
              <div class="icon-bubble bg-opacity-25 bg-primary-subtle text-primary"><i class="bi bi-calendar-check"></i></div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-soft p-4">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="text-body-secondary small mb-1">Citas Completadas</div>
                <div class="h4 mb-1" id="citasCompletadas">0</div>
                <div class="small text-success" id="porcentajeCitasCompletadas">+0%</div>
              </div>
              <div class="icon-bubble bg-opacity-25 bg-success text-success"><i class="bi bi-clipboard-check"></i></div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-soft p-4">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="text-body-secondary small mb-1">Consultas Realizadas</div>
                <div class="h4 mb-1" id="consultasRealizadas">0</div>
                <div class="small text-success" id="porcentajeConsultas">+0%</div>
              </div>
              <div class="icon-bubble bg-opacity-25 bg-info text-info"><i class="bi bi-heart-pulse"></i></div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <div class="card card-soft p-4">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <div class="text-body-secondary small mb-1">Mascotas Atendidas Hoy</div>
                <div class="h4 mb-1" id="mascotasAtendidas">0</div>
                <div class="small text-success" id="porcentajeMascotas">+0%</div>
              </div>
              <div class="icon-bubble bg-opacity-25 bg-warning text-warning"><i class="bi bi-heart"></i></div>
            </div>
          </div>
        </div>
      </div>
      <div class="row g-3 g-lg-4 mb-4">
        <!-- Gráfica -->
        <div class="col-12 col-lg-8">
          <div class="card card-soft p-4 h-100">
            <h3 class="h6 mb-3">Citas Esta Semana</h3>
            <div class="chart-container" style="height: 300px; position: relative;">
              <canvas id="appointmentsChart"></canvas>
            </div>
          </div>
        </div>
        
        <!-- Citas disponibles -->
        <div class="col-12 col-lg-4">
          <div class="card card-soft p-4 h-100 d-flex flex-column">
            <h3 class="h6 mb-3">Citas disponibles</h3>
            <div class="flex-grow-1 overflow-hidden d-flex flex-column">
              <div class="citas-container flex-grow-1 overflow-auto" style="max-height: 300px;">
                <div class="vstack gap-3" id="actividadReciente">
                  <div class="text-center text-body-secondary py-4">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    Cargando citas disponibles...
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
    
    // Actualizamos la función renderActividades para mejor manejo del scroll
    setTimeout(() => {
      renderActividades();
    }, 100);

  } else if (section === 'actividad') {
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">Actividad de Hoy.</h1>
        <p class="text-body-secondary small">Registro de actividades y procedimientos del día actual</p>
      </div>
      
      <!-- Botón para crear consulta manual -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div></div>
        <button class="btn btn-primary" onclick="abrirModalConsultaManual()">
          <i class="bi bi-plus-circle me-2"></i>Crear Consulta
        </button>
      </div>
      
      <div class="card card-soft p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h3 class="h6 mb-0">📋 Registro de Actividades</h3>
          <span class="badge bg-primary">Hoy</span>
        </div>
        
        <div class="table-responsive">
          <table class="table align-middle">
            <thead class="table-light">
              <tr>
                <th>HORA</th>
                <th>PACIENTE</th>
                <th>PROPIETARIO</th> 
                <th>ESPECIE</th>
                <th>RAZA</th>
                <th>TIPO DE ACTIVIDAD</th>
                <th>PROCEDIMIENTO</th>
                <th>ESTADO</th>
              </tr>
            </thead>
            <tbody id="actividadHoyBody">
              <tr>
                <td colspan="8" class="text-center text-body-secondary py-5">
                  <div class="py-3">
                    <i class="bi bi-calendar-check fs-1 text-body-secondary opacity-50 d-block mb-3"></i>
                    <h6 class="text-body-secondary">No hay actividades registradas para hoy</h6>
                    <small class="text-body-secondary">Las actividades aparecerán aquí conforme se realicen procedimientos</small>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div class="mt-3 pt-3 border-top">
          <div class="row text-center">
            <div class="col-4">
              <div class="text-body-secondary small">Total Actividades</div>
              <div class="h5 mb-0">0</div>
            </div>
            <div class="col-4">
              <div class="text-body-secondary small">Canceladas</div>
              <div class="h5 mb-0 text-danger">0</div>
            </div>
            <div class="col-4">
              <div class="text-body-secondary small">Completadas</div>
              <div class="h5 mb-0 text-success">0</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal para atender cita -->
      <div class="modal fade" id="modalAtenderCita" tabindex="-1" aria-labelledby="modalAtenderCitaLabel" aria-hidden="true">
          <div class="modal-dialog modal-xl">
              <div class="modal-content">
                  <div class="modal-header bg-primary text-white">
                      <h5 class="modal-title" id="modalAtenderCitaLabel">
                          <i class="bi bi-clipboard-plus me-2"></i>Atender Cita
                      </h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body" id="modalAtenderCitaBody">
                      <!-- Contenido dinámico -->
                  </div>
              </div>
          </div>
      </div>

            <!-- Modal para consulta manual -->
      <div class="modal fade" id="modalConsultaManual" tabindex="-1" aria-labelledby="modalConsultaManualLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg">
              <div class="modal-content">
                  <div class="modal-header">
                      <h5 class="modal-title" id="modalConsultaManualLabel">
                          <i class="bi bi-plus-circle me-2"></i>Crear Consulta
                      </h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="limpiarFormularioConsulta()"></button>
                  </div>
                  <div class="modal-body">
                      <form id="formConsultaManual">
                          <div class="row g-3">
                              <!-- Columna Izquierda -->
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="nombre_mascota" class="form-label">Nombre de la Mascota *</label>
                                      <input type="text" class="form-control" id="nombre_mascota" name="nombre_mascota" required>
                                  </div>
                                  
                                  <div class="mb-3">
                                      <label for="nombre_cliente" class="form-label">Nombre del Cliente *</label>
                                      <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" required>
                                  </div>
                                  
                                  <div class="mb-3">
                                      <label for="apellido_cliente" class="form-label">Apellido del Cliente *</label>
                                      <input type="text" class="form-control" id="apellido_cliente" name="apellido_cliente" required>
                                  </div>
                                  
                                  <div class="mb-3">
                                      <label for="telefono_cliente" class="form-label">Teléfono del Cliente *</label>
                                      <input type="tel" class="form-control" id="telefono_cliente" name="telefono_cliente" required 
                                            pattern="[0-9]{10}" placeholder="Ej: 3141234567">
                                      <small class="text-muted">Ingresa 10 dígitos sin espacios ni guiones</small>
                                  </div>
                                  
                                  <div class="mb-3">
                                      <label for="especie" class="form-label">Especie *</label>
                                      <select class="form-select" id="especie" name="especie" required>
                                          <option value="">Seleccionar especie</option>
                                          <option value="Perro">Perro</option>
                                          <option value="Gato">Gato</option>
                                          <option value="Ave">Ave</option>
                                          <option value="Roedor">Roedor</option>
                                          <option value="Reptil">Reptil</option>
                                          <option value="Otro">Otro</option>
                                      </select>
                                  </div>
                              </div> 
                              
                              <!-- Columna Derecha -->
                              <div class="col-md-6">
                                  <div class="mb-3">
                                      <label for="raza" class="form-label">Raza</label>
                                      <input type="text" class="form-control" id="raza" name="raza" placeholder="Ej: Labrador, Siames, etc.">
                                  </div>
                                  
                                  <div class="mb-3">
                                      <label for="tipo_servicio" class="form-label">Tipo de Servicio</label>
                                      <input type="text" class="form-control" id="tipo_servicio" name="tipo_servicio" value="Consulta" readonly>
                                  </div>
                                  
                                  <div class="mb-3">
                                      <label for="horaConsulta" class="form-label">Hora</label>
                                      <input type="time" class="form-control" id="horaConsulta" name="hora" required>
                                  </div>
                                  
                                  <div class="mb-3">
                                      <label for="estado" class="form-label">Estado</label>
                                      <select class="form-select" id="estado" name="estado">
                                          <option value="pendiente">Pendiente</option>
                                          <option value="completada">Completada</option>
                                      </select>
                                  </div>
                                  
                                  <div class="mb-3">
                                      <label for="procedimiento" class="form-label">Procedimiento / Notas</label>
                                      <textarea class="form-control" id="procedimiento" name="procedimiento" rows="3" placeholder="Descripción del procedimiento a realizar..."></textarea>
                                  </div>
                              </div> 
                          </div>
                      </form>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="limpiarFormularioConsulta()">Cancelar</button>
                      <button type="button" class="btn btn-primary" id="btnCrearConsulta" onclick="crearConsultaManual()">
                          <i class="bi bi-check-circle me-2"></i>Crear Consulta
                      </button>
                  </div>
              </div>
          </div>
      </div>
    `;
} else if (section === 'historial') {
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">Historial</h1>
        <p class="text-body-secondary small">Historial de citas y procedimientos</p>
      </div>
      
      <!-- Filtros -->
      <div class="card card-soft p-4 mb-4">
        <div class="row g-3">
          <div class="col-12">
            <h3 class="h6 mb-3">Filtrar por fecha</h3>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Desde</label>
            <input type="date" class="form-control" id="historial-desde">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Hasta</label>
            <input type="date" class="form-control" id="historial-hasta">
          </div>
          <div class="col-12 col-md-4 d-flex align-items-end">
            <button class="btn btn-primary w-100" onclick="aplicarFiltrosHistorial()">
              Aplicar filtros
            </button>
          </div>
        </div>
      </div>

      <!-- Tabla de historial -->
      <div class="card card-soft p-4">
        <h3 class="h6 mb-3">Historial de Consultas</h3>
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>Propietario</th>
                <th>Tipo de actividad</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody id="historialBody">
              <tr>
                <td colspan="5" class="text-center text-body-secondary py-4">No hay historial disponible</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
          <div class="text-body-secondary small">
            Mostrando <span id="historial-desde-registro">0</span> - <span id="historial-hasta-registro">0</span> 
            de <span id="historial-total">0</span> registros
          </div>
          <div class="pagination-container">
            <div class="btn-group">
              <button class="btn btn-outline-secondary btn-sm" id="historial-prev" onclick="cambiarPaginaHistorial(-1)" disabled>
                <i class="bi bi-chevron-left"></i>
              </button>
              <button class="btn btn-outline-secondary btn-sm" id="historial-next" onclick="cambiarPaginaHistorial(1)" disabled>
                <i class="bi bi-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    `;
  } else if (section === 'reportes') {
    const userName = mainContent.dataset.usuarioNombre || 'Usuario';
    mainContent.innerHTML = `
      <!-- NO FUNCIONA ESTA SECCIÓN! -->
      <div class="mb-3">
        <h1 class="mb-1">Reportes</h1>
        <p class="text-body-secondary small">Reportes y estadísticas veterinarias</p>
      </div>
      
      <!-- Filtros -->
      <div class="card card-soft p-4 mb-4">
        <div class="row g-3 align-items-end">
          <div class="col-12">
            <h5 class="mb-3">Rango de fechas:</h5>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Periodo</label>
            <select class="form-select" id="filtro-periodo">
              <option value="este-mes" selected>Este mes</option>
              <option value="mes-anterior">Mes anterior</option>
              <option value="trimestre-actual">Trimestre actual</option>
              <option value="personalizado">Personalizado</option>
            </select>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Desde</label>
            <input type="date" class="form-control" id="filtro-desde" value="2025-09-01">
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Hasta</label>
            <input type="date" class="form-control" id="filtro-hasta" value="2025-09-15">
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label">Trabajador</label>
            <select class="form-select" id="filtro-trabajador" disabled>
              <option selected> ${userName} </option>
            </select> 
          </div>
          <div class="col-12 col-md-6 d-flex gap-2 align-items-end">
            <button class="btn btn-primary flex-fill" id="btn-aplicar-filtro">Aplicar filtro</button>

            <!-- Exportar a PDF -->
            <div class="btn-group">
              <button class="btn btn-outline-secondary" id="btn-exportar-main" type="button">
                Exportar a PDF
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Métricas principales -->
      <div class="row g-3 g-lg-4 mb-4" id="metricas-principales">
        <div class="col-12 col-md-4">
          <div class="card card-soft p-4 text-center">
            <div class="h2 text-primary mb-2" id="metric-citas">--</div>
            <div class="text-body-secondary">Citas realizadas</div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="card card-soft p-4 text-center">
            <div class="h2 text-primary mb-2" id="metric-consultas">--</div>
            <div class="text-body-secondary">Consultas realizadas</div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="card card-soft p-4 text-center">
            <div class="h2 text-primary mb-2" id="metric-mascotas">--</div>
            <div class="text-body-secondary">Mascotas atendidas</div>
          </div>
        </div>
      </div>

      <!-- Gráficas -->
      <div class="row g-3 g-lg-4 mb-4">
        <div class="col-12 col-md-6">
          <div class="card card-soft p-4">
            <h5 class="mb-3">Mascotas atendidas</h5>
            <div class="chart-container" style="height: 300px; position: relative;">
              <canvas id="graficaMascotasAtendidas"></canvas>
              <div id="mensaje-sin-datos-mascotas" class="d-none text-center p-5">
                <div class="text-body-secondary mb-2">
                  <i class="bi bi-bar-chart" style="font-size: 3rem;"></i>
                </div>
                <p class="text-muted">No hay datos de mascotas atendidas para el periodo seleccionado</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="card card-soft p-4">
            <h5 class="mb-3">Mascotas por especie</h5>
            <div class="chart-container" style="height: 300px; position: relative;">
              <canvas id="graficaMascotasEspecie"></canvas>
              <div id="mensaje-sin-datos-especies" class="d-none text-center p-5">
                <div class="text-body-secondary mb-2">
                  <i class="bi bi-pie-chart" style="font-size: 3rem;"></i>
                </div>
                <p class="text-muted">No hay datos de especies para el periodo seleccionado</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabla de resumen -->
      <div class="card card-soft p-4">
        <h5 class="mb-3">Resumen de citas</h5>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Estado</th>
                <th>Cantidad</th>
                <th>Porcentaje</th>
                <th>Tendencia</th>
              </tr>
            </thead>
            <tbody id="tabla-resumen-citas">
              <tr id="fila-sin-datos">
                <td colspan="4" class="text-center text-muted py-4">
                  <i class="bi bi-info-circle me-2"></i>
                  Selecciona un rango de fechas para ver el resumen de citas
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    `;
  } else if (section === 'configuracion') { 
    mainContent.innerHTML = `
      <div class="mb-3">
        <h1 class="mb-1">Configuración</h1>
        <p class="text-body-secondary small">Configuración del panel</p>
      </div>
      <div class="card card-soft p-4">
        <h3 class="h6 mb-3">Preferencias</h3>
        <div class="vstack gap-3">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="notificacionesSwitch" checked>
            <label class="form-check-label" for="notificacionesSwitch">Notificaciones de urgencias</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="recordatoriosSwitch" checked>
            <label class="form-check-label" for="recordatoriosSwitch">Recordatorios de citas</label>
          </div>
        </div>
      </div>
    `; 
  }
}

function markActive(section) {
  document.querySelectorAll('.nav-btn').forEach((b) => b.classList.remove('active'));
  const btn = document.querySelector(`.nav-btn [data-section="${section}"]`)?.closest('.nav-btn') ||
    document.querySelector(`.nav-btn[data-section="${section}"]`);
  if (btn) btn.classList.add('active');
}

function initNavHandlers() {
  document.querySelectorAll('.nav-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const section = btn.dataset.section || btn.querySelector('[data-section]')?.dataset.section;
      if (!section) return;
      document.querySelectorAll('.nav-btn').forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');

      if (section === 'home') {
        fetch('/vet-dashboard/data/home')
          .then(async (res) => {
            const text = await res.text();
            console.log('🔍 Home response:', text.substring(0, 200));
            if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<!doctype')) {
              throw new Error('El servidor devolvió HTML en lugar de JSON');
            }
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return JSON.parse(text);
          })
          .then((data) => {
            renderSection('home', data);
            const { labels, data: series } = buildChartSeries(data.citasPorDia);
            updateDashboardMetrics(data);
            renderChart(labels, series);
            renderActividades();
            setTodayTexts();
            history.pushState({ section: 'home' }, '', '/vet-dashboard/home');
          })
          .catch((err) => {
            console.error(' Error cargando home:', err);
            // Datos de emergencia
            const emergencyData = {
              citasHoy: 0,
              citasCompletadas: 0,
              consultasRealizadas: 0,
              mascotasAtendidas: 0,
              citasPorDia: [],
              actividades: [],
              comparacionporcentaje: { citasHoy: 0, citasCompletadas: 0, consultasRealizadas: 0, mascotasAtendidas: 0 }
            };
            renderSection('home', emergencyData);
            updateDashboardMetrics(emergencyData);
            renderChart([], []);
            renderActividades();
            setTodayTexts();
          });
        return;
      }

      // Secciones específicas que requieren carga de datos
      if (section === 'actividad') {
        renderSection('actividad');
        actualizarTablaActividades();
        history.pushState({ section: 'actividad' }, '', '/vet-dashboard/actividad');
        return;
      }

      if (section === 'historial') {
        renderSection('historial');
        fetchHistorial();
        history.pushState({ section: 'historial' }, '', '/vet-dashboard/historial');
        return;
      }

      renderSection(section);
      history.pushState({ section }, '', `/vet-dashboard/${section}`);
    });
  });
}

function handlePopState() {
  window.addEventListener('popstate', (event) => {
    const section = event.state?.section || (location.pathname.split('/').pop() || 'home');
    markActive(section);
    
    if (section === 'home') {
      fetch('/vet-dashboard/data/home') 
        .then(async (res) => {
          const text = await res.text();
          if (text.trim().startsWith('<!DOCTYPE')) throw new Error('HTML instead of JSON');
          if (!res.ok) throw new Error(`HTTP ${res.status}`);
          return JSON.parse(text);
        })
        .then((data) => {
          renderSection('home', data);
          const { labels, data: series } = buildChartSeries(data.citasPorDia);
          updateDashboardMetrics(data);
          renderChart(labels, series);
          renderActividades();
          setTodayTexts();
        })
        .catch((err) => {
          console.error('Error en popstate home:', err);
        });
    } else {
      if (section === 'actividad') {
        renderSection('actividad');
        actualizarTablaActividades();
        return;
      }

      if (section === 'historial') {
        renderSection('historial');
        fetchHistorial();
        return;
      }

      renderSection(section);
    }
  });
}

// Inicialización
(function() {
  document.addEventListener('DOMContentLoaded', () => {
    console.log(' dashboard-vet.js inicializando...');
    
    const match = location.pathname.match(/^\/vet-dashboard(?:\/([^\/?#]+))?/);
    const initialSection = (match && match[1]) ? match[1] : 'home';
    markActive(initialSection);

    if (initialSection === 'home') {
      fetch('/vet-dashboard/data/home') 
        .then(async (r) => {
          const text = await r.text();
          console.log('🔍 Initial home response:', text.substring(0, 200));
          if (text.trim().startsWith('<!DOCTYPE')) throw new Error('HTML instead of JSON');
          if (!r.ok) throw new Error(`HTTP ${r.status}`);
          return JSON.parse(text);
        })
        .then((data) => {
          renderSection('home', data);
          const { labels, data: series } = buildChartSeries(data.citasPorDia);
          updateDashboardMetrics(data);
          renderChart(labels, series);
          renderActividades();
          setTodayTexts();
          history.replaceState({ section: 'home' }, '', location.pathname);
        })
        .catch((err) => {
          console.error(' Error inicializando home:', err);
          // Datos de emergencia
          const emergencyData = {
            citasHoy: 3, citasCompletadas: 1, consultasRealizadas: 1, mascotasAtendidas: 2,
            citasPorDia: [], actividades: [],
            comparacionporcentaje: { citasHoy: 0, citasCompletadas: 0, consultasRealizadas: 0, mascotasAtendidas: 0 }
          };
          renderSection('home', emergencyData);
          updateDashboardMetrics(emergencyData);
          renderChart([], []);
          renderActividades();
          setTodayTexts();
        });
    } else {
      if (initialSection === 'actividad') {
        renderSection('actividad');
        actualizarTablaActividades();
        history.replaceState({ section: 'actividad' }, '', location.pathname);
      } else if (initialSection === 'historial') {
        renderSection('historial');
        fetchHistorial();
        history.replaceState({ section: 'historial' }, '', location.pathname);
      } else {
        renderSection(initialSection);
        history.replaceState({ section: initialSection }, '', location.pathname);
      }
    }

    initNavHandlers();
    handlePopState();
    attachReportHandlers();
    
    console.log('✅ dashboard-vet.js inicializado correctamente');
  });
})();