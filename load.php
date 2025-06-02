<?php 
require 'config.php';

$columns = ['bibliotecat.palabra_clave', 'diarios.nombre AS diario_nombre', 'bibliotecat.fecha', 'bibliotecat.link'];
$table = "bibliotecat";
$id = 'bibliotecat.palabra_clave';

$campo = isset($_POST['campo']) ? trim($_POST['campo']) : '';
$diarios_ids = isset($_POST['diario']) && is_array($_POST['diario']) ? array_filter($_POST['diario'], 'is_numeric') : [];
$fecha_desde = isset($_POST['fecha_desde']) ? trim($_POST['fecha_desde']) : '';
$fecha_hasta = isset($_POST['fecha_hasta']) ? trim($_POST['fecha_hasta']) : '';

$where = [];
$params = [];

if (!empty($campo)) {
    $palabras = preg_split('/[\s\-,;]+/', $campo, -1, PREG_SPLIT_NO_EMPTY);
    
    if (count($palabras) > 1) {
        $condiciones_palabras = [];
        foreach ($palabras as $palabra) {
            $palabra = trim($palabra);
            if (!empty($palabra) && strlen($palabra) > 1) {
                $condiciones_palabras[] = "bibliotecat.palabra_clave LIKE ?";
                $params[] = "%$palabra%";
            }
        }
        if (!empty($condiciones_palabras)) {
            $where[] = "(" . implode(' AND ', $condiciones_palabras) . ")";
        }
    } else {
        $where[] = "bibliotecat.palabra_clave LIKE ?";
        $params[] = "%$campo%";
    }
}

if (!empty($diarios_ids)) {
    $placeholders = implode(',', array_fill(0, count($diarios_ids), '?'));
    $where[] = "bibliotecat.diario_id IN ($placeholders)";
    foreach ($diarios_ids as $id) {
        $params[] = $id;
    }
}

if ($fecha_desde && $fecha_hasta) {
    $where[] = "bibliotecat.fecha BETWEEN ? AND ?";
    $params[] = $fecha_desde;
    $params[] = $fecha_hasta;
} elseif ($fecha_desde) {
    $where[] = "bibliotecat.fecha >= ?";
    $params[] = $fecha_desde;
} elseif ($fecha_hasta) {
    $where[] = "bibliotecat.fecha <= ?";
    $params[] = $fecha_hasta;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$limit = isset($_POST['registros']) ? (int)$_POST['registros'] : 15;
$pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
$inicio = ($pagina - 1) * $limit;

$orderCol = isset($_POST['ordenarPor']) && in_array($_POST['ordenarPor'], ['palabra_clave', 'diario', 'fecha', 'link']) ? $_POST['ordenarPor'] : 'fecha';
$orderMapping = [
    'palabra_clave' => 'bibliotecat.palabra_clave',
    'diario' => 'diarios.nombre',
    'fecha' => 'bibliotecat.fecha',
    'link' => 'bibliotecat.link'
];
$orderColumn = $orderMapping[$orderCol] ?? 'bibliotecat.fecha';
$orderType = isset($_POST['orden']) && in_array(strtoupper($_POST['orden']), ['ASC', 'DESC']) ? strtoupper($_POST['orden']) : 'ASC';
$sOrder = "ORDER BY $orderColumn $orderType";

$sql = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $columns) . " 
        FROM bibliotecat 
        LEFT JOIN diarios ON bibliotecat.diario_id = diarios.id 
        $whereClause 
        $sOrder 
        LIMIT $inicio, $limit";

$stmt = $conn->prepare($sql);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

$totalFiltro = $conn->query("SELECT FOUND_ROWS()")->fetch_array()[0];
$totalRegistros = $conn->query("SELECT COUNT($id) FROM $table")->fetch_array()[0];

$output = [
    'totalRegistros' => $totalRegistros,
    'totalFiltro' => $totalFiltro,
    'data' => '',
    'paginacion' => ''
];

if ($resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $fechaFormateada = date("d/m/Y", strtotime($row['fecha']));
        $output['data'] .= '<tr>';
        $output['data'] .= '<td>' . htmlspecialchars($row['palabra_clave']) . '</td>';
        $output['data'] .= '<td>' . htmlspecialchars($row['diario_nombre']) . '</td>';
        $output['data'] .= '<td>' . $fechaFormateada . '</td>';
        $output['data'] .= '<td><a href="' . htmlspecialchars($row['link']) . '" target="_blank">Ver enlace</a></td>';
        $output['data'] .= '<td>';
        $output['data'] .= '<button class="btn btn-danger btn-sm" onclick="deleteRecord(\'' . htmlspecialchars($row['palabra_clave'], ENT_QUOTES) . '\')">Eliminar</button> ';
        $output['data'] .= '<button class="btn btn-warning btn-sm" onclick="editarRegistro(\'' . urlencode($row['palabra_clave']) . '\')">Editar</button>';
        $output['data'] .= '</td>';
        $output['data'] .= '</tr>';
    }
} else {
    $output['data'] .= '<tr><td colspan="5" class="text-center text-muted">No se encontraron resultados para los filtros aplicados</td></tr>';
}

$totalPaginas = ceil($totalFiltro / $limit);
$output['paginacion'] .= '<nav><ul class="pagination">';
if ($pagina > 1) {
    $output['paginacion'] .= '<li class="page-item"><a class="page-link" href="#" onclick="nextPage(1)">Primera</a></li>';
}
for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++) {
    $output['paginacion'] .= '<li class="page-item' . ($pagina == $i ? ' active' : '') . '"><a class="page-link" href="#" onclick="nextPage(' . $i . ')">' . $i . '</a></li>';
}
if ($pagina < $totalPaginas) {
    $output['paginacion'] .= '<li class="page-item"><a class="page-link" href="#" onclick="nextPage(' . $totalPaginas . ')">Última</a></li>';
}
$output['paginacion'] .= '</ul></nav>';

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>