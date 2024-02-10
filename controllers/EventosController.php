<?php

namespace Controllers;

use MVC\Router;
use Model\Categoria;
use Model\Dia;
use Model\Evento;
use Model\Hora;
use Classes\Paginacion;
use Model\Ponente;

class EventosController
{
    public static function index(Router $router)
    {
        //para proteger el panel de administracion
        if (!is_admin()) {
            header('Location: /login');
        }

        //Añadimos la paginacion
        $pagina_actual = $_GET['page'];
        $pagina_actual = filter_var($pagina_actual, FILTER_VALIDATE_INT);

        if (!$pagina_actual || $pagina_actual < 1) {
            //redireccionamos
            header('Location: /admin/eventos?page=1');
        }

        //Los registros que va a mostrar por pagina
        $registros_por_pagina = 10;
        //Nos va a retornar cuantos registros hay en total en la BD
        $total = Evento::total();

        //pasamos los valores al consructor
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        if ($paginacion->total_paginas() < $pagina_actual) {
            header('Location: /admin/ponentes?page=1');
        }

        //Vamos a llamar a todos los eventos de la BD
        //Gracias al ofset, se va a saltar los que no requiere y traera solo los que se necesita
        $eventos = Evento::paginar($registros_por_pagina, $paginacion->offset());

        //vamos a iterar en cada uno de los eventos para saber que categoria tienen
        foreach ($eventos as $evento) {
            //vamos a iterar en $evento->categoria con ayuda del metodo find() vamos a averiguar en la categoria_id que hay escrito
            //se va a crear una nueva $key a categoria la cual sera nombre
            $evento->categoria = Categoria::find($evento->categoria_id);
            $evento->dia = Dia::find($evento->dia_id);
            $evento->hora = Hora::find($evento->hora_id);
            $evento->ponente = Ponente::find($evento->ponente_id);
        }

        $router->render('admin/eventos/index', [
            'titulo' => 'Conferencias y Workshops',
            'eventos' => $eventos,
            'paginacion' => $paginacion->paginacion()
        ]);
    }

    public static function crear(Router $router)
    {
        //para proteger el panel de administracion
        if (!is_admin()) {
            header('Location: /login');
        }

        $alertas = [];

        $categorias = Categoria::all('ASC');
        $dias = Dia::all('ASC');
        $horas = Hora::all('ASC');

        //instanciamos y no toma nada
        $evento = new Evento;
        //POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //sincronizamos con lo que enviamos por post
            //en los input hidden se envia los id de cada FK
            $evento->sincronizar($_POST);

            //validamos las alertas
            $alertas = $evento->validar();

            //validamos que esten vacias las alertas para seguir
            if (empty($alertas)) {
                //Guardamos en la BD
                $resultado = $evento->guardar();

                //validamos que se haya guardado
                if ($resultado) {
                    //redireccionamos
                    header('Location: /admin/eventos');
                }
            }
        }

        $router->render('admin/eventos/crear', [
            'titulo' => 'Registrar Evento',
            'alertas' => $alertas,
            'categorias' => $categorias,
            'dias' => $dias,
            'horas' => $horas,
            'evento' => $evento
        ]);
    }

    public static function editar(Router $router)
    {
        //para proteger el panel de administracion
        if (!is_admin()) {
            header('Location: /login');
        }

        $alertas = [];

        $id = $_GET['id'];
        $id = filter_var($id, FILTER_VALIDATE_INT);

        //Validamos que haya un $id si no redireccionamos
        if (!$id) {
            header('Location: /admin/eventos');
        }

        $categorias = Categoria::all('ASC');
        $dias = Dia::all('ASC');
        $horas = Hora::all('ASC');

        //vamos a encontrar el evento por su $id
        $evento = Evento::find($id);
        //Validamos que haya un $evento si no redireccionamos
        if (!$evento) {
            header('Location: /admin/eventos');
        }

        //POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //para proteger el panel de administracion
            if (!is_admin()) {
                header('Location: /login');
            }
            
            //sincronizamos con lo que enviamos por post
            //en los input hidden se envia los id de cada FK
            $evento->sincronizar($_POST);

            //validamos las alertas
            $alertas = $evento->validar();

            //validamos que esten vacias las alertas para seguir
            if (empty($alertas)) {
                //Guardamos en la BD
                $resultado = $evento->guardar();

                //validamos que se haya guardado
                if ($resultado) {
                    //redireccionamos
                    header('Location: /admin/eventos');
                }
            }
        }

        $router->render('admin/eventos/editar', [
            'titulo' => 'Editar Evento',
            'alertas' => $alertas,
            'categorias' => $categorias,
            'dias' => $dias,
            'horas' => $horas,
            'evento' => $evento
        ]);
    }

    public static function eliminar()
    {
        //para proteger el panel de administracion
        if(!is_admin()) {
            header('Location: /login');
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $evento = Evento::find($id);

            //validamos si hay un evento o no
            if(!isset($evento)) {
                header('Location: /admin/eventos');
            }
            //eliminamos de la BD
            $resultado = $evento->eliminar();

            if($resultado) {
                header('Location: /admin/eventos'); 
            }
        }
    }
}
