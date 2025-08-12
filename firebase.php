<?php
require __DIR__ . '/vendor/autoload.php';
require 'metodos.php';

use Kreait\Firebase\Factory;

$factory = (new Factory)
    ->withServiceAccount(__DIR__ . '/secrets/firebase-key.json')
    ->withDatabaseUri('https://reporterra-433b5-default-rtdb.firebaseio.com/');

$database = $factory->createDatabase();
$referencia = $database->getReference('denuncias');
$denuncias = $referencia->getValue() ?? [];

//Validar si la base de datos esta vacía
if (empty($denuncias)) {
    echo "No hay denuncias registradas.\n";
    exit;
}

$denuncias_lista = [];
foreach ($denuncias as $id => $datos) {
    $denuncia = new Denuncia(
        $id,
        $datos["tipo"] ?? "",
        $datos["fecha"] ?? "",
        $datos["ubicacion"] ?? ""
    );
    if (isset($datos["imagen"])) {
        $denuncia->setImagen($datos["imagen"]);
    }
    if (isset($datos["estado"])) {
        $denuncia->setEstado($datos["estado"]);
    }
    array_push($denuncias_lista, $denuncia);
}

/*Pruebas - Emily Valarezo
$filtro = filtrarDenuncias($denuncias_lista, "Contaminación");
echo "Filtradas por tipo Contaminación:\n";
print_r($filtro);
*/

/*
if (cambiarEstado($denuncias_lista, 1, "En proceso")) {
    echo "\nEstado cambiado.\n";

    $database->getReference('denuncias')
            ->set($denuncias_lista);

    echo "Cambios guardados.\n";
} else {
    echo "\nNo se encontró la denuncia.\n";
}*/


/*
// Pruebas - Joshua Zaruma
// PRUEBA 1: Registrar nueva denuncia
$denunciaprueba = new Denuncia(2, "Contaminación", "2023-10-01", "Parque Central");
$denunciaprueba->setImagen("imagen1.jpg");

if (RegistrarDenuncia($denuncias_lista, $denunciaprueba)){
    echo "Denuncia registrada exitosamente.\n";
    // $database->insert("", $denuncia_lista);
    $database->getReference('denuncias')
            ->set($denunciasArray);
} else {
    echo "Error al registrar la denuncia.\n";
}

// PRUEBA 2: Eliminar denuncia
if (eliminarDenuncia($denuncias_lista, 1)) {
    echo "Denuncia eliminada exitosamente.\n";
    $database->getReference("denuncias")
            ->set($denunciasArray);
} else {
    echo "Error al eliminar la denuncia.\n";
}
// PRUEBA 3: Buscar denuncia por ID
$denunciaEncontrada = buscarDenunciaPorId($denuncias_lista, 0);
if ($denunciaEncontrada) {
    echo $denunciaEncontrada->toString() . "\n";
} else {
    echo "No se encontró la denuncia con ID 0.\n";
}
*/
