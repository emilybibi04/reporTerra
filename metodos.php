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

        $this->database = $factory->createDatabase();
        $this->referencia = $this->database->getReference('denuncias');
        $this->ultimoIdRef = $this->database->getReference('ultimoId');
    }
    public function RegistrarDenuncia($datos) {
        try {
            $errores = $this->validarDatosDenuncia($datos);
            if (!empty($errores)) {
                echo "Errores de validación:\n";
                foreach ($errores as $error) echo "  - $error\n";
                return false;
            }

            // Obtener el ultimo ID
            $ultimoId = $this->ultimoIdRef->getValue();
            if (!is_numeric($ultimoId)) {
                $ultimoId = 0;
            }
            $nuevoId = $ultimoId + 1;


            $datosNormalizados = $this->normalizarDatosDenuncia($datos);
            // Guardar con el nuevo ID
            $this->referencia->getChild($nuevoId)->set($datosNormalizados);
            // Actualizar ultimoId
            $this->ultimoIdRef->set($nuevoId);

            echo "Denuncia registrada con ID: $nuevoId\n";
            return $nuevoId;

        } catch (Exception $e) {
            echo "Error al registrar: " . $e->getMessage() . "\n";
            return false;
        }
    }
    public function cambiarEstado($id, $nuevoEstado) {
        try {
            $permitidos = ["Pendiente", "En proceso", "Resuelta"];
            if (!in_array($nuevoEstado, $permitidos)) {
                echo "Estado no válido. Debe ser: " . implode(", ", $permitidos) . "\n";
                return false;
            }

            $denunciaRef = $this->referencia->getChild($id);
            $snapshot = $denunciaRef->getSnapshot();

            if (!$snapshot->exists()) {
                echo "Denuncia no encontrada con ID: $id\n";
                return false;
            }


            $denunciaRef->update([
                'estado' => $nuevoEstado
            ]);

            echo "Estado cambiado a '$nuevoEstado' para denuncia $id\n";
            return true;

        } catch (Exception $e) {
            echo "Error al cambiar estado: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public function editarDenuncia($id, $nuevosDatos) {
        try {
            $denunciaRef = $this->referencia->getChild($id);
            $snapshot = $denunciaRef->getSnapshot();

            if (!$snapshot->exists()) {
                echo "Denuncia no encontrada con ID: $id\n";
                return false;
            }


            $actualizacion = [];
            foreach (['tipo', 'ubicacion', 'imagen', 'region'] as $campo) {
                if (isset($nuevosDatos[$campo])) {
                    $actualizacion[$campo] = $campo === 'region'
                        ? ucfirst(strtolower($nuevosDatos[$campo]))
                        : $nuevosDatos[$campo];
                }
            }
            $denunciaRef->update($actualizacion);

            echo "Denuncia $id actualizada exitosamente\n";
            return true;

        } catch (Exception $e) {
            echo "Error al editar: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public function eliminarDenuncia($id) {
        try {
            $denunciaRef = $this->referencia->getChild($id);
            $snapshot = $denunciaRef->getSnapshot();

            if (!$snapshot->exists()) {
                echo "Denuncia no encontrada con ID: $id\n";
                return false;
            }

            $denunciaRef->remove();
            echo "Denuncia $id eliminada\n";
            return true;

        } catch (Exception $e) {
            echo "Error al eliminar: " . $e->getMessage() . "\n";
            return false;
        }
    }

    public function obtenerDenuncia($id) {
        try {
            $datos = $this->referencia->getChild($id)->getValue();
            if (!$datos) {
                echo "Denuncia no encontrada con ID: $id\n";
                return null;
            }
            return $datos;

        } catch (Exception $e) {
            echo "Error al obtener denuncia: " . $e->getMessage() . "\n";
            return null;
        }
    }

    public function obtenerTodasLasDenuncias() {
        try {
            return $this->referencia->getValue() ?? [];
        } catch (Exception $e) {
            echo "Error al obtener denuncias: " . $e->getMessage() . "\n";
            return [];
        }
    }

    // MÉTODOS AUXILIARES
    private function validarDatosDenuncia($datos) {
        $errores = [];
        if (empty($datos['tipo'])) $errores[] = "El tipo es obligatorio";
        if (empty($datos['ubicacion'])) $errores[] = "La ubicación es obligatoria";
        return $errores;
    }

    private function normalizarDatosDenuncia($datos) {
        $fechaHoy = date('d-m-Y');
        return [
            'tipo' => ucfirst(strtolower($datos['tipo'])),
            'fecha' => $fechaHoy,
            'ubicacion' => $datos['ubicacion'],
            'region' => $datos['region'] ?? null,
            'imagen' => $datos['imagen'] ?? '',
            'estado' => 'Pendiente',
            'detalles' => $datos['detalles'] ?? ''
        ];
    }
}
