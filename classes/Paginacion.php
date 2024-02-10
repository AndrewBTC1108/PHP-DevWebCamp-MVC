<?php

namespace Classes;

class Paginacion
{
    public $pagina_actual;
    public $registros_por_pagina;
    public $total_registros;

    public function __construct($pagina_actual = 1, $registros_por_pagina = 10, $total_registros = 0)
    {
        //casteamos valores
        $this->pagina_actual = (int) $pagina_actual;
        $this->registros_por_pagina = (int) $registros_por_pagina;
        $this->total_registros = (int) $total_registros;
    }

    public function offset()
    {
        // //...calcula cuántos registros debes saltar antes de comenzar a mostrar resultados.
        //  Por ejemplo, si estás en la página 3 y estás mostrando 10 registros por página,
        //   entonces deberías saltarte los primeros 20 registros (10 registros/página * (3 - 1 páginas)) antes de empezar a mostrar resultados en la página 3.
        return $this->registros_por_pagina * ($this->pagina_actual - 1);
    }

    public function total_paginas()
    {
        //ceil() redondea hacia arriba
        return ceil($this->total_registros / $this->registros_por_pagina);
    }

    public function pagina_anterior()
    {
        //nos va a devolver a la pagina anterior
        $anterior = $this->pagina_actual - 1;
        //quiere decir que si estamos en la pg 1 ya no podria devolverse mas
        return ($anterior > 0) ? $anterior : false;
    }

    public function pagina_siguiente()
    {
        //vamos incrementando de 1
        $siguiente = $this->pagina_actual + 1;
        return ($siguiente <= $this->total_paginas()) ? $siguiente : false;
    }

    //va a imprimir el HTML
    public function enlace_anterior()
    {
        $html = '';

        if ($this->pagina_anterior()) {
            $html .= "<a class=\"paginacion__enlace paginacion__enlace--texto\" href=\"?page={$this->pagina_anterior()}\">&laquo; Anterior </a>";
        }
        return $html;
    }

    public function enlace_siguiente()
    {
        $html = '';

        if ($this->pagina_siguiente()) {
            $html .= "<a class=\"paginacion__enlace paginacion__enlace--texto\" href=\"?page={$this->pagina_siguiente()}\">Siguiente &raquo;</a>";
        }
        return $html;
    }

    public function numeros_paginas()
    {
        $html = '';
        for($i = 1; $i <= $this->total_paginas(); $i++) {
            if($i === $this->pagina_actual ) {
                $html .= "<span class=\"paginacion__enlace paginacion__enlace--actual \">{$i}</span>";
            } else {
                $html .= "<a class=\"paginacion__enlace paginacion__enlace--numero \" href=\"?page={$i} \">{$i}</a>"; 
            }
        }
        return $html;
    }

    public function paginacion()
    {
        $html = '';

        if ($this->total_registros > 1) {
            $html .= '<div class="paginacion">';
            $html .= $this->enlace_anterior();
            $html .= $this->numeros_paginas();
            $html .= $this->enlace_siguiente();
            $html .= '</div>';
        }

        return $html;
    }
}
