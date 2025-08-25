import './style.css';

window.reportarIncidente = function () { window.location.href = 'formulario.html'; };
window.verIncidentes   = function () { window.location.href = 'tablaincidentes.html'; };
window.redirigir       = function () { window.location.href = 'index.html'; };

async function fetchJSONSegura(resp) {
  const texto = await resp.text();
  try { return JSON.parse(texto); }
  catch {
    console.error('Respuesta NO JSON:', texto);
    throw new Error('Respuesta del servidor no es JSON');
  }
}

let _cacheIncidentes = {};
let _filtros = { tipo: '', fecha: '', region: '' };

window.enviarReporte = async function (reporte) {
  const intentar = async (url) => {
    const resp = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(reporte)
    });
    const data = await fetchJSONSegura(resp);
    if (!data.ok) throw new Error(data.error || 'Error desconocido');
    return data.id || null;
  };

  try {
    return await intentar('/api.php?action=registrar');
  } catch (e1) {
    console.warn('[registrar] Fallback directo a PHP 9000:', e1?.message);
    try {
      return await intentar('http://localhost:9000/api.php?action=registrar');
    } catch (e2) {
      console.error('Error al guardar (registrar):', e2);
      alert('No se pudo guardar el reporte: ' + e2.message);
      return null;
    }
  }
};

async function cambiarEstado(id, estado){
  if (!id || !estado) throw new Error('Datos inválidos: id o estado faltante');

  const resp = await fetch('/api.php?action=cambiar_estado', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ id: String(id), estado: String(estado) })
  });
  const texto = await resp.text();
  let data;
  try { data = JSON.parse(texto); }
  catch { throw new Error('Respuesta no JSON: ' + texto); }
  if(!data.ok) throw new Error(data.error||'Error cambiando estado');
}

async function obtenerDenuncia(id) {
  try {
    const resp = await fetch(`/api.php?action=obtener&id=${encodeURIComponent(id)}`);
    const data = await resp.json();
    if (!data.ok) throw new Error(data.error || 'Error obteniendo denuncia');
    return data.denuncia;
  } catch (err) {
    console.error('Error al obtener denuncia:', err);
    return null;
  }
}

