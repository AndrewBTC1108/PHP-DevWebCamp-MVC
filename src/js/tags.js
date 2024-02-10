(function(){
    const tagsInput = document.querySelector('#tags_input');

    if(tagsInput) {

        //seleccionamos el div donde vamos a colocar las tags
        const tagsDiv = document.querySelector('#tags');
        const tagsInputHidden = document.querySelector('[name="tags"]');
        //arreglo de etiquetas
        let tags = [];

        //Recuperar del input oculto cuando estemos editando el ponente
        if(tagsInputHidden !== '') {
            //.split() devuelve un arreglo con una divison sea de , o .
            tags = tagsInputHidden.value.split(',');
            mostrarTags();
        }
        
        //escucha los cambios en el input
        tagsInput.addEventListener('keypress', guardarTag);

        function guardarTag(e) {
            //al presionar una , se agrega al arreglo tags lo que se haya escrito
            if(e.keyCode === 44) {

                if(e.target.value.trim() === '' || e.target.value < 1) {
                    //paramos el codigo
                    return;
                }
                //para prevenir la accionde por default del formulario
                e.preventDefault();
                //.trim() quita espaciosen blanco
                tags = [...tags, e.target.value.trim()]

                //vamos a limpiar el campo donde escribimos
                tagsInput.value = '';
                
                //vamos a mostrar las etiquetas en el HTML
                mostrarTags();
            }
        }

        function mostrarTags() {
            tagsDiv.textContent = '';
            //Vamso a iterar en el arreglo de tags
            tags.forEach(tag => {
                const etiqueta = document.createElement('LI');
                etiqueta.classList.add('formulario__tag');
                //agregamos tag a LI
                etiqueta.textContent = tag;
                //agregamos evento de doble click a la etiqueta y le asociamos una una funcion
                etiqueta.ondblclick = eliminarTag
                //agregamos etiqueta al div de tagsDiv
                tagsDiv.appendChild(etiqueta);
            });

            actualizarInpuntHidden();
        }

        function eliminarTag(e) {
            //quitamos del HTML lo que le dimos click
            e.target.remove();

            //nos va a filtrar todas las tags menos a la que le hemos dado click
            //y nos retornara un nuevo arreglo en cual no vamos a insertar a tags
            tags = tags.filter(tag => tag !== e.target.textContent);
            //vamos a refrescar para que se borren del value
            actualizarInpuntHidden();
        }

        function actualizarInpuntHidden() {
            //convertimos el arreglo tags en string para poder insertarlo al input hidden, al [name="tags"]
            tagsInputHidden.value = tags.toString();
        }
    }
})()