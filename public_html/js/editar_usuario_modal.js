$(document).ready(function() {
    
    const formularioEditar = $('#editarUsuarioForm'); 
    const eliminarButtonModal = $('#eliminarUsuarioModal'); 
    const editUserModal = $('#editUserModal');     
    function mostrarMensajeModal(mensaje, esError = true) {
        if (esError) {
            alert('Error: ' + mensaje);
            console.error(mensaje);
        } else {
            alert('Éxito: ' + mensaje);
            console.log(mensaje);
        }
    }    
    function closeModal() {
        const modalElement = document.getElementById('editUserModal');
        const bsModal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
        bsModal.hide();
    }    
    function limpiarFormularioModal() {
        formularioEditar[0].reset();
        
        $('#id_usuario_editar').val('');
        $('#dni_usuario_editar').val('');
        $('#nombres_usuario_editar').val('');
        $('#apellidos_usuario_editar').val('');
        $('#correo_usuario_editar').val('');
        $('#telefono_usuario_editar').val('');
        $('#direccion_usuario_editar').val('');
        $('#rol_usuario_editar').val('empleado'); 
        $('#estado_usuario_editar').val('1'); 
        
        $('#creado_por_usuario_display').text('');
        $('#fecha_creacion_usuario_display').text('');
        $('#modificado_por_usuario_display').text('');
        $('#fecha_modificacion_usuario_display').text('');
    }    
    
    editUserModal.on('show.bs.modal', function (event) {
        
        const button = $(event.relatedTarget); 
        const userId = button.data('id');         if (userId) {
            $.ajax({
                url: '../controller/controlador_usuarios.php', 
                method: 'GET',
                dataType: 'json',
                data: {
                    accion: 'obtener_usuario', 
                    id_usuario: userId
                },
                success: function(respuesta) {
                    if (respuesta.status === 'success' && respuesta.data) {
                        const usuario = respuesta.data;
                        
                        $('#id_usuario_editar').val(usuario.idUsuarios);
                        $('#dni_usuario_editar').val(usuario.dni);
                        $('#nombres_usuario_editar').val(usuario.nombres);
                        $('#apellidos_usuario_editar').val(usuario.apellidos);
                        $('#correo_usuario_editar').val(usuario.correo);
                        $('#telefono_usuario_editar').val(usuario.telefono);
                        $('#direccion_usuario_editar').val(usuario.direccion);
                        $('#rol_usuario_editar').val(usuario.rol);
                        $('#estado_usuario_editar').val(usuario.estado);                        
                        const creadoPorTexto = (usuario.creado_por_nombre || '') + ' ' + (usuario.creado_por_apellido || '') + (usuario.creado_por ? ' (ID: ' + usuario.creado_por + ')' : '');
                        $('#creado_por_usuario_display').text(creadoPorTexto.trim() || 'N/A');
                        $('#fecha_creacion_usuario_display').text(usuario.fecha_creacion || 'N/A');                        const modificadoPorTexto = (usuario.modificado_por_nombre || '') + ' ' + (usuario.modificado_por_apellido || '') + (usuario.modificado_por ? ' (ID: ' + usuario.modificado_por + ')' : '');
                        $('#modificado_por_usuario_display').text(modificadoPorTexto.trim() || 'N/A');
                        $('#fecha_modificacion_usuario_display').text(usuario.fecha_modificacion || 'N/A');
                        
                    } else {
                        mostrarMensajeModal(respuesta.message || 'Error al obtener los datos del usuario para edición.');
                        closeModal(); 
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al obtener usuario para editar (desde modal):", error, xhr.responseText);
                    mostrarMensajeModal('Error al comunicarse con el servidor para obtener datos del usuario.');
                    closeModal(); 
                }
            });
        } else {
            mostrarMensajeModal('ID de usuario no proporcionado para edición.', true);
            closeModal();
        }
    });
    
    formularioEditar.on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize(); 
        const urlControlador = $(this).data('url'); 
        if (!urlControlador) {
            mostrarMensajeModal('URL del controlador no definida en el formulario.', true);
            return;
        }        $.ajax({
            url: urlControlador,
            method: 'POST',
            dataType: 'json',
            data: formData, 
            success: function(respuesta) {
                if (respuesta.status === 'success') {
                    mostrarMensajeModal('Usuario actualizado correctamente.', false);
                    closeModal(); 
                    
                    
                    if (typeof window.listar_usuarios === 'function' && typeof window.CONTROLADOR_USUARIOS_URL !== 'undefined') {
                        window.listar_usuarios(window.CONTROLADOR_USUARIOS_URL);
                    } else {
                        console.error("No se pudo recargar la lista de usuarios. Asegúrate de que 'listar_usuarios' y 'CONTROLADOR_USUARIOS_URL' sean globales.");
                    }
                } else {
                    mostrarMensajeModal(respuesta.message || 'Error al actualizar el usuario.');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al actualizar usuario:", error, xhr.responseText);
                mostrarMensajeModal('Error al comunicarse con el servidor.');
            }
        });
    });    
    eliminarButtonModal.on('click', function() {
        const idUsuario = $('#id_usuario_editar').val(); 
        if (!idUsuario) {
            mostrarMensajeModal('No hay un usuario seleccionado para eliminar.', true);
            return;
        }        if (confirm('¿Estás seguro de que deseas eliminar este usuario? Esta acción es irreversible.')) {
            const urlControlador = formularioEditar.data('url'); 
            if (!urlControlador) {
                mostrarMensajeModal('URL del controlador no definida.', true);
                return;
            }            $.ajax({
                url: urlControlador,
                method: 'POST',
                dataType: 'json',
                data: {
                    accion: 'eliminar_usuario',
                    id_usuario: idUsuario
                },
                success: function(respuesta) {
                    if (respuesta.status === 'success') {
                        mostrarMensajeModal('Usuario eliminado correctamente.', false);
                        closeModal(); 
                        
                        
                        if (typeof window.listar_usuarios === 'function' && typeof window.CONTROLADOR_USUARIOS_URL !== 'undefined') {
                            window.listar_usuarios(window.CONTROLADOR_USUARIOS_URL);
                        } else {
                            console.error("No se pudo recargar la lista de usuarios. Asegúrate de que 'listar_usuarios' y 'CONTROLADOR_USUARIOS_URL' sean globales.");
                        }
                    } else {
                        mostrarMensajeModal(respuesta.message || 'Error al eliminar el usuario.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error al eliminar usuario desde modal:", error, xhr.responseText);
                    mostrarMensajeModal('Error al comunicarse con el servidor al eliminar.');
                }
            });
        }
    });    
    $('#editUserModal').on('hidden.bs.modal', function () {
        limpiarFormularioModal();
    });
});
