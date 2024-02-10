<?php

namespace Controllers;

use Model\Categoria;
use Model\Ponente;
use Model\Dia;
use Model\Hora;
use Model\Evento;
use MVC\Router;


class PaginasController
{
    public static function index(Router $router)
    {
        $eventos = Evento::ordenar('hora_id', 'ASC');

        //Arreglo vacio
        $eventos_formateados = [];
        foreach ($eventos as $evento) {
            //vamos a iterar en $evento->categoria con ayuda del metodo find() vamos a averiguar en la categoria_id que hay escrito
            //se va a crear una nueva $key a categoria la cual sera nombre
            $evento->categoria = Categoria::find($evento->categoria_id);
            $evento->dia = Dia::find($evento->dia_id);
            $evento->hora = Hora::find($evento->hora_id);
            $evento->ponente = Ponente::find($evento->ponente_id);
            //Eventos = categoria_id "1"
            //workshops = categoria_id = "2"
            //dia viernes = "1"
            //dia sabado = "2"

            if ($evento->dia_id === '1' && $evento->categoria_id === '1') {
                //asignamos al arreglo vacio
                $eventos_formateados['conferencias_v'][] = $evento;
            }

            if ($evento->dia_id === '2' && $evento->categoria_id === '1') {
                //asignamos al arreglo vacio
                $eventos_formateados['conferencias_s'][] = $evento;
            }

            if ($evento->dia_id === '1' && $evento->categoria_id === '2') {
                //asignamos al arreglo vacio
                $eventos_formateados['workshops_v'][] = $evento;
            }

            if ($evento->dia_id === '2' && $evento->categoria_id === '2') {
                //asignamos al arreglo vacio
                $eventos_formateados['workshops_s'][] = $evento;
            }
        }
        //Obtener el total de cada bloque
        $ponentes_total = Ponente::total();
        
        //obtener el total de conferencias
        $conferencias_total = Evento::total('categoria_id', 1);

        //obtener el total de workshops
        $workshops_total = Evento::total('categoria_id', 2);

        //Obtener todos los Ponentes, vamos a extraer todo
        $ponentes = Ponente::all();
        //Vistas
        $router->render('paginas/index', [
            'titulo' => 'Inicio',
            'eventos' => $eventos_formateados,
            'ponentes_total' => $ponentes_total,
            'conferencias_total' => $conferencias_total,
            'workshops_total' => $workshops_total,
            'ponentes' => $ponentes
        ]);
    }

    public static function evento(Router $router)
    {

        //Vistas
        $router->render('paginas/devwebcamp', [
            'titulo' => 'Sobre DevwebCamp'
        ]);
    }

    public static function paquetes(Router $router)
    {

        //Vistas
        $router->render('paginas/paquetes', [
            'titulo' => 'Paquetes DevwebCamp'
        ]);
    }

    public static function conferencias(Router $router)
    {

        $eventos = Evento::ordenar('hora_id', 'ASC');

        //Arreglo vacio
        $eventos_formateados = [];
        foreach ($eventos as $evento) {
            //vamos a iterar en $evento->categoria con ayuda del metodo find() vamos a averiguar en la categoria_id que hay escrito
            //se va a crear una nueva $key a categoria la cual sera nombre
            $evento->categoria = Categoria::find($evento->categoria_id);
            $evento->dia = Dia::find($evento->dia_id);
            $evento->hora = Hora::find($evento->hora_id);
            $evento->ponente = Ponente::find($evento->ponente_id);
            //Eventos = categoria_id "1"
            //workshops = categoria_id = "2"
            //dia viernes = "1"
            //dia sabado = "2"

            if ($evento->dia_id === '1' && $evento->categoria_id === '1') {
                //asignamos al arreglo vacio
                $eventos_formateados['conferencias_v'][] = $evento;
            }

            if ($evento->dia_id === '2' && $evento->categoria_id === '1') {
                //asignamos al arreglo vacio
                $eventos_formateados['conferencias_s'][] = $evento;
            }

            if ($evento->dia_id === '1' && $evento->categoria_id === '2') {
                //asignamos al arreglo vacio
                $eventos_formateados['workshops_v'][] = $evento;
            }

            if ($evento->dia_id === '2' && $evento->categoria_id === '2') {
                //asignamos al arreglo vacio
                $eventos_formateados['workshops_s'][] = $evento;
            }
        }

        //Vistas
        $router->render('paginas/conferencias', [
            'titulo' => 'Conferencias & Wokshops',
            'eventos' => $eventos_formateados
        ]);
    }

    public static function error(Router $router) {


        //Vistas
        $router->render('paginas/error', [
            'titulo' => 'Pagina no encontrada'
        ]);
    }
}
