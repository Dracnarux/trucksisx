// Sistema de Alertas de Llantas para Camión Doble Troque
class TruckAlertSystem {
    // Cargar conductores y vehículos en el modal
    async loadConductoresVehiculos() {
        const select = document.getElementById('conductor-vehiculo-select');
        if (!select) return;
        select.innerHTML = '<option value="">Cargando...</option>';
        try {
            const response = await fetch('../controllers/ConductorVehiculoController.php');
            const data = await response.json();
            if (data.success && Array.isArray(data.conductores)) {
                if (data.conductores.length === 0) {
                    select.innerHTML = '<option value="">No hay conductores disponibles</option>';
                } else {
                    select.innerHTML = '<option value="">Seleccione un conductor y vehículo...</option>';
                    data.conductores.forEach(c => {
                        const label = `${c.nombre} ${c.apellido} - ${c.placa}`;
                        const option = document.createElement('option');
                        option.value = `${c.cond_id}|${c.vehiculo_id}`;
                        option.textContent = label;
                        select.appendChild(option);
                    });
                }
            } else {
                select.innerHTML = '<option value="">Error al cargar conductores</option>';
            }
        } catch (e) {
            select.innerHTML = '<option value="">Error de conexión</option>';
        }

        // Actualizar campos ocultos al seleccionar
        select.addEventListener('change', function() {
            const [condId, vehicId] = this.value.split('|');
            document.getElementById('cond-id').value = condId || '';
            document.getElementById('vehicle-id').value = vehicId || '';
        });
    }
    // Métodos vacíos para evitar errores si no están implementados
    createLegend() {}
    onTireHover() {}
    onTireLeave() {}
    // Manejar click en una llanta
    onTireClick(e) {
        const tire = e.target;
        const position = tire.getAttribute('data-position');
        const name = this.tirePositions[position] || position;
        this.selectedTire = { position, name };
        this.showAlertModal();
    }
    // Método vacío para evitar error JS y permitir funcionamiento del sistema
    displayAlertsList(alerts) {
        // Aquí puedes mostrar la lista de alertas si lo deseas
    }
    // Método vacío para evitar error JS y permitir funcionamiento del diagrama
    updateTireDiagram() {
        // Aquí puedes actualizar el estado visual de las llantas según this.alerts si lo deseas
    }

    // Método vacío para evitar error JS y permitir funcionamiento del diagrama
    setupEventListeners() {
        // Aquí puedes agregar listeners globales si lo deseas
    }
    constructor() {
        this.currentVehicleId = null;
        this.alertModal = null;
        this.selectedTire = null;
        this.alerts = [];
        this.tirePositions = {
            'direccion_izquierda': 'Dirección Izquierda',
            'direccion_derecha': 'Dirección Derecha',
            'traccion1_izquierda': 'Tracción 1 - Izquierda',
            'traccion1_derecha': 'Tracción 1 - Derecha',
            'traccion1_izquierda2': 'Tracción 1 - Izquierda 2',
            'traccion1_derecha2': 'Tracción 1 - Derecha 2',
            'traccion2_izquierda': 'Tracción 2 - Izquierda',
            'traccion2_derecha': 'Tracción 2 - Derecha'
        };
        
        this.init();
    }

    init() {
        this.createModal();
        this.loadTireAlerts();
        this.setupEventListeners();
        this.createTruckDiagram();
    }

