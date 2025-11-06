<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import axios from 'axios';
import { debounce } from 'lodash';
import DeleteHoliday from './DeleteHoliday.vue';
import UpdateHoliday from './UpdateHoliday.vue';
import Select from 'primevue/select';

interface Holiday {
    id: number;
    name: string;
    date: string;
    is_recurring: boolean | number;
    created_at?: string;
    updated_at?: string;
}

interface Pagination {
    currentPage: number;
    perPage: number;
    total: number;
}

const dt = ref<any>(null);
const holidays = ref<Holiday[]>([]);
const selectedHolidays = ref<Holiday[] | null>(null);
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const deleteHolidayDialog = ref<boolean>(false);
const updateHolidayDialog = ref<boolean>(false);
const holiday = ref<Holiday | null>(null);
const selectedHolidayId = ref<number | null>(null);
const selectedRecurringFilter = ref<{ name: string; value: any } | null>(null);
const currentPage = ref<number>(1);

const props = defineProps<{
    refresh: number;
}>();

watch(() => props.refresh, () => {
    loadHolidays();
});

watch(() => selectedRecurringFilter.value, () => {
    currentPage.value = 1;
    loadHolidays();
});

function editHoliday(h: Holiday) {
    selectedHolidayId.value = h.id;
    updateHolidayDialog.value = true;
}

function confirmDeleteHoliday(selected: Holiday) {
    holiday.value = selected;
    deleteHolidayDialog.value = true;
}

const recurringOptions = ref<{ name: string; value: any }[]>([
    { name: 'TODOS', value: '' },
    { name: 'ÚNICOS', value: 0 },
    { name: 'RECURRENTES', value: 1 },
]);

const pagination = ref<Pagination>({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const loadHolidays = async () => {
    loading.value = true;
    try {
        const params: any = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
        };

        if (selectedRecurringFilter.value && selectedRecurringFilter.value.value !== '') {
            params.is_recurring = selectedRecurringFilter.value.value;
        }

        const response = await axios.get('/asistencias/feriado', { params });
        holidays.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar feriados:', error);
    } finally {
        loading.value = false;
    }
};

const onPage = (event: any) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadHolidays();
};

const getSeverity = (value: boolean | number): 'info' | 'warning' | undefined => {
    const boolValue = value === true || value === 1;
    return boolValue ? 'info' : 'warning';
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadHolidays();
}, 500);

function handleHolidayUpdated() {
    loadHolidays();
}

function handleHolidayDeleted() {
    loadHolidays();
}

onMounted(() => {
    loadHolidays();
});
</script>

<template>
    <DataTable ref="dt" v-model:selection="selectedHolidays" :value="holidays" dataKey="id" :paginator="true"
        :rows="pagination.perPage" :totalRecords="pagination.total" :loading="loading" :lazy="true" @page="onPage"
        :rowsPerPageOptions="[15, 20, 25]" scrollable scrollHeight="574px"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} feriados">

        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">Feriados / Días Libres</h4>
                <div class="flex flex-wrap gap-2">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar feriado..." />
                    </IconField>
                    <Select v-model="selectedRecurringFilter" :options="recurringOptions" optionLabel="name"
                        placeholder="Tipo" class="w-full md:w-auto" />
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadHolidays" />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
        <Column field="name" header="Nombre" sortable style="min-width: 14rem"></Column>
        <Column field="date" header="Fecha" sortable style="min-width: 10rem"></Column>
        <Column field="is_recurring" header="Tipo" sortable style="min-width: 8rem">
            <template #body="{ data }">
                <Tag :value="data.is_recurring ? 'Recurrente' : 'Único'" :severity="getSeverity(data.is_recurring)" />
            </template>
        </Column>
        <Column field="created_at" header="Creación" sortable style="min-width: 10rem"></Column>
        <Column field="updated_at" header="Actualización" sortable style="min-width: 10rem"></Column>

        <Column field="accions" header="Acciones" :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
                <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editHoliday(slotProps.data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteHoliday(slotProps.data)" />
            </template>
        </Column>
    </DataTable>

    <DeleteHoliday
        v-model:visible="deleteHolidayDialog"
        :holiday="holiday"
        @deleted="handleHolidayDeleted"
    />
    <UpdateHoliday
        v-model:visible="updateHolidayDialog"
        :holidayId="selectedHolidayId"
        @updated="handleHolidayUpdated"
    />
</template>
