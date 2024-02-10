import Swal from 'sweetalert2';
(function() {
    //Donde vamos ainyectar los registros en el HTML
    const resumen = document.querySelector('#registro-resumen');
    let eventos = [];

    if(resumen) {
        const eventosBoton = document.querySelectorAll('.evento__agregar');
        eventosBoton.forEach(boton => boton.addEventListener('click', seleccionarEvento));

        //seleccionamos el boton de registro
        const formularioRegistro = document.querySelector('#registro');
        formularioRegistro.addEventListener('submit', submitFormulario);

        mostrarEventos();

        //le pasamos el evento, lo que le hemos dado click
        function seleccionarEvento(e) {
            //validar el limite de evntos que se pueden agregar
            if(eventos.length < 5) {
                //Deshabilitar el evento
                e.target.disabled = true;
                eventos = [...eventos, {
                    id: e.target.dataset.id,
                    titulo: e.target.parentElement.querySelector('.evento__nombre').textContent.trim()
                }]

                mostrarEventos();
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'Maximo 5 eventos por registro',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    })
                }

        }

        function mostrarEventos() {
            //Limpiar el html
            limpiarEventos();

            //un poco de scripting
            if(eventos.length > 0) {
                eventos.forEach( evento => {
                    const eventoDOM = document.createElement('DIV');
                    eventoDOM.classList.add('registro__evento');

                    const titulo = document.createElement('H3');
                    titulo.classList.add('registro__nombre');
                    titulo.textContent = evento.titulo;

                    const botonEliminar = document.createElement('BUTTON');
                    botonEliminar.classList.add('registro__eliminar');
                    botonEliminar.innerHTML = `<i class="fa-solid fa-trash"></i>`;
                    botonEliminar.onclick = function() {
                        eliminarEvento(evento.id)
                    }

                    //renderizar en el html
                    eventoDOM.appendChild(titulo);
                    eventoDOM.appendChild(botonEliminar);
                    resumen.appendChild(eventoDOM);
                })
            }else {
                const noRegistro = document.createElement('P');
                noRegistro.textContent = 'No hay eventos, añade hasta 5 del lado izquierdo';
                noRegistro.classList.add('registro__texto');
                resumen.appendChild(noRegistro);
            }
        }

        function eliminarEvento(id) {
            //nos va a traer todos los eventos que sean diferentes al del id que le dimos click
            eventos = eventos.filter(evento => evento.id !== id);
            const botonAgregar = document.querySelector(`[data-id="${id}"]`);
            botonAgregar.disabled = false;
            mostrarEventos();
        }
        function limpiarEventos() {
            while(resumen.firstChild) {
                resumen.removeChild(resumen.firstChild);
            }
        }
       async function submitFormulario(e) {
            //prevenir accion de por default
            e.preventDefault();

            //obtener el regalo
            const regaloId = document.querySelector('#regalo').value;

            //vamos a extraer del arreglo eventos los id qeu tiene cada elemento, y los almacenara 
            //en un nuevo arreglo
            const eventosId = eventos.map(evento => evento.id);


            //validmaos que se hayan elejido bien las conferencias o workshops y el regalo

            if(eventosId.length === 0 || regaloId.length === '') {
                Swal.fire({
                    title: 'Error',
                    text: 'Elije almenos un regalo y un evento',
                    icon: 'error',
                    confirmButtonText: 'OK'
                })
                //para que no se ejecuten las siguientes lineas
                return;
            }
            //objeto de formdata
            const datos = new FormData();
            //vamos a enviar los eventos y el regalo
            datos.append('eventos', eventosId);
            datos.append('regalo_id', regaloId);
            //Comunicarnos con el back, llamar url
            const url = '/finalizar-registro/conferencias';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });
            const resultado = await respuesta.json();
            
            if(resultado.resultado) {
                Swal.fire(
                    'Registro Exitoso',
                    'Tus conferencias se han almacenado, y tu registro fue exitoso, te esperamos en DevWebCamp',
                    'success'
                ).then(() => location.href = `/boleto?id=${resultado.token}`)
            } else {
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un error',
                    icon: 'error',
                    confirmButtonText: 'OK'
                }).then(() => location.reload())
            }
        }
    }
})();