    // Crear el diagrama SVG del camión doble troque
    createTruckDiagram() {
        const container = document.getElementById('truck-diagram-container');
        if (!container) return;
        container.innerHTML = '';

        // SVG vertical, solo llantas y líneas
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('class', 'truck-diagram');
        svg.setAttribute('viewBox', '0 0 200 600');
        svg.setAttribute('width', '200');
        svg.setAttribute('height', '600');

        // Coordenadas verticales para cada eje
        const yDireccion = 80;
        const yTraccion1 = 220;
        const yTraccion2 = 360;
        const yTraccion3 = 500;

        // Llantas dirección (frontal)
        const tires = [
            {cx: 70, cy: yDireccion, pos: 'direccion_izquierda', label: 'DIR-I'},
            {cx: 130, cy: yDireccion, pos: 'direccion_derecha', label: 'DIR-D'},
            // Tracción 1 (dos llantas separadas al extremo izquierdo, dos separadas al extremo derecho)
            {cx: 35, cy: yTraccion1, pos: 'traccion1_izquierda', label: 'T1-I'},
            {cx: 55, cy: yTraccion1, pos: 'traccion1_izquierda2', label: 'T1-I2'},
            {cx: 145, cy: yTraccion1, pos: 'traccion1_derecha2', label: 'T1-D2'},
            {cx: 165, cy: yTraccion1, pos: 'traccion1_derecha', label: 'T1-D'},
            // Tracción 3 (dos llantas separadas al extremo izquierdo, dos separadas al extremo derecho)
            {cx: 35, cy: yTraccion3, pos: 'traccion3_izquierda', label: 'T3-I'},
            {cx: 55, cy: yTraccion3, pos: 'traccion3_izquierda2', label: 'T3-I2'},
            {cx: 145, cy: yTraccion3, pos: 'traccion3_derecha2', label: 'T3-D2'},
            {cx: 165, cy: yTraccion3, pos: 'traccion3_derecha', label: 'T3-D'}
        ];

        // Dibujar llantas y etiquetas
        tires.forEach(t => {
            const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            circle.setAttribute('cx', t.cx);
            circle.setAttribute('cy', t.cy);
            circle.setAttribute('r', 25);
            circle.setAttribute('class', 'tire');
            circle.setAttribute('data-position', t.pos);
            circle.setAttribute('fill', '#333');
            svg.appendChild(circle);

            const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            text.setAttribute('x', t.cx);
            text.setAttribute('y', t.cy + 40);
            text.setAttribute('class', 'tire-label');
            text.setAttribute('text-anchor', 'middle');
            text.textContent = t.label;
            svg.appendChild(text);
        });

        // Unir llantas de cada eje con dos líneas horizontales (izquierda y derecha)
        [yDireccion, yTraccion1, yTraccion3].forEach(y => {
            // Línea izquierda (vertical central a llanta izquierda más externa)
            const lineLeft = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            lineLeft.setAttribute('x1', 100);
            lineLeft.setAttribute('x2', 40);
            lineLeft.setAttribute('y1', y);
            lineLeft.setAttribute('y2', y);
            lineLeft.setAttribute('stroke', '#888');
            lineLeft.setAttribute('stroke-width', 4);
            svg.appendChild(lineLeft);
            // Línea derecha (vertical central a llanta derecha más externa)
            const lineRight = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            lineRight.setAttribute('x1', 100);
            lineRight.setAttribute('x2', 160);
            lineRight.setAttribute('y1', y);
            lineRight.setAttribute('y2', y);
            lineRight.setAttribute('stroke', '#888');
            lineRight.setAttribute('stroke-width', 4);
            svg.appendChild(lineRight);
        });

    // Ya no hay tracción 2, así que no se dibuja línea de unión

        // Línea vertical central
        const vLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        vLine.setAttribute('x1', 100);
        vLine.setAttribute('x2', 100);
        vLine.setAttribute('y1', yDireccion);
        vLine.setAttribute('y2', yTraccion3);
        vLine.setAttribute('stroke', '#888');
        vLine.setAttribute('stroke-width', 4);
        svg.appendChild(vLine);

        // Listeners
        svg.querySelectorAll('.tire').forEach(tire => {
            tire.addEventListener('click', (e) => this.onTireClick(e));
            tire.addEventListener('mouseenter', (e) => this.onTireHover(e));
            tire.addEventListener('mouseleave', (e) => this.onTireLeave(e));
        });

        container.appendChild(svg);
        this.createLegend(container);
    }

    hideTireTooltip() {
        const tooltip = document.getElementById('tire-tooltip');
        if (tooltip) {
            tooltip.style.display = 'none';
        }
    }

