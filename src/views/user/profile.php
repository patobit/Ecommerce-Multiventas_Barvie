<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/rutas.php';
require_once __DIR__ . '/../../config/strapi_client.php';
header('Cache-Control: no-store, private');
$usuario = $_SESSION['usuario'] ?? null;
if (!is_array($usuario) || empty($usuario['id']) || empty($usuario['jwt'])) {
    header('Location: ' . BASE_URL . '/src/views/auth/login.php');
    exit;
}
$jwt = $usuario['jwt'];
$_SESSION['perfil_csrf'] ??= bin2hex(random_bytes(32));
$errorPerfil = '';
$guardado = false;
$esPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
if ($esPost) {
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($_SESSION['perfil_csrf'], $_POST['csrf'])) {
        $resultado = ['ok' => false, 'status' => 403, 'error' => 'La sesión del formulario cambió. Recargá la página.'];
    } else {
        $body = [
            'Nombre' => $_POST['nombre'] ?? '', 'Apellido' => $_POST['apellido'] ?? '',
            'email' => $_POST['email'] ?? '', 'Telefono' => $_POST['telefono'] ?? '',
            'Direcciones' => is_array($_POST['direcciones'] ?? []) ? array_values($_POST['direcciones'] ?? []) : null,
            'Vehiculos' => is_array($_POST['vehiculos'] ?? []) ? array_values($_POST['vehiculos'] ?? []) : null,
        ];
        $resultado = strapiRequest('PUT', 'profile', [], $body, $jwt);
    }
} else {
    $resultado = strapiRequest('GET', 'profile', [], null, $jwt);
}
if ($resultado['ok']) {
    $cuenta = $resultado['data'];
    // Refresca las claves compartidas sin cambiar la sesión durante cada autoguardado.
    $_SESSION['nombre'] = $cuenta['Nombre'] ?: $cuenta['username'];
    $_SESSION['usuario'] = array_merge($usuario, [
        'nombre' => $_SESSION['nombre'], 'name' => $_SESSION['nombre'],
        'apellido' => $cuenta['Apellido'] ?? '', 'email' => $cuenta['email'],
        'telefono' => $cuenta['Telefono'] ?? '',
    ]);
    $guardado = $esPost;
} else {
    $errorPerfil = $resultado['error'] ?? 'No se pudieron cargar los datos.';
    $cuenta = [];
}
if ($esPost && str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
    http_response_code($resultado['ok'] ? 200 : ($resultado['status'] ?: 503));
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => $resultado['ok'], 'error' => $errorPerfil]);
    exit;
}
if (!$resultado['ok'] && $resultado['status'] === 401) {
    unset($_SESSION['usuario'], $_SESSION['usuario_id'], $_SESSION['nombre']);
    header('Location: ' . BASE_URL . '/src/views/auth/login.php');
    exit;
}
if ($guardado) {
    header('Location: ' . BASE_URL . '/src/views/user/profile.php?guardado=1');
    exit;
}
$userData = [
    'nombre' => $cuenta['Nombre'] ?? $cuenta['username'] ?? '', 'apellido' => $cuenta['Apellido'] ?? '',
    'email' => $cuenta['email'] ?? '', 'telefono' => $cuenta['Telefono'] ?? '',
];
$direccionesUsuario = $cuenta['Direcciones'] ?? (empty($cuenta['Provincia']) && empty($cuenta['Ciudad']) && empty($cuenta['Direccion']) ? [] : [[
    'provincia' => $cuenta['Provincia'] ?? '', 'ciudad' => $cuenta['Ciudad'] ?? '', 'direccion' => $cuenta['Direccion'] ?? '',
]]);
$vehiculosUsuario = $cuenta['Vehiculos'] ?? (empty($cuenta['Auto_marca']) && empty($cuenta['Auto_modelo']) && empty($cuenta['Auto_anio']) ? [] : [[
    'marca' => $cuenta['Auto_marca'] ?? '', 'modelo' => $cuenta['Auto_modelo'] ?? '', 'anio' => $cuenta['Auto_anio'] ?? '',
]]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Panel de Usuario</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../../../assets/css/style.css') ?>">

    <style>
        .profile-container { max-width: 850px; }
        .card-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            position: relative;
        }
        .card-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: #aaa;
        }
        .btn-add-item {
            background: transparent;
            border: 1px dashed #e63946;
            color: #e63946;
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.2s;
        }
        .btn-add-item:hover {
            background: rgba(230, 57, 70, 0.1);
        }
        .btn-remove-item {
            background: none;
            border: none;
            color: #ff4d4d;
            cursor: pointer;
            font-size: 1rem;
        }
        .grid-compact {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        .form-group-compact {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .form-group-compact label {
            font-size: 0.8rem;
            color: #ccc;
        }
        .form-control-compact {
            background: #18181c;
            border: 1px solid #333;
            color: #fff;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 0.88rem;
        }
    </style>
</head>
<body class="mvb-profile-page">
    <nav class="mvb-profile-nav" aria-label="Navegación de cuenta"><a href="<?= BASE_URL ?>/index.php"><strong>MVB</strong> <span>Multiventas Barvie</span></a><a href="<?= BASE_URL ?>/src/views/catalogo.php">Volver al catálogo ↗</a></nav>

    <div class="profile-page-wrapper">
        <div class="profile-container">
            
            <div class="profile-header">
                <div class="mvb-profile-avatar" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($userData['nombre'] ?: 'M', 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
                <div><p class="mvb-profile-eyebrow">TU CUENTA MVB</p><h1 class="profile-title">Hola<?= $userData['nombre'] ? ', ' . htmlspecialchars($userData['nombre'], ENT_QUOTES, 'UTF-8') : '' ?>.</h1><p class="profile-subtitle">Tus datos, direcciones y vehículos.</p></div>
            </div>

            <div class="profile-tabs">
                <button class="profile-tab-btn active" type="button" onclick="openTab(event, 'personal')">
                    <i class="bi bi-person-fill"></i> Datos
                </button>
                <button class="profile-tab-btn" type="button" onclick="openTab(event, 'envio')">
                    <i class="bi bi-geo-alt-fill"></i> Direcciones
                </button>
                <button class="profile-tab-btn" type="button" onclick="openTab(event, 'vehiculo')">
                    <i class="bi bi-car-front-fill"></i> Vehículos
                </button>
            </div>

            <!-- APUNTA AL ROUTER O SCRIPT PÚBLICO -->
            <?php if ($errorPerfil): ?><p role="alert"><?= htmlspecialchars($errorPerfil, ENT_QUOTES, 'UTF-8') ?> Recargá para volver a intentar.</p><?php endif; ?>
            <p id="profile-save-status" role="status" aria-live="polite"><?= isset($_GET['guardado']) ? 'Guardado en tu cuenta.' : 'Los cambios se guardan automáticamente al salir de cada campo.' ?></p>
            <a href="<?= BASE_URL ?>/index.php">Volver a la tienda</a>
            <form action="<?php echo BASE_URL; ?>/src/views/user/profile.php" method="POST" class="profile-form">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['perfil_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                <fieldset <?= $errorPerfil ? 'disabled' : '' ?> style="border:0;padding:0;margin:0;min-width:0">
                
                <!-- Pestaña 1: Datos Personales -->
                <div id="personal" class="profile-tab-content active">
                    <div class="grid-compact">
                        <div class="form-group-compact">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" required maxlength="254" value="<?php echo htmlspecialchars($userData['nombre']); ?>" class="form-control-compact">
                        </div>
                        <div class="form-group-compact">
                            <label for="apellido">Apellido</label>
                            <input type="text" id="apellido" name="apellido" required maxlength="254" value="<?php echo htmlspecialchars($userData['apellido']); ?>" class="form-control-compact">
                        </div>
                        <div class="form-group-compact">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required maxlength="254" value="<?php echo htmlspecialchars($userData['email']); ?>" class="form-control-compact">
                        </div>
                        <div class="form-group-compact">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($userData['telefono']); ?>" class="form-control-compact">
                        </div>
                    </div>
                </div>

                <!-- Pestaña 2: Direcciones (Múltiples) -->
                <div id="envio" class="profile-tab-content">
                    <div id="direcciones-list">
                        <?php foreach ($direccionesUsuario as $i => $dir): ?>
                            <div class="card-item">
                                <div class="card-item-header">
                                    <span><i class="bi bi-geo-alt"></i> Dirección #<?php echo $i + 1; ?></span>
                                    <button type="button" class="btn-remove-item" onclick="removeCard(this)"><i class="bi bi-trash"></i></button>
                                </div>
                                <div class="grid-compact">
                                    <div class="form-group-compact">
                                        <label>Provincia</label>
                                        <input type="text" name="direcciones[<?php echo $i; ?>][provincia]" value="<?php echo htmlspecialchars((string)($dir['provincia'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="form-control-compact">
                                    </div>
                                    <div class="form-group-compact">
                                        <label>Ciudad</label>
                                        <input type="text" name="direcciones[<?php echo $i; ?>][ciudad]" value="<?php echo htmlspecialchars((string)($dir['ciudad'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="form-control-compact">
                                    </div>
                                    <div class="form-group-compact" style="grid-column: span 2;">
                                        <label>Calle, Número, Piso/Depto</label>
                                        <input type="text" name="direcciones[<?php echo $i; ?>][direccion]" value="<?php echo htmlspecialchars((string)($dir['direccion'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="form-control-compact">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn-add-item" onclick="addDireccion()">
                        <i class="bi bi-plus-lg"></i> Agregar otra dirección
                    </button>
                </div>

                <!-- Pestaña 3: Vehículos (Múltiples) -->
                <div id="vehiculo" class="profile-tab-content">
                    <div id="vehiculos-list">
                        <?php foreach ($vehiculosUsuario as $i => $veh): ?>
                            <div class="card-item">
                                <div class="card-item-header">
                                    <span><i class="bi bi-car-front"></i> Vehículo #<?php echo $i + 1; ?></span>
                                    <button type="button" class="btn-remove-item" onclick="removeCard(this)"><i class="bi bi-trash"></i></button>
                                </div>
                                <div class="grid-compact">
                                    <div class="form-group-compact">
                                        <label>Marca</label>
                                        <input type="text" name="vehiculos[<?php echo $i; ?>][marca]" value="<?php echo htmlspecialchars((string)($veh['marca'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="form-control-compact">
                                    </div>
                                    <div class="form-group-compact">
                                        <label>Modelo</label>
                                        <input type="text" name="vehiculos[<?php echo $i; ?>][modelo]" value="<?php echo htmlspecialchars((string)($veh['modelo'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="form-control-compact">
                                    </div>
                                    <div class="form-group-compact">
                                        <label>Año</label>
                                        <input type="number" name="vehiculos[<?php echo $i; ?>][anio]" value="<?php echo htmlspecialchars((string)($veh['anio'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="form-control-compact">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn-add-item" onclick="addVehiculo()">
                        <i class="bi bi-plus-lg"></i> Agregar otro vehículo
                    </button>
                </div>

                <div class="profile-actions" style="margin-top: 20px;">
                    <button type="submit" class="btn-premium-red btn-save">
                        Guardar Cambios
                    </button>
                </div>

            </fieldset>
            </form>
        </div>
    </div>

    <script>
        const profileForm = document.querySelector('.profile-form');
        const saveStatus = document.getElementById('profile-save-status');
        let saving = false;
        let pendingSave = false;
        let dirty = false;
        profileForm.addEventListener('input', () => {
            dirty = true;
            saveStatus.textContent = 'Cambios pendientes. Se guardarán al salir del campo.';
        });
        async function saveProfile() {
            dirty = true;
            if (!profileForm.reportValidity()) return;
            if (saving) { pendingSave = true; return; }
            saving = true;
            pendingSave = false;
            dirty = false;
            saveStatus.textContent = 'Guardando…';
            try {
                const response = await fetch(profileForm.action, {
                    method: 'POST', body: new FormData(profileForm),
                    headers: { Accept: 'application/json' }, credentials: 'same-origin'
                });
                const result = await response.json();
                if (!response.ok || !result.ok) throw new Error(result.error || 'No se pudo guardar.');
                saveStatus.textContent = dirty ? 'Hay cambios pendientes.' : 'Guardado en tu cuenta.';
            } catch (error) {
                dirty = true;
                saveStatus.textContent = 'No guardado: ' + error.message + ' Podés reintentar con Guardar cambios.';
            } finally {
                saving = false;
                if (pendingSave) saveProfile();
            }
        }
        profileForm.addEventListener('change', saveProfile);
        profileForm.addEventListener('submit', (event) => { event.preventDefault(); saveProfile(); });
        window.addEventListener('beforeunload', (event) => {
            if (dirty || saving) { event.preventDefault(); event.returnValue = ''; }
        });

        function openTab(evt, tabName) {
            const contents = document.querySelectorAll('.profile-tab-content');
            contents.forEach(content => content.classList.remove('active'));

            const tabs = document.querySelectorAll('.profile-tab-btn');
            tabs.forEach(tab => tab.classList.remove('active'));

            document.getElementById(tabName).classList.add('active');
            evt.currentTarget.classList.add('active');
        }

        // Obtener el índice inicial basado en lo que cargó PHP
        let dirCount = <?php echo count($direccionesUsuario); ?>;
        function addDireccion() {
            const container = document.getElementById('direcciones-list');
            const newCard = document.createElement('div');
            newCard.className = 'card-item';
            newCard.innerHTML = `
                <div class="card-item-header">
                    <span><i class="bi bi-geo-alt"></i> Dirección #${dirCount + 1}</span>
                    <button type="button" class="btn-remove-item" onclick="removeCard(this)"><i class="bi bi-trash"></i></button>
                </div>
                <div class="grid-compact">
                    <div class="form-group-compact">
                        <label>Provincia</label>
                        <input type="text" name="direcciones[${dirCount}][provincia]" class="form-control-compact" placeholder="Ej: Buenos Aires">
                    </div>
                    <div class="form-group-compact">
                        <label>Ciudad</label>
                        <input type="text" name="direcciones[${dirCount}][ciudad]" class="form-control-compact" placeholder="Ej: Quilmes">
                    </div>
                    <div class="form-group-compact" style="grid-column: span 2;">
                        <label>Calle, Número, Piso/Depto</label>
                        <input type="text" name="direcciones[${dirCount}][direccion]" class="form-control-compact" placeholder="Calle 123">
                    </div>
                </div>
            `;
            container.appendChild(newCard);
            dirCount++;
        }

        let vehCount = <?php echo count($vehiculosUsuario); ?>;
        function addVehiculo() {
            const container = document.getElementById('vehiculos-list');
            const newCard = document.createElement('div');
            newCard.className = 'card-item';
            newCard.innerHTML = `
                <div class="card-item-header">
                    <span><i class="bi bi-car-front"></i> Vehículo #${vehCount + 1}</span>
                    <button type="button" class="btn-remove-item" onclick="removeCard(this)"><i class="bi bi-trash"></i></button>
                </div>
                <div class="grid-compact">
                    <div class="form-group-compact">
                        <label>Marca</label>
                        <input type="text" name="vehiculos[${vehCount}][marca]" class="form-control-compact" placeholder="Ej: Volkswagen">
                    </div>
                    <div class="form-group-compact">
                        <label>Modelo</label>
                        <input type="text" name="vehiculos[${vehCount}][modelo]" class="form-control-compact" placeholder="Ej: Suran">
                    </div>
                    <div class="form-group-compact">
                        <label>Año</label>
                        <input type="number" name="vehiculos[${vehCount}][anio]" class="form-control-compact" placeholder="Ej: 2018">
                    </div>
                </div>
            `;
            container.appendChild(newCard);
            vehCount++;
        }

        function removeCard(btn) {
            const card = btn.closest('.card-item');
            card.remove();
            saveProfile();
        }
    </script>
<?php require_once __DIR__ . '/../_layouts/chatbot.php'; ?>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="<?= BASE_URL ?>/assets/js/app.js" defer></script>
</body>
</html>
