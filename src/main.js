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
      `;
      tbody.appendChild(tr);
    });

    tbody.onclick = (e) => {
      const btn = e.target.closest('.btn-ver-mas');
      if (btn) alert(btn.title || 'Sin descripción');
    };

  } catch (err) {
    console.error(err);
  }
};
