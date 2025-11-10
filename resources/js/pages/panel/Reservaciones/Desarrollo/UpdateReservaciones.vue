<script setup lang="ts">
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
//import Dropdown from 'primevue/dropdown';

interface Reservacion {
    id?: number;
    customer_id: number | null;
    number_people: number | null;
    date: Date | null;
    hour: Date | null;
    state: boolean;
    reservation_code: string;
    customer?: {
        id: number;
        name: string;
        lastname: string;
        email: string;
        phone: string;
        state: boolean;
    };
    Cliente_name?: string;
}

interface Cliente {
    id: number;
    name: string;
    lastname: string;
    email: string;
    phone: string;
    state: boolean;
    fullName?: string;
}

interface ServerErrors {
    [key: string]: string[];
}

const props = defineProps<{
    visible: boolean;
    reservacionId: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

const toast = useToast();
const dialogVisible = ref<boolean>(props.visible);
const loading = ref<boolean>(false);
const submitted = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});
const clientes = ref<Cliente[]>([]);
const minDate = ref(new Date());

const reservacion = ref<Reservacion>({
    customer_id: null,
    number_people: null,
    date: null,
    hour: null,
    state: true,
    reservation_code: ''
});

watch(() => props.visible, (val) => {
    dialogVisible.value = val;
    if (val && props.reservacionId) {
        fetchReservacion();
        fetchClientes();
    }
});

watch(dialogVisible, (val) => emit('update:visible', val));

const fetchReservacion = async (): Promise<void> => {
    try {
        loading.value = true;
        const res = await axios.get(`/reservacion/${props.reservacionId}`);
        const data = res.data.reservation;
        
        // Convertir fecha del formato dd-mm-yyyy a Date object
        const [day, month, year] = data.date.split('-');
        // Crear fecha en formato yyyy-mm-dd para Date constructor
        const dateObj = new Date(parseInt(year), parseInt(month) - 1, parseInt(day));
        
        // Convertir la hora del formato HH:MM a Date object
        const [hours, minutes] = data.hour.split(':');
        const timeObj = new Date();
        timeObj.setHours(parseInt(hours), parseInt(minutes), 0, 0);
        
        reservacion.value = {
            id: data.id,
            customer_id: data.customer_id,
            number_people: data.number_people,
            date: dateObj,
            hour: timeObj,
            state: data.state,
            reservation_code: data.reservation_code,
            customer: data.customer,
            Cliente_name: data.Cliente_name
        };
        
        console.log('Fecha original del API:', data.date);
        console.log('Fecha convertida:', dateObj);
    } catch (error: any) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar la reservación', life: 3000 });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const fetchClientes = async (): Promise<void> => {
    try {
        const res = await axios.get('/cliente', { params: { state: 1 } });
        // Agregar fullName a cada cliente para mostrar en el dropdown
        clientes.value = res.data.data.map((cliente: Cliente) => ({
            ...cliente,
            fullName: `${cliente.name} ${cliente.lastname}`
        }));
    } catch (error: any) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar la lista de clientes', life: 3000 });
        console.error(error);
    }
};

// Función para obtener el nombre completo del cliente seleccionado
function getClienteNombre(customerId: number | null): string {
    if (customerId === null) return ''; // si no hay cliente
    const cliente = clientes.value.find(c => c.id === customerId);
    return cliente ? `${cliente.name} ${cliente.lastname}` : '';
}

