<?php

namespace Controllers;

use Model\Ponente;

class APIPonentes
{
    public static function index()
    {
        $ponentes = Ponente::all();
        echo json_encode($ponentes);
    }

    public static function ponente()
    {
        $id = $_GET['id'];
        $id = filter_var($id, FILTER_VALIDATE_INT);

        //validamos
        if(!$id || $id < 1) {
            //mandamos un arreglo vacio
            echo json_encode([]);
            return;
        }

        //buscamos por id
        $ponente = Ponente::find($id);
        echo json_encode($ponente, JSON_UNESCAPED_SLASHES);
    }
}
