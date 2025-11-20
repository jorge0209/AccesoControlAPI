<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config.php';
$pdo = getPDO();

// Leer JSON desde Ionic
$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    echo json_encode(["success" => false, "message" => "JSON inválido"]);
    exit;
}

$num_cedula   = trim($input["num_cedula"] ?? "");
$tipo         = trim($input["tipo"] ?? "");
$areaDestino  = trim($input["area_destino"] ?? "");

if ($num_cedula === "" || $tipo === "" || $areaDestino === "") {
    echo json_encode([
        "success" => false,
        "message" => "Debe enviar cédula, tipo y área destino."
    ]);
    exit;
}

$tipoUpper = strtoupper($tipo);

// =============================
// PERMISOS POR TIPO DE PERSONA
// =============================

$permisos = [
    "ESTUDIANTE" => [
        "ENTRADA_PRINCIPAL","AULAS","BIBLIOTECA","LABORATORIOS",
        "CAFETERIA","AUDITORIO","GIMNASIO","ESTACIONAMIENTO",
        "AREA_DEPORTIVA","LABORATORIO_COMPUTO"
    ],

    "PROFESOR" => [
        "ENTRADA_PRINCIPAL","AULAS","BIBLIOTECA","LABORATORIOS",
        "CAFETERIA","AUDITORIO","ESTACIONAMIENTO","AREA_DEPORTIVA",
        "SALA_PROFESORES","LABORATORIO_COMPUTO"
    ],

    "EMPLEADO" => [
        "ENTRADA_PRINCIPAL","AULAS","BIBLIOTECA","LABORATORIOS",
        "OFICINAS_ADMIN","CAFETERIA","AUDITORIO","GIMNASIO",
        "ESTACIONAMIENTO","AREA_DEPORTIVA","SALA_PROFESORES",
        "LABORATORIO_COMPUTO"
    ],

    "VISITANTE" => [
        "ENTRADA_PRINCIPAL","AUDITORIO","ESTACIONAMIENTO",
        "AREA_DEPORTIVA","OFICINAS_ADMIN","SALA_PROFESORES"
    ]
];

try {

    // 1) Verificar usuario
    $stmt = $pdo->prepare("
        SELECT id, nombre, apellido, tipo_persona
        FROM credenciales
        WHERE num_cedula = :cedula
    ");
    $stmt->execute([":cedula" => $num_cedula]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["success" => false, "message" => "Cédula no encontrada."]);
        exit;
    }

    $credencialId = $user["id"];
    $tipoPersona = $user["tipo_persona"]; // ESTUDIANTE, PROFESOR…

    // ======================================
    // VALIDACIÓN 1: ¿Puede entrar a esa área?
    // ======================================

    if (!in_array($areaDestino, $permisos[$tipoPersona])) {
        echo json_encode([
            "success" => false,
            "message" => "Los {$tipoPersona}s NO pueden acceder al área {$areaDestino}."
        ]);
        exit;
    }

    // =================================================
    // VALIDACIÓN 2: ¿Está actualmente dentro de un área?
    // =================================================

    // Saber si hubo una entrada sin salida
    $stmt2 = $pdo->prepare("
        SELECT area, tipo_acceso, fecha_hora
        FROM registro_accesos
        WHERE credencial_id = :cid
        ORDER BY fecha_hora DESC
        LIMIT 1
    ");

    $stmt2->execute([":cid" => $credencialId]);
    $last = $stmt2->fetch(PDO::FETCH_ASSOC);

    $estaDentro = false;
    $ultimaArea = null;

    if ($last) {
        if ($last["tipo_acceso"] === "ENTRADA") {
            $estaDentro = true;
            $ultimaArea = $last["area"];
        }
    }

    // Si intenta entrar a un área distinta sin salir primero
    if ($tipoUpper === "ENTRADA" && $estaDentro && $ultimaArea !== $areaDestino) {
        echo json_encode([
            "success" => false,
            "message" => "Debe salir de {$ultimaArea} antes de entrar a {$areaDestino}."
        ]);
        exit;
    }

    // =============================
    // GUARDAR REGISTRO CORRECTO
    // =============================

    $sql = "
        INSERT INTO registro_accesos
            (credencial_id, tipo_persona, tipo_acceso, area, fecha_hora, acceso_permitido, observacion)
        VALUES
            (:cid, :tipo_persona, :tipo_acceso, :area, NOW(), 1, 'OK')
    ";

    $pdo->prepare($sql)->execute([
        ":cid"          => $credencialId,
        ":tipo_persona" => $tipoPersona,
        ":tipo_acceso"  => $tipoUpper,
        ":area"         => $areaDestino,
    ]);

    // =============================
    // CÁLCULO DE PERSONAS EN CAMPUS
    // =============================

    $sqlCount = "
        SELECT
            SUM(CASE WHEN tipo_acceso = 'ENTRADA' THEN 1 ELSE 0 END) -
            SUM(CASE WHEN tipo_acceso = 'SALIDA'  THEN 1 ELSE 0 END)
        FROM registro_accesos
    ";

    $personasCampus = (int)$pdo->query($sqlCount)->fetchColumn();

    // =============================
    // ÚLTIMO ACCESO (FORMATEADO)
    // =============================

    $sqlLast = "
        SELECT ra.fecha_hora, ra.tipo_acceso, ra.area,
               c.nombre, c.apellido
        FROM registro_accesos ra
        JOIN credenciales c ON c.id = ra.credencial_id
        ORDER BY ra.fecha_hora DESC
        LIMIT 1
    ";

    $l = $pdo->query($sqlLast)->fetch(PDO::FETCH_ASSOC);

    $txt = null;
    if ($l) {
        $tipoTxt = $l["tipo_acceso"] === "ENTRADA" ? "entró" : "salió";
        $fecha = (new DateTime($l["fecha_hora"]))->format("d/m/Y H:i");
        $txt = "{$l['nombre']} {$l['apellido']} {$tipoTxt} por {$l['area']} el {$fecha}";
    }

    $mensaje = ($tipoUpper === "SALIDA")
    ? "Salida registrada correctamente."
    : "Entrada registrada correctamente.";

echo json_encode([
    "success" => true,
    "message" => $mensaje,
    "personas_en_campus" => $personasCampus,
    "ultimo_acceso" => $txt
]);


} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "ERROR INTERNO",
        "error"   => $e->getMessage()
    ]);
}
