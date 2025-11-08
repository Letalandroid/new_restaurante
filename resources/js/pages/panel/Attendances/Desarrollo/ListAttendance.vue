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
import DatePicker from 'primevue/datepicker'; // Importar DatePicker
import axios from 'axios';
import { debounce } from 'lodash';

interface Attendance {
    id: number;
    user_id: number;
    employee_name?: string;
    work_date: string;
    check_in?: string | null;
    check_out?: string | null;
    status?: string;
    justification?: string | null;
    created_at?: string;
    updated_at?: string;
}

interface Pagination {
    currentPage: number;
    perPage: number;
    total: number;
}

interface Filter {
    status: any;
    dateRange: Date[] | null; // Nuevo filtro de fecha
}

const dt = ref<any>(null);
const attendances = ref<Attendance[]>([]);
const selectedAttendances = ref<Attendance[] | null>(null);
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const deleteAttendanceDialog = ref<boolean>(false);
const attendance = ref<Attendance | null>(null);
const currentPage = ref<number>(1);

import UpdateAttendance from './UpdateAttendance.vue';
import DeleteAttendance from './DeleteAttendance.vue';

const selectedStatus = ref<{ name: string; value: string | null } | null>(null);
const updateAttendanceDialog = ref(false);
const selectedAttendanceId = ref<number | null>(null);
const selectedDateRange = ref<Date[] | null>(null); // Ref para el DatePicker

function editAttendance(a: any) {
    selectedAttendanceId.value = a.id;
    updateAttendanceDialog.value = true;
}

function handleAttendanceUpdated() {
    loadAttendances();
}

const props = defineProps<{
    refresh: number;
}>();

watch(() => props.refresh, () => {
    loadAttendances();
});

watch(() => selectedStatus.value, () => {
    currentPage.value = 1;
    loadAttendances();
});

// Watch para el filtro de fecha
watch(() => selectedDateRange.value, () => {
    currentPage.value = 1;
    loadAttendances();
});

const statusOptions = ref<{ name: string; value: number | null }[]>([
  { name: 'TODOS', value: null },
  { name: 'PRESENTE', value: 1 },
  { name: 'TARDE', value: 2 },
  { name: 'FALTA', value: 3 },
  { name: 'JUSTIFICADO', value: 4 },
  { name: 'DÍA LIBRE', value: 5 },
]);


function confirmDeleteAttendance(selected: Attendance) {
    attendance.value = selected;
    deleteAttendanceDialog.value = true;
}

const pagination = ref<Pagination>({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const filters = ref<Filter>({
    status: null,
    dateRange: null
});

function handleAttendanceDeleted() {
    loadAttendances();
}

// Función para formatear fecha a YYYY-MM-DD
const formatDate = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const loadAttendances = async () => {
    loading.value = true;
    try {
        const params: any = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            state: filters.value.status,
        };
        
        if (selectedStatus.value && selectedStatus.value.value) {
            params.state = selectedStatus.value.value;
        }

        // Agregar parámetros de fecha
        if (selectedDateRange.value && selectedDateRange.value.length > 0) {
            if (selectedDateRange.value.length === 1) {
                // Un solo día
                params.date = formatDate(selectedDateRange.value[0]);
            } else if (selectedDateRange.value.length === 2) {
                // Rango de fechas
                params.date_from = formatDate(selectedDateRange.value[0]);
                params.date_to = formatDate(selectedDateRange.value[1]);
            }
        }

        const response = await axios.get('/asistencia', { params });

        attendances.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar asistencias:', error);
    } finally {
        loading.value = false;
    }
};

const onPage = (event: any) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadAttendances();
};
const getSeverity = (status: string | null): 'success' | 'danger' | 'warning' | 'info' | undefined => {
    switch (status?.toLowerCase()) {
        case 'presente':
            return 'success';     // Verde
        case 'tarde':
            return 'warning';     // Amarillo
        case 'ausente':
            return 'danger';      // Rojo
        case 'justificado':
            return 'info';        // Azul
        case 'día libre':
        case 'dia libre':         // cubrir sin tilde también
            return 'secondary';   // Gris (neutral)
        default:
            return undefined;
    }
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadAttendances();
}, 500);

// Función para limpiar filtro de fecha
const clearDateFilter = () => {
    selectedDateRange.value = null;
};

onMounted(() => {
    loadAttendances();
});
const emit = defineEmits(['filters-changed'])

watch([selectedStatus, globalFilterValue, selectedDateRange], () => {
  emit('filters-changed', {
    selectedStatus: selectedStatus.value,
    globalFilterValue: globalFilterValue.value,
    selectedDateRange: selectedDateRange.value
  })
}, { deep: true })

</script>
<template>
    <DataTable
        ref="dt"
        v-model:selection="selectedAttendances"
        :value="attendances"
        dataKey="id"
        :paginator="true"
        :rows="pagination.perPage"
        :totalRecords="pagination.total"
        :loading="loading"
        :lazy="true"
        @page="onPage"
        :rowsPerPageOptions="[15, 20, 25]"
        scrollable
        scrollHeight="574px"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} asistencias"
    >
        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">ASISTENCIAS</h4>
                <div class="flex flex-wrap gap-2">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText
                            v-model="globalFilterValue"
                            @input="onGlobalSearch"
                            placeholder="Buscar empleado..."
                        />
                    </IconField>
                   
                    <!-- DatePicker con botón de limpiar separado -->
                    <div class="flex gap-1">
                        <DatePicker
                            v-model="selectedDateRange"
                            selectionMode="range"
                            :manualInput="false"
                            dateFormat="dd/mm/yy"
                            placeholder="Busqueda por fecha"
                            :showIcon="true"
                            iconDisplay="input"
                            class="w-full md:w-50"
                        />
                        <Button
                            v-if="selectedDateRange && selectedDateRange.length > 0"
                            icon="pi pi-times"
                            text
                            rounded
                            severity="secondary"
                            @click="clearDateFilter"
                            v-tooltip.top="'Limpiar fechas'"
                        />
                    </div>
                     <Select
                        v-model="selectedStatus"
                        :options="statusOptions"
                        optionLabel="name"
                        placeholder="Estado"
                        class="w-full md:w-auto"
                    />
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadAttendances" />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
        <Column field="employee_name" header="Empleado" sortable style="min-width: 13rem"></Column>
        <Column field="work_date" header="Fecha" sortable style="min-width: 10rem"></Column>
        <Column field="check_in" header="Entrada" sortable style="min-width: 8rem"></Column>
        <Column field="check_out" header="Salida" sortable style="min-width: 8rem"></Column>
        <Column field="status" header="Estado" sortable style="min-width: 8rem">
            <template #body="{ data }">
                <Tag :value="data.status" :severity="getSeverity(data.status)" />
            </template>
        </Column>
        <Column field="justification" header="Justificación" sortable style="min-width: 14rem"></Column>
        <Column field="worked_hours" header="Horas Trabajadas" sortable style="min-width: 8rem"></Column>
        <Column field="acciones" header="Acciones" :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
                <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editAttendance(slotProps.data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteAttendance(slotProps.data)" />
            </template>
        </Column>
    </DataTable>

    <DeleteAttendance
        v-model:visible="deleteAttendanceDialog"
        :attendance="attendance"
        @deleted="handleAttendanceDeleted"
    />

    <UpdateAttendance
        v-model:visible="updateAttendanceDialog"
        :attendanceId="selectedAttendanceId"
        @updated="handleAttendanceUpdated"
    />
    
</template>