const updateReservacion = async (): Promise<void> => {
    submitted.value = true;
    serverErrors.value = {};

    // Validación de campos requeridos
    if (!reservacion.value.number_people || !reservacion.value.date || !reservacion.value.hour) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Todos los campos son obligatorios', life: 3000 });
        return;
    }

    try {
        const reservacionData = {
            number_people: reservacion.value.number_people,
            date: formatDateForBackend(reservacion.value.date),
            hour: formatTimeForBackend(reservacion.value.hour),
            state: reservacion.value.state
        };

        await axios.put(`/reservacion/${props.reservacionId}`, reservacionData);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Reservación actualizada correctamente y correo enviado con los datos actualizados.', // ← Cambiar este mensaje
            life: 3000
        });

        dialogVisible.value = false;
        emit('updated');
    } catch (error: any) {
        if (error.response && error.response.data?.errors) {
            serverErrors.value = error.response.data.errors;
            toast.add({
                severity: 'error',
                summary: 'Error de validación',
                detail: 'Revisa los campos e intenta nuevamente.',
                life: 5000
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo actualizar la reservación',
                life: 3000
            });
        }
        console.error(error);
    }
};

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
</script>

<template>
    <Dialog 
        v-model:visible="dialogVisible" 
        header="Editar Reservación" 
        modal 
        :closable="true" 
        :style="{ width: '85%', maxWidth: '700px' }"
    >
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Campo de Cliente (solo lectura) -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Cliente</label>
                    <InputText
                        :value="getClienteNombre(reservacion.customer_id)"
                        readonly
                        disabled
                        class="w-full bg-gray-100"
                        placeholder="Cliente de la reservación"
                    />
                    <small class="text-gray-500">El cliente no se puede modificar</small>
                </div>

                <!-- Campo de Número de personas -->
                <div class="col-span-12 sm:col-span-6">
                    <label class="block font-bold mb-2">Número de personas <span class="text-red-500">*</span></label>
                    <InputNumber
                        v-model="reservacion.number_people"
                        required
                        placeholder="Ingrese el número de personas"
                        :min="1"
                        :max="50"
                        fluid
                        :class="{ 'p-invalid': serverErrors.number_people }"
                        class="w-full"
                    />
                    <small v-if="serverErrors.number_people" class="text-red-500">{{ serverErrors.number_people[0] }}</small>
                </div>

                <!-- Campo de Fecha -->
                <div class="col-span-12 sm:col-span-6">
                    <label class="block font-bold mb-2">Fecha <span class="text-red-500">*</span></label>
                    <Calendar
                        v-model="reservacion.date"
                        required
                        placeholder="Seleccione la fecha"
                        dateFormat="dd-mm-yy"
                        :minDate="minDate"
                        fluid
                        :class="{ 'p-invalid': serverErrors.date }"
                        class="w-full"
                    />
                    <small v-if="serverErrors.date" class="text-red-500">{{ serverErrors.date[0] }}</small>
                </div>

                <!-- Campo de Hora -->
                <div class="col-span-12 sm:col-span-6">
                    <label class="block font-bold mb-2">Hora <span class="text-red-500">*</span></label>
                    <Calendar
                        v-model="reservacion.hour"
                        required
                        placeholder="Seleccione la hora"
                        timeOnly
                        hourFormat="24"
                        fluid
                        :class="{ 'p-invalid': serverErrors.hour }"
                        class="w-full"
                    />
                    <small v-if="serverErrors.hour" class="text-red-500">{{ serverErrors.hour[0] }}</small>
                </div>

                <!-- Campo de Estado -->
                <div class="col-span-12 sm:col-span-6">
                    <label class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                        <Checkbox v-model="reservacion.state" :binary="true" />
                        <Tag :value="reservacion.state ? 'Activo' : 'Inactivo'" :severity="reservacion.state ? 'success' : 'danger'" />
                    </div>
                    <small v-if="serverErrors.state" class="text-red-500">{{ serverErrors.state[0] }}</small>
                    <small v-if="!reservacion.state" class="text-orange-500 block mt-1">
                        Al desactivar la reservación, el cliente también se desactivará
                    </small>
                </div>

                <!-- Código de reservación (solo lectura) -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Código de reservación</label>
                    <InputText
                        v-model="reservacion.reservation_code"
                        readonly
                        fluid
                        class="bg-gray-100 w-full"
                        disabled
                    />
                    <small class="text-gray-500">Código único de la reservación (no modificable)</small>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateReservacion" :loading="loading" />
        </template>
    </Dialog>
</template>