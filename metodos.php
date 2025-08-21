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
function cambiarEstado( $id, $nuevoEstado) {
    global $database;
    $permitidos=["Pendiente", "En proceso", "Resuelta"];
    if (!in_array($nuevoEstado, $permitidos)) {
        return false;
    }

    $referenciaDenuncia= $database->getReference('denuncias/' . $id);
    $snapshot= $referenciaDenuncia->getSnapshot();

    if ($snapshot->exists()){
        $referenciaDenuncia->update([
            'estado' => $nuevoEstado
        ]);
        return true;
    }
    return false; // Denuncia no encontrada
}

// Escritura - Joshua Zaruma
function RegistrarDenuncia($denunciaData) {
    global $database;
    $reference = $database->getReference('denuncias');
    $newDenuncia = $reference->push([
        'tipo' => $denunciaData["tipo"],
        'fecha' => $denunciaData["fecha"],
        'ubicacion' => $denunciaData["ubicacion"],
        'imagen'=> null, 
        'estado'=> "Pendiente"
    ]);

    if ($newDenuncia) {
        return true; // Denuncia registrada exitosamente en Firebase
    }
    return false;
}
// Escritura - Joshua Zaruma
function eliminarDenuncia( $id) {
    global $database;
    $referenciaDenuncia = $database->getReference('denuncias/' . $id);
    $referenciaDenuncia->remove();
    return true;
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
function editarDenuncia($id, $nuevosDatos) {
    global $database;
    $referenciaDenuncia = $database->getReference("denuncias/". $id);
    $snapshot=$referenciaDenuncia->getSnapshot();
        if (!$snapshot->exists()) {
            return false;
        }
    $actualizacion=[];
    if (isset($nuevosDatos["tipo"])) {
                $actualizacion["tipo"] = $nuevosDatos["tipo"];
            }
            if (isset($nuevosDatos["fecha"])) {
                $actualizacion["fecha"] = $nuevosDatos["fecha"];
            }
            if (isset($nuevosDatos["ubicacion"])) {
                $actualizacion["ubicacion"] = $nuevosDatos["ubicacion"];
            }
            if (isset($nuevosDatos["imagen"])) {
                $actualizacion["imagen"]= $nuevosDatos["imagen"];
            }
            if (isset($nuevosDatos["estado"])) {
                $actualizacion["estado"]=$nuevosDatos["estado"];
            }
            $referenciaDenuncia->update($actualizacion);
            return true;

}