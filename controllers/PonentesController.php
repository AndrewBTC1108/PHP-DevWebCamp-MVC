<?php

namespace Controllers;

use Classes\Paginacion;
use Model\Ponente;
use MVC\Router;
use Intervention\Image\ImageManagerStatic as Image;

class PonentesController
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
            header('Location: /admin/ponentes?page=1');
        }

        //Los registros que va a mostrar por pagina
        $registros_por_pagina = 10;
        //Nos va a retornar cuantos registros hay en total en la BD
        $total = Ponente::total();
        // pasamos los valores al constructor
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total);

        if($paginacion->total_paginas() < $pagina_actual) {
            header('Location: /admin/ponentes?page=1');
        }

        //Vamos a llamar a todos los ponentes de la BD
        //Gracias al ofset, se va a saltar los que no requiere y traera solo los que se necesita
        $ponentes = Ponente::paginar($registros_por_pagina, $paginacion->offset());

        $router->render('admin/ponentes/index', [
            'titulo' => 'Ponentes / Conferencistas',
            'ponentes' => $ponentes,
            'paginacion' => $paginacion->paginacion()
        ]);
    }

    public static function crear(Router $router)
    {
        //para proteger el panel de administracion
        if(!is_admin()) {
            header('Location: /login');
        }

        $alertas = [];

        //creamos instancia
        $ponente = new Ponente;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //para proteger el panel de administracion
            if(!is_admin()) {
                header('Location: /login');
            }

            //Leer Imagen
            //Importante para que funcione intervetion image o archivos en el formulario debe tener
            //"enctype="multipart/form-data""
            if (!empty($_FILES['imagen']['tmp_name'])) {
                //Creamos carpeta para las imagenes
                $carpeta_imagenes = '../public/img/speakers';

                //Crear la carpeta si no existe
                if (!is_dir($carpeta_imagenes)) {
                    mkdir($carpeta_imagenes, 0755, true);
                }
                //PNG
                $imagen_png = Image::make($_FILES['imagen']['tmp_name'])->fit(800, 800)->encode('png', 80);

                //webp
                $imagen_webp = Image::make($_FILES['imagen']['tmp_name'])->fit(800, 800)->encode('webp', 80);

                //Nombre imagen
                $nombre_imagen = md5(uniqid(rand(), true));

                //agregamso nombre a la imagen
                $_POST['imagen'] = $nombre_imagen;
            }

            //vamos a pasar a string el arreglo de redes, para que se pueda sanitizar correctamente por active record
            //json_encode toma el objeto y lo convierte en un json y string
            //con json_unescaped_slashes le quitamos a las url los \/ slashes que pueden hacer que falle la url
            $_POST['redes'] = json_encode($_POST['redes'], JSON_UNESCAPED_SLASHES);

            //Sincronisammos
            $ponente->sincronizar($_POST);

            //validar 
            $alertas = $ponente->validar();

            //Guardar el registro
            //validamos que el arreglo de alertas este vacio
            if (empty($alertas)) {
                //Guardar las imagenes
                $imagen_png->save($carpeta_imagenes . '/' . $nombre_imagen . '.png');
                $imagen_webp->save($carpeta_imagenes . '/' . $nombre_imagen . '.webp');

                //Guardar en la base de datos
                $resultado = $ponente->guardar();

                if ($resultado) {
                    header('Location: /admin/ponentes');
                }
            }
        }

        $router->render('admin/ponentes/crear', [
            'titulo' => 'Registrar Ponente',
            'alertas' => $alertas,
            'ponente' => $ponente,
            'redes' => json_decode($ponente->redes)
        ]);
    }

    public static function editar(Router $router)
    {
        //para proteger el panel de administracion
        if(!is_admin()) {
            header('Location: /login');
        }

        $alertas = [];
        //validar el ID
        $id = $_GET['id'];
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if (!$id) {
            header('Location: /admin/ponentes');
        }

        //Obtener Ponente a editar
        $ponente = Ponente::find($id);

        if (!$ponente) {
            header('Location: /admin/ponentes');
        }

        //Creamos una varibale auxiliar
        $ponente->imagen_actual = $ponente->imagen;

        //Guardamos cambios
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //para proteger el panel de administracion
            if(!is_admin()) {
                header('Location: /login');
            }

            if (!empty($_FILES['imagen']['tmp_name'])) {
                //Creamos carpeta para las imagenes
                $carpeta_imagenes = '../public/img/speakers';

                //Crear la carpeta si no existe
                if (!is_dir($carpeta_imagenes)) {
                    mkdir($carpeta_imagenes, 0755, true);
                }
                //PNG
                $imagen_png = Image::make($_FILES['imagen']['tmp_name'])->fit(800, 800)->encode('png', 80);

                //webp
                $imagen_webp = Image::make($_FILES['imagen']['tmp_name'])->fit(800, 800)->encode('webp', 80);

                //Nombre imagen
                $nombre_imagen = md5(uniqid(rand(), true));

                //agregamso nombre a la imagen
                $_POST['imagen'] = $nombre_imagen;
            } else {
                //En caso de que no se haya puesto una imagen
                $_POST['imagen'] = $ponente->imagen_actual;
            }

            $_POST['redes'] = json_encode($_POST['redes'], JSON_UNESCAPED_SLASHES);
            //Sincronisamos el post actual
            $ponente->sincronizar($_POST);

            $alertas = $ponente->validar();

            if (empty($alertas)) {
                if (isset($nombre_imagen)) {
                    //Guardar las imagenes
                    $imagen_png->save($carpeta_imagenes . '/' . $nombre_imagen . '.png');
                    $imagen_webp->save($carpeta_imagenes . '/' . $nombre_imagen . '.webp');
                }
                $resultado = $ponente->guardar();

                if ($resultado) {
                    header('Location: /admin/ponentes');
                }
            }
        }

        //Json decode toma el string y lo convierte en objeto
        $router->render('admin/ponentes/editar', [
            'titulo' => 'Editar Ponente',
            'alertas' => $alertas,
            'ponente' => $ponente,
            'redes' => json_decode($ponente->redes)
        ]);
    }

    public static function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            //para proteger el panel de administracion
            if(!is_admin()) {
                header('Location: /login');
            }

            $id = $_POST['id'];
            $ponente = Ponente::find($id);

            if(!isset($ponente)) {
                header('Location: /admin/ponentes');
            }

            $resultado = $ponente->eliminar();
            
            if($resultado) {
                header('Location: /admin/ponentes'); 
            }
        }
    }
}
