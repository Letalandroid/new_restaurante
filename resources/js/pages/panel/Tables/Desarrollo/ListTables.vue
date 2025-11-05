<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import { debounce } from 'lodash';
import DeleteTable from './DeleteTables.vue';
import UpdateTable from './UpdateTables.vue';

// Tipos
interface Table {
    id: number;
    name: string;
    tablenum: string;
    capacity: number;
    area_name?: string;
    floor_name?: string;
    creacion?: string;
    actualizacion?: string;
    state: boolean;
}

interface Pagination {
    currentPage: number;
    perPage: number;
    total: number;
}

interface EstadoOption {
    name: string;
    value: string | number | '';
}

// Variables
const tables = ref<Table[]>([]);
const loading = ref<boolean>(false);
const selectedTables = ref<Table[] | null>(null);
const globalFilterValue = ref<string>('');
const deleteTableDialog = ref<boolean>(false);
const updateTableDialog = ref<boolean>(false);
const selectedTableId = ref<number | null>(null);
const table = ref<Table | null>(null);
const currentPage = ref<number>(1);
const selectedAreas = ref<number | null>(null);
const selectedFloor = ref<number | null>(null);
const selectedEstadoTable = ref<EstadoOption | null>(null);

const pagination = ref<Pagination>({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const estadoTableOptions = ref<EstadoOption[]>([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 1 },
    { name: 'INACTIVOS', value: 0 },
]);

// Cargar mesas
const loadTables = async (): Promise<void> => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            areas: selectedAreas?.value,
            floor: selectedFloor?.value,
            state: selectedEstadoTable.value?.value ?? '',
        };
        const response = await axios.get('/mesa', { params });
        tables.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar mesas:', error);
    } finally {
        loading.value = false;
    }
};

// Props
const props = defineProps<{
    refresh: number;
}>();

// Watchers
watch(() => props.refresh, loadTables);
watch(() => selectedEstadoTable.value, () => {
    currentPage.value = 1;
    loadTables();
});
watch(deleteTableDialog, (val) => {
    console.log('Dialogo eliminar visible:', val);
});

// Paginación
const onPage = (event: { page: number; rows: number }): void => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadTables();
};

// Búsqueda global con debounce
const onGlobalSearch = debounce((): void => {
    pagination.value.currentPage = 1;
    loadTables();
}, 500);

// Estado (Tag)
const getSeverity = (value: boolean): string => {
    return value ? 'success' : 'danger';
};

// Editar mesa
const editarTable = (prod: Table): void => {
    selectedTableId.value = prod.id;
    updateTableDialog.value = true;
};

// Confirmar eliminación
const confirmarDeleteTable = (prod: Table): void => {
    table.value = prod;
    deleteTableDialog.value = true;
};

// Eventos emitidos desde los hijos
function handleTableUpdated(): void {
    loadTables();
}

function handleTableDeleted(): void {
    loadTables();
}

onMounted(loadTables);
</script>

<template>
    <DataTable
        ref="dt"
        v-model:selection="selectedTables"
        :value="tables"
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
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} Mesas"
        class="w-full overflow-x-auto"
    >
        <!-- Header -->
        <template #header>
            <div class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-2 items-start sm:items-center justify-between w-full">
                <h4 class="m-0">Mesas</h4>

                <div class="flex flex-col sm:flex-row flex-wrap gap-2 w-full sm:w-auto">
                    <IconField class="w-full sm:w-64">
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText
                            v-model="globalFilterValue"
                            @input="onGlobalSearch"
                            placeholder="Buscar por N° mesa..."
                            class="w-full"
                        />
                    </IconField>

                    <Select
                        v-model="selectedEstadoTable"
                        :options="estadoTableOptions"
                        optionLabel="name"
                        placeholder="Estado"
                        class="w-full sm:w-48 md:w-56"
                    />

                    <Button
                        icon="pi pi-refresh"
                        outlined
                        rounded
                        aria-label="Refresh"
                        class="w-full sm:w-auto"
                        @click="loadTables"
                    />
                </div>
            </div>
        </template>

        <!-- Columnas -->
        <Column selectionMode="multiple" style="width: 1rem" />

        <Column field="name" header="Nombre" sortable style="min-width: 15rem" />
        <Column field="tablenum" header="Número" sortable style="min-width: 10rem" />
        <Column field="capacity" header="Capacidad" sortable style="min-width: 10rem" />
        <Column field="area_name" header="Área" sortable style="min-width: 10rem" />
        <Column field="floor_name" header="Piso" sortable style="min-width: 10rem" />
        <Column field="creacion" header="Creación" sortable style="min-width: 12rem" />
        <Column field="actualizacion" header="Actualización" sortable style="min-width: 12rem" />

        <Column field="state" header="Estado" sortable style="min-width: 6rem">
            <template #body="{ data }">
                <Tag
                    :value="data.state ? 'Activo' : 'Inactivo'"
                    :severity="getSeverity(data.state)"
                    class="text-xs sm:text-sm md:text-base"
                />
            </template>
        </Column>

        <Column field="accions" header="Acciones" :exportable="false" style="min-width: 8rem">
            <template #body="{ data }">
                <div class="flex flex-wrap justify-center sm:justify-start gap-2">
                    <Button
                        icon="pi pi-pencil"
                        outlined
                        rounded
                        class="mr-0 sm:mr-2 w-10 h-10 sm:w-auto sm:h-auto"
                        @click="editarTable(data)"
                    />
                    <Button
                        icon="pi pi-trash"
                        outlined
                        rounded
                        severity="danger"
                        class="w-10 h-10 sm:w-auto sm:h-auto"
                        @click="confirmarDeleteTable(data)"
                    />
                </div>
            </template>
        </Column>
    </DataTable>

    <!-- Diálogos -->
    <DeleteTable
        v-model:visible="deleteTableDialog"
        :table="table"
        @deleted="handleTableDeleted"
    />
    <UpdateTable
        v-model:visible="updateTableDialog"
        :tableId="selectedTableId"
        @updated="handleTableUpdated"
    />
</template>
