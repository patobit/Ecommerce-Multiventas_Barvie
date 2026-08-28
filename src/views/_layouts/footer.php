<!-- Footer Premium -->
    <footer class="bg-dark py-5 mt-5 border-top border-secondary border-opacity-25">
        <div class="container">
            <div class="row align-items-center justify-content-between gy-4">
                <div class="col-12 col-lg-5 text-center text-lg-start">
                    <h5 class="fw-bold text-white mb-1 text-uppercase tracking-wider">Multiventas Barvie</h5>
                    <p class="text-secondary small mb-0">Pasión por los fierros. Componentes premium y el asesoramiento que tu motor se merece.</p>
                </div>
                <div class="col-12 col-lg-6">
                    <form class="d-flex flex-column flex-sm-row gap-2">
                        <input type="email" class="form-control form-control-premium" placeholder="Unite al Club de Ofertas" aria-label="Email">
                        <button class="btn btn-premium-red px-4 fw-semibold" type="button">Suscribirse</button>
                    </form>
                </div>
            </div>
            <div class="text-center text-secondary small mt-4 pt-4 border-top border-secondary border-opacity-10">
                <p class="mb-0">&copy; 2026 Multiventas Barvie. Todos los derechos reservados. TP-7 Plataformas Móviles.</p>
            </div>
        </div>
    </footer>

    <!-- Modal de Autenticación -->
    <div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark text-white border border-danger border-opacity-50 shadow-lg">
                <div class="modal-header border-secondary border-opacity-25 pb-0">
                    <ul class="nav nav-tabs card-header-tabs w-100" id="authTabs" role="tablist">
                        <li class="nav-item w-50" role="presentation">
                            <button class="nav-link active text-center w-100 border-0 bg-transparent text-uppercase fw-bold py-3 text-white"
                                id="login-tab" data-bs-toggle="tab" data-bs-target="#login-pane" type="button" role="tab" aria-controls="login-pane" aria-selected="true">
                                Iniciar Sesión
                            </button>
                        </li>
                        <li class="nav-item w-50" role="presentation">
                            <button class="nav-link text-center w-100 border-0 bg-transparent text-uppercase fw-bold py-3 text-secondary"
                                id="register-tab" data-bs-toggle="tab" data-bs-target="#register-pane" type="button" role="tab" aria-controls="register-pane" aria-selected="false">
                                Registrarse
                            </button>
                        </li>
                    </ul>
                    <button type="button" class="btn-close btn-close-white align-self-center mb-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="tab-content" id="authTabsContent">
                        <!-- Formulario de Iniciar Sesión -->
                        <div class="tab-pane fade show active" id="login-pane" role="tabpanel" aria-labelledby="login-tab" tabindex="0">
                            <form id="loginForm" novalidate>
                                <div class="mb-3">
                                    <label for="loginEmail" class="form-label text-secondary small text-uppercase fw-semibold">Correo Electrónico</label>
                                    <input type="email" class="form-control bg-black text-white border-secondary border-opacity-50 form-control-premium" id="loginEmail" placeholder="nombre@correo.com" required>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <label for="loginPassword" class="form-label text-secondary small text-uppercase fw-semibold">Contraseña</label>
                                        <a href="#" class="text-danger small text-decoration-none">¿La olvidaste?</a>
                                    </div>
                                    <input type="password" class="form-control bg-black text-white border-secondary border-opacity-50 form-control-premium" id="loginPassword" placeholder="••••••••" required>
                                </div>
                                <div class="mb-3 form-check d-flex align-items-center gap-2">
                                    <input type="checkbox" class="form-check-input mt-0" id="rememberMe">
                                    <label class="form-check-label text-secondary small" for="rememberMe">Recordar mi sesión</label>
                                </div>
                                <button type="submit" class="btn btn-premium-red w-100 py-2.5 text-uppercase fw-bold mt-2">Entrar</button>
                            </form>
                        </div>

                        <!-- Formulario de Registro -->
                        <div class="tab-pane fade" id="register-pane" role="tabpanel" aria-labelledby="register-tab" tabindex="0">
                            <form id="registerForm" novalidate>
                                <div class="mb-3">
                                    <label for="regName" class="form-label text-secondary small text-uppercase fw-semibold">Nombre Completo</label>
                                    <input type="text" class="form-control bg-black text-white border-secondary border-opacity-50 form-control-premium" id="regName" placeholder="Tu Nombre" required>
                                </div>
                                <div class="mb-3">
                                    <label for="regEmail" class="form-label text-secondary small text-uppercase fw-semibold">Correo Electrónico</label>
                                    <input type="email" class="form-control bg-black text-white border-secondary border-opacity-50 form-control-premium" id="regEmail" placeholder="nombre@correo.com" required>
                                </div>
                                <div class="mb-2">
                                    <label for="regPassword" class="form-label text-secondary small text-uppercase fw-semibold">Contraseña</label>
                                    <input type="password" class="form-control bg-black text-white border-secondary border-opacity-50 form-control-premium" id="regPassword" placeholder="Mínimo 8 caracteres" required>
                                </div>
                                <div class="mb-3 form-check d-flex align-items-center gap-2">
                                    <input type="checkbox" class="form-check-input mt-0" id="termsCheck" required>
                                    <label class="form-check-label text-secondary small" for="termsCheck">Acepto los términos y condiciones de la tienda</label>
                                </div>
                                <button type="submit" class="btn btn-premium-red w-100 py-2.5 text-uppercase fw-bold">Crear Cuenta</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // BASE_URL viene del PHP para que app.js pueda armar links absolutos
        // (por ejemplo, para ir a la página de detalle de un producto).
        window.BASE_URL = "<?= BASE_URL ?>";
    </script>
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>

</html>
