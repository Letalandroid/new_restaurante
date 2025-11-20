<template>
  <Toolbar class="mb-6">
    <template #start>
      <Button label="Registrar Asistencia" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
    </template>
    <template #end>
  <ToolsAttendance
  :selected-status="props.selectedStatus"
  :global-filter-value="props.globalFilterValue"
  :selected-date-range="props.selectedDateRange"
/>

    </template>
  </Toolbar>
<Dialog v-model:visible="attendanceDialog" :style="{ width: '600px' }" header="Registrar Asistencia" :modal="true">
  <div class="flex flex-col gap-6">
    <div class="grid grid-cols-12 gap-4">
      <!-- Selección de empleado -->
      <div class="col-span-12 sm:col-span-12 md:col-span-12">
        <label for="employee" class="block font-bold mb-3">Empleado <span class="text-red-500">*</span></label>
        <Dropdown 
          id="employee" 
          v-model="attendance.employee_id" 
          :options="employees" 
          optionLabel="name" 
          optionValue="id" 
          placeholder="Seleccione un empleado"
          class="w-full"
        />
        <small v-if="submitted && !attendance.employee_id" class="text-red-500">El empleado es obligatorio.</small>
        <small v-else-if="serverErrors.employee_id" class="text-red-500">{{ serverErrors.employee_id[0] }}</small>
      </div>

      <!-- Fecha -->
      <div class="col-span-12 sm:col-span-6 md:col-span-6">
        <label for="work_date" class="block font-bold mb-3">Fecha <span class="text-red-500">*</span></label>
        <Calendar 
          id="work_date" 
          v-model="attendance.work_date" 
          dateFormat="dd-mm-yy" 
          showIcon 
          class="w-full"
        />
        <small v-if="submitted && !attendance.work_date" class="text-red-500">La fecha es obligatoria.</small>
        <small v-else-if="serverErrors.work_date" class="text-red-500">{{ serverErrors.work_date[0] }}</small>
      </div>

<!-- Hora de entrada -->
<div
  v-if="attendance.status_id !== 3 && attendance.status_id !== 5" 
  class="col-span-12 sm:col-span-6 md:col-span-3"
>
  <label for="check_in" class="block font-bold mb-3">Hora entrada</label>
  <input
    type="time"
    id="check_in"
    v-model="attendance.check_in"
    class="border border-gray-300 rounded p-2 w-full max-w-[120px]"
  />
  <small v-if="serverErrors.check_in" class="text-red-500">{{ serverErrors.check_in[0] }}</small>
</div>




      <!-- Estado de asistencia -->
      <div class="col-span-12 sm:col-span-12 md:col-span-12">
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
<small v-if="submitted && !attendance.status_id" class="text-red-500">El estado es obligatorio.</small>

      </div>

      <!-- Justificación -->
      <div class="col-span-12 sm:col-span-12 md:col-span-12">
        <label for="justification" class="block font-bold mb-3">Justificación</label>
        <InputText 
          id="justification" 
          v-model="attendance.justification" 
          maxlength="255" 
          class="w-full"
        />
        <small v-if="serverErrors.justification" class="text-red-500">{{ serverErrors.justification[0] }}</small>
      </div>
    </div>
  </div>

  <template #footer>
    <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
    <Button label="Guardar" icon="pi pi-check" @click="guardarAttendance" />
  </template>
</Dialog>

</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import ToolsAttendance from './toolsAttendance.vue';
import { useToast } from 'primevue/usetoast';


interface ServerErrors {
    [key: string]: string[];
}

interface Employee {
    id: number;
    name: string;
}

const toast = useToast();
const submitted = ref(false);
const attendanceDialog = ref(false);
const serverErrors = ref<ServerErrors>({});
const employees = ref<Employee[]>([]);
const emit = defineEmits<{
    (e: 'attendance-registered'): void;
}>();

// Cargar empleados y estados
const loadEmployees = async () => {
    try {
        const response = await axios.get('/empleado');
        employees.value = response.data.data || [];
    } catch (error) {
        console.error(error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los empleados', life: 3000 });
    }
};



onMounted(() => {
    loadEmployees();
});

// Funciones de diálogo
function resetAttendance() {
    attendance.value = {
        employee_id: null,
        work_date: null,
        check_in: null,
        status_id: null,
        justification: null
    };
    serverErrors.value = {};
    submitted.value = false;
}

function openNew() {
    resetAttendance();
    attendanceDialog.value = true;
}

function hideDialog() {
    attendanceDialog.value = false;
    resetAttendance();
}
const props = defineProps({
  selectedStatus: Object,
  globalFilterValue: String,
  selectedDateRange: Array
})


const statusOptions = ref<{ name: string; value: number | null }[]>([
  { name: 'PRESENTE', value: 1 },
  { name: 'TARDE', value: 2 },
  { name: 'FALTA', value: 3 },
  { name: 'JUSTIFICADO', value: 4 },
  { name: 'DÍA LIBRE', value: 5 },
]);


interface Attendance {
    employee_id: number | null;
    work_date: Date | null;
    check_in: string | null;
    status_id: number | null;
    justification: string | null;
}

const attendance = ref<Attendance>({
    employee_id: null,
    work_date: null,
    check_in: null,
    status_id: null,
    justification: null
});
// Guardar asistencia
function guardarAttendance() {
    submitted.value = true;
    serverErrors.value = {};

    // Si el estado es "FALTA" o "DÍA LIBRE", no se envían horas
    const omitHours = attendance.value.status_id === 3 || attendance.value.status_id === 5;

    const payload: any = {
        employee_id: attendance.value.employee_id,
        work_date: attendance.value.work_date,
        status_id: attendance.value.status_id,
        justification: attendance.value.justification,
    };

    if (!omitHours) {
        payload.check_in = attendance.value.check_in;
    }

    axios.post('/asistencia', payload)
        .then(() => {
            toast.add({
                severity: 'success',
                summary: 'Éxito',
                detail: 'Asistencia registrada correctamente.',
                life: 3000
            });
            hideDialog();
            emit('attendance-registered');
        })
        .catch((error: AxiosError) => {
            if (error.response && error.response.status === 422) {
                const data = error.response.data as any;
                serverErrors.value = data.errors || {};
                Object.values(serverErrors.value).flat().forEach((msg: string) => {
                    toast.add({
                        severity: 'warn',
                        summary: 'Validación',
                        detail: msg,
                        life: 3500
                    });
                });
            } else {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'No se pudo registrar la asistencia.',
                    life: 3000
                });
            }
        });
}



</script>
