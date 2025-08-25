<?php
require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;

class MetodosFirebase {
    private $database;
    private $referencia;
    private $ultimoIdRef;

    public function __construct() {
        $factory = (new Factory)
            ->withServiceAccount(__DIR__ . '/secrets/firebase-key.json')
            ->withDatabaseUri('https://reporterra-433b5-default-rtdb.firebaseio.com/');

        $this->database   = $factory->createDatabase();
        $this->referencia = $this->database->getReference('denuncias');
        $this->ultimoIdRef = $this->database->getReference('ultimoId');
    }

    public function RegistrarDenuncia($datos) {
        try {
            $errores = $this->validarDatosDenuncia($datos);
            if (!empty($errores)) {
                error_log("Errores de validación: " . implode(', ', $errores));
                return false;
            }

            $ultimoId = $this->ultimoIdRef->getValue();
            if (!is_numeric($ultimoId)) $ultimoId = 0;
            $nuevoId = $ultimoId + 1;

            $datosNormalizados = $this->normalizarDatosDenuncia($datos);
            $this->referencia->getChild($nuevoId)->set($datosNormalizados);
            $this->ultimoIdRef->set($nuevoId);

            error_log("Denuncia registrada con ID: $nuevoId");
            return $nuevoId;

        } catch (Exception $e) {
            error_log("Error al registrar: " . $e->getMessage());
            return false;
        }
    }

    public function cambiarEstado($id, $nuevoEstado) {
        try {
            $permitidos = ["Pendiente", "En proceso", "Resuelta"];
            if (!in_array($nuevoEstado, $permitidos)) {
                error_log("Estado no válido: $nuevoEstado");
                return false;
            }

            $denunciaRef = $this->referencia->getChild($id);
            $snapshot = $denunciaRef->getSnapshot();

            if (!$snapshot->exists()) {
                error_log("Denuncia no encontrada: $id");
                return false;
            }

            $denunciaRef->update(['estado' => $nuevoEstado]);

            error_log("Estado cambiado a $nuevoEstado para $id");
            return true;

        } catch (Exception $e) {
            error_log("Error al cambiar estado: " . $e->getMessage());
            return false;
        }
    }

    public function editarDenuncia($id, $nuevosDatos) {
        try {
            $denunciaRef = $this->referencia->getChild($id);
            $snapshot = $denunciaRef->getSnapshot();

            if (!$snapshot->exists()) {
                error_log("Denuncia no encontrada: $id");
                return false;
            }

            $actualizacion = [];
            foreach (['tipo', 'ubicacion', 'imagen', 'region', 'detalles', 'estado', 'fecha'] as $campo) {
                if (isset($nuevosDatos[$campo])) {
                    $actualizacion[$campo] = ($campo === 'region' || $campo === 'tipo')
                        ? ucfirst(strtolower($nuevosDatos[$campo]))
                        : $nuevosDatos[$campo];
                }
            }

            if (empty($actualizacion)) return true;

            $denunciaRef->update($actualizacion);

            error_log("Denuncia $id actualizada");
            return true;

        } catch (Exception $e) {
            error_log("Error al editar: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarDenuncia($id) {
        try {
            $denunciaRef = $this->referencia->getChild($id);
            $snapshot = $denunciaRef->getSnapshot();

            if (!$snapshot->exists()) {
                error_log("Denuncia no encontrada: $id");
                return false;
            }

            $denunciaRef->remove();
            error_log("Denuncia $id eliminada");
            return true;

        } catch (Exception $e) {
            error_log("Error al eliminar: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerDenuncia($id) {
        try {
            $datos = $this->referencia->getChild($id)->getValue();
            if (!$datos) {
                error_log("Denuncia no encontrada: $id");
                return null;
            }
            return $datos;

        } catch (Exception $e) {
            error_log("Error al obtener denuncia: " . $e->getMessage());
            return null;
        }
    }

    public function obtenerTodasLasDenuncias() {
        try {
            return $this->referencia->getValue() ?? [];
        } catch (Exception $e) {
            error_log("Error al obtener denuncias: " . $e->getMessage());
            return [];
        }
    }

    private function validarDatosDenuncia($datos) {
        $errores = [];
        if (empty($datos['tipo'])) $errores[] = "El tipo es obligatorio";
        if (empty($datos['ubicacion'])) $errores[] = "La ubicación es obligatoria";
        if (empty($datos['region'])) $errores[] = "La región es obligatoria";
        return $errores;
    }

    private function normalizarDatosDenuncia($datos) {
        $fechaHoy = date('d-m-Y');
        return [
            'tipo'      => ucfirst(strtolower($datos['tipo'])),
            'fecha'     => $fechaHoy,
            'ubicacion' => $datos['ubicacion'],
            'region'    => isset($datos['region']) ? ucfirst(strtolower($datos['region'])) : null,
            'imagen'    => $datos['imagen'] ?? '',
            'estado'    => 'Pendiente',
            'detalles'  => $datos['detalles'] ?? ''
        ];
    }
}
