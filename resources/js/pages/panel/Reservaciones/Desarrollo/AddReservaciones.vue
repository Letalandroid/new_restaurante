<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Nueva reservación" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #end>
            <ConfigTiempoReserva/>  
        </template>
    </Toolbar>

    <Dialog v-model:visible="reservaDialog" :style="{ width: '85%', maxWidth: '700px' }" header="Registro de reservación" :modal="true">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Campo de Cliente -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Cliente <span class="text-red-500">*</span></label>
                    <div class="flex flex-col gap-2">
                        <!-- Campo de búsqueda -->
                        <InputText
                            v-model="clienteBusqueda"
                            placeholder="Ingrese el nombre del cliente"
                            @input="buscarCliente"
                            :disabled="!!reserva.customer_id"
                            fluid
                            class="w-full"
                        />

                        <!-- Sugerencias -->
                        <ul v-if="clientesFiltrados.length > 0" class="border rounded-md bg-white shadow-md max-h-40 overflow-y-auto">
                            <li 
                                v-for="cliente in clientesFiltrados" 
                                :key="cliente.id"
                                @click="seleccionarCliente(cliente)"
                                class="px-3 py-2 cursor-pointer hover:bg-gray-100"
                            >
                                {{ cliente.name }} {{ cliente.lastname }} 
                                <small class="text-gray-500">({{ cliente.email }})</small>
                            </li>
                        </ul>

                        <!-- Cliente seleccionado -->
                        <div v-if="reserva.customer_id" class="mt-2 p-3 bg-gray-50 rounded-md border flex justify-between items-center">
                            <div>
                                <p class="font-semibold">{{ clienteSeleccionado?.name }} {{ clienteSeleccionado?.lastname }}</p>
                                <p class="text-sm text-gray-600">{{ clienteSeleccionado?.email }} - {{ clienteSeleccionado?.phone }}</p>
                            </div>
                            <Button 
                                icon="pi pi-times" 
                                severity="danger" 
                                text 
                                rounded 
                                aria-label="Quitar cliente"
                                @click="quitarCliente"
                            />
                        </div>
                    </div>

                    <small v-if="submitted && !reserva.customer_id" class="text-red-500">El cliente es obligatorio.</small>
                    <small v-if="serverErrors.customer_id" class="text-red-500">{{ serverErrors.customer_id[0] }}</small>
                </div>

                <!-- Campo de Número de personas -->
                <div class="col-span-12 sm:col-span-6">
                    <label class="block font-bold mb-2">Número de personas <span class="text-red-500">*</span></label>
                    <InputNumber
                        v-model="reserva.number_people"
                        required
                        placeholder="Ingrese el número de personas"
                        :min="1"
                        :max="50"
                        fluid
                        class="w-full"
                    />
                    <small v-if="submitted && !reserva.number_people" class="text-red-500">El número de personas es obligatorio.</small>
                    <small v-if="serverErrors.number_people" class="text-red-500">{{ serverErrors.number_people[0] }}</small>
                </div>

                <!-- Campo de Fecha -->
                <div class="col-span-12 sm:col-span-6">
                    <label class="block font-bold mb-2">Fecha <span class="text-red-500">*</span></label>
                    <Calendar
                        v-model="reserva.date"
                        required
                        placeholder="Seleccione la fecha"
                        dateFormat="dd-mm-yy"
                        :minDate="minDate"
                        fluid
                        class="w-full"
                    />
                    <small v-if="submitted && !reserva.date" class="text-red-500">La fecha es obligatoria.</small>
                    <small v-if="serverErrors.date" class="text-red-500">{{ serverErrors.date[0] }}</small>
                </div>

                <!-- Campo de Hora -->
                <div class="col-span-12 sm:col-span-6">
                    <label class="block font-bold mb-2">Hora <span class="text-red-500">*</span></label>
                    <Calendar
                        v-model="reserva.hour"
                        required
                        placeholder="Seleccione la hora"
                        timeOnly
                        hourFormat="24"
                        fluid
                        class="w-full"
                    />
                    <small v-if="submitted && !reserva.hour" class="text-red-500">La hora es obligatoria.</small>
                    <small v-if="serverErrors.hour" class="text-red-500">{{ serverErrors.hour[0] }}</small>
                </div>

                <!-- Campo de Estado -->
                <div class="col-span-12 sm:col-span-6">
                    <label class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                        <Checkbox v-model="reserva.state" :binary="true" />
                        <Tag :value="reserva.state ? 'Activo' : 'Inactivo'" :severity="reserva.state ? 'success' : 'danger'" />
                    </div>
                    <small v-if="serverErrors.state" class="text-red-500">{{ serverErrors.state[0] }}</small>
                </div>

                <!-- Código de reservación (generado automáticamente, solo lectura) -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Código de reservación</label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <InputText
                            v-model="reserva.reservation_code"
                            readonly
                            fluid
                            placeholder="Haga clic en generar código"
                            class="bg-gray-100 w-full"
                            disabled
                        />
                        <Button 
                            label="Generar código" 
                            icon="pi pi-refresh" 
                            severity="secondary" 
                            @click="generarCodigoReserva" 
                            :disabled="!reserva.customer_id || !reserva.date || !reserva.hour"
                            class="w-full sm:w-auto"
                        />
                    </div>
                    <div class="mt-1 space-y-1">
                        <small v-if="!reserva.reservation_code" class="text-orange-500 block">Debe generar un código antes de guardar</small>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarReserva" :disabled="!reserva.reservation_code" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Calendar from 'primevue/calendar';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import ConfigTiempoReserva from './ConfigTiempoReserva.vue';

