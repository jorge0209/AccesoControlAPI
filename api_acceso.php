<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config.php';
$pdo = getPDO();

// Leer JSON que envía Ionic
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "JSON inválido"]);
    exit;
}

$num_cedula   = trim($input["num_cedula"]   ?? "");
$tipo         = trim($input["tipo"]         ?? ""); // entrada / salida
$areaDestino  = trim($input["area_destino"] ?? "");

if ($num_cedula === "" || $tipo === "" || $areaDestino === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Debe enviar cédula, tipo de acceso y área de destino."
    ]);
    exit;
}

// Normalizamos
$tipoUpper = strtoupper($tipo); // ENTRADA / SALIDA
$areaEnum  = $areaDestino;      // Ya viene como ENUM válido desde el select

try {
    // 1) Buscar la credencial y el tipo_persona
    $stmt = $pdo->prepare("
        SELECT id, nombre, apellido, tipo_persona
        FROM credenciales
        WHERE num_cedula = :cedula
    ");
    $stmt->execute([":cedula" => $num_cedula]);
    $credencial = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$credencial) {
        echo json_encode([
            "success" => false,
            "message" => "No existe un usuario con esa cédula."
        ]);
        exit;
    }

    $credencialId = (int)$credencial["id"];
    $tipoPersona  = $credencial["tipo_persona"]; // ESTUDIANTE / PROFESOR / ...

    // 2) Insertar en registro_accesos (usando estructura real de la tabla)
    $sqlInsert = "
        INSERT INTO registro_accesos
            (credencial_id, tipo_persona, tipo_acceso, area, fecha_hora, acceso_permitido, observacion)
        VALUES
            (:credencial_id, :tipo_persona, :tipo_acceso, :area, NOW(), :acceso_permitido, :observacion)
    ";

    $stmtIns = $pdo->prepare($sqlInsert);
    $stmtIns->execute([
        ":credencial_id"    => $credencialId,
        ":tipo_persona"     => $tipoPersona,
        ":tipo_acceso"      => $tipoUpper,    // ENTRADA / SALIDA
        ":area"             => $areaEnum,     // uno de los ENUM de `area`
        ":acceso_permitido" => 1,
        ":observacion"      => "Acceso registrado correctamente",
    ]);

    // 3) Calcular personas en campus
    $sqlCount = "
        SELECT
          SUM(CASE WHEN tipo_acceso = 'ENTRADA' THEN 1 ELSE 0 END) -
          SUM(CASE WHEN tipo_acceso = 'SALIDA'  THEN 1 ELSE 0 END) AS personas_campus
        FROM registro_accesos
    ";
    $personasCampus = (int)($pdo->query($sqlCount)->fetchColumn() ?? 0);

    // 4) Obtener último acceso
    $sqlLast = "
        SELECT ra.fecha_hora,
               ra.tipo_acceso,
               ra.area,
               c.nombre,
               c.apellido
        FROM registro_accesos ra
        JOIN credenciales c ON c.id = ra.credencial_id
        ORDER BY ra.fecha_hora DESC
        LIMIT 1
    ";
    $last = $pdo->query($sqlLast)->fetch(PDO::FETCH_ASSOC);

    $ultimoTexto = null;
    if ($last) {
        $nombre  = $last["nombre"];
        $apell   = $last["apellido"];
        $area    = $last["area"];             // p.ej. AUDITORIO
        $tipoTxt = ($last["tipo_acceso"] === 'ENTRADA') ? 'entró' : 'salió';

        $fechaObj = new DateTime($last["fecha_hora"]);
        $fechaFmt = $fechaObj->format('d/m/Y H:i');

        $ultimoTexto = "{$nombre} {$apell} {$tipoTxt} por {$area} el {$fechaFmt}";
    }

    echo json_encode([
        "success"            => true,
        "message"            => "Acceso registrado correctamente.",
        "personas_en_campus" => $personasCampus,
        "ultimo_acceso"      => $ultimoTexto,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error interno al registrar acceso",
        "error"   => $e->getMessage()
    ]);
}
