<?php

namespace Controllers;

use Model\Evento;
use Model\Registro;
use MVC\Router;
use Model\Usuario;

class DashboardController
{
    public static function index(Router $router)
    {
        //para proteger el panel de administracion
        if(!is_admin()) {
            header('Location: /login');
        }

        //Obtener ultimos registros
        $registros = Registro::get(5);

        //iteramos en registros con el fin de obtener de los registros el nombre y mas del usuario
        foreach($registros as $registro) {
            $registro->usuario = Usuario::find($registro->usuario_id);
        }

        //calcular los ingresos, de cada eleccion de paquetes
        $virtuales = Registro::total('paquete_id', 2);
        $presenciales = Registro::total('paquete_id', 1);

        //ya descontadas las comisiones por parte de Pay pal es el total
        //46.41, vamos a traer el total de lo que hay en cada paquete_id y lo multiplicamos respecto su precio para luego sumar todo y dar un total
        $ingresos = ($virtuales * 46.41) + ($presenciales * 189.54);

        //Obtener eventos con mas y menos lugares diponibles
        $menos_disponibles = Evento::ordenarLimite('disponibles', 'ASC', 5);
        $mas_disponibles = Evento::ordenarLimite('disponibles', 'DESC', 5);

        $router->render('admin/dashboard/index', [
            'titulo' => 'Panel de Administracion',
            'registros' => $registros,
            'ingresos' => $ingresos,
            'menos_disponibles' => $menos_disponibles,
            'mas_disponibles' => $mas_disponibles
        ]);
    }
}
