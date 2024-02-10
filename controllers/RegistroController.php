<?php

namespace Controllers;

use Model\Dia;
use Model\Hora;
use MVC\Router;
use Model\Evento;
use Model\Paquete;
use Model\Ponente;
use Model\Usuario;
use Model\Registro;
use Model\Categoria;
use Model\EventosRegistros;
use Model\Regalo;

class RegistroController
{
    public static function crear(Router $router)
    {
        if(!is_auth()) {
            header('Location: /');
            return;
        }

        //Vamos a verificar primero si el usuario ya eligio un plan
        $registro = Registro::where('usuario_id', $_SESSION['id']);

        //validamos si ya se ha comprado y completado el registro
        if($registro->regalo_id === 1 && $registro->paquete_id === "1") {
            header('Location: /finalizar-registro/conferencias');
            return;
        }

        if(isset($registro) && ($registro->paquete_id === "3" || $registro->paquete_id === "2" )) {
            //redireccionamos y usamos urlencode para evitar caracteres especiales
            header('Location: /boleto?id=' . urlencode($registro->token));
            return;
        }
        // debuguear($registro);

        $router->render('registro/crear', [
            'titulo' => 'Finalizar Registro'
        ]);
    }

    public static function gratis(Router $router)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //verificar que este autenticado
            if(!is_auth()) {
                header('Location: /login');
                return;
            }

            //Vamos a verificar primero si el usuario ya eligio un plan
            $registro = Registro::where('usuario_id', $_SESSION['id']);
            if(isset($registro) && $registro->paquete_id === '3') {
                //redireccionamos y usamos urlencode para evitar caracteres especiales
                header('Location: /boleto?id=' . urlencode($registro->token));
                return;
            }

            //vamos a tener un boleto virtual
            $token = substr(md5(uniqid( rand(), true )), 0, 8);
            
            //Crear Nuevo Registro
            $datos = [
                'paquete_id' => 3,
                'pago_id' => '',
                'token' => $token,
                'usuario_id' => $_SESSION['id']
            ];

            $registro = new Registro($datos);
            //Insertamos el registro en la BD
            $resultado = $registro->guardar();

            //validamos
            if($resultado) {
                //redireccionamos y usamos urlencode para evitar caracteres especiales
                header('Location: /boleto?id=' . urlencode($registro->token));
                return;
            }
        }
    }

    public static function boleto(Router $router)
    {
        //verificar que este autenticado
        if(!is_auth()) {
            header('Location: /login');
            return;
        }

        //validar la URL
        $id = $_GET['id'];

        //validamso que haya un id y que su extencion sea igual a 8
        if(!$id || !strlen($id) === 8 ) {
            header('Location: /');
            return;
        }

        //buscarlo en la BD
        $registro = Registro::where('token', $id);
        if(!$registro) {
            //redireccionamos
            header('Location: /');
            return;
        }
        
        //llenar las tablas de referencia, se crea 2 elementos mas para el array
        $registro->usuario = Usuario::find($registro->usuario_id);
        $registro->paquete = Paquete::find($registro->paquete_id);

        $router->render('registro/boleto', [
            'titulo' => 'Asistencia a Devwebcamp',
            'registro' => $registro
        ]);
    }

    public static function pagar(Router $router) {

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            //verificar que este autenticado
            if(!is_auth()) {
                header('Location: /login');
                return;
            }

            // Validar que Post no venga vacio
            if(empty($_POST)) {
                echo json_encode([]);
                return;
            }

            // Crear el registro
            $datos = $_POST;
            $datos['token'] = substr( md5(uniqid( rand(), true )), 0, 8);
            $datos['usuario_id'] = $_SESSION['id'];
            
            try {
                //creamos una instancia del modelo para pasarle los datos
                $registro = new Registro($datos);
                
                //Insertamos el registro en la BD
                $resultado = $registro->guardar();
                echo json_encode($resultado);
            } catch (\Throwable $th) {
                echo json_encode([
                    'resultado' => 'error'
                ]);
            }

        }
    }

    public static function conferencias(Router $router)
    {
        //verificar que este autenticado
        if(!is_auth()) {
            header('Location: /login');
            return;
        }

        //validar que el usuario tenga el plan presencial
        $usuario_id = $_SESSION['id'];
        $registro = Registro::where('usuario_id', $usuario_id );

        //redireccionar a boleto virtual en caso de haber finalizado su registro
        if(isset($registro) && $registro->paquete_id === "2") {
            //redireccionamos y usamos urlencode para evitar caracteres especiales
            header('Location: /boleto?id=' . urlencode($registro->token));
            return;
        }

        if($registro->paquete_id !== "1") {
            header('Location: /');
            return;
        }

        //redireccionar a boleto virtual en caso de haber finalizado su registro
        if($registro->regalo_id != 1 && $registro->paquete_id === "1") {
            //redireccionamos y usamos urlencode para evitar caracteres especiales
            header('Location: /boleto?id=' . urlencode($registro->token));
            return;
        }

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

        //instanciamosla clase regalo
        $regalos = Regalo::all('ASC');

        //Manejando el registro mendiante $_POST
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            //verificar que este autenticado
            if(!is_auth()) {
                header('Location: /login');
                return;
            }

            //separar los eventos
            $eventos = explode(',', $_POST['eventos']);
            if(empty($eventos)) {
                echo json_encode(['resultado' => false]);
                return;
            }

            //Obtener el registro de usuario
            $registro = Registro::where('usuario_id', $_SESSION['id']);
            if(!isset($registro) || $registro->paquete_id !== "1") {
                echo json_encode(['resultado' => false]);
                return;
            }

            //validar la disponibilidad de los eventos seleccionados
            //vamos a iterar en cada uno de los eventos que haya seleccioando para verificar que esten
            //disponibles
            //Creamos un array para guardar los eventos memoria
            $eventos_array = [];
            foreach($eventos as $evento_id) {
                $evento = Evento::find($evento_id);
                //comprobar que el evento exista
                if(!isset($evento) || $evento->disponibles === "0") {
                    echo json_encode(['resultado' => false]);
                    return;
                }
                //si se valida que exista y que haya disponibilidad se guardara en memoria
                $eventos_array[] = $evento;
            }

            foreach($eventos_array as $evento) {
                //al encontrar el evento le disminuimos su cantidad en -1
                $evento->disponibles -= 1;
                $evento->guardar();

                //Almacenar el registro
                $datos = [
                    'evento_id' => (int) $evento->id,
                    'registro_id' => (int) $registro->id
                ];
                
                //instanciamos la clase EventosRegistros
                $registro_usuario = new EventosRegistros($datos);
                //alamcenamos en la BD
                $registro_usuario->guardar();
            }

            //almacenar el regalo
            $registro->sincronizar(['regalo_id' => $_POST['regalo_id']]);
            
            $resultado = $registro->guardar();
            if($resultado) {
                echo json_encode([
                    'resultado' => $resultado,
                    'token' => $registro->token
                ]);
            } else {
                echo json_encode(['resultado' => false]);
            }

            return;
        }

        $router->render('registro/conferencias', [
            'titulo' => 'Elije Workshops y Conferencias',
            'eventos' => $eventos_formateados,
            'regalos' => $regalos
 
        ]);
    }

}