async function enviarActualizacion(id, datos) {
  try {
    const resp = await fetch(`/api.php?action=editar&id=${encodeURIComponent(id)}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(datos)
    });
    const data = await resp.json();
    if (!data.ok) throw new Error(data.error || 'Error desconocido');
    return true;
  } catch (err) {
    console.error('Error al actualizar:', err);
    alert('No se pudo actualizar el reporte: ' + err.message);
    return false;
  }
}

function parseFecha(fechaStr){
  if(!fechaStr) return null;
  const m1 = fechaStr.match(/^(\d{2})-(\d{2})-(\d{4})$/);
  if (m1) { const [_, d, m, y] = m1; return new Date(+y, +m-1, +d); }
  const m2 = fechaStr.match(/^(\d{4})-(\d{2})-(\d{2})$/);
  if (m2) { const [_, y, m, d] = m2; return new Date(+y, +m-1, +d); }
  return null;
}
function esHoy(d){ const t = new Date(); return d && d.toDateString() === t.toDateString(); }
function esEstaSemana(d){
  if(!d) return false;
  const hoy = new Date();
  const inicio = new Date(hoy); inicio.setDate(hoy.getDate() - hoy.getDay());
  const fin = new Date(inicio); fin.setDate(inicio.getDate() + 6);
  return d >= inicio && d <= fin;
}
function esEsteMes(d){ const h = new Date(); return d && d.getMonth()===h.getMonth() && d.getFullYear()===h.getFullYear(); }

function botonEstadoHTML(estadoActual, id){
  if (estadoActual === 'Pendiente') return `<button class="btn-estado" data-id="${id}" data-next="En proceso">Atender</button>`;
  if (estadoActual === 'En proceso') return `<button class="btn-estado" data-id="${id}" data-next="Resuelta">Finalizar</button>`;
  return '';
}

function aplicarFiltrosYRender(){
  const tbody = document.getElementById('incidentes-lista');
  if (!tbody) return;

  tbody.innerHTML = '';

  const ids = Object.keys(_cacheIncidentes || {});
  if (ids.length === 0) {
    tbody.innerHTML = `<tr><td colspan="7">No hay incidentes aún.</td></tr>`;
    return;
  }

  const tipoSel   = (_filtros.tipo || '').trim().toLowerCase();
  const fechaSel  = (_filtros.fecha || '').trim().toLowerCase();
  const regionSel = (_filtros.region || '').trim().toLowerCase();

  Object.entries(_cacheIncidentes).forEach(([id, d]) => {
    if (tipoSel)   { const t = (d.tipo||'').toString().toLowerCase();   if (t !== tipoSel) return; }
    if (regionSel) { const r = (d.region||'').toString().toLowerCase(); if (r !== regionSel) return; }
    if (fechaSel)  {
      const df = parseFecha(d.fecha);
      if (fechaSel==='hoy' && !esHoy(df)) return;
      if (fechaSel==='semana' && !esEstaSemana(df)) return;
      if (fechaSel==='mes' && !esEsteMes(df)) return;
    }

    const estado = d.estado || 'Pendiente';
    const tipo = d.tipo || '';
    const fecha = d.fecha || '';
    const ubic = d.ubicacion || '';
    const regionTxt = d.region ? ` (${d.region})` : '';
    const desc = (d.detalles && String(d.detalles).trim()) ? d.detalles : '—';

    const tr = document.createElement('tr');
    tr.dataset.id = id;
    tr.innerHTML = `
      <td>${estado}</td>
      <td>${id}</td>
      <td>${tipo}</td>
      <td>${fecha}</td>
      <td>${ubic}${regionTxt}</td>
      <td><button class="btn-ver-mas" data-id="${id}" title="${desc}">Ver más</button></td>
      <td>${botonEstadoHTML(estado, id)}</td>
    `;
    tbody.appendChild(tr);
  });

  tbody.onclick = async (e) => {
    const btnVer = e.target.closest('.btn-ver-mas');
    if (btnVer) {
      const id = btnVer.dataset.id;
      window.location.href = `formulario.html?id=${encodeURIComponent(id)}&modo=ver`;
      return;
    }
    const btnEstado = e.target.closest('.btn-estado');
    if (btnEstado) {
      const id = btnEstado.dataset.id;
      const next = btnEstado.dataset.next;
      console.log('Click estado:', {id, next});
      try {
        await cambiarEstado(id, next);
        if (_cacheIncidentes[id]) _cacheIncidentes[id].estado = next;
        aplicarFiltrosYRender();
      } catch (err) {
        alert(err.message);
      }
      return;
    }
  };
}

window.cargarIncidentes = async function () {
  try {
    const resp = await fetch('/api.php?action=listar');
    const data = await resp.json();
    if (!data || data.ok !== true) throw new Error((data && data.error) || 'Error listando');

    let d = data.denuncias;
    if (!d) d = {};
    if (Array.isArray(d)) d = Object.fromEntries(d.map((v, i) => [String(i), v || {}]));
    if (typeof d !== 'object') d = {};
    _cacheIncidentes = d;

    aplicarFiltrosYRender();
  } catch (err) {
    console.error('Error en cargarIncidentes:', err);
    const tbody = document.getElementById('incidentes-lista');
    if (tbody) tbody.innerHTML = `<tr><td colspan="7">Error cargando: ${err.message}</td></tr>`;
  }
};

function setFormDisabled(disabled){
  document.querySelectorAll('input, select, textarea, button.incidente-type-btn').forEach(el=>{
    if (el) el.disabled = !!disabled;
  });
}

function insertarBotonEditarONuevo(label, id){
  let contDestino = document.querySelector('.form-hero-section') || document.querySelector('.incidente-form') || document.body;
  let btn = document.getElementById('btn-editar-ver');
  if (!btn) {
    btn = document.createElement('button');
    btn.id = 'btn-editar-ver';
    btn.className = 'btn-reportar';
    btn.style.marginTop = '12px';
    contDestino.appendChild(btn);
  }
  btn.textContent = label;
  btn.dataset.id = id || '';
  return btn;
}

async function recogerDatosFormulario(){
  const tipo = document.querySelector(".incidente-type-btn.selected")?.innerText || "Otro";
  const region = document.querySelector(".region-select")?.value || "";
  const ubicacion = document.querySelector(".ubicacion-row input")?.value || "";
  const detalles = document.querySelector("textarea")?.value || "";
  return { tipo, region, ubicacion, detalles };
}

window.manejarFormulario = async function (e) {
  if (e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation?.();
  }

  const incidenteId = document.getElementById("incidente-id")?.value || "";
  const datosReporte = await recogerDatosFormulario();
  let exito = false;

  if (incidenteId) {
    exito = await enviarActualizacion(incidenteId, datosReporte);
  } else {
    const nuevoId = await window.enviarReporte(datosReporte);
    if (nuevoId) exito = true;
  }

  if (exito) {
    const modal = document.getElementById('modal-exito');
    if (modal) {
      modal.style.display = 'flex';
      setTimeout(() => {
        modal.style.display = 'none';
        window.location.href = 'tablaincidentes.html';
      }, 1200);
    } else {
      window.location.href = 'tablaincidentes.html';
    }
  }
  return false;
};

function bindFormHandlers(){
  const globalSubmit = (ev) => {
    const form = ev.target;
    if (form && form.tagName === 'FORM') {
      console.log('[bind] submit capturado en', form);
      ev.preventDefault();
      ev.stopPropagation();
      ev.stopImmediatePropagation?.();
      window.manejarFormulario(ev);
    }
  };
  document.addEventListener('submit', globalSubmit, true);

  const form =
    document.querySelector('.incidente-form') ||
    document.querySelector('form');
  if (form) {
    console.log('[bind] enganchado form:', form.className || form.id || form.tagName);
    form.addEventListener('submit', window.manejarFormulario);
  } else {
    console.warn('[bind] no se encontró formulario en esta página');
  }

  const btn = document.getElementById('btn-reportar');
  if (btn) {
    console.log('[bind] enganchado #btn-reportar');
    btn.addEventListener('click', (ev) => {
      ev.preventDefault();
      ev.stopPropagation();
      ev.stopImmediatePropagation?.();
      window.manejarFormulario(ev);
    });
  } else {
    console.warn('[bind] no se encontró #btn-reportar (ok si no estás en el form)');
  }
}

document.addEventListener('DOMContentLoaded', async function () {
  const botonesTipo = document.querySelectorAll('.incidente-type-btn');
  botonesTipo.forEach(btn => {
    btn.addEventListener('click', function () {
      botonesTipo.forEach(b => b.classList.remove('selected'));
      this.classList.add('selected');
    });
  });

  const logoImg  = document.getElementById('logo-img');
  if (logoImg)  logoImg.onclick  = () => window.location.href = 'index.html';
  const logoText = document.getElementById('logo-text');
  if (logoText) logoText.onclick = () => window.location.href = 'index.html';

  const tbody = document.getElementById('incidentes-lista');
  if (tbody) {
    await window.cargarIncidentes();
  }

  bindFormHandlers();

  const url = new URLSearchParams(window.location.search);
  const incidenteId = url.get('id');
  const modo = (url.get('modo') || '').toLowerCase();
  const form = document.querySelector('.incidente-form') || document.querySelector('form');

  if (form && incidenteId) {
    const denuncia = await obtenerDenuncia(incidenteId);
    if (denuncia) {
      const tipoBtn = document.querySelector(`.incidente-type-btn[data-tipo="${denuncia.tipo}"]`);
      if (tipoBtn) tipoBtn.click();
      const regionSelect = document.querySelector(".region-select");
      if (regionSelect && denuncia.region) regionSelect.value = denuncia.region;
      const ubicInput = document.querySelector(".ubicacion-row input");
      if (ubicInput && denuncia.ubicacion) ubicInput.value = denuncia.ubicacion;
      const ta = document.querySelector("textarea");
      if (ta && typeof denuncia.detalles === 'string') ta.value = denuncia.detalles;

      const hiddenId = document.getElementById('incidente-id') || (()=>{
        const h = document.createElement('input');
        h.type='hidden'; h.id='incidente-id'; h.value=incidenteId;
        form.appendChild(h); return h;
      })();
      hiddenId.value = incidenteId;

      if (!modo) {
        const titulo = document.querySelector('.form-hero-section h1');
        if (titulo) titulo.innerText = 'Editar Incidente';
        const btnReportar = document.getElementById('btn-reportar');
        if (btnReportar) btnReportar.innerText = 'Guardar Cambios';
      }

      if (modo === 'ver') {
        setFormDisabled(true);
        const btnVer = insertarBotonEditarONuevo('Editar', incidenteId);
        btnVer.onclick = async () => {
          const d = await obtenerDenuncia(incidenteId);
          const estado = (d && d.estado) || 'Pendiente';
          if (estado !== 'Pendiente') {
            alert('Esta denuncia no se puede editar porque no está en estado Pendiente.');
            return;
          }
          setFormDisabled(false);
          btnVer.textContent = 'Guardar cambios';
          btnVer.onclick = async () => {
            const datos = await recogerDatosFormulario();
            const ok = await enviarActualizacion(incidenteId, datos);
            if (ok) {
              alert('Cambios guardados');
              setFormDisabled(true);
              btnVer.textContent = 'Editar';
            }
          };
        };
      }
    }
  }

  const selTipo  = document.getElementById('filtro-tipo');
  const selFecha = document.getElementById('filtro-fecha');
  const selUbic  = document.getElementById('filtro-ubicacion');

  function leerTextoSelect(sel){
    if (!sel) return '';
    const opt = sel.selectedOptions && sel.selectedOptions[0];
    return (opt ? opt.textContent : '').trim();
  }

  if (selTipo) {
    selTipo.addEventListener('change', () => {
      const t = leerTextoSelect(selTipo);
      _filtros.tipo = t.toLowerCase() === 'todos' ? '' : t.toLowerCase();
      aplicarFiltrosYRender();
    });
  }
  if (selFecha) {
    selFecha.addEventListener('change', () => {
      const f = (selFecha.value || '').toLowerCase();
      _filtros.fecha = f;
      aplicarFiltrosYRender();
    });
  }
  if (selUbic) {
    selUbic.addEventListener('change', () => {
      const r = leerTextoSelect(selUbic);
      _filtros.region = r.toLowerCase() === 'todas' ? '' : r.toLowerCase();
      aplicarFiltrosYRender();
    });
  }
});
