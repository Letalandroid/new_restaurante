<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Nuevo tipo de empleado" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #end>
            <ToolsEmployeeType @import-success="loadTipoEmpleado"/>
        </template>
    </Toolbar>

    <Dialog v-model:visible="tipoEmpleadoDialog" :style="{ width: '90%', maxWidth: '700px' }" header="Registro de tipo de empleado" :modal="true">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Nombre -->
                <div class="col-span-6">
                    <label for="name" class="block font-bold mb-3">Nombre <span class="text-red-500">*</span></label>
                    <InputText id="name" v-model.trim="tipoEmpleado.name" required maxlength="100" fluid />
                    <small v-if="submitted && !tipoEmpleado.name" class="text-red-500">El nombre es obligatorio.</small>
                    <small v-else-if="submitted && tipoEmpleado.name && tipoEmpleado.name.length < 2" class="text-red-500">
                        El nombre debe tener al menos 2 caracteres.
                    </small>
                    <small v-else-if="serverErrors.name" class="text-red-500">{{ serverErrors.name[0] }}</small>
                </div>

                <!-- Estado -->
                <div class="col-span-3">
                    <label for="state" class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="tipoEmpleado.state" :binary="true" inputId="state" />
                        <Tag :value="tipoEmpleado.state ? 'Activo' : 'Inactivo'" :severity="tipoEmpleado.state ? 'success' : 'danger'" />
                        <small v-if="submitted && tipoEmpleado.state === null" class="text-red-500">El estado es obligatorio.</small>
                        <small v-else-if="serverErrors.state" class="text-red-500">{{ serverErrors.state[0] }}</small>
                    </div>
                </div>

                <!-- Tipo de pago -->
                <div class="col-span-3">
                    <label for="payment_type" class="block font-bold mb-2">Tipo de pago <span class="text-red-500">*</span></label>
<Select 
    v-model="tipoEmpleado.payment_type" 
    :options="paymentOptions" 
    optionLabel="label" 
    optionValue="value" 
    placeholder="Seleccionar tipo"
    multiple
/>
                    <small v-if="submitted && !tipoEmpleado.payment_type" class="text-red-500">El tipo de pago es obligatorio.</small>
                </div>

                <!-- Sueldo base -->
                <div class="col-span-6">
                    <label for="base_salary" class="block font-bold mb-2">Sueldo base</label>
                    <InputText v-model.number="tipoEmpleado.base_salary" type="number" step="0.01" />
                </div>

                <!-- Costo por hora -->
                <div class="col-span-6">
                    <label for="hourly_rate" class="block font-bold mb-2">Costo por hora</label>
                    <InputText v-model.number="tipoEmpleado.hourly_rate" type="number" step="0.01" />
                </div>

                <!-- Bonificación puntualidad -->
                <div class="col-span-6 flex items-center gap-3 mt-3">
                    <Checkbox v-model="tipoEmpleado.has_punctuality_bonus" :binary="true" inputId="has_punctuality_bonus" />
                    <label for="has_punctuality_bonus">Tiene bonificación de puntualidad</label>
                </div>

                <div class="col-span-6">
                    <label for="punctuality_bonus" class="block font-bold mb-2">Monto bonificación</label>
                    <InputText v-model.number="tipoEmpleado.punctuality_bonus" type="number" step="0.01" :disabled="!tipoEmpleado.has_punctuality_bonus" />
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarTipoEmpleado" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';
import ToolsEmployeeType from './toolsEmployeeType.vue';

interface TipoEmpleado {
    name: string;
    state: boolean;
    payment_type: string | null;
    base_salary: number | null;
    hourly_rate: number | null;
    has_punctuality_bonus: boolean;
    punctuality_bonus: number | null;
}

interface ServerErrors {
    [key: string]: string[];
}

const toast = useToast();
const submitted = ref(false);
const tipoEmpleadoDialog = ref(false);
const serverErrors = ref<ServerErrors>({});
const tipoEmpleado = ref<TipoEmpleado>({
    name: '',
    state: true,
    payment_type: null,
    base_salary: null,
    hourly_rate: null,
    has_punctuality_bonus: false,
    punctuality_bonus: null
});

const paymentOptions = ref([
    { label: 'Fijo', value: 'fijo' },
    { label: 'Por hora', value: 'por_hora' },
]);

const emit = defineEmits<{
    (e: 'tipo-empleado-agregado'): void
}>();

const loadTipoEmpleado = async (): Promise<void> => {
    try {
        const response = await axios.get('/tipos_empleados'); 
        console.log(response.data);
        emit('tipo-empleado-agregado');
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar los tipos de empleado', life: 3000 });
        console.error(error);
    }
}

function resetTipoEmpleado(): void {
    tipoEmpleado.value = {
        name: '',
        state: true,
        payment_type: null,
        base_salary: null,
        hourly_rate: null,
        has_punctuality_bonus: false,
        punctuality_bonus: null
    };
    serverErrors.value = {};
    submitted.value = false;
}

function openNew(): void {
    resetTipoEmpleado();
    tipoEmpleadoDialog.value = true;
}

function hideDialog(): void {
    tipoEmpleadoDialog.value = false;
    resetTipoEmpleado();
}

function guardarTipoEmpleado(): void {
    submitted.value = true;
    serverErrors.value = {};

    axios.post('/tipo_empleado', tipoEmpleado.value)
        .then(() => {
            toast.add({ severity: 'success', summary: 'Éxito', detail: 'Tipo de empleado registrado', life: 3000 });
            hideDialog();
            emit('tipo-empleado-agregado');
        })
        .catch((error: AxiosError) => {
            if (error.response && error.response.status === 422) {
                serverErrors.value = (error.response.data as any).errors || {};
            } else {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'No se pudo registrar el tipo de empleado',
                    life: 3000
                });
            }
        });
}
</script>
