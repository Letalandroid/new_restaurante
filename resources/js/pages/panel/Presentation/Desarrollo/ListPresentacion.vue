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
import DeletePresentacion from './DeletePresentacion.vue';
import UpdatePresentacion from './UpdatePresentacion.vue';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';

// Tipos e interfaces
interface Presentacion {
    id: number;
    name: string;
    description: string;
    creacion?: string;
    actualizacion?: string;
    state: boolean | number | string;
}

const toast = useToast();

interface Pagination {
    currentPage: number;
    perPage: number;
    total: number;
}

interface EstadoOption {
    name: string;
    value: string | number | '';
}

interface Props {
    refresh: number;
}

interface Filters {
    state: number | string | null;
    online: number | string | null;
}

interface Meta {
    current_page: number;
    total: number;
}

interface PresentacionResponse {
    data: Presentacion[];
    meta: Meta;
}

// Refs y variables
const dt = ref();
const presentaciones = ref<Presentacion[]>([]);
const selectedPresentaciones = ref<Presentacion[]>();
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const deletePresentacionDialog = ref<boolean>(false);
const presentacion = ref<Presentacion | null>(null);
const selectedPresentacionId = ref<number | null>(null);
const selectedEstadoPresentacion = ref<EstadoOption | null>(null);
const updatePresentacionDialog = ref<boolean>(false);
const currentPage = ref<number>(1);

const props = defineProps<Props>();

watch(() => props.refresh, () => {
    loadPresentacion();
});

watch(() => selectedEstadoPresentacion.value, () => {
    currentPage.value = 1;
    loadPresentacion();
});

function editPresentacion(p: Presentacion) {
    selectedPresentacionId.value = p.id;
    updatePresentacionDialog.value = true;
}

const estadoPresentacionOptions = ref<EstadoOption[]>([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 1 },
    { name: 'INACTIVOS', value: 0 },
]);

function handlePresentacionUpdated() {
    loadPresentacion();
}

function confirmDeletePresentacion(selected: Presentacion) {
    presentacion.value = selected;
    deletePresentacionDialog.value = true;
}

const pagination = ref<Pagination>({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const filters = ref<Filters>({
    state: null,
    online: null
});

function handlePresentacionDeleted() {
    loadPresentacion();
}

const loadPresentacion = async (): Promise<void> => {
    loading.value = true;
    try {
        const params: Record<string, any> = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            state: filters.value.state,
        };
        if (selectedEstadoPresentacion.value !== null && selectedEstadoPresentacion.value.value !== '') {
            params.state = selectedEstadoPresentacion.value.value;
        }

        const response = await axios.get<PresentacionResponse>('/presentacion', { params });

        presentaciones.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar presentaciones:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar las presentaciones', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const onPage = (event: any) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadPresentacion();
};

const getSeverity = (value: boolean | number): 'success' | 'danger' | undefined => {
    const boolValue = value === true || value === 1 ;
    return boolValue ? 'success' : value === false || value === 0 ? 'danger' : undefined;
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadPresentacion();
}, 500);

onMounted(() => {
    loadPresentacion();
});
</script>

<template>
    <div class="w-full overflow-x-auto">
        <DataTable 
            ref="dt" 
            v-model:selection="selectedPresentaciones" 
            :value="presentaciones" 
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
            currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} presentaciones"
            class="min-w-full text-sm sm:text-base"
        >

            <!-- HEADER -->
            <template #header>
                <div class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4 items-start sm:items-center justify-between w-full">
                    <h4 class="m-0">PRESENTACIONES</h4>
                    <div class="flex flex-col sm:flex-row flex-wrap gap-2 w-full sm:w-auto">
                        <IconField class="w-full sm:w-auto">
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText 
                                v-model="globalFilterValue" 
                                @input="onGlobalSearch" 
                                placeholder="Buscar presentación..." 
                                class="w-full sm:w-64"
                            />
                        </IconField>

                        <Select 
                            v-model="selectedEstadoPresentacion" 
                            :options="estadoPresentacionOptions" 
                            optionLabel="name"
                            placeholder="Estado" 
                            class="w-full sm:w-auto"
                        />

                        <Button 
                            icon="pi pi-refresh" 
                            outlined 
                            rounded 
                            aria-label="Refresh" 
                            @click="loadPresentacion" 
                            class="w-full sm:w-auto"
                        />
                    </div>
                </div>
            </template>

            <!-- COLUMNAS -->
            <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
            <Column field="name" header="Nombre" sortable style="min-width: 12rem" />
            <Column field="description" header="Descripción" sortable style="min-width: 15rem" />
            <Column field="creacion" header="Creación" sortable style="min-width: 13rem" />
            <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem" />

            <Column field="state" header="Estado" sortable style="min-width: 6rem">
                <template #body="{ data }">
                    <Tag :value="data.state ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.state)" />
                </template>
            </Column>

            <Column field="actions" header="Acciones" :exportable="false" style="min-width: 8rem">
                <template #body="slotProps">
                    <div class="flex flex-wrap justify-center gap-2">
                        <Button 
                            icon="pi pi-pencil" 
                            outlined 
                            rounded 
                            class="mr-0 sm:mr-2" 
                            @click="editPresentacion(slotProps.data)" 
                        />
                        <Button 
                            icon="pi pi-trash" 
                            outlined 
                            rounded 
                            severity="danger"
                            @click="confirmDeletePresentacion(slotProps.data)" 
                        />
                    </div>
                </template>
            </Column>
        </DataTable>
    </div>

    <!-- MODALES -->
    <DeletePresentacion 
        v-model:visible="deletePresentacionDialog" 
        :presentacion="presentacion" 
        @deleted="handlePresentacionDeleted" 
    />
    <UpdatePresentacion 
        v-model:visible="updatePresentacionDialog" 
        :presentacionId="selectedPresentacionId"
        @updated="handlePresentacionUpdated" 
    />
</template>
