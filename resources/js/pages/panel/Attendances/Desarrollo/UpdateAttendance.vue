<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import axios, { AxiosError } from 'axios';
import { useToast } from 'primevue/usetoast';
import Tag from 'primevue/tag';

interface Attendance {
    employee_id: number | null;
    work_date: string | null;
    check_in: string | null;
    check_out: string | null;
    status_id: number | null;
    justification: string | null;
}

interface Employee {
    id: number;
    name: string;
}

interface ServerErrors {
    [key: string]: string[];
}

const props = defineProps<{
    visible: boolean;
    attendanceId: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

const toast = useToast();
const serverErrors = ref<ServerErrors>({});
const loading = ref<boolean>(false);
const submitted = ref<boolean>(false);

const dialogVisible = ref<boolean>(props.visible);
watch(() => props.visible, (val) => (dialogVisible.value = val));
watch(dialogVisible, (val) => emit('update:visible', val));

const employees = ref<Employee[]>([]);

const attendance = ref<Attendance>({
    employee_id: null,
    work_date: null,
    check_in: null,
    check_out: null,
    status_id: null,
    justification: null
});

const statusOptions = ref<{ name: string; value: number | null }[]>([
    { name: 'PRESENTE', value: 1 },
    { name: 'TARDE', value: 2 },
    { name: 'FALTA', value: 3 },
    { name: 'JUSTIFICADO', value: 4 },
    { name: 'DÍA LIBRE', value: 5 },
]);

// Cargar empleados
const loadEmployees = async () => {
    try {
        const response = await axios.get('/empleado');
        employees.value = response.data.data || [];
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar los empleados',
            life: 3000
        });
    }
};

// Cargar asistencia existente
const fetchAttendance = async () => {
    if (!props.attendanceId) return;
    loading.value = true;
    try {
        const response = await axios.get(`/asistencia/${props.attendanceId}`);
        const data = response.data.attendance;

        attendance.value.employee_id = data.employee_id;
        attendance.value.work_date = data.work_date;
        attendance.value.check_in = data.check_in;
        attendance.value.check_out = data.check_out;
        attendance.value.status_id = data.status_id;
        attendance.value.justification = data.justification;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar la asistencia',
            life: 3000
        });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

watch(
  () => [props.visible, props.attendanceId],
  ([visible, attendanceId]) => {
    if (visible && attendanceId) {
      loadEmployees();
      fetchAttendance();
    }
  },
  { immediate: true }
);


// Actualizar asistencia
const updateAttendance = async (): Promise<void> => {
    submitted.value = true;
    serverErrors.value = {};

    if (!props.attendanceId) return;

    try {
        const payload = {
            employee_id: attendance.value.employee_id,
            work_date: attendance.value.work_date,
            check_in: attendance.value.check_in,
            check_out: attendance.value.check_out,
            status_id: attendance.value.status_id,
            justification: attendance.value.justification,
        };

        await axios.put(`/asistencia/${props.attendanceId}`, payload);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Asistencia actualizada correctamente',
            life: 3000
        });

        dialogVisible.value = false;
        emit('updated');
    } catch (error: any) {
        const axiosError = error as AxiosError;
        if (axiosError.response?.status === 422 && axiosError.response.data) {
            serverErrors.value = (axiosError.response.data as any).errors || {};
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
                detail: 'No se pudo actualizar la asistencia',
                life: 3000
            });
        }
        console.error(error);
    }
};
</script>

<template>
    <Dialog
        v-model:visible="dialogVisible"
        header="Editar Asistencia"
        modal
        :style="{ width: '600px' }"
    >
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Empleado -->
                <div class="col-span-12">
                    <label for="employee_id" class="block font-bold mb-3">Empleado <span class="text-red-500">*</span></label>
                    <Dropdown
                        id="employee_id"
                        v-model="attendance.employee_id"
                        :options="employees"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Seleccione un empleado"
                        class="w-full"
                    />
                    <small v-if="serverErrors.employee_id" class="p-error">{{ serverErrors.employee_id[0] }}</small>
                </div>

                <!-- Fecha -->
                <div class="col-span-6">
                    <label for="work_date" class="block font-bold mb-3">Fecha <span class="text-red-500">*</span></label>
                    <Calendar
                        id="work_date"
                        v-model="attendance.work_date"
                        dateFormat="dd-mm-yy"
                        showIcon
                        class="w-full"
                    />
                    <small v-if="serverErrors.work_date" class="p-error">{{ serverErrors.work_date[0] }}</small>
                </div>

                <!-- Hora entrada -->
                <div class="col-span-3">
                    <label for="check_in" class="block font-bold mb-3">Entrada</label>
                    <input
                        type="time"
                        id="check_in"
                        v-model="attendance.check_in"
                        class="border border-gray-300 rounded p-2 w-full"
                    />
                </div>

                <!-- Hora salida -->
                <div class="col-span-3">
                    <label for="check_out" class="block font-bold mb-3">Salida</label>
                    <input
                        type="time"
                        id="check_out"
                        v-model="attendance.check_out"
                        class="border border-gray-300 rounded p-2 w-full"
                    />
                </div>

                <!-- Estado -->
                <div class="col-span-12">
                    <label for="status_id" class="block font-bold mb-3">Estado</label>
                    <Dropdown
                        id="status_id"
                        v-model="attendance.status_id"
                        :options="statusOptions"
                        optionLabel="name"
                        optionValue="value"
                        placeholder="Seleccione un estado"
                        class="w-full"
                    />
                    <small v-if="serverErrors.status_id" class="p-error">{{ serverErrors.status_id[0] }}</small>
                </div>

                <!-- Justificación -->
                <div class="col-span-12">
                    <label for="justification" class="block font-bold mb-3">Justificación</label>
                    <InputText
                        id="justification"
                        v-model="attendance.justification"
                        maxlength="255"
                        class="w-full"
                    />
                    <small v-if="serverErrors.justification" class="p-error">{{ serverErrors.justification[0] }}</small>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateAttendance" :loading="loading" />
        </template>
    </Dialog>
</template>

<style scoped>
.p-error {
    color: #ef4444;
    font-size: 0.875rem;
}
</style>
