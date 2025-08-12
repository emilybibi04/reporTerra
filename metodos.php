<?php
// Escritura - Joshua Zaruma
class Denuncia {
    private $id;
    public $tipo;
    public $fecha;
    public $ubicacion;
    public $imagen;
    public $estado;

    public function __construct($id, $tipo,$fecha, $ubicacion) {
        $this->id = $id;
        $this->tipo = $tipo;
        $this->imagen = null;
        $this->fecha = $fecha;
        $this->ubicacion = $ubicacion;
        $this->estado = "Pendiente";
    }
    public function getEstado() {
        return $this->estado;
    }
    public function setEstado($estadoNuevo) {
        $permitido = ["Pendiente", "En proceso", "Resuelta"];
        if (in_array($estadoNuevo, $permitido, true)) {
            $this->estado = $estadoNuevo;
            return true;
        }
        return false;
    }
    public function getId() {
        return $this->id;
    }
    public function getImagen() {
        return $this->imagen;
    }
    public function setImagen($imagen) {
        $this->imagen = $imagen;
        return true;
    }
    public function toString() {
        return "ID: {$this->id}, Tipo: {$this->tipo}, Fecha: {$this->fecha}, Ubicación: {$this->ubicacion}, Estado: {$this->estado}";
    }
}

// Lectura - Emily Valarezo
function filtrarDenuncias($denuncias, $tipo = null, $fecha = null, $ubicacion = null) {
    return array_filter($denuncias, function($denuncia) use ($tipo, $fecha, $ubicacion) {
        return (!$tipo || $denuncia->tipo === $tipo) &&
               (!$fecha || $denuncia->fecha === $fecha) &&
               (!$ubicacion || $denuncia->ubicacion === $ubicacion);
    });
}

// Escritura - Emily Valarezo
function cambiarEstado(&$denuncias, $id, $nuevoEstado) {
    foreach ($denuncias as $index => $denuncia) {
        if ($denuncia->getId() == $id) {
            return $denuncias[$index]->setEstado($nuevoEstado);
        }
    }
    return false; // Denuncia no encontrada
}

// Escritura - Joshua Zaruma
function RegistrarDenuncia($denuncias, $nuevaDenuncia) {
    $id = count($denuncias);
    $denuncia = new Denuncia($id, $nuevaDenuncia["tipo"], $nuevaDenuncia["fecha"], $nuevaDenuncia["ubicacion"]);
    array_push($denuncias, $denuncia);
    return true; // Denuncia registrada exitosamente
}
// Escritura - Joshua Zaruma
function eliminarDenuncia(&$denuncias, $id) {
    foreach ($denuncias as $index => $denuncia) {
        if ($denuncia->id == $id) {
            array_splice($denuncias, $index, 1);
            return true;
        }
    }
    return false;
}

// Lectura - Joshua Zaruma
function buscarDenunciaPorId($denuncias, $id) {
    foreach ($denuncias as $denuncia) {
        if ($denuncia->id == $id) {
            return $denuncia;
        }
    }
    return null;
}

// Lectura - Raul Laurido
function verDetalleDenuncia($denuncias, $id){
    $denuncia = buscarDenunciaPorId($denuncias, $id);
    if ($denuncia){
        return "Tipo: {$denuncia->tipo}, Fecha: {$denuncia->fecha}, Ubicación: {$denuncia->ubicacion}, Estado: {$denuncia->estado}";
    }
    return "Denuncia no encontrada";
}

// Escritura - Raul Laurido
function editarDenuncia(&$denuncias, $id, $nuevosDatos) {
    foreach ($denuncias as $denuncia) {
        if ($denuncia->getId() == $id) {
            if (isset($nuevosDatos["tipo"])) {
                $denuncia->tipo = $nuevosDatos["tipo"];
            }
            if (isset($nuevosDatos["fecha"])) {
                $denuncia->fecha = $nuevosDatos["fecha"];
            }
            if (isset($nuevosDatos["ubicacion"])) {
                $denuncia->ubicacion = $nuevosDatos["ubicacion"];
            }
            if (isset($nuevosDatos["imagen"])) {
                $denuncia->setImagen($nuevosDatos["imagen"]);
            }
            if (isset($nuevosDatos["estado"])) {
                $denuncia->setEstado($nuevosDatos["estado"]);
            }
            return true;
        }
    }
    return false;
}