<?php
if (!defined('BASE_URL')) {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $pos = strpos($scriptName, '/src/');

    if ($pos !== false) {
        $baseUrl = substr($scriptName, 0, $pos);
    } else {
        $baseUrl = rtrim(dirname($scriptName), '/\\');
    }

    define('BASE_URL', $baseUrl);
}

// SIMULACIÓN DE DATOS DESDE BASE DE DATOS (Sustituir con consultas reales)
$userData = [
    'nombre' => $usuario['nombre'] ?? 'Uriel',
    'apellido' => $usuario['apellido'] ?? '',
    'email' => $usuario['email'] ?? '',
    'telefono' => $usuario['telefono'] ?? ''
];

$direccionesUsuario = $direccionesUsuario ?? [
    ['provincia' => 'Buenos Aires', 'ciudad' => 'Bosques', 'direccion' => 'Hilario ascasubi 1039']
];

$vehiculosUsuario = $vehiculosUsuario ?? [
    ['marca' => 'Chevrolet', 'modelo' => 'Classic', 'anio' => '2014']
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Panel de Usuario</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">

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
<body>

    <div class="profile-page-wrapper">
        <div class="profile-container">
            
            <div class="profile-header">
                <h2 class="profile-title">Mi Cuenta</h2>
                <p class="profile-subtitle">Gestiona tu información personal, direcciones de envío y datos de tu vehículo.</p>
            </div>

            <div class="profile-tabs">
                <button class="profile-tab-btn active" type="button" onclick="openTab(event, 'personal')">
                    <i class="bi bi-person-fill"></i> Datos Personales
                </button>
                <button class="profile-tab-btn" type="button" onclick="openTab(event, 'envio')">
                    <i class="bi bi-geo-alt-fill"></i> Direcciones
                </button>
                <button class="profile-tab-btn" type="button" onclick="openTab(event, 'vehiculo')">
                    <i class="bi bi-car-front-fill"></i> Vehículos
                </button>
            </div>

            <!-- APUNTA AL ROUTER O SCRIPT PÚBLICO -->
            <form action="<?php echo BASE_URL; ?>/index.php?action=updateProfile" method="POST" class="profile-form">
                
                <!-- Pestaña 1: Datos Personales -->
                <div id="personal" class="profile-tab-content active">
                    <div class="grid-compact">
                        <div class="form-group-compact">
                            <label for="nombre">Nombre</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($userData['nombre']); ?>" class="form-control-compact">
                        </div>
                        <div class="form-group-compact">
                            <label for="apellido">Apellido</label>
                            <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($userData['apellido']); ?>" class="form-control-compact">
                        </div>
                        <div class="form-group-compact">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" class="form-control-compact">
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
                                        <input type="text" name="direcciones[<?php echo $i; ?>][provincia]" value="<?php echo htmlspecialchars($dir['provincia']); ?>" class="form-control-compact">
                                    </div>
                                    <div class="form-group-compact">
                                        <label>Ciudad</label>
                                        <input type="text" name="direcciones[<?php echo $i; ?>][ciudad]" value="<?php echo htmlspecialchars($dir['ciudad']); ?>" class="form-control-compact">
                                    </div>
                                    <div class="form-group-compact" style="grid-column: span 2;">
                                        <label>Calle, Número, Piso/Depto</label>
                                        <input type="text" name="direcciones[<?php echo $i; ?>][direccion]" value="<?php echo htmlspecialchars($dir['direccion']); ?>" class="form-control-compact">
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
                                        <input type="text" name="vehiculos[<?php echo $i; ?>][marca]" value="<?php echo htmlspecialchars($veh['marca']); ?>" class="form-control-compact">
                                    </div>
                                    <div class="form-group-compact">
                                        <label>Modelo</label>
                                        <input type="text" name="vehiculos[<?php echo $i; ?>][modelo]" value="<?php echo htmlspecialchars($veh['modelo']); ?>" class="form-control-compact">
                                    </div>
                                    <div class="form-group-compact">
                                        <label>Año</label>
                                        <input type="number" name="vehiculos[<?php echo $i; ?>][anio]" value="<?php echo htmlspecialchars($veh['anio']); ?>" class="form-control-compact">
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

            </form>
        </div>
    </div>

    <script>
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
        }
    </script>
<?php require_once __DIR__ . '/../_layouts/chatbot.php'; ?>
</body>
</html>
