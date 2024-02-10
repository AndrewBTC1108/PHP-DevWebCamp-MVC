<?php

function debuguear($variable): string
{
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}
function s($html): string
{
    $s = htmlspecialchars($html);
    return $s;
}
function pagina_actual($path): bool
{
    return str_contains($_SERVER['REQUEST_URI'] ?? '/', $path) ? true : false;
}
function is_auth() : bool
{
    //se valida wue haya una sesion, si no la hay se crea
    if(!isset($_SESSION)) {
        session_start();   
    }
    return isset($_SESSION['nombre']) && !empty($_SESSION);
}

function is_admin() : bool
{
    if(!isset($_SESSION)) {
        session_start();   
    }
    return isset($_SESSION['admin']) && !empty($_SESSION['admin']);
}

//usamos funcion que va a elejir aleatoriamente un elemento de array
function aos_animacion() : void {
    $efectos = ['fade-up', 'fade-down', 'fade-left', 'fade-right', 'flip-left', 'flip-right', 'zoom-in', 'zoom-in-up', 'zoom-in-down', 'zoom-out'];

    //ponemos el arreglo efectos y le indicamos que nos retorne un valor
    $efecto = array_rand($efectos, 1);
    //nos retorna el valor que hay en la posicion del array
    echo ' data-aos="' . $efectos[$efecto] . '" ';
}
