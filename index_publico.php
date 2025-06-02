<?php 
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
    <title>Hemeroteca Digital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #baf0fd;
        }
    </style>
</head>
<body class="container mt-5">
    <nav class="mb-4">
        <h1 class="text-center mb-4">Hemeroteca Digital</h1><br>
        <div class="d-flex gap-2">
            <a href="https://drive.google.com/drive/folders/10YyFLehIsy9HR5eRKyhvmnySOdIGKNBQ" class="btn btn-secondary">Ver en Drive</a>
            <button class="btn btn-warning" onclick="mostrarModal()">Modo Editor</button>
        </div>
    </nav>

    <div id="modalPassword" class="modal" tabindex="-1" style="display:none;">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Modo Editor</h5>
            <button type="button" class="btn-close" onclick="cerrarModal()"></button>
          </div>
          <div class="modal-body">
            <input type="password" id="claveEditor" class="form-control" placeholder="Contraseña">
            <div id="errorClave" class="text-danger mt-2"></div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
            <button class="btn btn-primary" onclick="verificarClave()">Acceder</button>
          </div>
        </div>
      </div>
    </div>

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
            </tr>
        </thead>
        <tbody id="data-table"></tbody>
    </table>
    <div id="pagination" class="d-flex justify-content-center"></div>

    <script>
        let sortColumn = 'fecha';
        let sortOrder = 'ASC';

        function getData(page = 1) {
            const campo = document.getElementById("campo").value;
            const fecha_desde = document.getElementById("fecha_desde").value;
            const fecha_hasta = document.getElementById("fecha_hasta").value;
            const diarios = [];
    $('.diario-checkbox:checked').each(function () {
        diarios.push($(this).val());
    });

            $.ajax({
                url: "load_publico.php",
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
                    document.getElementById("data-table").innerHTML = data.data;
                    document.getElementById("pagination").innerHTML = data.paginacion;
                    document.getElementById("message").innerHTML = '';
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

        function nextPage(page) {
            getData(page);
        }

        function mostrarModal() {
            document.getElementById('modalPassword').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('modalPassword').style.display = 'none';
            document.getElementById('claveEditor').value = '';
            document.getElementById('errorClave').innerText = '';
        }

        function verificarClave() {
            const clave = document.getElementById("claveEditor").value;

            fetch("verificar_clave.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `clave=${encodeURIComponent(clave)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.acceso === true) {
                    window.location.href = "index.php";
                } else {
                    document.getElementById("errorClave").innerText = "Contraseña incorrecta.";
                }
            });
        }

        document.addEventListener("DOMContentLoaded", () => {
            $('.selectpicker').selectpicker(); 
            getData();
        });
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
</body>
</html>
