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
import axios from 'axios';
import { debounce } from 'lodash';
import DeletePiso from './DeletePisos.vue';
import UpdatePiso from './UpdatePisos.vue';

interface Piso {
    id: number;
    name: string;
    description?: string;
    creacion?: string;
    actualizacion?: string;
    state: boolean | number | string;
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

const pisos = ref<Piso[]>([]);
const selectedPisos = ref<Piso[] | null>(null);
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const selectedEstadoPiso = ref<EstadoOption | null>(null);
const pagination = ref<Pagination>({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const piso = ref<Piso | null>(null);
const selectedPisoId = ref<number | null>(null);
const deletePisoDialog = ref<boolean>(false);
const updatePisoDialog = ref<boolean>(false);

const props = defineProps<{
    refresh: number | boolean;
}>();

watch(
    () => props.refresh,
    () => {
        loadPisos();
    }
);

const estadoPisoOptions = ref<EstadoOption[]>([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 1 },
    { name: 'INACTIVOS', value: 0 }
]);

const loadPisos = async (): Promise<void> => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            state: selectedEstadoPiso.value?.value ?? ''
        };

        const response = await axios.get('/piso', { params });
        pisos.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar pisos:', error);
    } finally {
        loading.value = false;
    }
};

const onPage = (event: { page: number; rows: number }): void => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadPisos();
};

const getSeverity = (value: boolean | number): 'success' | 'danger' | undefined => {
    const boolValue = value === true || value === 1 ;
    return boolValue ? 'success' : value === false || value === 0 ? 'danger' : undefined;
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadPisos();
}, 500);

watch(
    () => selectedEstadoPiso.value,
    () => {
        pagination.value.currentPage = 1;
        loadPisos();
    }
);

const editarPiso = (p: Piso): void => {
    selectedPisoId.value = p.id;
    updatePisoDialog.value = true;
};

const confirmarEliminarPiso = (p: Piso): void => {
    piso.value = p;
    deletePisoDialog.value = true;
};

const handlePisoEliminado = (): void => {
    loadPisos();
};

const handlePisoActualizado = (): void => {
    loadPisos();
};

onMounted(() => {
    loadPisos();
});
</script>

<template>
    <DataTable
        ref="dt"
        v-model:selection="selectedPisos"
        :value="pisos"
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
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} pisos"
        responsiveLayout="scroll"
    >
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-2">
                <h4 class="m-0">PISOS</h4>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-2 w-full sm:w-auto">
                    <IconField class="w-full sm:w-48 mb-2 sm:mb-0">
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText 
                            v-model="globalFilterValue" 
                            @input="onGlobalSearch" 
                            placeholder="Buscar por piso..." 
                            class="w-full" 
                        />
                    </IconField>
                    <Select
                        v-model="selectedEstadoPiso"
                        :options="estadoPisoOptions"
                        optionLabel="name"
                        placeholder="Estado del Piso"
                        class="w-full sm:w-48 mb-2 sm:mb-0"
                    />
                    <Button 
                        icon="pi pi-refresh" 
                        outlined 
                        rounded 
                        aria-label="Refresh" 
                        class="w-full sm:w-auto"
                        @click="loadPisos" 
                    />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
        <Column field="name" header="Nombre" sortable style="min-width: 100px; max-width: 150px">
            <template #body="{ data }">
                <span class="text-xs sm:text-sm">{{ data.name }}</span>
            </template>
        </Column>
        <Column field="description" header="Descripción" sortable style="min-width: 120px; max-width: 180px">
            <template #body="{ data }">
                <span class="text-xs sm:text-sm">{{ data.description }}</span>
            </template>
        </Column>
        <Column field="creacion" header="Creación" sortable style="min-width: 100px; max-width: 150px">
            <template #body="{ data }">
                <span class="text-xs sm:text-sm">{{ data.creacion }}</span>
            </template>
        </Column>
        <Column field="actualizacion" header="Actualización" sortable style="min-width: 100px; max-width: 150px">
            <template #body="{ data }">
                <span class="text-xs sm:text-sm">{{ data.actualizacion }}</span>
            </template>
        </Column>
        <Column field="state" header="Estado" sortable style="min-width: 80px; max-width: 120px">
            <template #body="{ data }">
                <Tag 
                    :value="data.state ? 'Activo' : 'Inactivo'" 
                    :severity="getSeverity(data.state)" 
                    class="text-xs sm:text-sm" 
                />
            </template>
        </Column>
        <Column field="accions" header="Acciones" :exportable="false" style="min-width: 100px; max-width: 130px">
            <template #body="slotProps">
                <div class="flex gap-1 sm:gap-2">
                    <Button 
                        icon="pi pi-pencil" 
                        outlined 
                        rounded 
                        class="w-8 h-8 sm:w-10 sm:h-10" 
                        @click="editarPiso(slotProps.data)" 
                    />
                    <Button 
                        icon="pi pi-trash" 
                        outlined 
                        rounded 
                        severity="danger" 
                        class="w-8 h-8 sm:w-10 sm:h-10"
                        @click="confirmarEliminarPiso(slotProps.data)" 
                    />
                </div>
            </template>
        </Column>
    </DataTable>

    <DeletePiso
        v-model:visible="deletePisoDialog"
        :piso="piso"
        @deleted="handlePisoEliminado"
    />
    <UpdatePiso
        v-model:visible="updatePisoDialog"
        :pisoId="selectedPisoId"
        @updated="handlePisoActualizado"
    />
</template>

<style scoped>
@media (max-width: 640px) {
    .responsive-table {
        font-size: 0.875rem;
    }
}

@media (min-width: 641px) and (max-width: 1024px) {
    .responsive-table {
        font-size: 0.9rem;
    }
}

@media (min-width: 1025px) {
    .responsive-table {
        font-size: 1rem;
    }
}
</style>