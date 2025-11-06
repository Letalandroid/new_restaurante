<template>
    <Dialog v-model:visible="dialogVisible" header="Editar Tipo de Empleado" modal :closable="true" :closeOnEscape="true"
        :style="{ width: '90%', maxWidth: '700px' }">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Nombre -->
                <div class="col-span-6">
                    <label for="name" class="block font-bold mb-3">Nombre <span class="text-red-500">*</span></label>
                    <InputText
                        id="name"
                        v-model.trim="tipoEmpleado.name"
                        required
                        maxlength="100"
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.name }"
                    />
                    <small v-if="serverErrors.name" class="p-error">{{ serverErrors.name[0] }}</small>
                </div>

                <!-- Estado -->
                <div class="col-span-3">
                    <label for="state" class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="tipoEmpleado.state" :binary="true" inputId="state" />
                        <Tag :value="tipoEmpleado.state ? 'Activo' : 'Inactivo'" :severity="tipoEmpleado.state ? 'success' : 'danger'" />
                        <small v-if="serverErrors.state" class="p-error">{{ serverErrors.state[0] }}</small>
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
                    />
                    <small v-if="serverErrors.payment_type" class="p-error">{{ serverErrors.payment_type[0] }}</small>
                </div>

                <!-- Sueldo base -->
                <div class="col-span-6">
                    <label for="base_salary" class="block font-bold mb-2">Sueldo base</label>
                    <InputText v-model.number="tipoEmpleado.base_salary" type="number" step="0.01" />
                    <small v-if="serverErrors.base_salary" class="p-error">{{ serverErrors.base_salary[0] }}</small>
                </div>

                <!-- Costo por hora -->
                <div class="col-span-6">
                    <label for="hourly_rate" class="block font-bold mb-2">Costo por hora</label>
                    <InputText v-model.number="tipoEmpleado.hourly_rate" type="number" step="0.01" />
                    <small v-if="serverErrors.hourly_rate" class="p-error">{{ serverErrors.hourly_rate[0] }}</small>
                </div>

                <!-- Bonificación puntualidad -->
                <div class="col-span-6 flex items-center gap-3 mt-3">
                    <Checkbox v-model="tipoEmpleado.has_punctuality_bonus" :binary="true" inputId="has_punctuality_bonus" />
                    <label for="has_punctuality_bonus">Tiene bonificación de puntualidad</label>
                </div>

                <div class="col-span-6">
                    <label for="punctuality_bonus" class="block font-bold mb-2">Monto bonificación</label>
                    <InputText v-model.number="tipoEmpleado.punctuality_bonus" type="number" step="0.01" :disabled="!tipoEmpleado.has_punctuality_bonus" />
                    <small v-if="serverErrors.punctuality_bonus" class="p-error">{{ serverErrors.punctuality_bonus[0] }}</small>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="updateTipoEmpleado" :loading="loading" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';

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

const props = defineProps<{
    visible: boolean;
    tipoEmpleadoId: number | null;
}>();
const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

const dialogVisible = ref(props.visible);
const tipoEmpleado = ref<TipoEmpleado>({
    name: '',
    state: false,
    payment_type: null,
    base_salary: null,
    hourly_rate: null,
    has_punctuality_bonus: false,
    punctuality_bonus: null
});
const serverErrors = ref<ServerErrors>({});
const loading = ref(false);
const toast = useToast();
const paymentOptions = ref([
    { label: 'Fijo', value: 'fijo' },
    { label: 'Por hora', value: 'por_hora' },
]);

watch(() => props.visible, val => dialogVisible.value = val);
watch(dialogVisible, val => emit('update:visible', val));

watch(() => props.visible, async val => {
    if (val && props.tipoEmpleadoId) {
        await fetchTipoEmpleado();
    }
});

const fetchTipoEmpleado = async (): Promise<void> => {
    if (!props.tipoEmpleadoId) return;
    loading.value = true;
    try {
        const response = await axios.get(`/tipo_empleado/${props.tipoEmpleadoId}`);
        const data = response.data.employeeType;
        tipoEmpleado.value = {
            name: data.name,
            state: data.state,
            payment_type: data.payment_type,
            base_salary: data.base_salary,
            hourly_rate: data.hourly_rate,
            has_punctuality_bonus: data.has_punctuality_bonus,
            punctuality_bonus: data.punctuality_bonus
        };
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar el tipo de empleado', life: 3000 });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

function hideDialog(): void {
    dialogVisible.value = false;
    serverErrors.value = {};
}

const updateTipoEmpleado = async (): Promise<void> => {
    serverErrors.value = {};
    if (!props.tipoEmpleadoId) return;

    loading.value = true;
    try {
        await axios.put(`/tipo_empleado/${props.tipoEmpleadoId}`, tipoEmpleado.value);
        toast.add({ severity: 'success', summary: 'Actualizado', detail: 'Tipo de empleado actualizado correctamente', life: 3000 });
        dialogVisible.value = false;
        emit('updated');
    } catch (error: any) {
        const axiosError = error as AxiosError;
        if (axiosError.response?.status === 422 && axiosError.response.data) {
            serverErrors.value = (axiosError.response.data as any).errors || {};
            toast.add({ severity: 'error', summary: 'Error de validación', detail: 'Revisa los campos e intenta nuevamente.', life: 5000 });
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo actualizar el tipo de empleado', life: 3000 });
        }
        console.error(error);
    } finally {
        loading.value = false;
    }
};
</script>
