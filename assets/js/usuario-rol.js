// Script para redirigir o mostrar especialidad según el rol seleccionado en el modal de crear usuario

document.addEventListener('DOMContentLoaded', function() {
    const rolSelect = document.getElementById('rol_create');
    if (!rolSelect) return;

    rolSelect.addEventListener('change', function() {
        const selected = this.value;
        if (selected === 'conductor') {
            // Redirigir a gestión de conductores
            window.location.href = 'cond.php?fromUserCreate=1';
        } else if (selected === 'tecnico') {
            // Mostrar ventana emergente para especialidad
            showEspecialidadModal();
        } else if (selected === 'admin') {
            // Ocultar todo menos gestión de usuarios (puedes personalizar esto)
            alert('El usuario administrador solo tendrá acceso a la gestión de usuarios.');
        }
    });
});

function showEspecialidadModal() {
    // Puedes personalizar este modal según tus necesidades
    const especialidad = prompt('Ingrese la especialidad del técnico:');
    if (especialidad) {
        // Aquí podrías guardar la especialidad en un campo oculto o enviarla al backend
        alert('Especialidad registrada: ' + especialidad + '\nEl técnico podrá ser asignado automáticamente a la orden de trabajo según la alerta.');
    }
}
