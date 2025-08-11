<?php

// Lectura - Emily Valarezo
function filtrarDenuncias($denuncias, $tipo = null, $fecha = null, $ubicacion = null) {
    return array_filter($denuncias, function($denuncia) use ($tipo, $fecha, $ubicacion) {
        return (!$tipo || $denuncia["tipo"] === $tipo) &&
               (!$fecha || $denuncia["fecha"] === $fecha) &&
               (!$ubicacion || $denuncia["ubicacion"] === $ubicacion);
    });
}

// Escritura - Emily Valarezo
function cambiarEstado(&$denuncias, $id, $nuevoEstado) {
    $permitido = ["Pendiente", "En proceso", "Resuelta"];

    if (!in_array($nuevoEstado, $permitido, true)) {
        return false;
    }

    foreach ($denuncias as &$denuncia) {
        if ($denuncia["id"] == $id) {
            $denuncia["estado"] = $nuevoEstado;
            return true;
        }
    }
    return false;
}