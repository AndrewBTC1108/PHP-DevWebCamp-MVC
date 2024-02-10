<?php

namespace Controllers;

use MVC\Router;
use Model\Registro;
use Classes\Paginacion;
use Model\Paquete;
use Model\Usuario;

class RegistradosController
{
    public static function index(Router $router)
    {
        //para proteger el panel de administracion
        if(!is_admin()) {
            header('Location: /login');
        }

        //Añadimos la paginacion
        $pagina_actual = $_GET['page'];
        $pagina_actual = filter_var($pagina_actual, FILTER_VALIDATE_INT);

        if(!$pagina_actual || $pagina_actual < 1) {
            //redireccionamos
            header('Location: /admin/registrados?page=1');
        }

        //Los registros que va a mostrar por pagina
        $registros_por_pagina = 10;
        //Nos va a retornar cuantos registros hay en total en la BD
        $total = Registro::total();
        // pasamos los valores al constructor
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        if($paginacion->total_paginas() < $pagina_actual) {
            header('Location: /admin/registrados?page=1');
        }

        //Vamos a llamar a todos los registros de la BD
        //Gracias al ofset, se va a saltar los que no requiere y traera solo los que se necesita
        $registros = Registro::paginar($registros_por_pagina, $paginacion->offset());

        //instanciamos la clase Usuario y la de paquete
        foreach($registros as $registro) {
            $registro->usuario = Usuario::find($registro->usuario_id);
            $registro->paquete = Paquete::find($registro->paquete_id);
        }
        // debuguear($registros);

        //pasamos a la vista
        $router->render('admin/registrados/index', [
            'titulo' => 'Usuarios Registrados',
            'registros' => $registros,
            'paginacion' => $paginacion->paginacion()
        ]);
    }
}