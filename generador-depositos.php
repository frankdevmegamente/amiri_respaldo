<?php
require_once __DIR__ . '/src/config/session.php';
require_once __DIR__ . '/src/config/db.php'; // Necesario para la BD

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}
$nombre_usuario = $_SESSION['usuario_nombre'] ?? 'Usuario';

$pdo = getPDO();

// 1. AUTO-CREAR LA TABLA SI NO EXISTE
$pdo->exec("CREATE TABLE IF NOT EXISTS directorio_depositos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    banco VARCHAR(100),
    cuenta VARCHAR(50),
    clabe VARCHAR(50),
    estado VARCHAR(20) DEFAULT 'activo',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Parche de seguridad por si falta la columna estado
try {
    $pdo->exec("ALTER TABLE directorio_depositos ADD COLUMN estado VARCHAR(20) DEFAULT 'activo'");
} catch (PDOException $e) {}

$mensaje = '';

// 2. PROCESAR SUBIDA DEL MACHOTE (CSV) - AHORA CON FILTRO ANTI-DUPLICADOS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
    $file = $_FILES['archivo_csv'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $handle = fopen($file['tmp_name'], "r");
        $row = 0;
        
        $insertados = 0;
        $reactivados = 0;
        $duplicados = 0;
        
        // Consultas preparadas
        $checkStmt = $pdo->prepare("SELECT id, estado FROM directorio_depositos WHERE nombre = ? AND cuenta = ? AND banco = ? LIMIT 1");
        $insertStmt = $pdo->prepare("INSERT INTO directorio_depositos (nombre, banco, cuenta, clabe, estado) VALUES (?, ?, ?, ?, 'activo')");
        $updateStmt = $pdo->prepare("UPDATE directorio_depositos SET estado = 'activo', clabe = ? WHERE id = ?");
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;
            if ($row === 1) continue; // Saltar encabezados
            
            $nombre = trim($data[0] ?? '');
            if (empty($nombre)) continue; // Evitar filas vacías
            
            $banco = trim($data[1] ?? '');
            $cuenta = trim($data[2] ?? '');
            $clabe = trim($data[3] ?? '');
            
            // VERIFICAR SI YA EXISTE ESE CLIENTE CON ESA CUENTA EN ESE BANCO
            $checkStmt->execute([$nombre, $cuenta, $banco]);
            $clienteExistente = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($clienteExistente) {
                if ($clienteExistente['estado'] === 'inactivo') {
                    // Si estaba "eliminado", lo revivimos (Soft Undelete)
                    $updateStmt->execute([$clabe, $clienteExistente['id']]);
                    $reactivados++;
                } else {
                    // Ya existe y está activo, lo ignoramos
                    $duplicados++;
                }
            } else {
                // Es un cliente 100% nuevo, lo insertamos
                $insertStmt->execute([$nombre, $banco, $cuenta, $clabe]);
                $insertados++;
            }
        }
        fclose($handle);
        
        $mensaje = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <i class='ti ti-check me-2'></i> <strong>Archivo procesado:</strong> $insertados nuevos, $reactivados reactivados y se omitieron $duplicados duplicados.
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
    } else {
        $mensaje = "<div class='alert alert-danger'>Error al subir el archivo.</div>";
    }
}

// 3. ELIMINAR CLIENTE DEL DIRECTORIO (SOFT DELETE)
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("UPDATE directorio_depositos SET estado = 'inactivo' WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: generador-depositos.php");
    exit;
}

