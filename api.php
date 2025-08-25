<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

ini_set('display_errors', 0);
ini_set('html_errors', 0);
error_reporting(E_ALL);

if (!ob_get_level()) { ob_start(); }

set_error_handler(function($severity, $message, $file, $line){
  if (!(error_reporting() & $severity)) return;
  while (ob_get_level()) ob_end_clean();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>"PHP error: $message at $file:$line"]);
  exit;
});

set_exception_handler(function(Throwable $e){
  while (ob_get_level()) ob_end_clean();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>"Excepción: ".$e->getMessage()]);
  exit;
});

register_shutdown_function(function(){
  $err = error_get_last();
  if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
    while (ob_get_level()) ob_end_clean();
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>"Fatal: {$err['message']} at {$err['file']}:{$err['line']}"]);
  }
});

function ok($data = []) {
  while (ob_get_level()) ob_end_clean();
  echo json_encode(['ok'=>true] + $data, JSON_UNESCAPED_UNICODE);
  exit;
}
function fail($msg, $code=400) {
  while (ob_get_level()) ob_end_clean();
  http_response_code($code);
  echo json_encode(['ok'=>false,'error'=>$msg], JSON_UNESCAPED_UNICODE);
  exit;
}

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/metodos.php';

$firebase = new MetodosFirebase();

$action = $_GET['action'] ?? $_POST['action'] ?? null;
if (!$action) fail('Falta action');

switch ($action) {
  case 'registrar': {
    $raw  = file_get_contents('php://input');
    $body = $raw ? json_decode($raw, true) : $_POST;

    foreach (['tipo','ubicacion','region'] as $k) {
      if (empty($body[$k])) fail("Falta el campo: $k");
    }

    $id = $firebase->RegistrarDenuncia([
      'tipo'      => $body['tipo'],
      'ubicacion' => $body['ubicacion'],
      'region'    => $body['region'],
      'detalles'  => $body['detalles'] ?? ''
    ]);

    if ($id === false) fail('No se pudo registrar');
    ok(['id'=>$id]);
  }

  case 'listar': {
    $todas = $firebase->obtenerTodasLasDenuncias() ?? [];
    ok(['denuncias'=>$todas]);
  }

    case 'cambiar_estado': {
      $raw  = file_get_contents('php://input');
      $body = $raw ? json_decode($raw, true) : null;

      $id = null;
      $estado = null;

      if (is_array($body)) {
        $id     = $body['id']     ?? $id;
        $estado = $body['estado'] ?? $estado;
      }
      $id     = $_POST['id']     ?? $_GET['id']     ?? $id;
      $estado = $_POST['estado'] ?? $_GET['estado'] ?? $estado;

      $id     = isset($id) ? trim((string)$id) : null;
      $estado = isset($estado) ? trim((string)$estado) : null;

      if ($id === null || $id === '' || $estado === null || $estado === '') {
        fail('id y estado son obligatorios');
      }

      $done = $firebase->cambiarEstado($id, $estado);
      if (!$done) fail('No se pudo cambiar el estado');
      ok();
    }


  case 'editar': {
    $id = $_GET['id'] ?? null;
    if (!$id) fail('Falta el ID de la denuncia');

    $raw  = file_get_contents('php://input');
    $body = $raw ? json_decode($raw, true) : null;
    if (!$body || !is_array($body)) fail('Datos no válidos o faltantes para la edición');

    $done = $firebase->editarDenuncia($id, $body);
    if (!$done) fail('No se pudo editar la denuncia');
    ok();
  }

  case 'obtener': {
    $id = $_GET['id'] ?? null;
    if (!$id) fail('Falta el ID para obtener la denuncia');

    $denuncia = $firebase->obtenerDenuncia($id);
    if (!$denuncia) fail("Denuncia no encontrada con ID: $id", 404);
    ok(['denuncia'=>$denuncia]);
  }

  case 'eliminar': {
    $raw  = file_get_contents('php://input');
    $body = $raw ? json_decode($raw, true) : $_POST;
    $id = $body['id'] ?? ($_GET['id'] ?? null);
    if (!$id) fail('Falta id');

    $done = $firebase->eliminarDenuncia($id);
    if (!$done) fail('No se pudo eliminar la denuncia');
    ok();
  }

  default:
    fail('action no soportada', 404);
}
