<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/metodos.php';

$firebase = new MetodosFirebase();

function ok($data = []) { echo json_encode(['ok'=>true] + $data); exit; }
function fail($msg, $code=400){ http_response_code($code); echo json_encode(['ok'=>false,'error'=>$msg]); exit; }

$action = $_GET['action'] ?? $_POST['action'] ?? null;
if (!$action) fail('Falta action');

ob_start();

try {
  switch ($action) {
    case 'registrar': {
      $raw = file_get_contents('php://input');
      $body = $raw ? json_decode($raw, true) : $_POST;

      foreach (['tipo','ubicacion','region'] as $k) {
        if (empty($body[$k])) {
          $noise = trim(ob_get_clean());
          fail("Falta el campo: $k" . ($noise ? " | $noise" : ""));
        }
      }

      $id = $firebase->RegistrarDenuncia([
        'tipo'      => $body['tipo'],
        'ubicacion' => $body['ubicacion'],
        'region'    => $body['region'],
        'detalles'  => $body['detalles'] ?? ''
      ]);

      $noise = trim(ob_get_clean());
      if ($id === false) fail('No se pudo registrar' . ($noise ? " | $noise" : ""));
      ok(['id'=>$id]);
    }

    case 'listar': {
      $todas = $firebase->obtenerTodasLasDenuncias() ?? [];
      ob_end_clean();
      ok(['denuncias'=>$todas]);
    }

    case 'cambiar_estado': {
      $raw = file_get_contents('php://input');
      $body = $raw ? json_decode($raw, true) : $_POST;
      $id = $body['id'] ?? null;
      $estado = $body['estado'] ?? null;
      if (!$id || !$estado) {
        $noise = trim(ob_get_clean());
        fail('id y estado son obligatorios' . ($noise ? " | $noise" : ""));
      }
      $done = $firebase->cambiarEstado($id, $estado);
      $noise = trim(ob_get_clean());
      if (!$done) fail('No se pudo cambiar el estado' . ($noise ? " | $noise" : ""));
      ok();
    }

    case 'editar': {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $noise = trim(ob_get_clean());
            fail('Falta el ID de la denuncia' . ($noise ? " | $noise" : ""));
        }

        $raw = file_get_contents('php://input');
        $body = $raw ? json_decode($raw, true) : null;
        if (!$body || !is_array($body)) {
            $noise = trim(ob_get_clean());
            fail('Datos no válidos o faltantes para la edición' . ($noise ? " | $noise" : ""));
        }

        $done = $firebase->editarDenuncia($id, $body);

        $noise = trim(ob_get_clean());
        if (!$done) {
            fail('No se pudo editar la denuncia' . ($noise ? " | $noise" : ""));
        }
        ok();
    }

    case 'obtener': {
      $id = $_GET['id'] ?? null;
      if (!$id) fail('Falta el ID para obtener la denuncia');
      $denuncia = $firebase->obtenerDenuncia($id);
      if (!$denuncia) fail("Denuncia no encontrada con ID: $id");
      ob_end_clean();
      ok(['denuncia' => $denuncia]);
    }

    default: {
      $noise = trim(ob_get_clean());
      fail('action no soportada' . ($noise ? " | $noise" : ""));
    }
  }
} catch (Throwable $e) {
  $noise = trim(ob_get_clean());
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'error' => 'Excepción: '.$e->getMessage() . ($noise ? " | $noise" : "")
  ]);
  exit;
}
