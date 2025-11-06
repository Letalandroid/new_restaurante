<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Generar Nómina" icon="pi pi-calendar" severity="success" class="mr-2" @click="openDialog" />
        </template>
    </Toolbar>

    <!-- Modal profesional para generar nómina -->
    <Dialog
        v-model:visible="showGenerateDialog"
        header="Generar Nómina"
        :modal="true"
        :closable="true"
        :style="{ width: '520px' }"
        :breakpoints="{ '960px': '90vw', '640px': '100vw' }"
    >
        <div class="grid gap-5">
            <!-- Seleccionar empleado -->
            <div class="field col-12">
                <label for="employee" class="font-semibold">Empleado</label>
                <Select
                    id="employee"
                    v-model="selectedEmployee"
                    :options="employeeOptions"
                    optionLabel="name"
                    optionValue="id"
                    placeholder="Seleccione un empleado"
                    class="w-full"
                />
            </div>

            <!-- Seleccionar mes -->
            <div class="field col-12 sm:col-6">
                <label for="month" class="font-semibold">Mes</label>
                <Select
                    id="month"
                    v-model="selectedMonth"
                    :options="monthOptions"
                    optionLabel="name"
                    optionValue="value"
                    placeholder="Seleccione un mes"
                    class="w-full"
                />
            </div>

            <!-- Seleccionar año -->
            <div class="field col-12 sm:col-6">
                <label for="year" class="font-semibold">Año</label>
                <InputText
                    id="year"
                    v-model="selectedYear"
                    type="number"
                    placeholder="Ingrese año"
                    class="w-full"
                />
            </div>

            <!-- Botones de acción -->
            <div class="col-12 flex justify-end gap-3 mt-3">
                <Button
                    label="Cancelar"
                    icon="pi pi-times"
                    class="p-button-secondary"
                    @click="hideDialog"
                />
                <Button
                    label="Generar"
                    icon="pi pi-check"
                    severity="success"
                    :disabled="!selectedEmployee || !selectedMonth || !selectedYear"
                    @click="generatePayroll"
                />
            </div>
        </div>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios, { AxiosError } from 'axios';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Toolbar from 'primevue/toolbar';
import { useToast } from 'primevue/usetoast';

const toast = useToast();

// Modal
const showGenerateDialog = ref(false);

// Empleados
const employeeOptions = ref<{ id: number; name: string }[]>([]);
const selectedEmployee = ref<number | null>(null);

// Mes y año
const monthOptions = ref([
    { name: 'Enero', value: 1 },
    { name: 'Febrero', value: 2 },
    { name: 'Marzo', value: 3 },
    { name: 'Abril', value: 4 },
    { name: 'Mayo', value: 5 },
    { name: 'Junio', value: 6 },
    { name: 'Julio', value: 7 },
    { name: 'Agosto', value: 8 },
    { name: 'Setiembre', value: 9 },
    { name: 'Octubre', value: 10 },
    { name: 'Noviembre', value: 11 },
    { name: 'Diciembre', value: 12 },
]);
const selectedMonth = ref<number | null>(new Date().getMonth() + 1);
const selectedYear = ref<number>(new Date().getFullYear());

// Emit para actualizar la tabla padre
const emit = defineEmits<{
    (e: 'payroll-agregado'): void;
}>();

// Cargar empleados
const loadEmployees = async () => {
    try {
        const response = await axios.get('/empleado');
        employeeOptions.value = response.data.data.map((e: any) => ({
            id: e.id,
            name: `${e.name} (${e.codigo})`,
        }));
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar los empleados',
            life: 4000,
        });
        console.error(error);
    }
};

// Abrir y cerrar modal
function openDialog() {
    showGenerateDialog.value = true;
}
function hideDialog() {
    showGenerateDialog.value = false;
    selectedEmployee.value = null;
    selectedMonth.value = new Date().getMonth() + 1;
    selectedYear.value = new Date().getFullYear();
}

// Generar nómina
const generatePayroll = async () => {
    if (!selectedEmployee.value || !selectedMonth.value || !selectedYear.value) return;

    try {
        const response = await axios.get('/nominas/generate', {
            params: {
                employee_id: selectedEmployee.value,
                month: selectedMonth.value,
                year: selectedYear.value,
            },
        });

        const data = response.data;

        if (data.success) {
            const messages = [
                `Nómina generada: Neto S/. ${data.net_salary}, Bruto S/. ${data.gross_salary}`,
                `¡Éxito! Nómina registrada correctamente.`,
                `Actualización completada: Nómina del empleado generada con éxito.`,
            ];
            const message = messages[Math.floor(Math.random() * messages.length)];

            toast.add({ severity: 'success', summary: 'Nómina Generada', detail: message, life: 4000 });
            hideDialog();
            emit('payroll-agregado'); // 🔹 Actualiza tabla padre
        } else {
            const warnMessages = [
                data.message || 'No se pudo generar la nómina',
                'Empleado sin días asistidos, verifique la asistencia',
                'La nómina ya existe y está pendiente de pago',
            ];
            const message = warnMessages[Math.floor(Math.random() * warnMessages.length)];
            toast.add({ severity: 'warn', summary: 'Atención', detail: message, life: 4000 });
        }
    } catch (error) {
        const err = error as AxiosError;
        let detailMessage = 'No se pudo conectar con el servidor';
        if (err.response) detailMessage = err.response.data?.message || detailMessage;

        toast.add({ severity: 'error', summary: 'Error', detail: detailMessage, life: 4000 });
        console.error(err);
    }
};

onMounted(() => {
    loadEmployees();
});
</script>
