
// Script para redirigir solo la notificación de registro exitoso, sin especialidad ni prompts


document.addEventListener('DOMContentLoaded', function() {
    const formCrearUsuario = document.getElementById('form-crear-usuario');
    if (!formCrearUsuario) return;
    formCrearUsuario.addEventListener('submit', function(e) {
        setTimeout(function() {
            alert('¡Registro exitoso!');
        }, 100);
    });
});