    // Crear modal para registro de alertas
    createModal() {
    // Eliminar cualquier modal anterior para evitar duplicados
    const oldModal = document.getElementById('alert-modal');
    if (oldModal) oldModal.remove();

    const modalHTML = `
            <div id="alert-modal" class="alert-modal">
                <div class="alert-modal-content">
                    <div class="alert-modal-header">
                        <h3 class="alert-modal-title">Registrar Alerta de Llanta</h3>
                        <span class="close">&times;</span>
                    </div>
                    <form id="alert-form">
                        <div class="form-group">
                            <label for="tire-position-display">Posición de la Llanta:</label>
                            <input type="text" id="tire-position-display" readonly>
                                        <input type="hidden" id="tire-position" name="posicion_llanta">
                                    </div>
                                    <div class="form-group">
                                        <label for="conductor-vehiculo-select">Conductor y Vehículo Asignado *</label>
                                        <select id="conductor-vehiculo-select" name="conductor_vehiculo" class="form-select" required>
                                            <option value="">Seleccione un conductor y vehículo...</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="alert-priority">Prioridad:</label>
                                        <select id="alert-priority" name="prioridad">
                                            <option value="baja">Baja</option>
                                            <option value="media" selected>Media</option>
                                            <option value="alta">Alta</option>
                                            <option value="critica">Crítica</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="alert-description">Descripción del Problema *:</label>
                                        <textarea id="alert-description" name="descripcion" required 
                                                  placeholder="Describa el problema observado en la llanta"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="alert-image">Imagen de Evidencia:</label>
                                        <input type="file" id="alert-image" name="imagen_evidencia" 
                                               accept="image/*">
                                    </div>
                                    <div class="form-group">
                                        <label for="alert-observations">Observaciones Adicionales:</label>
                                        <textarea id="alert-observations" name="observaciones" 
                                                  placeholder="Observaciones adicionales o contexto"></textarea>
                                    </div>
                                    <input type="hidden" id="cond-id" name="cond_id">
                                    <input type="hidden" id="vehicle-id" name="regis_vehic_id">
                                    <div style="text-align: right; margin-top: 20px;">
                                        <button type="button" class="btn btn-secondary" onclick="truckAlerts.closeModal()">
                                            Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <span class="btn-text">Crear Alerta</span>
                                            <span class="loading" style="display: none;"></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    `;
        // Llenar el selector de conductores y vehículos al abrir el modal
    setTimeout(() => { this.loadConductoresVehiculos(); }, 100);

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.alertModal = document.getElementById('alert-modal');
        this.setupModalEvents();
    }

    // Configurar eventos del modal
    setupModalEvents() {
        // Cerrar modal
        const closeBtn = this.alertModal.querySelector('.close');
        closeBtn.onclick = () => this.closeModal();

        // Cerrar al hacer click fuera del modal
        window.onclick = (event) => {
            if (event.target === this.alertModal) {
                this.closeModal();
            }
        };

    // No hay campo conductor-code, así que no se agrega validación aquí

        // Envío del formulario
        const form = document.getElementById('alert-form');
        form.addEventListener('submit', (e) => this.submitAlert(e));
    }

    // Mostrar modal de alerta
    showAlertModal() {
        if (!this.selectedTire) return;

        // Siempre recargar el selector de conductores y vehículos al abrir el modal
        this.loadConductoresVehiculos();

        const positionDisplay = document.getElementById('tire-position-display');
        const positionInput = document.getElementById('tire-position');

        positionDisplay.value = this.selectedTire.name;
        positionInput.value = this.selectedTire.position;

        this.alertModal.style.display = 'block';

        // Focus en el selector de conductor y vehículo
        setTimeout(() => {
            const select = document.getElementById('conductor-vehiculo-select');
            if (select) select.focus();
        }, 100);
    }

    // Cerrar modal
    closeModal() {
        this.alertModal.style.display = 'none';
        this.resetForm();
    }

    // Resetear formulario
    resetForm() {
    const form = document.getElementById('alert-form');
    form.reset();
    const validation = document.getElementById('conductor-validation');
    if (validation) validation.innerHTML = '';
    this.selectedTire = null;
    }

