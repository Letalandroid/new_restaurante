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
import DeleteCliente from './DeleteCliente.vue';
import UpdateCliente from './UpdateCliente.vue';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';

interface Cliente {
    id: number;
    name: string;
    lastname: string;
    email: string;
    phone: string;
    codigo: string;
    Cliente_Tipo?: string;
    creacion?: string;
    actualizacion?: string;
    state: boolean | number;
}

interface Pagination {
    currentPage: number;
    perPage: number;
    total: number;
}

interface EstadoOption {
    name: string;
    value: string | number | boolean;
}

interface Filters {
    state: number | null;
}

const dt = ref();
const clientes = ref<Cliente[]>([]);
const selectedClientes = ref<Cliente[] | null>(null);
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const deleteClienteDialog = ref<boolean>(false);
const cliente = ref<Cliente | null>(null);
const selectedClienteId = ref<number | null>(null);
const selectedEstadoCliente = ref<EstadoOption | null>(null);
const updateClienteDialog = ref<boolean>(false);

const toast = useToast();

const props = defineProps<{
    refresh: number;
}>();

watch(() => props.refresh, () => {
    loadCliente();
});

watch(() => selectedEstadoCliente.value, () => {
    pagination.value.currentPage = 1;
    loadCliente();
});

function editCliente(selectedCliente: Cliente) {
    selectedClienteId.value = selectedCliente.id;
    updateClienteDialog.value = true;
}

const estadoClienteOptions = ref<EstadoOption[]>([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 1 },
    { name: 'INACTIVOS', value: 0 },
]);

function handleClienteUpdated() {
    loadCliente();
}

function confirmDeleteCliente(selected: Cliente) {
    cliente.value = selected;
    deleteClienteDialog.value = true;
}

const pagination = ref<Pagination>({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const filters = ref<Filters>({
    state: null
});

function handleClienteDeleted() {
    loadCliente();
}

const loadCliente = async (): Promise<void> => {
    loading.value = true;
    try {
        const params: Record<string, any> = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            state: filters.value.state,
        };

        if (selectedEstadoCliente.value !== null && selectedEstadoCliente.value.value !== '') {
            params.state = selectedEstadoCliente.value.value;
        }

        const response = await axios.get('/cliente', { params });

        clientes.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error: any) {
        console.error('Error al cargar clientes:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los clientes', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const onPage = (event: { page: number; rows: number }) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadCliente();
};

const getSeverity = (value: boolean | number): 'success' | 'danger' | undefined => {
    const boolValue = value === true || value === 1 ;
    return boolValue ? 'success' : value === false || value === 0 ? 'danger' : undefined;
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadCliente();
}, 500);

onMounted(() => {
    loadCliente();
});
</script>

<template>
    <DataTable
        ref="dt"
        v-model:selection="selectedClientes"
        :value="clientes"
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
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} clientes"
        class="w-full overflow-x-auto"
    >

        <template #header>
            <div class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4 items-start sm:items-center justify-between w-full">
                <h4 class="m-0">CLIENTES</h4>
                <div class="flex flex-col sm:flex-row flex-wrap gap-2 w-full sm:w-auto items-stretch sm:items-center justify-start sm:justify-end">
                    <IconField class="w-full sm:w-auto">
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText
                            v-model="globalFilterValue"
                            @input="onGlobalSearch"
                            placeholder="Buscar cliente..."
                            class="w-full sm:w-60"
                        />
                    </IconField>

                    <Select
                        v-model="selectedEstadoCliente"
                        :options="estadoClienteOptions"
                        optionLabel="name"
                        placeholder="Estado"
                        class="w-full sm:w-40"
                    />

                    <Button
                        icon="pi pi-refresh"
                        outlined
                        rounded
                        aria-label="Refresh"
                        @click="loadCliente"
                        class="w-full sm:w-auto"
                    />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
        <Column field="name" header="Nombre" sortable style="min-width: 12rem" />
        <Column field="lastname" header="Apellido" sortable style="min-width: 12rem" />
        <Column field="email" header="Email" sortable style="min-width: 15rem" />
        <Column field="phone" header="Teléfono" sortable style="min-width: 10rem" />
        <Column field="codigo" header="Código" sortable style="min-width: 10rem" />
        <Column field="Cliente_Tipo" header="Tipo de Cliente" sortable style="min-width: 12rem" />
        <Column field="creacion" header="Creación" sortable style="min-width: 13rem" />
        <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem" />

        <Column field="state" header="Estado" sortable style="min-width: 6rem">
            <template #body="{ data }">
                <Tag :value="data.state ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.state)" />
            </template>
        </Column>

        <Column field="accions" header="Acciones" :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
                <div class="flex justify-center sm:justify-start gap-2">
                    <Button
                        icon="pi pi-pencil"
                        outlined
                        rounded
                        class="mr-0 sm:mr-2"
                        @click="editCliente(slotProps.data)"
                    />
                    <Button
                        icon="pi pi-trash"
                        outlined
                        rounded
                        severity="danger"
                        @click="confirmDeleteCliente(slotProps.data)"
                    />
                </div>
            </template>
        </Column>
    </DataTable>

    <DeleteCliente
        v-model:visible="deleteClienteDialog"
        :cliente="cliente"
        @deleted="handleClienteDeleted"
    />
    <UpdateCliente
        v-model:visible="updateClienteDialog"
        :clienteId="selectedClienteId"
        @updated="handleClienteUpdated"
    />
</template>
