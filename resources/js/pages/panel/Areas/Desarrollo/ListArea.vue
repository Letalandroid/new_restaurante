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
import DeleteArea from './DeleteArea.vue';
import UpdateArea from './UpdateArea.vue';
import { useToast } from 'primevue/usetoast';

interface Area {
    id: number;
    name: string;
    state: boolean | number;
    creacion?: string;
    actualizacion?: string;
    [key: string]: any;
}

interface EstadoOption {
    name: string;
    value: string | number | '';
}


const toast = useToast();
const dt = ref<any>(null);
const areas = ref<Area[]>([]);
const selectedAreas = ref<Area[] | null>(null);
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const deleteAreaDialog = ref<boolean>(false);
const area = ref<Area | null>(null);
const selectedAreaId = ref<number | null>(null);
const selectedEstadoArea = ref<{ name: string; value: any } | null>(null);
const updateAreaDialog = ref<boolean>(false);
const currentPage = ref<number>(1);

const props = defineProps<{
    refresh: number;
}>();

watch(() => props.refresh, () => {
    loadAreas();
});

watch(() => selectedEstadoArea.value, () => {
    currentPage.value = 1;
    loadAreas();
});

function editArea(selectedArea: Area): void {
    selectedAreaId.value = selectedArea.id;
    updateAreaDialog.value = true;
}

const estadoAreaOptions = ref<EstadoOption[]>([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 1 },
    { name: 'INACTIVOS', value: 0 },
]);

function handleAreaUpdated(): void {
    loadAreas();
}

function confirmDeleteArea(selected: Area): void {
    area.value = selected;
    deleteAreaDialog.value = true;
}

const pagination = ref({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const filters = ref<{ state: any; online?: any }>({ state: null });

function handleAreaDeleted(): void {
    loadAreas();
}

const loadAreas = async (): Promise<void> => {
    loading.value = true;
    try {
        const params: Record<string, any> = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            state: filters.value.state,
        };
        if (selectedEstadoArea.value !== null && selectedEstadoArea.value.value !== '') {
            params.state = selectedEstadoArea.value.value;
        }

        const response= await axios.get('/area', { params });
        areas.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar areas:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar las áreas', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const onPage = (event: { page: number; rows: number }) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadAreas();
};

const getSeverity = (value: boolean | number): 'success' | 'danger' | undefined => {
    const boolValue = value === true || value === 1 ;
    return boolValue ? 'success' : value === false || value === 0 ? 'danger' : undefined;
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadAreas();
}, 500);

onMounted(() => {
    loadAreas();
});
</script>

<template>
    <DataTable ref="dt" v-model:selection="selectedAreas" :value="areas" dataKey="id" :paginator="true"
        :rows="pagination.perPage" :totalRecords="pagination.total" :loading="loading" :lazy="true" @page="onPage"
        :rowsPerPageOptions="[15, 20, 25]" scrollable scrollHeight="574px"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} áreas">

        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">Áreas</h4>
                <div class="flex flex-wrap gap-2">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar..." />
                    </IconField>
                    <Select v-model="selectedEstadoArea" :options="estadoAreaOptions" optionLabel="name"
                        placeholder="Estado del Área" class="w-full md:w-auto" />
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadAreas" />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
        <Column field="name" header="Nombre" sortable style="min-width: 13rem"></Column>
        <Column field="creacion" header="Creación" sortable style="min-width: 13rem"></Column>
        <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem"></Column>
        <Column field="state" header="Estado" sortable style="min-width: 4rem">
            <template #body="{ data }">
                <Tag :value="data.state ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.state)" />
            </template>
        </Column>
        <Column :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
                <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editArea(slotProps.data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteArea(slotProps.data)" />
            </template>
        </Column>
    </DataTable>

    <DeleteArea
        v-model:visible="deleteAreaDialog"
        :area="area"
        @deleted="handleAreaDeleted"
    />
    <UpdateArea
        v-model:visible="updateAreaDialog"
        :AreaId="selectedAreaId"
        @updated="handleAreaUpdated"
    />
</template>