//IFE para que no se mezcle con las variables de otros archivos
(function () {
    const horas = document.querySelector('#horas');

    if (horas) {
        const categoria = document.querySelector('[name="categoria_id"]');
        const dias = document.querySelectorAll('[name="dia"]');
        const inputHiddenDia = document.querySelector('[name="dia_id"]');
        const inputHiddenHora = document.querySelector('[name="hora_id"]');

        categoria.addEventListener('change', terminoBusqueda);
        dias.forEach(dia => dia.addEventListener('change', terminoBusqueda))//Vamos a iterar en cada uno de ellos, para agregar un evento

        //para recuperar la categoria y el dia(si estamos editando) hacemos una validacion, colocamos la categoria_id y el dia si ya lo teniamos antes
        //caso contrario si es primer vez solo se mantrendra en vacio hasta ser asignada(signo mas+ para poner en entero)
        let busqueda = {
            categoria_id: +categoria.value || '',
            dia: +inputHiddenDia.value || ''
        }
        // console.log(busqueda);
        //SI alguno de los campos del objeto no esta vacio va a deneter el codigo para no hacer la consulta
        if (!Object.values(busqueda).includes('')) {
            //funcion anonima para que funcione correctamente
            (async() => {
                await buscarEventos(); //Como si tienen algo ya podemos andar a llamar la funcion para buscar los disponibles
                const id = inputHiddenHora.value;
                //Resaltar la hora actual
                const horaSeleccionada = document.querySelector(`[data-hora-id="${id}"]`);
                horaSeleccionada.classList.remove('horas__hora--deshabilitada');
                horaSeleccionada.classList.add('horas__hora--seleccionada');

                //le agegamos nuevamente un evento de click para que se pueda seleccionar otra hora diferente
                horaSeleccionada.onclick = horaSeleccionada;
            })()
        }

        function terminoBusqueda(e) {
            //llenar el objeto busqueda 
            busqueda[e.target.name] = e.target.value;
            console.log(busqueda);

            //reinciar los campos ocultos y el selector de hora y DIa
            inputHiddenHora.value = '';
            inputHiddenDia.value = '';

            //deshabilitar la hora previa si hay un nuevo click
            const horaPrevia = document.querySelector('.horas__hora--seleccionada');
            //Se valida que exista la clase .horas__hora--seleccionada
            if(horaPrevia) {
                horaPrevia.classList.remove('horas__hora--seleccionada');
            }

            //SI alguno de los campos del objeto esta vacio va a deneter el codigo para no hacer la consulta
            if (Object.values(busqueda).includes('')) {
                return;
            }

            buscarEventos();
        }

        //Funcion para consultarla API
        async function buscarEventos() {
            //Destructuring
            const { dia, categoria_id } = busqueda;
            const url = `/api/eventos-horario?dia_id=${dia}&categoria_id=${categoria_id}`;
            const resultado = await fetch(url);
            const eventos = await resultado.json();
            //le pasamos como parametro el evento del API
            obtenerHorasDisponibles(eventos);
        }

        function obtenerHorasDisponibles(eventos) {
            //Reinciar las horas, vamos a colocar la clase de horas__hora--deshabilitada a todos los selectores
            //para luego ir quitandola dependiendo si estan libres
            const listadoHoras = document.querySelectorAll('#horas li');
            listadoHoras.forEach( li => li.classList.add('horas__hora--deshabilitada') );


            //comprobar eventos ya tomados, y quitar la variable de deshabilitado
            //va a separar en otro arreglo las horas ya tomadas
            const horasTomadas = eventos.map( evento => evento.hora_id )
            //pasamos de NodeLits listadoHoras, a un array para poder usar .filter()
            const listadoHorasArray = Array.from(listadoHoras); 

            //nos va a dar un nuevo arreglo con todas las horas dispobibles de ese evento
            //nos va a retornar todos los resultados que no tengan horas tomasdas y que tengan el dataset
            const resultado = listadoHorasArray.filter(li => !horasTomadas.includes(li.dataset.horaId));

            resultado.forEach(li => li.classList.remove('horas__hora--deshabilitada'));

            //para aplicar la clase de seleccionar hora a las que estan disponibles
            const horasDisponibles = document.querySelectorAll('#horas li:not(.horas__hora--deshabilitada)');
            horasDisponibles.forEach( hora => hora.addEventListener('click' , seleccionarHora) );
        }

        function seleccionarHora(e) {
            //deshabilitar la hora previa si hay un nuevo click
            const horaPrevia = document.querySelector('.horas__hora--seleccionada');
            //Se valida que exista la clase .horas__hora--seleccionada
            if(horaPrevia) {
                horaPrevia.classList.remove('horas__hora--seleccionada');
            }
            //agregar clase de seleccionado
            e.target.classList.add('horas__hora--seleccionada');

            //Asignamos el data id de hora al inputHiddenHora en value=
            inputHiddenHora.value = e.target.dataset.horaId;

            //llenar el campo oculto de dia, le ponemos el que este checked
            inputHiddenDia.value = document.querySelector('[name="dia"]:checked').value;

        }
    }
})();