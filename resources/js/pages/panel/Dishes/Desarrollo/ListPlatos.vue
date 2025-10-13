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
import DeletePlatos from './DeletePlatos.vue';
import UpdatePlatos from './UpdatePlatos.vue';
import { useToast } from 'primevue/usetoast';

// Interfaces
interface Plato {
    id: number;
    name: string;
    price: number;
    quantity: number;
    nameCategory?: string;
    creacion?: string;
    actualizacion?: string;
    state: boolean | number | string;
}

interface Pagination {
    currentPage: number;
    perPage: number;
    total: number;
}

interface EstadoPlatoOption {
    name: string;
    value: string | number | boolean | '';
}

// Initialize toast
const toast = useToast();

const dt = ref();
const platos = ref<Plato[]>([]);
const selectedPlatos = ref<Plato[] | null>(null);
const loading = ref(false);
const globalFilterValue = ref('');
const deletePlatoDialog = ref(false);
const plato = ref<Plato | null>(null);
const selectedPlatoId = ref<number | null>(null);
const selectedEstadoPlato = ref<EstadoPlatoOption | null>(null);
const updatePlatoDialog = ref(false);
const currentPage = ref(1);

const props = defineProps<{
    refresh: number;
}>();

watch(() => props.refresh, () => {
    loadPlatos();
});

watch(() => selectedEstadoPlato.value, () => {
    currentPage.value = 1;
    loadPlatos();
});

function editPlato(platoData: Plato) {
    selectedPlatoId.value = platoData.id;
    updatePlatoDialog.value = true;
}

const estadoPlatoOptions = ref<EstadoPlatoOption[]>([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 1 },
    { name: 'INACTIVOS', value: 0 },
]);

function handlePlatoUpdated() {
    loadPlatos();
}

function confirmDeletePlato(selected: Plato) {
    plato.value = selected;
    deletePlatoDialog.value = true;
}

const pagination = ref<Pagination>({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const filters = ref<{ state: string | number | null }>({
    state: null
});

function handlePlatoDeleted() {
    loadPlatos();
}

const loadPlatos = async () => {
    loading.value = true;
    try {
        const params: Record<string, any> = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            state: filters.value.state,
        };
        if (selectedEstadoPlato.value && selectedEstadoPlato.value.value !== '') {
            params.state = selectedEstadoPlato.value.value;
        }

        const response = await axios.get('/plato', { params });

        if (response.data && response.data.data) {
            platos.value = response.data.data as Plato[];

            if (response.data.meta) {
                pagination.value.currentPage = response.data.meta.current_page || 1;
                pagination.value.total = response.data.meta.total || 0;
            } else {
                pagination.value.currentPage = 1;
                pagination.value.total = response.data.data.length || 0;
            }
        } else {
            platos.value = [];
            pagination.value.total = 0;
        }
    } catch (error) {
        console.error('Error al cargar platos:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los platos', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const onPage = (event: { page: number; rows: number }) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadPlatos();
};

const getSeverity = (value: boolean | number): 'success' | 'danger' | undefined => {
    const boolValue = value === true || value === 1 ;
    return boolValue ? 'success' : value === false || value === 0 ? 'danger' : undefined;
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadPlatos();
}, 500);

const formatCurrency = (value: number | string | null): string => {
    if (value != null) {
        return 'S/. ' + parseFloat(value as string).toFixed(2);
    }
    return '';
};

onMounted(() => {
    loadPlatos();
});
</script>

<template>
    <DataTable ref="dt" v-model:selection="selectedPlatos" :value="platos" dataKey="id" :paginator="true"
        :rows="pagination.perPage" :totalRecords="pagination.total" :loading="loading" :lazy="true" @page="onPage"
        :rowsPerPageOptions="[15, 20, 25]" scrollable scrollHeight="574px"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} platos">

        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">Platos</h4>
                <div class="flex flex-wrap gap-2">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar plato..." />
                    </IconField>
                    <Select v-model="selectedEstadoPlato" :options="estadoPlatoOptions" optionLabel="name"
                        placeholder="Estado del Plato" class="w-full md:w-auto" />
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadPlatos" />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
        <Column field="name" header="Nombre" sortable style="min-width: 12rem"></Column>
        <Column field="price" header="Precio" sortable style="min-width: 8rem">
            <template #body="{ data }">
                {{ formatCurrency(data.price) }}
            </template>
        </Column>
        <Column field="quantity" header="Cantidad" sortable style="min-width: 6rem"></Column>
        <Column field="nameCategory" header="Categoría" sortable style="min-width: 12rem"></Column>
        <Column field="creacion" header="Creación" sortable style="min-width: 10rem"></Column>
        <Column field="actualizacion" header="Actualización" sortable style="min-width: 10rem"></Column>
        <Column field="state" header="Estado" sortable style="min-width: 4rem">
            <template #body="{ data }">
                <Tag :value="data.state ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.state)" />
            </template>
        </Column>
        <Column field="accions" header="Acciones" :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
                <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editPlato(slotProps.data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeletePlato(slotProps.data)" />
            </template>
        </Column>
    </DataTable>

    <DeletePlatos
        v-model:visible="deletePlatoDialog"
        :plato="plato"
        @deleted="handlePlatoDeleted"
    />
    <UpdatePlatos
        v-model:visible="updatePlatoDialog"
        :PlatoId="selectedPlatoId"
        @updated="handlePlatoUpdated"
    />
</template>