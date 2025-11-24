<?php
// ================= CORS + JSON =================
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ================= CONEXIÓN =================
require_once __DIR__ . '/config.php';
$pdo = getPDO();

// ================= LEER JSON DE IONIC =================
$input = json_decode(file_get_contents('php://input'), true);

// Si no vino nada, lo tratamos como array vacío
if (!is_array($input)) {
    $input = [];
}

$num_cedula   = trim($input['num_cedula']   ?? '');
$nombre       = trim($input['nombre']       ?? '');
$apellido     = trim($input['apellido']     ?? '');
$correo       = trim($input['correo']       ?? '');
$contrasena   = trim($input['contrasena']   ?? '');
$num_telefono = trim($input['num_telefono'] ?? '');
$tipo_persona = trim($input['tipo_persona'] ?? '');

// Campos dinámicos que vienen del formulario Ionic
$carrera               = $input['carrera']               ?? null;
$semestre              = $input['semestre']              ?? null;
$departamento          = $input['departamento']          ?? null; 
$cargo                 = $input['cargo']                 ?? null; 
$departamento_empleado = $input['departamento_empleado'] ?? null; 
$motivo                = $input['motivo']                ?? null; 
$persona_visita        = $input['persona_visita']        ?? null; 
$empresa               = $input['empresa']               ?? null; 

// ================= VALIDACIONES BÁSICAS =================
if (
    $num_cedula === '' ||
    $nombre === '' ||
    $apellido === '' ||
    $correo === '' ||
    $contrasena === '' ||
    $num_telefono === '' ||
    $tipo_persona === ''
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Todos los campos principales son obligatorios',
    ]);
    exit;
}

// En el select de Ionic usamos "estudiante/profesor/empleado/visitante" en minúsculas.
// La BD usa ENUM en mayúsculas: ESTUDIANTE, PROFESOR, EMPLEADO, VISITANTE.
$tipo_persona = strtoupper($tipo_persona);

$permitidos = ['ESTUDIANTE', 'PROFESOR', 'EMPLEADO', 'VISITANTE'];
if (!in_array($tipo_persona, $permitidos, true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Tipo de persona no válido',
    ]);
    exit;
}


$hash = $contrasena;

try {
    // Usamos transacción: o se guarda todo, o nada
    $pdo->beginTransaction();

    // ========== 1) INSERTAR EN CREDENCIALES ==========
    $sqlCred = "INSERT INTO credenciales
                (num_cedula, nombre, apellido, correo, `contraseña`, num_telefono, tipo_persona)
                VALUES
                (:num_cedula, :nombre, :apellido, :correo, :contrasena, :num_telefono, :tipo_persona)";

    $stmtCred = $pdo->prepare($sqlCred);
    $stmtCred->execute([
        ':num_cedula'   => $num_cedula,
        ':nombre'       => $nombre,
        ':apellido'     => $apellido,
        ':correo'       => $correo,
        ':contrasena'   => $hash,
        ':num_telefono' => $num_telefono,
        ':tipo_persona' => $tipo_persona,
    ]);

    $credencialId = (int)$pdo->lastInsertId();

    // ========== 2) INSERTAR EN TABLA SEGÚN TIPO ==========
    $detalles = [];

    switch ($tipo_persona) {
        case 'ESTUDIANTE':
            // Tabla: estudiantes (credencial_id, carrera, semestre)
            if ($carrera !== null || $semestre !== null) {
                $sqlEst = "INSERT INTO estudiantes (credencial_id, carrera, semestre)
                           VALUES (:credencial_id, :carrera, :semestre)";
                $stmtEst = $pdo->prepare($sqlEst);
                $stmtEst->execute([
                    ':credencial_id' => $credencialId,
                    ':carrera'       => $carrera,
                    ':semestre'      => $semestre !== '' ? (int)$semestre : null,
                ]);
                $detalles['estudiante_id'] = (int)$pdo->lastInsertId();
            }
            break;

        case 'PROFESOR':
            // Tabla: profesores (credencial_id, departamento)
            if ($departamento !== null && $departamento !== '') {
                $sqlProf = "INSERT INTO profesores (credencial_id, departamento)
                            VALUES (:credencial_id, :departamento)";
                $stmtProf = $pdo->prepare($sqlProf);
                $stmtProf->execute([
                    ':credencial_id' => $credencialId,
                    ':departamento'  => $departamento,
                ]);
                $detalles['profesor_id'] = (int)$pdo->lastInsertId();
            }
            break;

        case 'EMPLEADO':
            // Tabla: empleados (credencial_id, departamento, area_laboral, cargo)
            // Usamos departamento_empleado para 'departamento'
            // y dejamos area_laboral NULL
            $depEmp = $departamento_empleado ?: null;
            $sqlEmp = "INSERT INTO empleados (credencial_id, departamento, area_laboral, cargo)
                       VALUES (:credencial_id, :departamento, :area_laboral, :cargo)";
            $stmtEmp = $pdo->prepare($sqlEmp);
            $stmtEmp->execute([
                ':credencial_id' => $credencialId,
                ':departamento'  => $depEmp,
                ':area_laboral'  => null,   
                ':cargo'         => $cargo,
            ]);
            $detalles['empleado_id'] = (int)$pdo->lastInsertId();
            break;

        case 'VISITANTE':
            // Tabla: visitantes (credencial_id, motivo_visita, persona_visitar, empresa_organizacion, fecha_visita, autorizacion_previa)
            $sqlVis = "INSERT INTO visitantes 
                       (credencial_id, motivo_visita, persona_visitar, empresa_organizacion, fecha_visita, autorizacion_previa)
                       VALUES
                       (:credencial_id, :motivo_visita, :persona_visitar, :empresa_organizacion, :fecha_visita, :autorizacion_previa)";
            $stmtVis = $pdo->prepare($sqlVis);
            $stmtVis->execute([
                ':credencial_id'       => $credencialId,
                ':motivo_visita'       => $motivo,
                ':persona_visitar'     => $persona_visita,
                ':empresa_organizacion'=> $empresa,
                ':fecha_visita'        => date('Y-m-d'),
                ':autorizacion_previa' => 1, // Por defecto, 1 = sí
            ]);
            $detalles['visitante_id'] = (int)$pdo->lastInsertId();
            break;
    }

    // TODO OK → hacemos commit
    $pdo->commit();

    echo json_encode([
        'success'      => true,
        'message'      => 'Usuario y detalles registrados correctamente',
        'credencialId' => $credencialId,
        'detalles'     => $detalles,
    ]);
} catch (PDOException $e) {

    // Si algo falla, deshacemos todo
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Error por duplicado (num_cedula o correo únicos)
    if ($e->errorInfo[1] == 1062) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe un usuario con esa cédula o correo',
        ]);
        exit;
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al registrar el usuario',
        'error'   => $e->getMessage(),
    ]);
}
