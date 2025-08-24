import './style.css';
import { db, ref, push, set, get, child } from "./firebase.js";

// Cambio de páginas
window.reportarIncidente = function () {
  window.location.href = 'formulario.html'; 
};

window.verIncidentes = function () {
  window.location.href = 'tablaincidentes';
};

window.redirigir = function () {
  window.location.href = 'index';
};

// Función para guardar en firebase
window.enviarReporte = async function (reporte) {
  try {
    const ultimoIdRef = ref(db, "ultimoId");
    const snapshot = await get(ultimoIdRef);
    let ultimoId = snapshot.exists() ? snapshot.val() : 0;

    const nuevoId = ultimoId + 1;
    const denunciaRef = ref(db, `denuncias/${nuevoId}`);
    await set(denunciaRef, {
      ...reporte,
      fecha: new Date().toISOString().split("T")[0],
      estado: "Pendiente"
    });

    await set(ultimoIdRef, nuevoId);

    console.log(`Reporte guardado con ID: ${nuevoId}`);
  } catch (err) {
    console.error("Error al guardar:", err);
  }
};

// Para que la tabla salga con los datos de firebase
window.cargarIncidentes = function () {
  const dbRef = ref(db);
  get(child(dbRef, "denuncias")).then((snapshot) => {
    if (snapshot.exists()) {
      const incidentes = snapshot.val();
      const tbody = document.getElementById("incidentes-lista");
      tbody.innerHTML = "";

      Object.entries(incidentes).forEach(([id, data]) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${data.estado || 'Pendiente'}</td>
          <td>${id}</td>
          <td>${data.tipo}</td>
          <td>${data.fecha}</td>
          <td>${data.ubicacion} (${data.region})</td>
          <td><button class="btn-ver-mas">Ver más</button></td>
        `;
        tbody.appendChild(tr);
      });
    } else {
      console.log("No hay incidentes aún");
    }
  }).catch((err) => console.error(err));
};