interface Reserva {
    customer_id: number | null;
    number_people: number | null;
    date: Date | null;
    hour: Date | null;
    state: boolean;
    reservation_code: string;
}

interface Cliente {
    id: number;
    name: string;
    lastname: string;
    email: string;
    phone: string;
    codigo: string;
    client_type_id: number;
    state: boolean;
    fullName?: string;
}

interface ServerErrors {
    [key: string]: string[];
}

const toast = useToast();
const submitted = ref(false);
const reservaDialog = ref(false);
const serverErrors = ref<ServerErrors>({});
const clientes = ref<Cliente[]>([]);
const minDate = ref(new Date());
const clienteBusqueda = ref('');
const clientesFiltrados = ref<Cliente[]>([]);
const clienteSeleccionado = ref<Cliente | null>(null);

const reserva = ref<Reserva>({
    customer_id: null,
    number_people: null,
    date: null,
    hour: null,
    state: true,
    reservation_code: ''
});

function resetReserva(): void {
    reserva.value = {
        customer_id: null,
        number_people: null,
        date: null,
        hour: null,
        state: true,
        reservation_code: ''
    };
    serverErrors.value = {};
    submitted.value = false;

    //Limpieza del campo cliente y sus auxiliares
    clienteBusqueda.value = '';
    clientesFiltrados.value = [];
    clienteSeleccionado.value = null;
}

function openNew(): void {
    resetReserva();
    reservaDialog.value = true;
    fetchClientes();
}

function hideDialog(): void {
    reservaDialog.value = false;
    resetReserva();
}

function quitarCliente() {
    reserva.value.customer_id = null;
    clienteSeleccionado.value = null;
    clienteBusqueda.value = '';
    clientesFiltrados.value = [];
}

function fetchClientes(): void {
    axios.get('/cliente', { params: { state: 1 } })
        .then(res => {
            // Agregar fullName a cada cliente para mostrar en el dropdown
            clientes.value = res.data.data.map((cliente: Cliente) => ({
                ...cliente,
                fullName: `${cliente.name} ${cliente.lastname}`
            }));
        })
        .catch(() => {
            toast.add({ 
                severity: 'error', 
                summary: 'Error', 
                detail: 'No se pudo cargar la lista de clientes', 
                life: 3000 
            });
        });
}

// Buscar cliente por nombre en el array de clientes cargado
function buscarCliente() {
    const termino = clienteBusqueda.value.toLowerCase();
    if (!termino) {
        clientesFiltrados.value = [];
        return;
    }

    clientesFiltrados.value = clientes.value.filter(cliente =>
        `${cliente.name} ${cliente.lastname}`.toLowerCase().includes(termino)
    );
}
// Cuando se selecciona un cliente de la lista
function seleccionarCliente(cliente: Cliente) {
    reserva.value.customer_id = cliente.id;
    clienteSeleccionado.value = cliente;
    clienteBusqueda.value = `${cliente.name} ${cliente.lastname}`;
    clientesFiltrados.value = [];
}

function generarCodigoReserva(): void {
    const caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    let codigo = '';
    for (let i = 0; i < 6; i++) {
        codigo += caracteres[Math.floor(Math.random() * caracteres.length)];
    }
    reserva.value.reservation_code = codigo;
    toast.add({
        severity: 'info',
        summary: 'Código generado',
        detail: `Código: ${codigo}`,
        life: 2000
    });
}

// Generar código automáticamente cuando se llenen los campos principales
watch([() => reserva.value.customer_id, () => reserva.value.date, () => reserva.value.hour], 
([customerId, date, hour]) => {
    if (customerId && date && hour && !reserva.value.reservation_code) {
        generarCodigoReserva();
    }
});

function guardarReserva(): void {
    submitted.value = true;
    serverErrors.value = {};

    // Validar campos requeridos
    if (!reserva.value.customer_id || !reserva.value.number_people || !reserva.value.date || !reserva.value.hour) {
        return;
    }

    // Validar que se haya generado el código
    if (!reserva.value.reservation_code) {
        toast.add({
            severity: 'warn',
            summary: 'Advertencia',
            detail: 'Debe generar un código de reservación primero',
            life: 3000
        });
        return;
    }

    // Formatear fecha y hora para el backend
    const reservaData = {
        customer_id: reserva.value.customer_id,
        number_people: reserva.value.number_people,
        date: formatDateForBackend(reserva.value.date),
        hour: formatTimeForBackend(reserva.value.hour),
        state: reserva.value.state,
        reservation_code: reserva.value.reservation_code
    };

    axios.post('/reservacion', reservaData)
        .then(() => {
            toast.add({ 
                severity: 'success', 
                summary: 'Éxito', 
                detail: 'Reservación registrada correctamente', 
                life: 3000 
            });
            hideDialog();
            emit('reserva-agregada');
        })
        .catch(error => {
            if (error.response && error.response.status === 422) {
                serverErrors.value = error.response.data.errors || {};
                // Si hay error con el código, generar uno nuevo
                if (serverErrors.value.reservation_code) {
                    generarCodigoReserva();
                }
            } else {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'No se pudo registrar la reservación',
                    life: 3000
                });
            }
        });
}

function formatDateForBackend(date: Date | null): string {
    if (!date) return '';
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatTimeForBackend(time: Date | null): string {
    if (!time) return '';
    const hours = String(time.getHours()).padStart(2, '0');
    const minutes = String(time.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
}

const emit = defineEmits(['reserva-agregada']);

onMounted(() => {
    fetchClientes();
});
</script>