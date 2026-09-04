<?php
require_once __DIR__ . '/../_layouts/auth.layout.php';
$error = $_GET['error'] ?? null;
?>

<div class="text-center mb-3">
    <h1 class="h4 fw-bold text-white mb-1">Crear Cuenta</h1>
    <p class="text-secondary small mb-0">Sumate al Club de Ofertas de Multiventas Barvie</p>
</div>

<?php if ($error === 'email'): ?>
    <div class="alert alert-danger py-2 small">Ese correo ya está registrado.</div>
<?php elseif ($error === 'password'): ?>
    <div class="alert alert-danger py-2 small">Las contraseñas no coinciden.</div>
<?php elseif ($error): ?>
    <div class="alert alert-danger py-2 small">Revisá los datos ingresados e intentá de nuevo.</div>
<?php endif; ?>

<form action="<?= BASE_URL ?>/src/controllers/auth/register.php" method="POST" novalidate>

    <!-- ============ DATOS OBLIGATORIOS ============ -->
    <h6 class="text-danger text-uppercase small fw-bold mb-3">Datos de la cuenta</h6>

    <!-- Nombre y Apellido acoplados en 2 columnas -->
    <div class="row g-2 mb-3">
        <div class="col-6">
            <label for="nombre" class="form-label text-secondary small text-uppercase fw-semibold">Nombre</label>
            <input type="text" class="form-control form-control-premium" id="nombre" name="nombre" required autofocus>
        </div>
        <div class="col-6">
            <label for="apellido" class="form-label text-secondary small text-uppercase fw-semibold">Apellido</label>
            <input type="text" class="form-control form-control-premium" id="apellido" name="apellido" required>
        </div>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label text-secondary small text-uppercase fw-semibold">Correo Electrónico</label>
        <input type="email" class="form-control form-control-premium" id="email" name="email" required>
    </div>

    <!-- Contraseñas acopladas en 2 columnas -->
    <div class="row g-2 mb-3">
        <div class="col-6">
            <label for="clave" class="form-label text-secondary small text-uppercase fw-semibold">Contraseña</label>
            <input type="password" class="form-control form-control-premium" id="clave" name="clave" minlength="8" required>
        </div>
        <div class="col-6">
            <label for="repetir_clave" class="form-label text-secondary small text-uppercase fw-semibold">Repetí contraseña</label>
            <input type="password" class="form-control form-control-premium" id="repetir_clave" name="repetir_clave" minlength="8" required>
        </div>
    </div>

    <hr class="border-secondary border-opacity-25 my-3">

    <!-- ============ DATOS OPCIONALES ============ -->
    <h6 class="text-danger text-uppercase small fw-bold mb-1">Contanos más sobre vos <span class="text-secondary fw-normal normal-case">(opcional)</span></h6>
    <p class="text-secondary small mb-3">Nos ayuda a ofrecerte mejores precios y compatibilidad de productos.</p>

    <!-- Teléfono y Frecuencia de Compra juntos -->
    <div class="row g-2 mb-3">
        <div class="col-6">
            <label for="telefono" class="form-label text-secondary small text-uppercase fw-semibold">WhatsApp / Teléfono</label>
            <input type="tel" class="form-control form-control-premium" id="telefono" name="telefono" placeholder="11 1234-5678">
        </div>
        <div class="col-6">
            <label for="frecuencia_compra" class="form-label text-secondary small text-uppercase fw-semibold">Frecuencia de compra</label>
            <select class="form-select form-control-premium" id="frecuencia_compra" name="frecuencia_compra">
                <option value="" selected>Preferís no decir</option>
                <option value="ocasional">De vez en cuando</option>
                <option value="mensual">Una vez al mes</option>
                <option value="frecuente">Frecuentemente</option>
            </select>
        </div>
    </div>

    <!-- Domicilio acoplado en 3 columnas (Provincia, Ciudad, Dirección) -->
    <div class="row g-2 mb-3">
        <div class="col-4">
            <label for="provincia" class="form-label text-secondary small text-uppercase fw-semibold">Provincia</label>
            <select class="form-select form-control-premium" id="provincia" name="provincia">
                <option value="" selected>Seleccionar...</option>
                <option value="Buenos Aires">Buenos Aires</option>
                <option value="CABA">CABA</option>
                <option value="Catamarca">Catamarca</option>
                <option value="Chaco">Chaco</option>
                <option value="Chubut">Chubut</option>
                <option value="Córdoba">Córdoba</option>
                <option value="Corrientes">Corrientes</option>
                <option value="Entre Ríos">Entre Ríos</option>
                <option value="Formosa">Formosa</option>
                <option value="Jujuy">Jujuy</option>
                <option value="La Pampa">La Pampa</option>
                <option value="La Rioja">La Rioja</option>
                <option value="Mendoza">Mendoza</option>
                <option value="Misiones">Misiones</option>
                <option value="Neuquén">Neuquén</option>
                <option value="Río Negro">Río Negro</option>
                <option value="Salta">Salta</option>
                <option value="San Juan">San Juan</option>
                <option value="San Luis">San Luis</option>
                <option value="Santa Cruz">Santa Cruz</option>
                <option value="Santa Fe">Santa Fe</option>
                <option value="Santiago del Estero">Santiago del Estero</option>
                <option value="Tierra del Fuego">Tierra del Fuego</option>
                <option value="Tucumán">Tucumán</option>
            </select>
        </div>
        <div class="col-4">
            <label for="ciudad" class="form-label text-secondary small text-uppercase fw-semibold">Ciudad / Localidad</label>
            <input type="text" class="form-control form-control-premium" id="ciudad" name="ciudad" placeholder="Ej: Quilmes">
        </div>
        <div class="col-4">
            <label for="direccion" class="form-label text-secondary small text-uppercase fw-semibold">Calle y Número</label>
            <input type="text" class="form-control form-control-premium" id="direccion" name="direccion" placeholder="Ej: Av. Mitre 1234">
        </div>
    </div>

    <!-- Vehículo acoplado -->
    <div class="row g-2 mb-3">
        <div class="col-5">
            <label for="auto_marca" class="form-label text-secondary small text-uppercase fw-semibold">Marca del auto</label>
            <input type="text" class="form-control form-control-premium" id="auto_marca" name="auto_marca" placeholder="Ej: Nissan">
        </div>
        <div class="col-5">
            <label for="auto_modelo" class="form-label text-secondary small text-uppercase fw-semibold">Modelo</label>
            <input type="text" class="form-control form-control-premium" id="auto_modelo" name="auto_modelo" placeholder="Ej: Kicks">
        </div>
        <div class="col-2">
            <label for="auto_anio" class="form-label text-secondary small text-uppercase fw-semibold">Año</label>
            <input type="number" class="form-control form-control-premium" id="auto_anio" name="auto_anio" placeholder="2020" min="1980" max="2030">
        </div>
    </div>

    <!-- Checkboxes -->
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" id="acepta_descuentos" name="acepta_descuentos" value="1">
        <label class="form-check-label text-secondary small" for="acepta_descuentos">
            Quiero recibir descuentos y ofertas exclusivas
        </label>
    </div>

    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" id="acepta_promociones" name="acepta_promociones" value="1">
        <label class="form-check-label text-secondary small" for="acepta_promociones">
            Quiero recibir novedades del negocio por WhatsApp/correo
        </label>
    </div>

    <button type="submit" class="btn btn-premium-red w-100 py-2 fw-bold text-uppercase">Crear Cuenta</button>
</form>

<p class="text-center text-secondary small mt-4 mb-0">
    ¿Ya tenés una cuenta?
    <a href="<?= BASE_URL ?>/src/views/auth/login.php" class="text-danger text-decoration-none fw-semibold">Iniciá sesión</a>
</p>

<?php require_once __DIR__ . '/../_layouts/auth.footer.php'; ?>
