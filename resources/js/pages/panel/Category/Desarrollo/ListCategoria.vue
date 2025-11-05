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
import DeleteCategoria from './DeleteCategoria.vue';
import UpdateCategoria from './UpdateCategoria.vue';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';

interface Categoria {
    id: number;
    name: string;
    state: boolean | number | string;
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
    value: string | number | boolean;
}

const dt = ref();
const categorias = ref<Categoria[]>([]);
const selectedCategorias = ref<Categoria[] | null>(null);
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const deleteCategoriaDialog = ref<boolean>(false);
const categoria = ref<Categoria | null>(null);
const selectedCategoriaId = ref<number | null>(null);
const selectedEstadoCategoria = ref<EstadoOption | null>(null);
const updateCategoriaDialog = ref<boolean>(false);
const currentPage = ref<number>(1);

const toast = useToast();

const props = defineProps<{
    refresh: number;
}>();

watch(() => props.refresh, () => {
    loadCategoria();
});

watch(() => selectedEstadoCategoria.value, () => {
    currentPage.value = 1;
    loadCategoria();
});

function editCategoria(categoriaItem: Categoria): void {
    selectedCategoriaId.value = categoriaItem.id;
    updateCategoriaDialog.value = true;
}

const estadoCategoriaOptions = ref<EstadoOption[]>([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 1 },
    { name: 'INACTIVOS', value: 0 },
]);

function handleCategoriaUpdated(): void {
    loadCategoria();
}

function confirmDeleteCategoria(selected: Categoria): void {
    categoria.value = selected;
    deleteCategoriaDialog.value = true;
}

const pagination = ref<Pagination>({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const filters = ref({
    state: null as string | number | null,
    online: null as string | number | null
});

function handleCategoriaDeleted(): void {
    loadCategoria();
}

const loadCategoria = async (): Promise<void> => {
    loading.value = true;
    try {
        const params: Record<string, any> = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            state: filters.value.state,
        };
        if (selectedEstadoCategoria.value !== null && selectedEstadoCategoria.value.value !== '') {
            params.state = selectedEstadoCategoria.value.value;
        }

        const response = await axios.get('/categoria', { params });

        categorias.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error: any) {
        console.error('Error al cargar categoría:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar las categorías', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const onPage = (event: { page: number; rows: number }): void => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadCategoria();
};

const getSeverity = (value: boolean | number): 'success' | 'danger' | undefined => {
    const boolValue = value === true || value === 1 ;
    return boolValue ? 'success' : value === false || value === 0 ? 'danger' : undefined;
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadCategoria();
}, 500);

onMounted(() => {
    loadCategoria();
});
</script>

<template>
    <DataTable 
        ref="dt" 
        v-model:selection="selectedCategorias" 
        :value="categorias" 
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
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} categorías"
        class="w-full overflow-x-auto"
    >

        <!-- ENCABEZADO RESPONSIVE -->
        <template #header>
            <div class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-4 items-start sm:items-center justify-between w-full">
                <h4 class="m-0">CATEGORÍAS</h4>

                <div class="flex flex-col sm:flex-row flex-wrap gap-2 w-full sm:w-auto">
                    <div class="flex items-center w-full sm:w-auto">
                        <IconField class="flex-1 sm:flex-initial w-full sm:w-64">
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText 
                                v-model="globalFilterValue" 
                                @input="onGlobalSearch" 
                                placeholder="Buscar categoría..." 
                                class="w-full"
                            />
                        </IconField>
                    </div>

                    <Select 
                        v-model="selectedEstadoCategoria" 
                        :options="estadoCategoriaOptions" 
                        optionLabel="name"
                        placeholder="Estado" 
                        class="w-full sm:w-auto min-w-[160px]" 
                    />

                    <Button 
                        icon="pi pi-refresh" 
                        outlined 
                        rounded 
                        aria-label="Refresh" 
                        @click="loadCategoria"
                        class="w-full sm:w-auto"
                    />
                </div>
            </div>
        </template>

        <!-- COLUMNAS -->
        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
        <Column field="name" header="Nombre" sortable style="min-width: 13rem"></Column>
        <Column field="creacion" header="Creación" sortable style="min-width: 13rem"></Column>
        <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem"></Column>

        <Column field="state" header="Estado" sortable style="min-width: 4rem">
            <template #body="{ data }">
                <Tag 
                    :value="data.state ? 'Activo' : 'Inactivo'" 
                    :severity="getSeverity(data.state)" 
                    class="text-xs sm:text-sm"
                />
            </template>
        </Column>

        <Column field="accions" header="Acciones" :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
                <div class="flex gap-2 justify-center sm:justify-start">
                    <Button 
                        icon="pi pi-pencil" 
                        outlined 
                        rounded 
                        class="mr-0 sm:mr-2"
                        @click="editCategoria(slotProps.data)" 
                    />
                    <Button 
                        icon="pi pi-trash" 
                        outlined 
                        rounded 
                        severity="danger" 
                        @click="confirmDeleteCategoria(slotProps.data)" 
                    />
                </div>
            </template>
        </Column>
    </DataTable>

    <!-- MODALES -->
    <DeleteCategoria
        v-model:visible="deleteCategoriaDialog"
        :categoria="categoria"
        @deleted="handleCategoriaDeleted"
    />

    <UpdateCategoria
        v-model:visible="updateCategoriaDialog"
        :categoriaId="selectedCategoriaId"
        @updated="handleCategoriaUpdated"
    />
</template>
