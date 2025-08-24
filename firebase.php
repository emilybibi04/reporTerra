<?php
require 'metodos.php';

$firebase = new MetodosFirebase();

function mostrarMenu() {
    echo "\n=== SISTEMA DE DENUNCIAS ===\n";
    echo "1. Registrar nueva denuncia\n";
    echo "2. Ver denuncia específica\n";
    echo "3. Editar denuncia\n";
    echo "4. Cambiar estado\n";
    echo "5. Listar denuncias\n";
    echo "6. Eliminar denuncia\n";
    echo "0. Salir\n";
    echo "Opción: ";
}

function ejecutarConsola($firebase) {
    while (true) {
        mostrarMenu();
        $opcion = trim(fgets(STDIN));

        switch ($opcion) {
            case '1':
                echo "Tipo: "; $tipo = trim(fgets(STDIN));
                echo "Ubicación: "; $ubicacion = trim(fgets(STDIN));
                echo "Detalles: "; $detalles = trim(fgets(STDIN));
                echo "Región (Costa/Sierra/Oriente): ";
                $region = trim(fgets(STDIN));
                $region = ucfirst(strtolower($region));
                if (!in_array($region, ['Costa', 'Sierra', 'Oriente'])) {
                    echo "Región no válida. Debe ser Costa, Sierra u Oriente.\n";
                    break;
                }
                $firebase->RegistrarDenuncia([
                    'tipo' => $tipo,
                    'ubicacion' => $ubicacion,
                    'region' => $region,
                    'detalles'=> $detalles
                ]);
                break;

            case '2':
                echo "ID denuncia: "; $id = trim(fgets(STDIN));
                print_r($firebase->obtenerDenuncia($id));
                break;

            case '3':
                echo "ID denuncia: "; $id = trim(fgets(STDIN));
                echo "Nueva ubicación: "; $ubicacion = trim(fgets(STDIN));
                $firebase->editarDenuncia($id, ['ubicacion' => $ubicacion]);
                break;

            case '4':
                echo "ID denuncia: "; $id = trim(fgets(STDIN));
                echo "Nuevo estado (Pendiente/En proceso/Resuelta): ";
                $estado = trim(fgets(STDIN));
                $firebase->cambiarEstado($id, $estado);
                break;

            case '5':
                print_r($firebase->obtenerTodasLasDenuncias());
                break;

            case '6':
                echo "ID denuncia: "; $id = trim(fgets(STDIN));
                $firebase->eliminarDenuncia($id);
                break;

            case '0':
                echo "¡Adiós!\n";
                exit;

            default:
                echo "Opción inválida\n";
        }

        echo "\nPresiona Enter para continuar...";
        fgets(STDIN);
    }
}

if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
    echo "---------Sistema de Denuncias--------\n";
    ejecutarConsola($firebase);
}
