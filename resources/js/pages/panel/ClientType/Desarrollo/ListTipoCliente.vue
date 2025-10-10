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
import Select from 'primevue/select';
import DeleteTipoCliente from './DeleteTipoCliente.vue';
import UpdateTipoCliente from './UpdateTipoCliente.vue';

interface TipoCliente {
    id: number;
    name: string;
    state: boolean | number;
    creacion?: string;
    actualizacion?: string;
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

const dt = ref();
const tiposClientes = ref<TipoCliente[]>([]);
const selectedTiposClientes = ref<TipoCliente[]>();
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const deleteTipoClienteDialog = ref<boolean>(false);
const tipoCliente = ref<TipoCliente| null>(null);
const selectedTipoClienteId = ref<number | null>(null);
const selectedEstadoTipoCliente = ref<EstadoOption | null>(null);
const updateTipoClienteDialog = ref<boolean>(false);
const currentPage = ref<number>(1);

const props = defineProps<{
    refresh: number;
}>();

watch(() => props.refresh, () => {
    loadTipoCliente();
});

watch(() => selectedEstadoTipoCliente.value, () => {
    currentPage.value = 1;
    loadTipoCliente();
});

const estadoTipoClienteOptions = ref<EstadoOption[]>([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 1 },
    { name: 'INACTIVOS', value: 0 },
]);

const pagination = ref<Pagination>({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const filters = ref({
    state: null as number | null,
    online: null as number | null
});

function editTipoCliente(tc: TipoCliente) {
    selectedTipoClienteId.value = tc.id;
    updateTipoClienteDialog.value = true;
}

function confirmDeleteTipoCliente(selected: TipoCliente) {
    tipoCliente.value = selected;
    deleteTipoClienteDialog.value = true;
}

function handleTipoClienteUpdated() {
    loadTipoCliente();
}

function handleTipoClienteDeleted() {
    loadTipoCliente();
}

const loadTipoCliente = async (): Promise<void> => {
    loading.value = true;
    try {
        const params: Record<string, any> = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            state: filters.value.state,
        };

        if (selectedEstadoTipoCliente.value !== null && selectedEstadoTipoCliente.value.value !== '') {
            params.state = selectedEstadoTipoCliente.value.value;
        }

        const response = await axios.get('/tipo_cliente', { params });

        tiposClientes.value = response.data.data as TipoCliente[];
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar tipo clientes:', error);
    } finally {
        loading.value = false;
    }
};

const onPage = (event: { page: number; rows: number }): void => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadTipoCliente();
};

const getSeverity = (value: boolean | number): 'success' | 'danger' | undefined => {
    const boolValue = value === true || value === 1 ;
    return boolValue ? 'success' : value === false || value === 0 ? 'danger' : undefined;
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadTipoCliente();
}, 500);

onMounted(() => {
    loadTipoCliente();
});
</script>

<template>
    <DataTable
        ref="dt"
        v-model:selection="selectedTiposClientes"
        :value="tiposClientes"
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
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} tipo cliente"
    >
        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">Tipo Clientes</h4>
                <div class="flex flex-wrap gap-2">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar tipo cliente..." />
                    </IconField>
                    <Select
                        v-model="selectedEstadoTipoCliente"
                        :options="estadoTipoClienteOptions"
                        optionLabel="name"
                        placeholder="Estado del tipo cliente"
                        class="w-full md:w-auto"
                    />
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadTipoCliente" />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
        <Column field="name" header="Nombre" sortable style="min-width: 13rem" />
        <Column field="creacion" header="Creación" sortable style="min-width: 13rem" />
        <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem" />
        <Column field="state" header="Estado" sortable style="min-width: 4rem">
            <template #body="{ data }">
                <Tag :value="data.state ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.state)" />
            </template>
        </Column>
        <Column field="accions" header="Acciones" :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
                <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editTipoCliente(slotProps.data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteTipoCliente(slotProps.data)" />
            </template>
        </Column>
    </DataTable>

    <DeleteTipoCliente
        v-model:visible="deleteTipoClienteDialog"
        :tipoCliente="tipoCliente"
        @deleted="handleTipoClienteDeleted"
    />

    <UpdateTipoCliente
        v-model:visible="updateTipoClienteDialog"
        :tipoClienteId="selectedTipoClienteId"
        @updated="handleTipoClienteUpdated"
    />
</template>
