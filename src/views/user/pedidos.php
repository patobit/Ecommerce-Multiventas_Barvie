<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../config/rutas.php';
require_once __DIR__ . '/../../config/strapi_client.php';
header('Cache-Control: no-store, private');
if (empty($_SESSION['usuario']['jwt'])) {
    header('Location: ' . BASE_URL . '/src/views/auth/login.php');
    exit;
}
$resultado = strapiRequest('GET','mis-pedidos',[],null,$_SESSION['usuario']['jwt']);
$pedidos = $resultado['data']['data'] ?? [];
function pedidoTexto($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function pedidoImporte($value): string { return '$' . number_format((float)$value,2,',','.'); }
$isOrdersPage = true;
require_once __DIR__ . '/../_layouts/header.php';
?>
<main class="container my-4 order-history">
<div class="d-flex justify-content-between align-items-center gap-3 mb-4"><div><a href="<?= BASE_URL ?>/src/views/user/profile.php" class="text-secondary small">← Mi cuenta</a><h1 class="text-white mt-2">Mis pedidos</h1></div><a class="btn btn-premium-outline" href="<?= BASE_URL ?>/src/views/catalogo.php">Seguir comprando</a></div>
<?php if (!$resultado['ok']): ?><p role="alert" class="alert alert-warning">No pudimos consultar tus pedidos. Intentá de nuevo.</p>
<?php elseif (!$pedidos): ?><div class="order-empty"><h2>Todavía no tenés pedidos.</h2><p>Cuando confirmes una compra, la vas a encontrar acá.</p><a class="btn btn-premium-red" href="<?= BASE_URL ?>/src/views/catalogo.php">Ver productos</a></div>
<?php else: foreach ($pedidos as $pedido):
$cliente = $pedido['Datos_cliente'] ?? [];
$fecha = !empty($pedido['Fecha']) ? (new DateTime($pedido['Fecha']))->setTimezone(new DateTimeZone('America/Argentina/Buenos_Aires'))->format('d/m/Y · H:i') : 'Fecha sin registrar';
?>
<article class="order-card"><header><div><span>Pedido #<?= pedidoTexto(strtoupper(substr($pedido['documentId'],-8))) ?></span><p><?= pedidoTexto($fecha) ?></p></div><span class="order-state"><?= pedidoTexto($pedido['Estado']) ?></span></header>
<div class="order-items"><?php foreach ($pedido['Productos_comprados'] as $item): ?><div class="order-item"><div><strong><?= pedidoTexto($item['nombre']) ?></strong><p><?= pedidoTexto($item['marca']) ?> · <?= (int)$item['cantidad'] ?> unidad<?= (int)$item['cantidad'] === 1 ? '' : 'es' ?> × <?= pedidoImporte($item['precio_unitario']) ?></p></div><strong><?= pedidoImporte($item['subtotal']) ?></strong></div><?php endforeach; ?></div>
<div class="order-summary"><span>Total de productos (<?= pedidoTexto($pedido['Moneda']) ?>)</span><strong><?= pedidoImporte($pedido['Total']) ?></strong></div>
<details class="order-details"><summary>Entrega, pago y datos del pedido</summary><dl>
<dt>Entrega</dt><dd><?= pedidoTexto($pedido['Metodo_entrega']) ?></dd>
<dt>Método de pago</dt><dd><?= pedidoTexto($pedido['Metodo_pago']) ?></dd>
<dt>Estado del pago</dt><dd><?= pedidoTexto($pedido['Estado_pago']) ?></dd>
<dt>Costo de envío</dt><dd><?= $pedido['Costo_envio'] === null ? 'Sin registrar / a confirmar' : pedidoImporte($pedido['Costo_envio']) ?></dd>
<?php if ($pedido['Costo_envio'] !== null): ?><dt>Total con entrega</dt><dd><?= pedidoImporte((float)$pedido['Total'] + (float)$pedido['Costo_envio']) ?></dd><?php endif; ?>
<dt>Recibe / retira</dt><dd><?= pedidoTexto($cliente['nombre'] ?? 'Sin registrar') ?></dd>
<dt>Contacto</dt><dd><?= pedidoTexto($cliente['telefono'] ?? 'Sin registrar') ?> · <?= pedidoTexto($cliente['email'] ?? '') ?></dd>
<?php if ($pedido['Metodo_entrega'] === 'Envio'): ?><dt>Dirección</dt><dd><?= pedidoTexto(implode(', ',array_filter([$cliente['direccion'] ?? '',$cliente['ciudad'] ?? '',$cliente['provincia'] ?? '',$cliente['codigo_postal'] ?? '']))) ?></dd><?php endif; ?>
<?php if (!empty($pedido['Seguimiento'])): ?><dt>Seguimiento</dt><dd><?= pedidoTexto($pedido['Seguimiento']) ?></dd><?php endif; ?>
<?php if (!empty($pedido['Notas'])): ?><dt>Indicaciones</dt><dd><?= pedidoTexto($pedido['Notas']) ?></dd><?php endif; ?>
</dl></details></article>
<?php endforeach; endif; ?>
</main>
<?php require_once __DIR__ . '/../_layouts/footer.php'; ?>
