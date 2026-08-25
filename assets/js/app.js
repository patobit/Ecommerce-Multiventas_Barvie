// =============================================================================
// FUNCIONES GLOBALES (accesibles desde los onclick="..." del HTML generado
// por PHP). Tienen que vivir FUERA del DOMContentLoaded de abajo, porque el
// HTML con onclick se ejecuta en el contexto global, no dentro de ese bloque.
// =============================================================================
let cartCount = 0;
let cartButton = null;
let toastContainer = null;

function showToast(productName) {
    if (!toastContainer) return;

    const toast = document.createElement("div");
    toast.className = "toast align-items-center text-white bg-dark border border-secondary border-opacity-50 show mb-2";
    toast.setAttribute("role", "alert");
    toast.setAttribute("aria-live", "assertive");
    toast.setAttribute("aria-atomic", "true");

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <strong class="text-danger">✓</strong> Se agregó: <span class="fw-semibold">${productName}</span> al carrito.
            </div>
            <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Se llama desde onclick="addToCart(id, cantidad, nombre)" en las tarjetas
 * de producto y en la vista de detalle.
 * TODO: hoy solo actualiza el contador visual + muestra el toast. Cuando
 * el carrito se conecte de verdad a la base (tablas carritos/detalle_carrito),
 * acá va a hacer falta un fetch()/POST al backend para persistirlo.
 */
function addToCart(id, cantidad = 1, nombre = 'Producto') {
    cantidad = parseInt(cantidad, 10) || 1;
    cartCount += cantidad;
    if (cartButton) {
        cartButton.textContent = `Mi Carrito (${cartCount})`;
    }
    showToast(nombre);
}

/**
 * Se llama desde onclick="openProductDetail(id)" en las tarjetas de producto.
 * Navega a la página de detalle real (src/views/productos.php?id=...).
 */
function openProductDetail(id) {
    const base = window.BASE_URL || '';
    window.location.href = `${base}/src/views/productos.php?id=${id}`;
}

document.addEventListener("DOMContentLoaded", () => {

    // =========================================================================
    // 1. INICIALIZACIÓN FORZADA DEL CARRUSEL PREMIUM
    // =========================================================================
    const myCarouselElement = document.querySelector('#heroCarousel');
    if (myCarouselElement) {
        const carouselInstance = new bootstrap.Carousel(myCarouselElement, {
            interval: 4000,
            ride: 'carousel',
            wrap: true
        });
    }

    // =========================================================================
    // 2. SISTEMA DE CARRITO DE COMPRAS CON TOASTS FLOTANTES
    // =========================================================================
    // cartButton y toastContainer son variables globales (declaradas arriba,
    // fuera de este bloque) para que addToCart() pueda usarlas desde los
    // onclick="..." del HTML. Acá solo las inicializamos con el DOM ya listo.
    cartButton = document.querySelector(".ms-lg-4 .btn-premium-red");

    toastContainer = document.createElement("div");
    toastContainer.style.position = "fixed";
    toastContainer.style.bottom = "20px";
    toastContainer.style.right = "20px";
    toastContainer.style.zIndex = "1050";
    document.body.appendChild(toastContainer);

    // NOTA: el filtro de categorías del dropdown del navbar ahora navega de
    // verdad a catalogo.php?categoria=ID (ver header.php), así que ya no hace
    // falta este bloque de filtrado por JavaScript que había antes acá.

    // =========================================================================
    // 4. ENVÍO DEL FORMULARIO DE NEWSLETTER
    // =========================================================================
    const subscribeForm = document.querySelector("footer form");
    if (subscribeForm) {
        const input = subscribeForm.querySelector("input[type='email']");
        const submitBtn = subscribeForm.querySelector("button");

        if (submitBtn && input) {
            submitBtn.addEventListener("click", (e) => {
                e.preventDefault();
                const email = input.value.trim();

                if (email === "" || !email.includes("@")) {
                    input.classList.add("is-invalid");
                    alert("Por favor, ingresá un correo electrónico válido.");
                    return;
                }

                input.classList.remove("is-invalid");
                submitBtn.disabled = true;

                const originalText = submitBtn.textContent;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`;

                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = "¡Suscrito!";
                    submitBtn.classList.replace("btn-premium-red", "btn-success");
                    input.value = "";

                    alert(`¡Excelente elección! Te enviamos las novedades a: ${email}`);

                    setTimeout(() => {
                        submitBtn.textContent = originalText;
                        submitBtn.classList.replace("btn-success", "btn-premium-red");
                    }, 4000);
                }, 1200);
            });
        }
    }

    // =========================================================================
    // 5. MANEJO DE LOGIN / REGISTRO CON VALIDACIONES SEGURAS
    // =========================================================================
    const loginTab = document.querySelector('#login-tab');
    const registerTab = document.querySelector('#register-tab');

    if (loginTab && registerTab) {
        loginTab.addEventListener('shown.bs.tab', () => {
            loginTab.classList.remove('text-secondary');
            registerTab.classList.add('text-secondary');
        });

        registerTab.addEventListener('shown.bs.tab', () => {
            registerTab.classList.remove('text-secondary');
            loginTab.classList.add('text-secondary');
        });
    }

    // Validación de Registro (Pestaña "Registrarse" - Valida la casilla obligatoria)
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            if (!registerForm.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                registerForm.classList.add('was-validated');
            } else {
                e.preventDefault();
                const email = document.getElementById('regEmail').value;

                const authModalEl = document.getElementById('authModal');
                if (authModalEl) {
                    const modal = bootstrap.Modal.getInstance(authModalEl);
                    if (modal) modal.hide();
                }

                alert(`¡Cuenta creada con éxito para: ${email}!`);
                registerForm.classList.remove('was-validated');
                registerForm.reset();
            }
        });
    }

    // Validación de Login (Pestaña "Iniciar Sesión" - No requiere aceptar términos)
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            if (!loginForm.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                loginForm.classList.add('was-validated');
            } else {
                e.preventDefault();
                const email = document.getElementById('loginEmail').value;

                const authModalEl = document.getElementById('authModal');
                if (authModalEl) {
                    const modal = bootstrap.Modal.getInstance(authModalEl);
                    if (modal) modal.hide();
                }

                alert(`¡Bienvenido de nuevo! Iniciaste sesión como ${email}`);
                loginForm.classList.remove('was-validated');
                loginForm.reset();
            }
        });
    }

    // =========================================================================
    // 6. BUSCADOR DE PRODUCTOS CON SUGERENCIAS (AUTOCOMPLETE)
    // =========================================================================
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const searchSuggestions = document.getElementById('searchSuggestions');

    // Construye el índice de productos leyendo las tarjetas ya presentes en el DOM,
    // así no duplicamos datos: si agregás/editás un producto en el HTML, el buscador se actualiza solo.
    function buildProductIndex() {
        const cards = document.querySelectorAll('.card-premium');
        return Array.from(cards).map(card => {
            const name = card.querySelector('h4')?.textContent.trim() || 'Producto';
            const desc = card.querySelector('p')?.textContent.trim() || '';
            const price = card.querySelector('.fs-5')?.textContent.trim() || '';
            const idMatch = card.getAttribute('onclick')?.match(/openProductDetail\((\d+)\)/);
            const id = idMatch ? idMatch[1] : null;
            return { id, name, desc, price, card };
        });
    }

    function renderSuggestions(term) {
        if (!searchSuggestions) return;

        if (term === '') {
            searchSuggestions.classList.add('d-none');
            searchSuggestions.innerHTML = '';
            return;
        }

        const productIndex = buildProductIndex();
        const matches = productIndex.filter(p =>
            p.name.toLowerCase().includes(term) || p.desc.toLowerCase().includes(term)
        ).slice(0, 6);

        searchSuggestions.innerHTML = '';

        if (matches.length === 0) {
            searchSuggestions.innerHTML = `<div class="search-suggestion-empty">Sin resultados para "${term}"</div>`;
            searchSuggestions.classList.remove('d-none');
            return;
        }

        matches.forEach(product => {
            const item = document.createElement('div');
            item.className = 'search-suggestion-item';
            item.setAttribute('role', 'button');
            item.setAttribute('tabindex', '0');
            item.innerHTML = `
                <div class="search-suggestion-thumb"></div>
                <div class="search-suggestion-info">
                    <div class="search-suggestion-name">${product.name}</div>
                    <div class="search-suggestion-desc">${product.desc}</div>
                </div>
                <div class="search-suggestion-price">${product.price}</div>
            `;

            const goToProduct = () => {
                searchSuggestions.classList.add('d-none');
                searchInput.value = product.name;
                product.card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (product.id && typeof openProductDetail === 'function') {
                    openProductDetail(Number(product.id));
                }
            };

            item.addEventListener('click', goToProduct);
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') goToProduct();
            });

            searchSuggestions.appendChild(item);
        });

        searchSuggestions.classList.remove('d-none');
    }

    function filterVisibleCards(term) {
        const productCards = document.querySelectorAll('.card-premium');

        productCards.forEach(card => {
            const wrapper = card.closest('.col-12');
            if (!wrapper) return;

            const title = card.querySelector('h4')?.textContent.toLowerCase() || '';
            const desc = card.querySelector('p')?.textContent.toLowerCase() || '';
            const matches = term === '' || title.includes(term) || desc.includes(term);

            wrapper.style.display = matches ? '' : 'none';
        });

        const categorySections = document.querySelectorAll('.product-section');
        categorySections.forEach(section => {
            if (term !== '') {
                section.style.display = 'block';
            }
        });
    }

    if (searchForm && searchInput && searchSuggestions) {
        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim().toLowerCase();
            renderSuggestions(term);
        });

        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const term = searchInput.value.trim().toLowerCase();
            filterVisibleCards(term);
            searchSuggestions.classList.add('d-none');
        });

        // Cierra el dropdown al clickear afuera
        document.addEventListener('click', (e) => {
            if (!e.target.closest('#searchWrapper')) {
                searchSuggestions.classList.add('d-none');
            }
        });
    }

    // =========================================================================
    // 7. ORDENAR POR PRECIO Y FILTRAR POR PRESUPUESTO
    // =========================================================================
    const sortSelect = document.getElementById('sortSelect');
    const minPriceInput = document.getElementById('minPrice');
    const maxPriceInput = document.getElementById('maxPrice');
    const applyPriceFilterBtn = document.getElementById('applyPriceFilter');
    const clearPriceFilterBtn = document.getElementById('clearPriceFilter');

    // Convierte "$45.000" -> 45000 (el "." se usa como separador de miles, no decimal)
    function parsePrice(text) {
        const digits = (text || '').replace(/[^\d]/g, '');
        return digits ? parseInt(digits, 10) : 0;
    }

    // Guarda el orden original de cada grilla de categoría para poder volver a "Relevancia"
    const originalOrderBySection = new Map();
    document.querySelectorAll('.product-section .row').forEach(row => {
        originalOrderBySection.set(row, Array.from(row.children));
    });

    function sortProducts(order) {
        document.querySelectorAll('.product-section .row').forEach(row => {
            if (order === 'relevancia') {
                const original = originalOrderBySection.get(row);
                if (original) original.forEach(child => row.appendChild(child));
                return;
            }

            const wrappers = Array.from(row.children);
            wrappers.sort((a, b) => {
                const priceA = parsePrice(a.querySelector('.fs-5')?.textContent);
                const priceB = parsePrice(b.querySelector('.fs-5')?.textContent);
                return order === 'asc' ? priceA - priceB : priceB - priceA;
            });
            wrappers.forEach(wrapper => row.appendChild(wrapper));
        });
    }

    function applyPriceRangeFilter() {
        const min = minPriceInput.value !== '' ? Number(minPriceInput.value) : 0;
        const max = maxPriceInput.value !== '' ? Number(maxPriceInput.value) : Infinity;

        document.querySelectorAll('.card-premium').forEach(card => {
            const wrapper = card.closest('.col-12');
            if (!wrapper) return;

            const price = parsePrice(card.querySelector('.fs-5')?.textContent);
            wrapper.style.display = (price >= min && price <= max) ? '' : 'none';
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', () => sortProducts(sortSelect.value));
    }

    if (applyPriceFilterBtn) {
        applyPriceFilterBtn.addEventListener('click', applyPriceRangeFilter);
    }

    if (clearPriceFilterBtn) {
        clearPriceFilterBtn.addEventListener('click', () => {
            minPriceInput.value = '';
            maxPriceInput.value = '';
            document.querySelectorAll('.card-premium').forEach(card => {
                const wrapper = card.closest('.col-12');
                if (wrapper) wrapper.style.display = '';
            });
        });
    }
});