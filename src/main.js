import './style.css';

window.reportarIncidente = function () {
  window.location.href = 'formulario.html'; 
};

window.verIncidentes = function () {
  window.location.href = 'tablaincidentes.html';
};

window.redirigir = function () {
  window.location.href = 'index.html';
};


window.enviarReporte = async function (reporte) {
  try {
    const resp = await fetch('/api.php?action=registrar', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(reporte)
    });
    const txt = await resp.text();
    let data;
    try { data = JSON.parse(txt); } catch {
      console.error('Respuesta NO JSON:', txt);
      throw new Error('Respuesta del servidor no es JSON');
    }
    console.log('API registrar ->', data);
    if (!data.ok) throw new Error(data.error || 'Error desconocido');
    return data.id;
  } catch (err) {
    console.error('Error al guardar:', err);
    alert('No se pudo guardar el reporte: ' + err.message);
    return null;
  }
};

window.cargarIncidentes = async function () {
  try {
    const resp = await fetch('/api.php?action=listar');
    const data = await resp.json();
    if (!data.ok) throw new Error(data.error || 'Error listando');

    const incidentes = data.denuncias || {};
    const tbody = document.getElementById('incidentes-lista');
    if (!tbody) return;

    tbody.innerHTML = '';
    Object.entries(incidentes).forEach(([id, d]) => {
      const tr = document.createElement('tr');
      tr.dataset.id = id;
      const estado = d.estado || 'Pendiente';
      const tipo = d.tipo || '';
      const fecha = d.fecha || '';
      const ubic = d.ubicacion || '';
      const region = d.region ? ` (${d.region})` : '';
      const desc = (d.detalles && d.detalles.trim()) ? d.detalles : '—';

      tr.innerHTML = `
        <td>${estado}</td>
        <td>${id}</td>
        <td>${tipo}</td>
        <td>${fecha}</td>
        <td>${ubic}${region}</td>
        <td><button class="btn-ver-mas" data-id="${id}" title="${desc}">Ver más</button></td>
        <td><button class="btn-editar" data-id="${id}">Editar</button></td>
      `;
      tbody.appendChild(tr);
    });

async function obtenerDenuncia(id) {
  try {
    const resp = await fetch(`/api.php?action=obtener&id=${id}`);
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
    const resp = await fetch(`/api.php?action=editar&id=${id}`, {
    method: 'POST', 
    headers: {'Content-Type': 'application/json'},
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


window.manejarFormulario = async function(e) {
  e.preventDefault();

  const tipo = document.querySelector(".incidente-type-btn.selected")?.innerText || "Otro";
  const region = document.querySelector(".region-select").value;
  const ubicacion = document.querySelector(".ubicacion-row input").value;
  const detalles = document.querySelector("textarea").value;
  const incidenteId = document.getElementById("incidente-id").value;


  const datosReporte = { tipo, region, ubicacion, detalles };
  let exito = false;

  if (incidenteId) {
  // Estamos en modo edición
    exito = await enviarActualizacion(incidenteId, datosReporte);
  } else {
  // Estamos en modo registro
      const nuevoId = await enviarReporte(datosReporte);
      if (nuevoId) exito = true;
    }

  if (exito) {
    const modal = document.getElementById('modal-exito');
    modal.style.display = 'flex';
    setTimeout(() => {
      modal.style.display = 'none';
      window.location.href = 'tablaincidentes.html';
    }, 1700);
  }
  return false;
};

document.addEventListener('DOMContentLoaded', async function() {
  const botonesTipo = document.querySelectorAll('.incidente-type-btn');
  botonesTipo.forEach(btn => {
    btn.addEventListener('click', function() {
    botonesTipo.forEach(b => b.classList.remove('selected'));
    this.classList.add('selected');
    });
  });

  document.getElementById('logo-img').onclick = () => window.location.href = 'index.html';
  document.getElementById('logo-text').onclick = () => window.location.href = 'index.html';

// **NUEVA LOGICA** para manejar el modo de edicion
  const urlParams = new URLSearchParams(window.location.search);
  const incidenteId = urlParams.get('id');

  if (incidenteId) {
  // Modo edicion: cambiar el titulo y el boton
    document.querySelector('.form-hero-section h1').innerText = 'Editar Incidente';
    document.getElementById('btn-reportar').innerText = 'Guardar Cambios';
    document.getElementById('incidente-id').value = incidenteId;

    const denuncia = await obtenerDenuncia(incidenteId);
    if (denuncia) {
      const tipoBtn = document.querySelector(`.incidente-type-btn[data-tipo="${denuncia.tipo}"]`);
      if (tipoBtn) tipoBtn.click();
      else document.querySelector('.incidente-type-btn:last-child').click();

      document.querySelector(".region-select").value = denuncia.region;
      document.querySelector(".ubicacion-row input").value = denuncia.ubicacion;
      document.querySelector("textarea").value = denuncia.detalles;
    }
  }

  if (form) {
    form.addEventListener('submit', window.manejarFormulario);
  }
});

    tbody.onclick = (e) => {
      const btn = e.target.closest('.btn-ver-mas');
      if (btn) alert(btn.title || 'Sin descripción');
    };

    const btnEditar = e.target.closest('.btn-editar');
      if (btnEditar) {
        const id = btnEditar.dataset.id;
        window.location.href = `formulario.html?id=${id}`;
      }
      

  } catch (err) {
    console.error(err);
  }
};
