<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import axios from 'axios';
import { debounce } from 'lodash';
const monthOptions = ref([]); 
interface PayrollDetail {
    concept: string;
    amount: string;
    type: string;
}

interface Payroll {
    id: number;
    employee_name: string;
    start_date: string;
    end_date: string;
    days_present: number;
    days_absent: number;
    hours_worked: string;
    overtime_hours: string;
    gross_total: string;
    net_total: string;
    paid: boolean;
    details: PayrollDetail[];
}

const payrolls = ref<Payroll[]>([]);
const loading = ref(false);
const globalFilterValue = ref('');
const selectedEstado = ref<{ name: string; value: any } | null>(null);
const currentPage = ref<number>(1);
const perPage = ref<number>(15);
const totalRecords = ref<number>(0);
// Emit para actualizar la tabla padre
const emit = defineEmits<{
  (e: 'payroll-agregado'): void;
  (e: 'update-filtros', filtros: { search: string; paid: string | boolean; month: number | null }): void;
}>();


const selectedPayroll = ref<Payroll | null>(null);
const viewDialog = ref(false);

const estadoOptions = ref([
    { name: 'TODOS', value: '' },
    { name: 'PAGADO', value: true },
    { name: 'PENDIENTE', value: false },
]);

const selectedMonth = ref<number | null>(null);
monthOptions.value = [
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
];

const props = defineProps<{
    refresh: number;
}>();

const loadPayrolls = async () => {
    loading.value = true;
    try {
        const params: any = {
            page: currentPage.value,
            per_page: perPage.value,
            search: globalFilterValue.value,
            paid: selectedEstado.value?.value ?? '',
            month: selectedMonth.value ?? '',  // <-- nuevo parámetro
        };
        const response = await axios.get('/nomina', { params });
        payrolls.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error('Error al cargar nóminas:', error);
    } finally {
        loading.value = false;
    }
};



const onPage = (event: any) => {
    currentPage.value = event.page + 1;
    perPage.value = event.rows;
    loadPayrolls();
};

const onGlobalSearch = debounce(() => {
    currentPage.value = 1;
    loadPayrolls();
}, 500);

const getSeverity = (value: boolean) => (value ? 'success' : 'danger');

const viewPayroll = (payroll: Payroll) => {
    selectedPayroll.value = payroll;
    viewDialog.value = true;
};

onMounted(() => {
    loadPayrolls();
        selectedMonth.value = new Date().getMonth() + 1;

});

watch(selectedEstado, () => {
    currentPage.value = 1;
    loadPayrolls();
});
watch(() => props.refresh, () => {
    loadPayrolls();
});
const confirmPayment = async (payroll: Payroll) => {
   

    try {
        // Update vía API
        await axios.put(`/nomina/${payroll.id}`, { paid: true });
        payroll.paid = true; // Actualizamos localmente
        toast.value.add({ severity: 'success', summary: 'Éxito', detail: 'Nómina pagada correctamente', life: 3000 });
    } catch (error) {
        console.error('Error al actualizar nómina:', error);
        toast.value.add({ severity: 'error', summary: 'Error', detail: 'No se pudo actualizar la nómina', life: 3000 });
    }
};
watch([globalFilterValue, selectedEstado, selectedMonth], () => {
  emit('update-filtros', {
    search: globalFilterValue.value,
    paid: selectedEstado.value?.value ?? '',
    month: selectedMonth.value ?? '',
  });
});

</script>

<template>
    <DataTable ref="dt" :value="payrolls" dataKey="id" :paginator="true"
        :rows="perPage" :totalRecords="totalRecords" :loading="loading" :lazy="true" @page="onPage"
        :rowsPerPageOptions="[15, 20, 25]" scrollable scrollHeight="574px"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} nómina">

        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">NÓMINA</h4>
         <div class="flex flex-wrap gap-2">
    <IconField>
        <InputIcon>
            <i class="pi pi-search" />
        </InputIcon>
        <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar empleado..." />
    </IconField>

    <Select 
    v-model="selectedMonth" 
    :options="monthOptions" 
    optionLabel="name" 
    optionValue="value"  
    placeholder="Mes" 
    class="w-full md:w-auto" 
    @change="loadPayrolls" 
/>


    <Select 
        v-model="selectedEstado" 
        :options="estadoOptions" 
        optionLabel="name" 
        placeholder="Estado" 
        class="w-full md:w-auto" 
    />

    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadPayrolls" />
</div>


            </div>
        </template>

        <Column field="employee_name" header="Empleado" sortable style="min-width: 12rem"></Column>
        <Column field="start_date" header="Inicio" sortable style="min-width: 8rem"></Column>
        <Column field="end_date" header="Fin" sortable style="min-width: 8rem"></Column>
        <Column field="days_present" header="Días Asistidos" style="min-width: 6rem"></Column>
        <Column field="days_absent" header="Días Ausentes" style="min-width: 6rem"></Column>
        <Column field="hours_worked" header="Horas Trabajadas" style="min-width: 6rem"></Column>
        <Column field="overtime_hours" header="Horas Extra" style="min-width: 6rem"></Column>
        <Column field="gross_total" header="Sueldo Bruto" style="min-width: 6rem"></Column>
        <Column field="net_total" header="Sueldo Neto" style="min-width: 6rem"></Column>
        <Column field="paid" header="Estado" style="min-width: 6rem">
            <template #body="{ data }">
                <Tag :value="data.paid ? 'Pagado' : 'Pendiente'" :severity="getSeverity(data.paid)" />
            </template>
        </Column>

        <!-- Columna de acción Ver -->
        <Column header="Acción" style="min-width: 6rem">
            <template #body="{ data }">
                <Button icon="pi pi-eye" label="Ver" outlined rounded @click="viewPayroll(data)" />
            </template>
        </Column>
    </DataTable>
