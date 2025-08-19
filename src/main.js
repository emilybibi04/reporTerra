import './style.css';

console.log("ReporTerra funcionando con Vite");

window.reportarIncidente = function () {
  alert("Formulario");
};

window.verIncidentes = function () {
  window.location.href = 'tablaincidentes';
};

window.redirigir = function () {
  window.location.href = 'index';
};