    // Validar código de conductor
    async validateConductor() {
        const conductorCode = document.getElementById('conductor-code').value.trim();
        const validation = document.getElementById('conductor-validation');
        
        if (!conductorCode) {
            validation.innerHTML = '';
            return;
        }

        try {
            const response = await fetch('../controllers/AlertController.php?action=validateConductor', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `codigo_conductor=${encodeURIComponent(conductorCode)}`
            });

            const data = await response.json();
            
            if (data.success) {
                validation.innerHTML = `<span style="color: green;">✓ Conductor válido: ${data.conductor.nombre} ${data.conductor.apellido}</span>`;
                return true;
            } else {
                validation.innerHTML = `<span style="color: red;">✗ ${data.message}</span>`;
                return false;
            }
        } catch (error) {
            validation.innerHTML = '<span style="color: red;">Error al validar conductor</span>';
            return false;
        }
    }

    // Enviar alerta
    async submitAlert(event) {
        event.preventDefault();
        
        const submitBtn = event.target.querySelector('button[type="submit"], #alert-create-btn');
        let btnText = null, loading = null;
        if (submitBtn) {
            btnText = submitBtn.querySelector('.btn-text');
            loading = submitBtn.querySelector('.loading');
            // Mostrar loading
            if (btnText) btnText.style.display = 'none';
            if (loading) loading.style.display = 'inline-block';
            submitBtn.disabled = true;
        }

        // Validar conductor antes de enviar
        const isValidConductor = await this.validateConductor();
        if (!isValidConductor) {
            this.resetSubmitButton(submitBtn, btnText, loading);
            return;
        }

        try {
            const formData = new FormData(event.target);
            
            const response = await fetch('../controllers/AlertController.php?action=create', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Alerta creada exitosamente y orden de trabajo generada', 'success');
                this.closeModal();
                this.loadTireAlerts(); // Recargar alertas
                this.updateTireDiagram(); // Actualizar diagrama
            } else {
                this.showNotification(data.message || 'Error al crear la alerta', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.showNotification('Error de conexión al crear la alerta', 'error');
        }

        if (submitBtn && btnText && loading) {
            this.resetSubmitButton(submitBtn, btnText, loading);
        }
    }

    resetSubmitButton(submitBtn, btnText, loading) {
        btnText.style.display = 'inline';
        loading.style.display = 'none';
        submitBtn.disabled = false;
    }

    // Cargar alertas de llantas
    async loadTireAlerts() {
        try {
            const vehicleParam = this.currentVehicleId ? `?vehicle_id=${this.currentVehicleId}` : '';
            const response = await fetch(`../controllers/AlertController.php?action=getTireAlerts${vehicleParam}`);
            const data = await response.json();
            
            if (data.success) {
                this.alerts = data.alerts;
                this.updateTireDiagram();
                this.displayAlertsList(data.alerts);
            }
        } catch (error) {
            console.error('Error loading tire alerts:', error);
        }
    }

    // Crear modal para registro de alertas
    createModal() {
        const modalHTML = `
            <div id="alert-modal" class="alert-modal">
                <div class="alert-modal-content">
                    <div class="alert-modal-header">
                        <h3 class="alert-modal-title">Registrar Alerta de Llanta</h3>
                        <span class="close">&times;</span>
                    </div>
                    <form id="alert-form">
                        <div class="form-group">
                            <label for="tire-position-display">Posición de la Llanta:</label>
                            <input type="text" id="tire-position-display" readonly>
                            <input type="hidden" id="tire-position" name="posicion_llanta">
                        </div>
                        <div class="form-group">
                            <label for="conductor-vehiculo-select">Conductor y Vehículo Asignado *</label>
                            <select id="conductor-vehiculo-select" name="conductor_vehiculo" class="form-select" required>
                                <option value="">Seleccione un conductor y vehículo...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="alert-priority">Prioridad:</label>
                            <select id="alert-priority" name="prioridad">
                                <option value="baja">Baja</option>
                                <option value="media" selected>Media</option>
                                <option value="alta">Alta</option>
                                <option value="critica">Crítica</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="alert-description">Descripción del Problema *:</label>
                            <textarea id="alert-description" name="descripcion" required 
                                      placeholder="Describa el problema observado en la llanta"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="alert-image">Imagen de Evidencia:</label>
                            <input type="file" id="alert-image" name="imagen_evidencia" 
                                   accept="image/*">
                        </div>
                        <div class="form-group">
                            <label for="alert-observations">Observaciones Adicionales:</label>
                            <textarea id="alert-observations" name="observaciones" 
                                      placeholder="Observaciones adicionales o contexto"></textarea>
                        </div>
                        <input type="hidden" id="cond-id" name="cond_id">
                        <input type="hidden" id="vehicle-id" name="regis_vehic_id">
                        <div style="text-align: right; margin-top: 20px;">
                            <button type="button" class="btn btn-secondary" onclick="truckAlerts.closeModal()">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <span class="btn-text">Crear Alerta</span>
                                <span class="loading" style="display: none;"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.alertModal = document.getElementById('alert-modal');
        this.setupModalEvents();
    }

    // Mostrar notificación
    showNotification(message, type = 'info') {
        // Crear o usar notificación existente
        let notification = document.getElementById('notification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'notification';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 5px;
                color: white;
                font-weight: bold;
                z-index: 2000;
                transition: all 0.3s ease;
            `;
            document.body.appendChild(notification);
        }

        // Colores según tipo
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };

        notification.style.backgroundColor = colors[type] || colors.info;
        notification.textContent = message;
        notification.style.display = 'block';

        // Ocultar después de 5 segundos
        setTimeout(() => {
            notification.style.display = 'none';
        }, 5000);
    }

    // Formatear fecha
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
}

// Inicializar el sistema cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.truckAlerts = new TruckAlertSystem();
});