<!-- Modal profesional y minimalista para ver la nómina -->
<Dialog  
    header="Detalle de Nómina" 
    v-model:visible="viewDialog" 
    :modal="true" 
    :closable="true" 
    :style="{ width: '700px' }" 
    :breakpoints="{ '960px': '95vw', '640px': '100vw' }"
>
    <div v-if="selectedPayroll" class="grid gap-4">

        <!-- Información general -->
        <Panel header="Información del Empleado" class="p-mb-3">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><strong>Empleado:</strong> {{ selectedPayroll.employee_name }}</div>
                <div><strong>Periodo:</strong> {{ selectedPayroll.start_date }} hasta {{ selectedPayroll.end_date }}</div>
                <div>
                    <strong>Días presentes:</strong>
                    <span v-tooltip="'Número de días que el empleado asistió efectivamente'">
                        {{ selectedPayroll.days_present }}
                    </span>
                </div>
                <div>
                    <strong>Días ausentes: </strong> 
                    <span v-tooltip="'Número de días no laborados que se descontaron del sueldo'">
                        {{ selectedPayroll.days_absent }}
                    </span>
                </div>
                <div><strong>Horas trabajadas:</strong> {{ selectedPayroll.hours_worked }}</div>
                <div><strong>Horas extra:</strong> {{ selectedPayroll.overtime_hours }}</div>
                <div>
                    <strong>Sueldo Base:</strong>
                    <span v-tooltip="selectedPayroll.payment_type === 'fijo' ? 'Sueldo fijo del periodo' : 'Calculado por horas trabajadas'">
                        S/. {{ selectedPayroll.proportional_base }}
                    </span>
                </div>
           <!-- Estado con opción de pagar -->
<div class="flex items-center gap-2">
    <strong>Estado:</strong>
    <Tag :value="selectedPayroll.paid ? 'Pagado' : 'Pendiente'" 
         :severity="getSeverity(selectedPayroll.paid)" />
    <!-- Botón de pagar solo si no está pagado -->
    <Button v-if="!selectedPayroll.paid"
            label="Pagar"
            icon="pi pi-check"
            size="small"
            severity="success"
            outlined
            @click="confirmPayment(selectedPayroll)" />
</div>
            </div>
        </Panel>

        <!-- Detalle de conceptos resumido -->
        <Panel header="Conceptos" class="p-mb-3">
            <DataTable :value="selectedPayroll.details" responsiveLayout="scroll" class="p-datatable-sm p-datatable-striped">
                <Column field="concept" header="Concepto" style="min-width: 150px"></Column>
                <Column field="amount" header="Monto (S/.)" style="text-align:right">
                    <template #body="{ data }">
                        <span v-tooltip="data.type === 'ingreso' 
                            ? 'Ingreso adicional: ' + data.concept 
                            : 'Descuento: ' + data.concept">
                            {{ data.amount }}
                        </span>
                    </template>
                </Column>
                <Column field="type" header="Tipo">
                    <template #body="{ data }">
                        <Tag :value="data.type === 'ingreso' ? 'Ingreso' : 'Descuento'" 
                             :severity="data.type === 'ingreso' ? 'success' : 'danger'" />
                    </template>
                </Column>
            </DataTable>
        </Panel>

        <!-- Resumen final compacto -->
        <Panel header="Resumen" class="p-mb-0">
            <div class="grid grid-cols-3 gap-4 text-sm font-semibold text-right">
                <div>
                    <span v-tooltip="'Ingresos adicionales como horas extra y bonificaciones'">
                        Ingresos:<br>
                        S/. {{ selectedPayroll.details
                                .filter(d => d.type === 'ingreso')
                                .reduce((acc, d) => acc + parseFloat(d.amount), 0)
                                .toFixed(2) }}
                    </span>
                </div>
                <div>
                    <span v-tooltip="'Descuentos por ausencias, AFP u otros conceptos'">
                        Descuentos:<br>
                        S/. {{ selectedPayroll.details
                                .filter(d => d.type === 'descuento')
                                .reduce((acc, d) => acc + parseFloat(d.amount), 0)
                                .toFixed(2) }}
                    </span>
                </div>
                <div>
                    <span v-tooltip="'Sueldo Neto = Sueldo Base + Ingresos − Descuentos'">
                        Neto:<br>
                        S/. {{ selectedPayroll.net_total }}
                    </span>
                </div>
            </div>
        </Panel>

    </div>
</Dialog>


</template>
