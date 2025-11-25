
document.addEventListener('DOMContentLoaded', function () {

(function () {
 
  // Tabs

  document.querySelectorAll('#configTabs a').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      document.querySelectorAll('#configTabs a').forEach(l => l.classList.remove('active'));
      link.classList.add('active');
      const target = link.dataset.target;
      document.querySelectorAll('.config-panel').forEach(p => p.classList.add('d-none'));
      const panel = document.getElementById(target);
      if (panel) panel.classList.remove('d-none');
    });
  });

  // Horario dinámico (render inicial)
  const dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
  const tbodyHorario = document.getElementById('tablaHorario');

  // Solo renderizamos si existe la tabla en el DOM
  if (tbodyHorario) {
    // Limpiamos por si acaso
    tbodyHorario.innerHTML = '';
    dias.forEach(d => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${d}</td>
        <td><input type="time" class="form-control form-control-sm apertura" value="08:00"></td>
        <td><input type="time" class="form-control form-control-sm cierre" value="18:00"></td>
        <td class="text-center"><input type="checkbox" class="form-check-input activo" ${['Sábado','Domingo'].includes(d) ? '' : 'checked'}></td>
      `;
      tbodyHorario.appendChild(tr);
    });
  }

  // Alert helper
  function showAlert(msg, type = 'success') {
    const ph = document.getElementById('alertPlaceholder');
    if (!ph) return;
    ph.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
      ${msg}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`;
    // Auto close después de 4s
    setTimeout(() => {
      const node = ph.querySelector('.alert');
      if (node) {
        try { node.classList.remove('show'); node.remove(); } catch (e) {}
      }
    }, 4000);
  }

  // Defaults (para restablecer)
  const DEFAULTS = {
    clinica: {
      nombre: 'Veterinaria Laika',
      direccion: 'Calle 123 #45-67',
      telefono: '+57 300 000 0000',
      email: 'contacto@laika.com',
      descripcion: 'Clínica veterinaria dedicada al cuidado integral de tus mascotas.'
    },
    notificaciones: {
      emailReminder: true,
      smsReminder: true,
      inventoryAlert: true,
      leadTime: 24
    },
    // key por día: nombres 
    horario: {
      'Lunes': { apertura: '08:00', cierre: '18:00', activo: true },
      'Martes': { apertura: '08:00', cierre: '18:00', activo: true },
      'Miércoles': { apertura: '08:00', cierre: '18:00', activo: true },
      'Jueves': { apertura: '08:00', cierre: '18:00', activo: true },
      'Viernes': { apertura: '08:00', cierre: '18:00', activo: true },
      'Sábado': { apertura: '09:00', cierre: '13:00', activo: false },
      'Domingo': { apertura: '09:00', cierre: '13:00', activo: false }
    },
    sistema: {
      tema: 'claro', // 'claro' | 'oscuro'
      idioma: 'es'   // 'es' | 'en'
    }
  };

  // Inicializar valores por defecto si no existen
  if (!localStorage.getItem('configClinica')) {
    localStorage.setItem('configClinica', JSON.stringify(DEFAULTS.clinica));
  }
  if (!localStorage.getItem('configNotificaciones')) {
    localStorage.setItem('configNotificaciones', JSON.stringify(DEFAULTS.notificaciones));
  }
  if (!localStorage.getItem('configHorario')) {
    localStorage.setItem('configHorario', JSON.stringify(DEFAULTS.horario));
  }
  if (!localStorage.getItem('configSistema')) {
    localStorage.setItem('configSistema', JSON.stringify(DEFAULTS.sistema));
  }

  // Cargar desde localStorage
  function loadClinica() {
    const data = JSON.parse(localStorage.getItem('configClinica') || '{}');
    if (!data) return;
    const map = {
      'nombre': 'clinicaNombre',
      'direccion': 'clinicaDireccion',
      'telefono': 'clinicaTelefono',
      'email': 'clinicaEmail',
      'descripcion': 'clinicaDescripcion'
    };
    Object.keys(map).forEach(k => {
      const el = document.getElementById(map[k]);
      if (el && data[k] !== undefined) el.value = data[k];
    });
  }

  function loadNotificaciones() {
    const data = JSON.parse(localStorage.getItem('configNotificaciones') || '{}');
    if (!data) return;
    ['emailReminder','smsReminder','inventoryAlert'].forEach(id => {
      const el = document.getElementById(id);
      if (el && data[id] !== undefined) el.checked = data[id];
    });
    const lt = document.getElementById('leadTime');
    if (lt && data.leadTime !== undefined) lt.value = data.leadTime;
  }

  function loadHorario() {
    const data = JSON.parse(localStorage.getItem('configHorario') || '{}');
    if (!Object.keys(data).length) return;
    if (!tbodyHorario) return;
    [...tbodyHorario.rows].forEach(row => {
      const dia = row.cells[0].textContent.trim();
      if (data[dia]) {
        const apertura = row.querySelector('.apertura');
        const cierre = row.querySelector('.cierre');
        const activo = row.querySelector('.activo');
        if (apertura && data[dia].apertura) apertura.value = data[dia].apertura;
        if (cierre && data[dia].cierre) cierre.value = data[dia].cierre;
        if (activo && typeof data[dia].activo !== 'undefined') activo.checked = !!data[dia].activo;
      }
    });
  }

  function loadSistema() {
    const data = JSON.parse(localStorage.getItem('configSistema') || '{}');
    if (data.tema && document.getElementById('temaSistema')) {
      document.getElementById('temaSistema').value = data.tema;
    }
    if (data.idioma && document.getElementById('idiomaSistema')) {
      document.getElementById('idiomaSistema').value = data.idioma;
    }
    aplicarTema((data && data.tema) ? data.tema : DEFAULTS.sistema.tema);
  }

  // Aplicar tema
  function aplicarTema(tema) {
    // Cambios simples en body;
    if (tema === 'oscuro') {
      document.documentElement.classList.add('modo-oscuro'); // clase útil para estilos custom
      document.body.classList.add('bg-dark', 'text-light');
    } else {
      document.documentElement.classList.remove('modo-oscuro');
      document.body.classList.remove('bg-dark', 'text-light');
    }
  }

  // Cargar inicialmente
  loadClinica();
  loadNotificaciones();
  loadHorario();
  loadSistema();


  // Formularios - guardado en localStorage (comportamiento actual)
  document.getElementById('formClinica')?.addEventListener('submit', e => {
    e.preventDefault();
    const payload = {
      nombre: document.getElementById('clinicaNombre').value.trim(),
      direccion: document.getElementById('clinicaDireccion').value.trim(),
      telefono: document.getElementById('clinicaTelefono').value.trim(),
      email: document.getElementById('clinicaEmail').value.trim(),
      descripcion: document.getElementById('clinicaDescripcion').value.trim()
    };
    localStorage.setItem('configClinica', JSON.stringify(payload));
    showAlert('Información de la clínica guardada', 'success');
    toggleClinicaEdit(false);
  });

  document.getElementById('formNotificaciones')?.addEventListener('submit', e => {
    e.preventDefault();
    const payload = {
      emailReminder: !!document.getElementById('emailReminder').checked,
      smsReminder: !!document.getElementById('smsReminder').checked,
      inventoryAlert: !!document.getElementById('inventoryAlert').checked,
      leadTime: parseInt(document.getElementById('leadTime').value || '0', 10)
    };
    localStorage.setItem('configNotificaciones', JSON.stringify(payload));
    showAlert('Configuración de notificaciones guardada', 'success');
  });

  document.getElementById('formHorario')?.addEventListener('submit', e => {
    e.preventDefault();
    if (!tbodyHorario) return;
    const data = {};
    [...tbodyHorario.rows].forEach(row => {
      const dia = row.cells[0].textContent.trim();
      data[dia] = {
        apertura: row.querySelector('.apertura').value,
        cierre: row.querySelector('.cierre').value,
        activo: !!row.querySelector('.activo').checked
      };
    });
    localStorage.setItem('configHorario', JSON.stringify(data));
    showAlert('Horario guardado', 'success');
  });

  // Si no existe el panel/elementos, se ignora
  document.getElementById('formSistema')?.addEventListener('submit', e => {
    e.preventDefault();
    const tema = document.getElementById('temaSistema').value;
    const idioma = document.getElementById('idiomaSistema').value;
    const payload = { tema, idioma };
    localStorage.setItem('configSistema', JSON.stringify(payload));
    aplicarTema(tema);
    showAlert('Preferencias guardadas', 'success');
  });

 
  // Modo edición clínica
  const camposClinica = ['clinicaNombre','clinicaDireccion','clinicaTelefono','clinicaEmail','clinicaDescripcion']
    .map(id => document.getElementById(id));
  const btnEditarClinica = document.getElementById('btnEditarClinica');
  const btnGuardarClinica = document.getElementById('btnGuardarClinica');
  const btnCancelarClinica = document.getElementById('btnCancelarClinica');
  let snapshotClinica = null;

  function toggleClinicaEdit(edit) {
    camposClinica.forEach(c => { if (c) c.disabled = !edit; });
    if (btnEditarClinica && btnGuardarClinica && btnCancelarClinica) {
      if (edit) {
        btnEditarClinica.classList.add('d-none');
        btnGuardarClinica.classList.remove('d-none');
        btnCancelarClinica.classList.remove('d-none');
      } else {
        btnEditarClinica.classList.remove('d-none');
        btnGuardarClinica.classList.add('d-none');
        btnCancelarClinica.classList.add('d-none');
      }
    }
  }

  btnEditarClinica?.addEventListener('click', () => {
    snapshotClinica = camposClinica.reduce((acc, c) => { if (c) acc[c.id] = c.value; return acc; }, {});
    toggleClinicaEdit(true);
  });

  btnCancelarClinica?.addEventListener('click', () => {
    if (snapshotClinica) {
      camposClinica.forEach(c => {
        if (c && snapshotClinica[c.id] !== undefined) c.value = snapshotClinica[c.id];
      });
    } else {
      loadClinica();
    }
    toggleClinicaEdit(false);
  });

  // Arrancamos con edición deshabilitada
  toggleClinicaEdit(false);

  // Restablecer valores predeterminados (nuevo)

  const btnReset = document.getElementById('btnResetConfig');
  if (btnReset) {
    btnReset.addEventListener('click', e => {
      e.preventDefault();
      if (!confirm('¿Estás seguro de que quieres restablecer la configuración a los valores predeterminados?')) return;
      localStorage.setItem('configClinica', JSON.stringify(DEFAULTS.clinica));
      localStorage.setItem('configNotificaciones', JSON.stringify(DEFAULTS.notificaciones));
      localStorage.setItem('configHorario', JSON.stringify(DEFAULTS.horario));
      localStorage.setItem('configSistema', JSON.stringify(DEFAULTS.sistema));
      loadClinica();
      loadNotificaciones();
      loadHorario();
      loadSistema();
      showAlert('Configuración restablecida a valores predeterminados', 'warning');
    });
  }

})();

});