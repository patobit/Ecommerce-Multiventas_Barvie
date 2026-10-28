// Detecta la ruta base del proyecto de forma dinámica
const BASE_URL = window.BASE_URL || window.location.pathname.substring(0, window.location.pathname.indexOf('/src'));
const ENDPOINT = new URL('../../src/controllers/auth/carrito_controller.php', import.meta.url).href;

let cartCount = 0;
const cartButton = document.getElementById('cartBtn');

export async function getAllCartProducts() {
    // Usamos BASE_URL para armar la ruta dinámica
    const endpoint = `${BASE_URL}/src/controllers/auth/carrito_controller.php`;

    try {
        const res = await fetch(endpoint, { method: 'GET' });
        const text = await res.text();
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Respuesta no es JSON válido:', text);
            return null;
        }
    } catch (error) {
        console.error('Error al obtener productos del carrito:', error);
        return null;
    }
}

export function addToCart(id, cantidad = 1, nombre = 'Producto') {
    cantidad = parseInt(cantidad, 10) || 1;
    const idProducto = parseInt(id, 10) || 0;

    if (idProducto <= 0) {
        alert('ID de producto no válido');
        return;
    }

    const datos = new FormData();
    datos.append('id_producto', idProducto);
    datos.append('cantidad', cantidad);

    // Mantenemos la ruta dinámica con BASE_URL hacia el controlador
    const endpoint = `${BASE_URL}/src/controllers/auth/carrito_controller.php`;

    fetch(endpoint, {
        method: 'POST',
        body: datos
    })
    .then(async response => {
        const text = await response.text();

        if (!response.ok) {
            throw new Error(`Error en servidor (${response.status})`);
        }
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Respuesta no es JSON válido:', text);
            throw new Error('El servidor devolvió un formato no válido.');
        }
    })
    .then(data => {
        if (data.success) {
            cartCount += cantidad;

            if (cartButton) {
                cartButton.textContent = `Mi Carrito (${cartCount})`;
            }

            if (typeof showToast === 'function') {
                showToast(nombre);
            } else {
                alert(`Agregado: ${nombre}`);
            }
        } else {
            alert(data.message || 'No se pudo agregar el producto.');
        }
    })
    .catch(error => {
        console.error('Error al agregar al carrito:', error);
        alert('Ocurrió un error al agregar el producto.');
    });
}

async function pedir(method, body) {
    try {
        const res = await fetch(ENDPOINT, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });
        const text = await res.text();
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Respuesta no válida:', text);
            const limpio = text.replace(/<[^>]*>/g, '').trim().slice(0, 300);
            return { success: false, message: "Respuesta 'inesperada (HTTP" + res.status + "): " + limpio };
        }
    } catch (error) {
        console.error('Error de red:', error);
        return { success: false, message: 'Error de conexión.' };
    }
}

export function actualizarCantidad(idDetalleCarrito, nuevaCantidad) {
    return pedir('POST', { accion: 'actualizar', id_detalle_carrito: idDetalleCarrito, cantidad: nuevaCantidad });
}

export function eliminarDelCarrito(idDetalleCarrito) {
    return pedir('POST', { accion: 'eliminar', id_detalle_carrito: idDetalleCarrito });
}

export async function finalizarCompra(datos) {
    const endpoint = `${BASE_URL}/src/controllers/auth/checkout_controller.php`;
    try {
        const res = await fetch(endpoint, { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(datos) });
        return await res.json();
    } catch (error) {
        console.error('Error al finalizar la compra:', error);
        return { success: false, message: 'Error de conexión.' };
    }
}