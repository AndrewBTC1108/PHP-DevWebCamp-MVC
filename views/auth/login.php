<main class="auth">
    <h2 class="auth__heading"><?php echo $titulo ;?></h2>
    <p class="auth__texto">Inicia sesion en DevWebcamp</p>

    <?php
        require_once __DIR__ . '/../templates/alertas.php';
    ?>
    <form method="POST" action="/login" class="formulario">
        <div class="formulario__campo">
            <label for="email" class="formulario__label">Email</label>
            <input 
                type="text"
                class="formulario__label" 
                placeholder="Tu email"
                id="email"
                name="email"
            />
        </div>

        <div class="formulario__campo">
            <label for="password" class="formulario__label">Password</label>
            <input 
                type="password"
                class="formulario__label" 
                placeholder="Tu Password"
                id="password"
                name="password"
            />
        </div>

        <input type="submit" class="formulario__submit" value="Iniciar Sesion">

        <div class="acciones">
            <a href="/registro" class="acciones__enlace">¿Aun no tienes una cuenta? Obtener una</a>
            <a href="/olvide" class="acciones__enlace">¿Olvidaste tu Password?</a>
        </div>
    </form>
</main>