<?php
require 'verificar_sesion.php';
require 'config.php';

$diarios = [];
$result = $conn->query("SELECT id, nombre FROM diarios ORDER BY nombre ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $diarios[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Biblioteca</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #baf0fd;
        }
        .dropdown-menu {
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="container mt-5">

<nav class="mb-4">
    <h1 class="text-center mb-4">Búsqueda</h1><br>
    <div class="d-flex gap-2">
        <a href="https://drive.google.com/drive/folders/10YyFLehIsy9HR5eRKyhvmnySOdIGKNBQ" class="btn btn-primary">Ir a Google Drive</a>
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Opciones
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="agregar.php">Agregar información</a></li>
                <li><a class="dropdown-item" href="agregar_diario.php">Agregar/Quitar diarios</a></li>
                <li><a class="dropdown-item" href="cambiar_contraseña.php">Cambiar contraseña</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="cerrar_sesion.php">Cerrar Modo Editor</a></li>
            </ul>
        </div>
    </div>
</nav>

<form id="search-form" class="row g-3 align-items-end mb-4">
    <div class="col-md-4">
        <label for="campo" class="form-label">Palabra clave:</label>
        <input type="text" name="campo" id="campo" class="form-control" placeholder="Buscar por palabra clave">
    </div>

    <div class="col-md-3">
        <label class="form-label">Diario:</label>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                Seleccionar diarios
            </button>
            <ul class="dropdown-menu w-100 p-2" id="diario">
                <?php foreach ($diarios as $diario): ?>
                    <li>
                        <div class="form-check">
                            <input class="form-check-input diario-checkbox" type="checkbox" value="<?= $diario['id'] ?>" id="diario<?= $diario['id'] ?>">
                            <label class="form-check-label" for="diario<?= $diario['id'] ?>">
                                <?= htmlspecialchars($diario['nombre']) ?>
                            </label>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="col-md-2">
        <label for="fecha_desde" class="form-label">Fecha desde:</label>
        <input type="date" name="fecha_desde" id="fecha_desde" class="form-control">
    </div>
    <div class="col-md-2">
        <label for="fecha_hasta" class="form-label">Fecha hasta:</label>
        <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control">
    </div>
    <div class="col-md-1 d-grid">
        <button type="button" onclick="getData()" class="btn btn-primary">Buscar</button>
    </div>
</form>

<div id="message"></div>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th onclick="sortData('palabra_clave')">Palabra Clave</th>
            <th onclick="sortData('diario')">Diario</th>
            <th onclick="sortData('fecha')">Fecha</th>
            <th>Archivo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody id="data-table"></tbody>
</table>
<div id="pagination" class="d-flex justify-content-center"></div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmar eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        ¿Estás seguro de que deseas eliminar este registro?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
let sortColumn = 'fecha';
let sortOrder = 'ASC';
let currentId = null;

function getData(page = 1) {
    const campo = $('#campo').val();
    const fecha_desde = $('#fecha_desde').val();
    const fecha_hasta = $('#fecha_hasta').val();
    
    const diarios = [];
    $('.diario-checkbox:checked').each(function () {
        diarios.push($(this).val());
    });

    $.ajax({
        url: "load.php",
        type: "POST",
        data: {
            campo: campo,
            fecha_desde: fecha_desde,
            fecha_hasta: fecha_hasta,
            diario: diarios,
            pagina: page,
            ordenarPor: sortColumn,
            orden: sortOrder,
            registros: 15
        },
        success: function(response) {
            const data = JSON.parse(response);
            $("#data-table").html(data.data);
            $("#pagination").html(data.paginacion);
            $("#message").html('');
        },
        error: function() {
            alert("Error al cargar los datos");
        }
    });
}

function sortData(column) {
    if (sortColumn === column) {
        sortOrder = sortOrder === 'ASC' ? 'DESC' : 'ASC';
    } else {
        sortColumn = column;
        sortOrder = 'ASC';
    }
    getData();
}

function deleteRecord(id) {
    currentId = id;
    $('#deleteModal').modal('show');
}

$('#confirmDeleteBtn').on('click', function () {
    $.ajax({
        url: `eliminar.php?id=${currentId}`,
        type: "GET",
        success: function(response) {
            const data = JSON.parse(response);
            $('#deleteModal').modal('hide');
            if (data.success) {
                $("#message").html(`<div class="alert alert-success">${data.message}</div>`);
                getData();
            } else {
                $("#message").html(`<div class="alert alert-danger">${data.message}</div>`);
            }
        },
        error: function() {
            $("#message").html(`<div class="alert alert-danger">Error al intentar eliminar</div>`);
        }
    });
});

function editarRegistro(id) {
    window.location.href = 'editar.php?id=' + encodeURIComponent(id);
}

function nextPage(page) {
    getData(page);
}

$(document).ready(function () {
    getData();
});
</script>
</body>
</html>
