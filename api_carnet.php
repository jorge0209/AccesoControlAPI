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

// Lee el JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    echo json_encode([
        'success' => false,
        'message' => 'JSON inválido',
    ]);
    exit;
}

$num_cedula = trim($input['num_cedula'] ?? '');
if ($num_cedula === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Debe enviar la cédula.',
    ]);
    exit;
}

try {
    // Buscar al usuario en credenciales
    $sqlCred = "
        SELECT
          id,
          num_cedula,
          nombre,
          apellido,
          tipo_persona
        FROM credenciales
        WHERE num_cedula = :cedula
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sqlCred);
    $stmt->execute([':cedula' => $num_cedula]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'No existe un usuario con esa cédula.',
        ]);
        exit;
    }

    $credencialId = (int)$user['id'];
    $tipo         = $user['tipo_persona']; // ESTUDIANTE / PROFESOR / EMPLEADO / VISITANTE
    $detalle      = '';

    // Buscar datos adicionales según tipo_persona
    switch ($tipo) {
        case 'ESTUDIANTE':
            $sqlEst = "
                SELECT carrera, semestre
                FROM estudiantes
                WHERE credencial_id = :cid
                LIMIT 1
            ";
            $stmtEst = $pdo->prepare($sqlEst);
            $stmtEst->execute([':cid' => $credencialId]);
            $est = $stmtEst->fetch(PDO::FETCH_ASSOC);

            if ($est) {
                $detalle = $est['carrera'] ?? '';
                if (!empty($est['semestre'])) {
                    $detalle .= ($detalle ? ' • ' : '') . 'Semestre ' . $est['semestre'];
                }
            }
            break;

        case 'PROFESOR':
            $sqlProf = "
                SELECT departamento
                FROM profesores
                WHERE credencial_id = :cid
                LIMIT 1
            ";
            $stmtProf = $pdo->prepare($sqlProf);
            $stmtProf->execute([':cid' => $credencialId]);
            $prof = $stmtProf->fetch(PDO::FETCH_ASSOC);

            if ($prof && !empty($prof['departamento'])) {
                $detalle = 'Departamento: ' . $prof['departamento'];
            }
            break;

        case 'EMPLEADO':
            $sqlEmp = "
                SELECT departamento, area_laboral, cargo
                FROM empleados
                WHERE credencial_id = :cid
                LIMIT 1
            ";
            $stmtEmp = $pdo->prepare($sqlEmp);
            $stmtEmp->execute([':cid' => $credencialId]);
            $emp = $stmtEmp->fetch(PDO::FETCH_ASSOC);

            $partes = [];
            if (!empty($emp['cargo'])) {
                $partes[] = $emp['cargo'];
            }
            if (!empty($emp['departamento'])) {
                $partes[] = $emp['departamento'];
            }
            if (!empty($emp['area_laboral'])) {
                $partes[] = $emp['area_laboral'];
            }
            $detalle = implode(' • ', $partes);
            break;

        case 'VISITANTE':
            $sqlVis = "
                SELECT motivo_visita, persona_visitar, empresa_organizacion
                FROM visitantes
                WHERE credencial_id = :cid
                LIMIT 1
            ";
            $stmtVis = $pdo->prepare($sqlVis);
            $stmtVis->execute([':cid' => $credencialId]);
            $vis = $stmtVis->fetch(PDO::FETCH_ASSOC);

            $partes = [];
            if (!empty($vis['persona_visitar'])) {
                $partes[] = 'Visita a: ' . $vis['persona_visitar'];
            }
            if (!empty($vis['motivo_visita'])) {
                $partes[] = 'Motivo: ' . $vis['motivo_visita'];
            }
            if (!empty($vis['empresa_organizacion'])) {
                $partes[] = 'Empresa: ' . $vis['empresa_organizacion'];
            }
            $detalle = implode(' • ', $partes);
            break;
    }

    // Por ahora null no hay foto
    $fotoUrl = null;

    echo json_encode([
        'success' => true,
        'data' => [
            'num_cedula'   => $user['num_cedula'],
            'nombre'       => $user['nombre'],
            'apellido'     => $user['apellido'],
            'tipo_persona' => $tipo,
            'detalle'      => $detalle,
            'foto_url'     => $fotoUrl,
            'qr_data'      => $user['num_cedula'], // lo que aparecerá en el QR
        ],
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error interno al obtener el carnet.',
        'error'   => $e->getMessage(), 
    ]);
}
