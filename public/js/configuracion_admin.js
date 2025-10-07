// Tabs
document.querySelectorAll('#configTabs a').forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    document.querySelectorAll('#configTabs a').forEach(l => l.classList.remove('active'));
    link.classList.add('active');
    const target = link.dataset.target;
    document.querySelectorAll('.config-panel').forEach(p => p.classList.add('d-none'));
    document.getElementById(target).classList.remove('d-none');
  });
});

// Horario dinámico
const dias = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
const tbodyHorario = document.getElementById('tablaHorario');
dias.forEach(d => {
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${d}</td>
    <td><input type="time" class="form-control form-control-sm apertura" value="08:00"></td>
    <td><input type="time" class="form-control form-control-sm cierre" value="18:00"></td>
    <td class="text-center"><input type="checkbox" class="form-check-input activo" ${['Sábado','Domingo'].includes(d)?'':'checked'}></td>
  `;
  tbodyHorario.appendChild(tr);
});

// Alert helper
function showAlert(msg,type='success'){
  const ph=document.getElementById('alertPlaceholder');
  if(!ph) return;
  ph.innerHTML=`<div class="alert alert-${type} alert-dismissible fade show" role="alert">
    ${msg}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>`;
}

// Cargar desde localStorage
function loadClinica(){ const data=JSON.parse(localStorage.getItem('configClinica')||'{}'); if(!data) return; ['Nombre','Direccion','Telefono','Email','Descripcion'].forEach(c=>{const el=document.getElementById('clinica'+c); if(el && data[c.toLowerCase()]) el.value=data[c.toLowerCase()];}); }
function loadNotificaciones(){ const data=JSON.parse(localStorage.getItem('configNotificaciones')||'{}'); if(!data) return; ['emailReminder','smsReminder','inventoryAlert'].forEach(id=>{ const el=document.getElementById(id); if(el) el.checked=data[id];}); const lt=document.getElementById('leadTime'); if(lt && data.leadTime!==undefined) lt.value=data.leadTime; }
function loadHorario(){ const data=JSON.parse(localStorage.getItem('configHorario')||'{}'); if(!Object.keys(data).length) return; [...tbodyHorario.rows].forEach(row=>{ const dia=row.cells[0].textContent; if(data[dia]){ row.querySelector('.apertura').value=data[dia].apertura; row.querySelector('.cierre').value=data[dia].cierre; row.querySelector('.activo').checked=data[dia].activo; }});}
if(!localStorage.getItem('configClinica')){ localStorage.setItem('configClinica',JSON.stringify({nombre:'Veterinaria Laika',direccion:'Calle 123 #45-67',telefono:'+57 300 000 0000',email:'contacto@laika.com',descripcion:'Clínica veterinaria dedicada al cuidado integral de tus mascotas.'})); }
loadClinica(); loadNotificaciones(); loadHorario();

// Formularios
document.getElementById('formClinica')?.addEventListener('submit', e=>{e.preventDefault(); localStorage.setItem('configClinica',JSON.stringify({nombre:document.getElementById('clinicaNombre').value.trim(),direccion:document.getElementById('clinicaDireccion').value.trim(),telefono:document.getElementById('clinicaTelefono').value.trim(),email:document.getElementById('clinicaEmail').value.trim(),descripcion:document.getElementById('clinicaDescripcion').value.trim()})); showAlert('Información de la clínica guardada'); toggleClinicaEdit(false);});
document.getElementById('formNotificaciones')?.addEventListener('submit', e=>{e.preventDefault(); localStorage.setItem('configNotificaciones',JSON.stringify({emailReminder:document.getElementById('emailReminder').checked,smsReminder:document.getElementById('smsReminder').checked,inventoryAlert:document.getElementById('inventoryAlert').checked,leadTime:parseInt(document.getElementById('leadTime').value||'0',10)})); showAlert('Configuración de notificaciones guardada');});
document.getElementById('formHorario')?.addEventListener('submit', e=>{e.preventDefault(); const data={}; [...tbodyHorario.rows].forEach(row=>{const dia=row.cells[0].textContent; data[dia]={apertura:row.querySelector('.apertura').value,cierre:row.querySelector('.cierre').value,activo:row.querySelector('.activo').checked};}); localStorage.setItem('configHorario',JSON.stringify(data)); showAlert('Horario guardado');});

// Modo edición clínica
const camposClinica=['clinicaNombre','clinicaDireccion','clinicaTelefono','clinicaEmail','clinicaDescripcion'].map(id=>document.getElementById(id));
const btnEditarClinica=document.getElementById('btnEditarClinica');
const btnGuardarClinica=document.getElementById('btnGuardarClinica');
const btnCancelarClinica=document.getElementById('btnCancelarClinica');
let snapshotClinica=null;
function toggleClinicaEdit(edit){camposClinica.forEach(c=>{if(c)c.disabled=!edit;}); if(edit){btnEditarClinica.classList.add('d-none'); btnGuardarClinica.classList.remove('d-none'); btnCancelarClinica.classList.remove('d-none');}else{btnEditarClinica.classList.remove('d-none'); btnGuardarClinica.classList.add('d-none'); btnCancelarClinica.classList.add('d-none');}}
btnEditarClinica?.addEventListener('click',()=>{snapshotClinica=camposClinica.reduce((acc,c)=>{if(c)acc[c.id]=c.value;return acc;},{}); toggleClinicaEdit(true);});
btnCancelarClinica?.addEventListener('click',()=>{if(snapshotClinica){camposClinica.forEach(c=>{if(c && snapshotClinica[c.id]!==undefined)c.value=snapshotClinica[c.id];});}else{loadClinica();} toggleClinicaEdit(false);});
toggleClinicaEdit(false);