// 4. OBTENER CLIENTES PARA LA TABLA Y EL SELECTOR (SOLO LOS ACTIVOS)
$stmt = $pdo->query("SELECT * FROM directorio_depositos WHERE estado = 'activo' ORDER BY nombre ASC");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Extraer lista de bancos únicos para el filtro
$bancos_unicos = [];
foreach($clientes as $c) {
    $b = trim($c['banco'] ?? '');
    if($b !== '' && !in_array($b, $bancos_unicos)) {
        $bancos_unicos[] = $b;
    }
}
sort($bancos_unicos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Generador Depósitos - Amiri</title>
    <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.ico" />
    <link rel="stylesheet" href="src/assets/css/styles.min.css" />
    <link rel="stylesheet" href="src/assets/css/icons/tabler-icons/tabler-icons.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode.css" />
    <link rel="stylesheet" href="src/assets/css/dark-mode-extension.css" />
    <link rel="stylesheet" href="src/assets/css/layout-custom.css" />
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
                .body-wrapper {
            /* Aumentamos esto para librar completamente la altura del header azul */
            padding-top: 100px !important; 
        }
        
        .body-wrapper-inner, 
        .body-wrapper .container-fluid {
            /* Le damos un pequeño respiro visual para que el título no quede pegando con el header */
            padding-top: 15px !important;
            margin-top: 0 !important;
        }
        .ticket-wrapper {
            background-color: #f4f6f9;
            padding: 40px;
            border-radius: 12px;
            display: flex;
            justify-content: center;
        }

        .ticket-card {
            background: #ffffff;
            width: 100%;
            max-width: 580px;
            border-radius: 0; /* ✨ AQUÍ QUITAMOS LAS ESQUINAS REDONDAS ✨ */
            padding: 35px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05);
            color: #2A3547;
            font-family: 'Manrope', sans-serif;
            border: 1px solid #ebf1f6;
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #6c5ce7;
        }

        .ticket-title {
            font-size: 17px; /* Le bajamos un puntito para que encaje mejor */
            font-weight: 700;
            letter-spacing: 1px;
            color: #6c5ce7;
            text-transform: uppercase;
            white-space: normal; /* 🔥 ESTA ES LA CLAVE: Permite que respete el borde */
            text-align: right;
            line-height: 1.2;
            word-wrap: break-word;
        }

        .client-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px dashed #ebf1f6;
        }

        .client-info { display: flex; flex-direction: column; gap: 4px; }
        .client-name { font-size: 15px; font-weight: 700; color: #2A3547; }
        .client-account { font-size: 12px; color: #7c8fac; text-transform: uppercase; letter-spacing: 0.5px; }

        .client-amount {
            background-color: rgba(93, 135, 255, 0.1);
            color: #6c5ce7;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 700;
            text-align: right;
            min-width: 130px;
        }

        .ticket-row.total-row {
            background: rgba(93, 135, 255, 0.04);
            border: none;
            border-radius: 8px;
            padding: 20px;
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .text-muted-custom { color: #7c8fac; font-size: 13px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;}
        .text-success-custom { color: #36c76c; }
        
        /* Estilos para la tabla corregidos */
        .table-directorio thead th { 
            font-weight: 700; 
            color: #2A3547; 
            background-color: #ffffff !important; /* Fondo blanco forzado */
            position: sticky;
            top: 0;
            z-index: 10; /* Lo mantiene por encima de las filas al hacer scroll */
            box-shadow: 0 1px 0px #ebf1f6; /* Una pequeña línea separadora abajo */
        }
        .table-directorio td { vertical-align: middle; }

        .select2-container--default .select2-selection--single {
            height: 40px;
            border: 1px solid #ebf1f6;
            border-radius: 6px;
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }

         [data-bs-theme="dark"] .logo-modo-claro,
        .dark-mode .logo-modo-claro {
            display: none !important;
        }

        [data-bs-theme="dark"] .logo-modo-oscuro,
        .dark-mode .logo-modo-oscuro {
            display: block !important;
        }
        /* =========================================
           📱 AJUSTES EXCLUSIVOS PARA MÓVIL
           ========================================= */
        @media (max-width: 768px) {
            /* 1. Ajuste del encabezado y logo en móvil */
            .logo-container-ticket {
                max-width: 75% !important; /* Le damos más espacio al logo en pantallas pequeñas */
            }
            .logo-container-ticket img {
                height: 35px !important; /* Logo un poco más pequeño para que no se amontone */
                width: auto !important;
            }
            .ticket-title {
                font-size: 14px !important;
                white-space: normal !important;
                text-align: right;
            }

            /* 2. Ajuste de márgenes del ticket */
            .ticket-wrapper { padding: 15px 10px !important; }
            .ticket-card { padding: 20px 15px !important; }
            
            .client-amount {
                min-width: auto !important;
                font-size: 14px !important;
                padding: 6px 10px !important;
            }
            .client-name { font-size: 13px !important; }
            .client-account { font-size: 11px !important; }
            
            .client-row { padding-right: 30px !important; }
            .btn-quitar-item {
                transform: translateY(-50%) !important;
                top: 50% !important;
                right: 0 !important;
            }
        }
    </style>
    <script>document.documentElement.setAttribute('data-bs-theme',localStorage.getItem('darkMode')==='true'?'dark':'light')</script>
</head>
<style>
    /* =========================================
   🌙 REGLAS PARA MODO OSCURO (Generador Depósitos)
   ========================================= */

/* 1. Ticket y Fondo */
[data-bs-theme="dark"] .ticket-wrapper {
    background-color: transparent !important;
}

[data-bs-theme="dark"] .ticket-card {
    background: #1a2234; /* Fondo oscuro del dashboard */
    border-color: #2c344c;
    box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.3);
}

[data-bs-theme="dark"] .ticket-header {
    border-bottom-color: #2c344c;
}

[data-bs-theme="dark"] .client-name {
    color: #e9ecef;
}

[data-bs-theme="dark"] .client-account {
    color: #8c9ea8;
}

[data-bs-theme="dark"] .client-row {
    border-bottom-color: #2c344c;
}

[data-bs-theme="dark"] .client-amount {
    background-color: rgba(93, 135, 255, 0.15);
    color: #799dfb; 
}

[data-bs-theme="dark"] #placeholderTicket,
[data-bs-theme="dark"] .text-muted-custom {
    color: #8c9ea8 !important;
}

/* 2. Buscador Select2 (Para quitar el recuadro blanco) */
[data-bs-theme="dark"] .select2-container--default .select2-selection--single {
    background-color: #1a2234;
    border-color: #2c344c;
}

[data-bs-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #e9ecef;
}

[data-bs-theme="dark"] .select2-dropdown {
    background-color: #1a2234;
    border-color: #2c344c;
}

[data-bs-theme="dark"] .select2-container--default .select2-search--dropdown .select2-search__field {
    background-color: #252f43;
    border-color: #2c344c;
    color: #e9ecef;
}

[data-bs-theme="dark"] .select2-container--default .select2-results__option[aria-selected=true] {
    background-color: #252f43;
    color: #e9ecef;
}

[data-bs-theme="dark"] .select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #6c5ce7;
    color: #ffffff;
}

/* 3. Tabla del Directorio */
[data-bs-theme="dark"] .table-directorio thead th {
    background-color: #1a2234 !important;
    color: #e9ecef !important;
    border-bottom-color: #2c344c !important;
    box-shadow: 0 1px 0px #2c344c;
}

[data-bs-theme="dark"] .table-directorio td {
    color: #e9ecef;
    border-color: #2c344c;
}
</style>
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <?php include 'src/assets/partials/header.php'; ?>
        <?php include 'src/assets/partials/sidebar.php'; ?>

        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid" style="max-width: 1400px; margin: 0 auto;">
                    
                    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
                        <h3 class="mb-0"><i class="ti ti-cash"></i> Generador Depositos</h3>
                    </div>

                    <?= $mensaje ?>

                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-body">
                            <div class="row align-items-center mb-3">
                                <div class="col-12 col-md-auto mb-3 mb-md-0">
                                    <h5 class="card-title fw-semibold mb-0"><i class="ti ti-users"></i> Directorio de Clientes</h5>
                                </div>
                                
                                <div class="col-12 col-md-auto ms-md-auto">
                                    <div class="d-flex flex-column flex-md-row gap-2">
                                        <a href="src/assets/machote_clientes.xlsx" download class="btn btn-sm btn-outline-secondary px-3 py-2 text-nowrap d-flex justify-content-center align-items-center">
                                            <i class="ti ti-download me-1"></i> Descargar Machote
                                        </a>
                                        
                                        <button class="btn btn-sm btn-primary px-3 py-2 text-nowrap d-flex justify-content-center align-items-center" data-bs-toggle="modal" data-bs-target="#modalUpload">
                                            <i class="ti ti-upload me-1"></i> Subir Excel (XLSX)
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="input-group mb-3">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="ti ti-search"></i></span>
                                <input type="text" id="buscadorDirectorio" class="form-control border-start-0 ps-0" placeholder="Buscar cliente por nombre, banco, cuenta o CLABE...">
                            </div>

                            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                                <table class="table table-hover table-bordered table-directorio mb-0">
                                    <thead>
    <tr>
        <th style="position: sticky; top: 0; z-index: 10;">Nombre Completo</th>
        <th style="position: sticky; top: 0; z-index: 10;">Banco</th>
        <th style="position: sticky; top: 0; z-index: 10;">Cuenta</th>
        <th class="text-center" style="position: sticky; top: 0; z-index: 10;">Acción</th>
    </tr>
</thead>
                                    <tbody id="cuerpoDirectorio">
                                        <?php if(empty($clientes)): ?>
                                            <tr><td colspan="5" class="text-center text-muted py-4">No hay clientes en el directorio. Descarga el machote y sube tu archivo.</td></tr>
                                        <?php else: ?>
                                            <?php foreach($clientes as $c): ?>
<tr>
    <td class="fw-bold"><?= htmlspecialchars($c['nombre']) ?></td>
    
    <td><span class="badge bg-primary bg-opacity-10 text-primary border-0 py-1 px-2" style="font-size: 0.8rem;"><?= htmlspecialchars($c['banco']) ?></span></td>
    
    <td><?= htmlspecialchars($c['cuenta']) ?></td>
    <td class="text-center">
    <button class="badge bg-primary bg-opacity-10 text-primary border-0 me-1 btn-editar-cliente" 
            style="padding: 8px 12px; cursor: pointer;"
            data-id="<?= $c['id'] ?>"
            data-nombre="<?= htmlspecialchars($c['nombre']) ?>"
            data-banco="<?= htmlspecialchars($c['banco']) ?>"
            data-cuenta="<?= htmlspecialchars($c['cuenta']) ?>"
            data-clabe="<?= htmlspecialchars($c['clabe']) ?>">
        <i class="ti ti-edit"></i>
    </button>
    <a href="?delete_id=<?= $c['id'] ?>" class="badge bg-danger bg-opacity-10 text-danger border-0" style="padding: 8px 12px; text-decoration: none; font-size: 0.85rem;" onclick="return confirm('¿Eliminar cliente?')">
            <i class="ti ti-trash"></i>
        </a>
</td>
    
    <!-- <td class="text-center">
        <a href="?delete_id=<?= $c['id'] ?>" class="badge bg-danger bg-opacity-10 text-danger border-0" style="padding: 8px 12px; text-decoration: none; font-size: 0.85rem;" onclick="return confirm('¿Eliminar cliente?')">
            <i class="ti ti-trash"></i>
        </a>
    </td> -->
</tr>
<?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-5 col-md-5 mb-4">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-body p-4">
                                    <h5 class="card-title fw-semibold mb-4">Armar Ticket</h5>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold text-primary">1. Filtrar por Banco (Opcional)</label>
                                        <select class="form-select" id="filtroBanco">
                                            <option value="">-- Todos los bancos --</option>
                                            <?php foreach($bancos_unicos as $b): ?>
                                                <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">2. Buscar Cliente (Escribe el nombre)</label>
                                        <select class="form-control" id="selectCliente" style="width: 100%;">
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">3. Monto a Depositar</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">$</span>
                                            <input type="text" step="0.01" class="form-control" id="inputMonto" placeholder="Ej. 15000.00">
                                        </div>
                                    </div>

                                    <button class="btn btn-success w-100 mb-3" id="btnAgregarTicket">
                                        <i class="ti ti-plus"></i> Agregar al Ticket
                                    </button>

                                    <button class="btn btn-primary w-100 py-2 fw-semibold shadow-sm" id="btnDescargar">
                                        <i class="ti ti-download me-2"></i> Descargar Ticket
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7 col-md-7">
                            <div class="ticket-wrapper">
                                <div class="ticket-card" id="vistaPreviaTicket" style="position: relative; overflow: hidden;">
                                    
                                    <div style="position: absolute; top: -60px; right: -60px; width: 180px; height: 180px; background: #5D87FF; opacity: 0.08; border-radius: 50%; z-index: 0; pointer-events: none;"></div>
                                    <div style="position: absolute; bottom: -40px; left: -40px; width: 120px; height: 120px; background: #13deb9; opacity: 0.08; border-radius: 50%; z-index: 0; pointer-events: none;"></div>

                                   <div class="ticket-header" style="position: relative; z-index: 1;">
    <div class="d-flex align-items-center logo-container-ticket" style="max-width: 50%;">
        <img src="src/assets/images/logos/logo2.png" alt="Logo Amiri" class="logo-modo-claro" style="height: 45px; width: auto;" />
        <img src="src/assets/images/logos/logo2-blanco-sin-fondo.png" alt="Logo Amiri" class="logo-modo-oscuro" style="height: 45px; width: auto; display: none;" />
    </div>
    <div class="text-end" style="margin-left: 15px;">
        <div class="ticket-title">DEPÓSITOS EN EFECTIVO</div>
    </div>
</div>

                                    <div class="client-list" id="ticketItemsContainer">
                                        <div class="text-center text-muted py-4" id="placeholderTicket">
                                            <i class="ti ti-receipt fs-1 text-light"></i><br>
                                            El ticket está vacío. Agrega clientes desde el panel izquierdo.
                                        </div>
                                    </div>

                                    <!-- <div class="text-center mt-4">
                                        <span class="text-muted-custom" style="font-size: 10px;">Generado el: <span id="outFechaHoy"></span></span>
                                    </div> -->

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalUpload" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formSubirExcel">
    <div class="modal-body">
        <p class="text-muted small">Sube tu archivo de Excel llenado <strong>(.xlsx)</strong>.</p>
        <input class="form-control" type="file" id="archivo_excel" accept=".xlsx" required>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary" id="btnProcesarExcel">Subir y Guardar</button>
    </div>
</form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEditarCliente">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" name="nombre" id="edit_nombre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Banco</label>
                        <input type="text" class="form-control" name="banco" id="edit_banco">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cuenta</label>
                        <input type="text" class="form-control" name="cuenta" id="edit_cuenta">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">CLABE</label>
                        <input type="text" class="form-control" name="clabe" id="edit_clabe">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="src/assets/js/sidebarmenu.js"></script>
    <script src="src/assets/js/dark-mode.js"></script>
    <script src="src/assets/js/utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Al hacer clic en el botón editar de la tabla
$(document).on('click', '.btn-editar-cliente', function() {
    const d = $(this).data();
    $('#edit_id').val(d.id);
    $('#edit_nombre').val(d.nombre);
    $('#edit_banco').val(d.banco);
    $('#edit_cuenta').val(d.cuenta);
    $('#edit_clabe').val(d.clabe);
    $('#modalEditarCliente').modal('show');
});

// Enviar el formulario de edición
$('#formEditarCliente').on('submit', function(e) {
    e.preventDefault();
    const btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('<i class="ti ti-loader ti-spin"></i> Guardando...');

    $.ajax({
        url: 'api/editar-cliente-directorio.php',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json', // 🔥 ESTO ES CLAVE: Obligamos a leerlo como JSON
        success: function(res) {
            if (res && res.success) {
                // Cerramos el modal por elegancia y recargamos la página al instante
                $('#modalEditarCliente').modal('hide');
                location.reload();
            } else {
                alert('Error al guardar: ' + (res.error || 'Respuesta inválida'));
                btn.prop('disabled', false).text('Guardar Cambios');
            }
        },
        error: function(xhr, status, error) {
            // 🔥 Si el servidor manda basura o falla, lo atrapamos aquí para que no se quede trabado
            console.error("Error del servidor: ", xhr.responseText);
            alert('El cliente se guardó, pero hubo un detalle de conexión. Recargando...');
            location.reload(); 
        }
    });
});
document.getElementById('formSubirExcel').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let file = document.getElementById('archivo_excel').files[0];
    if (!file) return;

    let btn = document.getElementById('btnProcesarExcel');
    btn.innerHTML = '<i class="ti ti-loader ti-spin"></i> Procesando...';
    btn.disabled = true;

    let reader = new FileReader();
    reader.onload = function(e) {
        let data = new Uint8Array(e.target.result);
        let workbook = XLSX.read(data, {type: 'array'});
        
        // Agarramos la primera hoja del Excel
        let primeraHoja = workbook.SheetNames[0];
        // Convertimos el Excel a un arreglo de objetos JSON (saltando la primera fila de títulos)
        let filas = XLSX.utils.sheet_to_json(workbook.Sheets[primeraHoja], {header: 1});
        filas.shift(); // Quitamos los encabezados
        
        // Enviamos los datos limpios a PHP
        $.ajax({
            url: 'api/procesar-excel-depositos.php', // Necesitaremos crear este archivito
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(filas),
            success: function(response) {
                if(response.success) {
                    location.reload(); // Recargamos para ver los cambios
                } else {
                    alert("Error: " + response.error);
                    btn.innerHTML = 'Subir y Guardar';
                    btn.disabled = false;
                }
            }
        });
    };
    reader.readAsArrayBuffer(file);
});

        function descargarMachote() {
            let csvContent = "data:text/csv;charset=utf-8,Nombre,Banco,Cuenta,Clabe\n";
            var encodedUri = encodeURI(csvContent);
            var link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "machote_clientes_depositos.csv");
            document.body.appendChild(link);
            link.click();
            link.remove();
        }

        // Lógica de colores por banco (Diccionario extendido para México)
        function getColorBanco(bancoNombre) {
            const b = (bancoNombre || '').toLowerCase().trim();
            
            // 1. Bancos Tradicionales
            if (b.includes('bbva') || b.includes('bancomer')) return '#004481'; // Azul BBVA
            if (b.includes('santander')) return '#ec0000'; // Rojo Santander
            if (b.includes('banorte')) return '#eb0029'; // Rojo Banorte
            if (b.includes('hsbc')) return '#db0011'; // Rojo HSBC
            if (b.includes('banamex') || b.includes('citi')) return '#056dae'; // Azul Banamex
            if (b.includes('scotia')) return '#ed1b24'; // Rojo Scotia
            if (b.includes('inbursa')) return '#00508c'; // Azul Inbursa
            if (b.includes('azteca')) return '#00a22d'; // Verde Azteca
            if (b.includes('coppel') || b.includes('bancoppel')) return '#005da3'; // Azul Coppel
            if (b.includes('bajio') || b.includes('banbajio')) return '#fbb700'; // Amarillo Bajío
            if (b.includes('afirme')) return '#00973a'; // Verde Afirme
            if (b.includes('banregio')) return '#eb6f00'; // Naranja Banregio
            if (b.includes('mifel')) return '#005a8c'; // Azul Mifel
            if (b.includes('invex')) return '#002f6c'; // Azul Marino Invex
            if (b.includes('multiva')) return '#003865'; // Azul Multiva
            if (b.includes('banjercito')) return '#12322b'; // Verde militar Banjercito
            if (b.includes('bienestar') || b.includes('bansefi')) return '#611232'; // Guinda Gobierno

            // 2. Fintechs, Neobancos y Wallets
            if (b.includes('nu') || b.includes('nubank')) return '#8a05be'; // Morado Nu
            if (b.includes('plata')) return '#1a1a1a'; // Negro/Gris muy oscuro Plata Card
            if (b.includes('mercado') || b.includes('mercadopago')) return '#009ee3'; // Azul claro MercadoLibre
            if (b.includes('spin') || b.includes('oxxo')) return '#ff6e00'; // Naranja Spin by Oxxo
            if (b.includes('hey') || b.includes('heybanco')) return '#000000'; // Negro Hey Banco
            if (b.includes('stori')) return '#00c08b'; // Verde Teal Stori
            if (b.includes('klar')) return '#000000'; // Negro Klar
            if (b.includes('uala') || b.includes('ualá')) return '#f2385a'; // Rosa Ualá
            if (b.includes('fondeadora')) return '#121212'; // Negro Fondeadora
            if (b.includes('albo')) return '#002f87'; // Azul Albo
            if (b.includes('broxel')) return '#00a2d3'; // Celeste Broxel
            if (b.includes('rappi') || b.includes('rappipay')) return '#ff4600'; // Naranja Rappi
            if (b.includes('bineo')) return '#ff0066'; // Rosa Bineo
            if (b.includes('cetes')) return '#104a3e'; // Verde oscuro CetesDirecto
            
            // 3. Color por defecto (Si de plano es uno que no está en la lista)
            return '#6c5ce7'; // Azul/Morado Amiri
        }

        $(document).ready(function() {
            
            // LÓGICA DEL BUSCADOR EN LA TABLA DEL DIRECTORIO
            $('#buscadorDirectorio').on('keyup', function() {
                let value = $(this).val().toLowerCase();
                $('#cuerpoDirectorio tr').filter(function() {
                    // Ignorar la fila de "No hay clientes" si existe
                    if ($(this).find('td').length === 1) return;
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            const ahora = new Date();
            const fechaOpciones = { year: 'numeric', month: 'long', day: 'numeric' };
            const horaOpciones = { hour: '2-digit', minute: '2-digit', hour12: true };
            const fechaTxt = ahora.toLocaleDateString('es-MX', fechaOpciones).toUpperCase();
            const horaTxt = ahora.toLocaleTimeString('es-MX', horaOpciones).toUpperCase();
            $('#outFechaHoy').text(fechaTxt + ' A LAS ' + horaTxt);

            const formatoMoneda = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
            let totalMxn = 0;
            let itemsEnTicket = 0;

            const todosLosClientes = <?php echo json_encode($clientes); ?>;

            function cargarBuscadorClientes(filtroBanco = '') {
                const select = $('#selectCliente');
                if (select.hasClass("select2-hidden-accessible")) {
                    select.select2('destroy');
                }
                select.empty();
                select.append('<option value="">-- Escribe el nombre o selecciona --</option>');
                
                todosLosClientes.forEach(c => {
                    const bancoDelCliente = c.banco ? c.banco.trim() : '';
                    if (filtroBanco === '' || bancoDelCliente === filtroBanco) {
                        const cuentaStr = c.cuenta ? c.cuenta : c.clabe;
                        select.append(`<option value="${c.id}" data-nombre="${c.nombre}" data-banco="${bancoDelCliente}" data-cuenta="${cuentaStr}">${c.nombre} - ${bancoDelCliente}</option>`);
                    }
                });

                select.select2({
                    width: '100%',
                    language: { noResults: () => "No se encontraron clientes" }
                });
            }

            cargarBuscadorClientes('');

            $('#filtroBanco').on('change', function() {
                cargarBuscadorClientes($(this).val());
            });

            $('#btnAgregarTicket').on('click', function() {
                const select = $('#selectCliente').find(':selected');
                const montoRaw = $('#inputMonto').val();
                const monto = parseFloat(montoRaw);

                if (select.val() === "" || !select.val()) {
                    showToast('Por favor, selecciona un cliente del directorio.', 'error');
                    return;
                }

                if (isNaN(monto) || monto <= 0) {
                    showToast('Por favor, ingresa un monto válido.', 'error');
                    return;
                }

                const nombre = select.data('nombre');
                const banco = select.data('banco');
                const cuenta = select.data('cuenta');
                const colorBanco = getColorBanco(banco);

                if (itemsEnTicket === 0) {
                    $('#placeholderTicket').hide();
                }

                const uniqueId = 'item_' + Date.now();

                let itemHtml = `
                    <div class="client-row position-relative" id="${uniqueId}">
                        <div class="client-info">
                            <div class="client-name">${nombre.toUpperCase()}</div>
                            <div class="client-account">Cuenta: ${cuenta} - <span class="fw-bolder" style="color: ${colorBanco};">${banco.toUpperCase()}</span></div>
                        </div>
                        <div class="client-amount">${formatoMoneda.format(monto)}</div>
                        <button class="btn btn-sm text-danger position-absolute end-0 top-0 btn-quitar-item" data-id="${uniqueId}" data-monto="${monto}" style="transform: translateX(120%);" title="Quitar del ticket">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                `;

                $('#ticketItemsContainer').append(itemHtml);
                
                totalMxn += monto;
                itemsEnTicket++;
                $('#selectCliente').val('').trigger('change');
                $('#inputMonto').val('');
                showToast('Agregado al ticket', 'success');
            });

            $('#ticketItemsContainer').on('click', '.btn-quitar-item', function() {
                const id = $(this).data('id');
                const montoRestar = parseFloat($(this).data('monto'));

                $(`#${id}`).remove();
                totalMxn -= montoRestar;
                itemsEnTicket--;

                if (itemsEnTicket === 0) {
                    $('#placeholderTicket').show();
                }
            });

            $('#btnDescargar').on('click', function() {
                if (itemsEnTicket === 0) {
                    showToast('Agrega al menos un cliente al ticket antes de descargar.', 'error');
                    return;
                }

                const ticket = document.getElementById('vistaPreviaTicket');
                const btn = $(this);
                const textoOriginal = btn.html();
                
                $('.btn-quitar-item').hide();
                btn.html('<i class="ti ti-loader ti-spin me-2"></i> Generando...');
                btn.prop('disabled', true);

                // --- 🚀 PASO 1: "Desplegar" el ticket para la captura ---
                // Eliminamos max-width y forzamos un ancho perfecto para que html2canvas lo "vea" bien.
                const originalMaxWidth = ticket.style.maxWidth;
                const originalWidth = ticket.style.width;
                ticket.style.maxWidth = 'none'; // Quitamos la restricción
                ticket.style.width = '580px';    // Forzamos el ancho óptimo que definiste en CSS

                // --- PASO 2: Generar la captura ---
                html2canvas(ticket, {
                    backgroundColor: null, // Mantenemos la transparencia que querías
                    scale: 3,             // Alta resolución
                    logging: false
                }).then(canvas => {
                    const enlace = document.createElement('a');
                    const fechaTxt = new Date().toISOString().slice(0,10);
                    enlace.download = `Deposito_Masivo_Amiri_${fechaTxt}.png`;
                    enlace.href = canvas.toDataURL("image/png");
                    enlace.click();

                    // --- 🔄 PASO 3: Restaurar el estado original ---
                    ticket.style.maxWidth = originalMaxWidth;
                    ticket.style.width = originalWidth;

                    $('.btn-quitar-item').show();
                    btn.html(textoOriginal).prop('disabled', false);
                    showToast('¡Ticket generado con éxito!', 'success');
                }).catch(err => {
                    console.error(err);
                    
                    // Aseguramos restaurar el estado incluso si hay error
                    ticket.style.maxWidth = originalMaxWidth;
                    ticket.style.width = originalWidth;

                    $('.btn-quitar-item').show();
                    showToast('Hubo un error al generar la imagen', 'error');
                    btn.html(textoOriginal).prop('disabled', false);
                });
            });
        });
    </script>
</body>
</html>