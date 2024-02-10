(function () {
    const ponentesInput = document.querySelector('#ponentes');

    //validamso que haya unponetesInput
    if (ponentesInput) {
        let ponentes = [];
        let ponentesFiltrados = [];

        const listadoPonentes = document.querySelector('#listado-ponentes'); //este es un UL para luego añadir los li
        const inputHiddenPonente = document.querySelector('[name="ponente_id"]');
        obtenerPonentes();

        //un input es cuando se hace en un formulario algun evento de escribir, o borrar lo que es comun en formularios
        ponentesInput.addEventListener('input', buscarPonentes);

        //al editar
        if(inputHiddenPonente.value) {
            (async() => {
                //lalamos funcion de ponente
                const ponente = await obtenerPonente(inputHiddenPonente.value);
                //destructuring
                const { nombre, apellido } = ponente;
                
                //insertar en el HTML
                const ponenteDom = document.createElement('LI');//creamos un li
                ponenteDom.classList.add('listado-ponentes__ponente', 'listado-ponentes__ponente--seleccionado');
                ponenteDom.textContent = `${nombre} ${apellido}`;

                //inyectamos al HTML
                listadoPonentes.appendChild(ponenteDom);
            })();
        }

        //hacemos busqueda en la API
        async function obtenerPonentes() {
            const url = `/api/ponentes`;
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();
            formatearPonentes(resultado);
        }

        //busueda de un ponente por su id
        async function obtenerPonente(id) {
            const url = `/api/ponente?id=${id}`;
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();
            return resultado; //nos retorna ese ponente
        }

        //Formateamos los ponentes por default el array estara vacio
        function formatearPonentes(arrayPonentes = []) {
            //.map(), crea un arreglo nuevo sin necesidad de mutar el original
            ponentes = arrayPonentes.map(ponente => {
                //nos retornara un objeto
                return {
                    nombre: `${ponente.nombre.trim()} ${ponente.apellido.trim()}`,
                    id: ponente.id
                }
            });
        }

        function buscarPonentes(e) {
            //variable de busqueda
            const busqueda = e.target.value;

            //validamos que lo que estemos digitando se mayor a 3 caracteres para que comience la busqueda
            if (busqueda.length >= 3) {
                //expresion regular buscar un patron en un valor, cuando esta en -1 significa que no encontro nada
                //expresion regular para que busque sin importar si es miniscula o mayuscula
                const expresion = new RegExp(busqueda.normalize('NFD').replace(/[\u0300-\u036f]/g, ""), "i");
                ponentesFiltrados = ponentes.filter(ponente => {
                    if (ponente.nombre.normalize('NFD').replace(/[\u0300-\u036f]/g, "").toLowerCase().search(expresion) != -1) {
                        return ponente;
                    }
                });
            } else {
                //para limpiar el arreglo y asi limpiar mostrarPonetes();
                ponentesFiltrados = [];
            }

            mostrarPonentes();
        }

        function mostrarPonentes() {
            //para limpiar el html
            while(listadoPonentes.firstChild) {
                listadoPonentes.removeChild(listadoPonentes.firstChild);
            }

            if(ponentesFiltrados.length > 0) {
                //vamos a iterar en el array de ponentes fitrados, va a iterar en cada uno de los elementos del array si los hay, para mostrarlos en el HTML
                ponentesFiltrados.forEach( ponente => {
                    const ponenteHTML = document.createElement('LI');
                    ponenteHTML.classList.add('listado-ponentes__ponente');
                    ponenteHTML.textContent = ponente.nombre;
                    ponenteHTML.dataset.ponenteId = ponente.id;
                    ponenteHTML.onclick = seleccionarPonente;
    
                    //añadir al DOM
                    listadoPonentes.appendChild(ponenteHTML);
                });
            } else {
                //validamos que lo que hayamos escrito en el input sea mayor o igual a 3
                if(ponentesInput.value.length >=3 ) {
                    const noResultados = document.createElement('P');
                    noResultados.classList.add('listado-ponentes__no-resultado');
                    noResultados.textContent = 'No hay resultados para tu búsqueda'
                    listadoPonentes.appendChild(noResultados);
                }
            }

        }

        function seleccionarPonente(e) {
            const ponente = e.target;

            //remover la clase previa
            const ponentePrevio = document.querySelector('.listado-ponentes__ponente--seleccionado');

            //validamos por que puede ser que la primera vez no este seleccionado
            if(ponentePrevio) {
                ponentePrevio.classList.remove('listado-ponentes__ponente--seleccionado');
            }

            ponente.classList.add('listado-ponentes__ponente--seleccionado');
            inputHiddenPonente.value = ponente.dataset.ponenteId;

        }

    }
})();