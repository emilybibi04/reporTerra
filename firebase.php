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
    $denuncias_lista[] = [
        "id" => $id,
        "tipo" => $datos["tipo"] ?? "",
        "fecha" => $datos["fecha"] ?? "",
        "ubicacion" => $datos["ubicacion"] ?? "",
        "estado" => $datos["estado"] ?? ""
    ];
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
}
*/