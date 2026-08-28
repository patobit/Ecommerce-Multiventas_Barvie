function getAllCartProducts() {
    /* completar */
}

function addToCart(id, cantidad = 1, nombre = 'Producto') {
    cantidad = parseInt(cantidad, 10) || 1;
    const idProducto = parseInt(id, 10) || 0;

    if (idProducto <= 0) {
        alert('ID de producto no válido');
        return;
    }

    const datos = new FormData();
    datos.append('id_producto', idProducto);
    datos.append('cantidad', cantidad);

    // Ruta absoluta desde la raíz del servidor http://localhost:8000/
    const endpoint = '/src/controllers/auth/carrito_controller.php';

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

            showToast(nombre);
        } else {
            alert(data.message || 'No se pudo agregar el producto.');
        }
    })
    .catch(error => {
        console.error('Error al agregar al carrito:', error);
        alert('Ocurrió un error al agregar el producto.');
    });
}