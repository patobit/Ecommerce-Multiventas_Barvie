// Detecta la ruta base del proyecto de forma dinámica
const BASE_URL = window.BASE_URL || window.location.pathname.substring(0, window.location.pathname.indexOf('/src'));
const ENDPOINT = '${BASE_URL}/src/controllers/auth/carrito_controller.php';

const cartButton = document.getElementById('cartBtn');

export async function getAllCartProducts() {
    try {
        const res = await fetch(ENDPOINT, { method: 'GET' });
        return await res.json();
    } catch (error) {
        console.error('Error al obtener productos del carrito:', error);
        return null;
    }
}

// Recalcula el número de "Mi Carrito (n)" con lo que hay realmente en el carrito
export async function actualizarContadorCarrito() {
    if (!cartButton) return;
    const data = await getAllCartProducts();
    if (!data || !data.success) return;
    const total = data.productos.reduce((suma, p) => suma + (parseInt(p.cantidad, 10) || 0), 0);
    cartButton.textContent = 'Mi Carrito (${total})';
}

export async function addToCart(id, cantidad = 1, nombre = 'Producto') {
    cantidad = parseInt(cantidad, 10) || 1;
    const idProducto = parseInt(id, 10) || 0;

    if (idProducto <= 0) {
        alert('ID de producto no válido');
        return;
    }

    try {
        const res = await fetch(ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_producto: idProducto, cantidad })
        });
        const data = await res.json();

        if (res.status === 401) {
            alert(data.message || 'Tenés que iniciar sesión para usar el carrito.');
            window.location.href ='${BASE_URL}/src/views/auth/login.php';
            return;
        }
        if (!data.success) {
            alert(data.message || 'No se pudo agregar el producto.');
            return;
        }

        await actualizarContadorCarrito();

        if (typeof showToast === 'function') {
            showToast(nombre);
        } else {
            alert('Agregado: ${nombre}');
        }
    } catch (error) {
        console.error('Error al agregar al carrito:', error);
        alert('Ocurrió un error al agregar el producto.');
    }
}

export async function actualizarCantidad(idDetalleCarrito, nuevaCantidad) {
    try {
        const res = await fetch(ENDPOINT, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_detalle_carrito: idDetalleCarrito, cantidad: nuevaCantidad })
        });
        return await res.json();
    } catch (error) {
        console.error('Error al actualizar la cantidad:', error);
        return { success: false, message: 'Error de conexión.' };
    }
}

export async function eliminarDelCarrito(idDetalleCarrito) {
    try {
        const res = await fetch(ENDPOINT, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_detalle_carrito: idDetalleCarrito })
        });
        return await res.json();
    } catch (error) {
        console.error('Error al eliminar el producto:', error);
        return { success: false, message: 'Error de conexión.' };
    }
}

export async function finalizarCompra() {
    const endpoint = '${BASE_URL}/src/controllers/auth/checkout_controller.php';
    try {
        const res = await fetch(endpoint, { method: 'POST' });
        return await res.json();
    } catch (error) {
        console.error('Error al finalizar la compra:', error);
        return { success: false, message: 'Error de conexión.' };
    }
}

// Para que funcione el onclick="addToCart(...)" de productos_card.php
window.addToCart = addToCart;