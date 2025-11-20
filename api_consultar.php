<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config.php';
$pdo = getPDO();

// Leer filtros desde JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = [];
}

$nombre      = trim($input['nombre'] ?? '');
$tipoPersona = strtoupper(trim($input['tipo_persona'] ?? ''));

// ========================
// Filtros dinámicos comunes
// ========================
$conditionsUsuarios = [];
$paramsUsuarios     = [];

$conditionsAccesos = [];
$paramsAccesos     = [];

// Filtro por nombre (en credenciales)
if ($nombre !== '') {
    $conditionsUsuarios[]      = "(nombre LIKE :nombre OR apellido LIKE :nombre)";
    $paramsUsuarios[':nombre'] = '%' . $nombre . '%';

    $conditionsAccesos[]       = "(c.nombre LIKE :nombreAcc OR c.apellido LIKE :nombreAcc)";
    $paramsAccesos[':nombreAcc'] = '%' . $nombre . '%';
}

// Filtro por tipo de persona
$tiposValidos = ['ESTUDIANTE', 'PROFESOR', 'EMPLEADO', 'VISITANTE'];
if ($tipoPersona !== '' && in_array($tipoPersona, $tiposValidos, true)) {
    $conditionsUsuarios[]         = "tipo_persona = :tipo";
    $paramsUsuarios[':tipo']      = $tipoPersona;

    $conditionsAccesos[]          = "ra.tipo_persona = :tipoAcc";
    $paramsAccesos[':tipoAcc']    = $tipoPersona;
}

// ========================
// Consulta de usuarios
// ========================
$sqlUsuarios = "
    SELECT
      id,
      num_cedula,
      nombre,
      apellido,
      correo,
      num_telefono,
      tipo_persona
    FROM credenciales
";

if (!empty($conditionsUsuarios)) {
    $sqlUsuarios .= " WHERE " . implode(' AND ', $conditionsUsuarios);
}
$sqlUsuarios .= " ORDER BY nombre, apellido";

try {
    $stmtU = $pdo->prepare($sqlUsuarios);
    $stmtU->execute($paramsUsuarios);
    $usuarios = $stmtU->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al consultar usuarios',
        'error'   => $e->getMessage(),
    ]);
    exit;
}

// ========================
// Consulta de historial de accesos
// ========================
$sqlAccesos = "
    SELECT
      ra.id,
      c.num_cedula,
      c.nombre,
      c.apellido,
      ra.tipo_persona,
      ra.tipo_acceso,
      ra.area,
      ra.fecha_hora,
      ra.observacion
    FROM registro_accesos ra
    JOIN credenciales c ON c.id = ra.credencial_id
";

if (!empty($conditionsAccesos)) {
    $sqlAccesos .= " WHERE " . implode(' AND ', $conditionsAccesos);
}
$sqlAccesos .= " ORDER BY ra.fecha_hora DESC";

try {
    $stmtA = $pdo->prepare($sqlAccesos);
    $stmtA->execute($paramsAccesos);
    $accesos = $stmtA->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al consultar historial de accesos',
        'error'   => $e->getMessage(),
    ]);
    exit;
}

// Respuesta conjunta
echo json_encode([
    'success'        => true,
    'usuarios'       => $usuarios,
    'accesos'        => $accesos,
    'total_usuarios' => count($usuarios),
    'total_accesos'  => count($accesos),
]);
