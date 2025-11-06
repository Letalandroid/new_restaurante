<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Checkbox from 'primevue/checkbox';
import { useToast } from 'primevue/usetoast';

interface Holiday {
    name: string;
    date: string | Date | null;
    is_recurring: boolean;
}

interface ServerErrors {
    [key: string]: string[];
}

const toast = useToast();
const loading = ref<boolean>(false);
const submitted = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});
const dialogVisible = ref<boolean>(false);

const props = defineProps<{
    visible: boolean;
    holidayId: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'holiday-actualizado'): void;
}>();

const holiday = ref<Holiday>({
    name: '',
    date: null,
    is_recurring: false,
});

watch(() => props.visible, (val) => (dialogVisible.value = val));
watch(dialogVisible, (val) => emit('update:visible', val));

// 🧠 Cargar datos del feriado existente
async function fetchHoliday() {
    if (!props.holidayId) return;
    loading.value = true;

    try {
        const response = await axios.get(`/asistencias/feriado/${props.holidayId}`);
const data = response.data.holiday;

holiday.value = {
    id: data.id,
    name: data.name,
    date: data.date,
    is_recurring: data.is_recurring,
};

    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar el feriado',
            life: 3000,
        });
        console.error(error);
    } finally {
        loading.value = false;
    }
}


watch(
  () => [props.visible, props.holidayId],
  ([visible, id]) => {
    if (visible && id) fetchHoliday();
  },
  { immediate: true }
);

// 📝 Actualizar feriado
async function updateHoliday() {
    submitted.value = true;
    serverErrors.value = {};

    if (!holiday.value.name || !holiday.value.date) {
        toast.add({
            severity: 'warn',
            summary: 'Campos obligatorios',
            detail: 'Completa los campos requeridos.',
            life: 3000,
        });
        return;
    }

    try {
        await axios.put(`/asistencias/feriado/${holiday.value.id ?? props.holidayId}`, {
    id: holiday.value.id ?? props.holidayId, // 👈 Se envía también en el body
    name: holiday.value.name,
    date: holiday.value.date,
    is_recurring: holiday.value.is_recurring,
});


        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Feriado actualizado correctamente',
            life: 3000,
        });

        dialogVisible.value = false;
emit('updated');
    } catch (error) {
        const err = error as AxiosError;
        if (err.response && err.response.status === 422) {
            serverErrors.value = (err.response.data as any).errors || {};
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo actualizar el feriado',
                life: 3000,
            });
        }
    }
}
</script>

<template>
    <Dialog
        v-model:visible="dialogVisible"
        :style="{ width: '600px' }"
        header="Editar feriado"
        :modal="true"
    >
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Nombre -->
                <div class="col-span-8">
                    <label for="name" class="block font-bold mb-3">
                        Nombre del feriado <span class="text-red-500">*</span>
                    </label>
                    <InputText id="name" v-model.trim="holiday.name" required maxlength="100" fluid />
                    <small v-if="submitted && !holiday.name" class="text-red-500">El nombre es obligatorio.</small>
                    <small v-else-if="serverErrors.name" class="text-red-500">{{ serverErrors.name[0] }}</small>
                </div>

                <!-- Fecha -->
                <div class="col-span-4">
                    <label for="date" class="block font-bold mb-3">
                        Fecha <span class="text-red-500">*</span>
                    </label>
                    <Calendar id="date" v-model="holiday.date" dateFormat="yy-mm-dd" showIcon />
                    <small v-if="submitted && !holiday.date" class="text-red-500">La fecha es obligatoria.</small>
                    <small v-else-if="serverErrors.date" class="text-red-500">{{ serverErrors.date[0] }}</small>
                </div>

                <!-- Se repite -->
                <div class="col-span-12 flex items-center gap-3 mt-3">
                    <Checkbox v-model="holiday.is_recurring" :binary="true" inputId="is_recurring" />
                    <label for="is_recurring" class="font-bold">Se repite cada año</label>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
            <Button label="Actualizar" icon="pi pi-check" @click="updateHoliday" :loading="loading" />
        </template>
    </Dialog>
</template>
