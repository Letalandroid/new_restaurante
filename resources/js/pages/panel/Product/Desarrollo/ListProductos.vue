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
import DeleteProducto from './DeleteProductos.vue';
import UpdateProducto from './UpdateProductos.vue';
import MultiSelect from 'primevue/multiselect';

// Tipos
interface Producto {
    id: number;
    name: string;
    details?: string;
    Categoria_name?: string;
    Almacen_name?: string;
    creacion?: string;
    actualizacion?: string;
    state?: boolean | number;
}

interface Pagination {
    currentPage: number;
    perPage: number;
    total: number;
}

interface ColumnOption {
    field: string;
    header: string;
}

interface EstadoProducto {
    name: string;
    value: number | string;
}

const dt = ref<any>(null);
const productos = ref<Producto[]>([]);
const selectedProductos = ref<Producto[] | null>(null);
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const deleteProductoDialog = ref<boolean>(false);
const updateProductoDialog = ref<boolean>(false);
const selectedProductoId = ref<number | null>(null);
const producto = ref<Producto | null>(null);
const currentPage = ref<number>(1);
const selectedColumns = ref<ColumnOption[]>([]);
const selectedCategory = ref<{ value?: number } | null>(null);
const selectedAlmacen = ref<{ value?: number } | null>(null);
const selectedEstadoProducto = ref<EstadoProducto | null>(null);

const pagination = ref<Pagination>({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const estadoProductoOptions = ref<EstadoProducto[]>([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 1 },
    { name: 'INACTIVOS', value: 0 },
]);

const isColumnSelected = (fieldName: string) => {
    return selectedColumns.value.some(col => col.field === fieldName);
};

const optionalColumns = ref<ColumnOption[]>([
    { field: 'details', header: 'Detalles' }
]);

const loadProductos = async () => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            category: selectedCategory?.value?.value,
            almacen: selectedAlmacen?.value?.value,
            state: selectedEstadoProducto.value?.value ?? '',
        };
        const response = await axios.get('/producto', { params });
        productos.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar productos:', error);
    } finally {
        loading.value = false;
    }
};

const props = defineProps<{
    refresh: number;
}>();

watch(() => props.refresh, loadProductos);
watch(() => selectedEstadoProducto.value, () => {
    currentPage.value = 1;
    loadProductos();
});

const onPage = (event: { page: number; rows: number }) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadProductos();
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadProductos();
}, 500);

const getSeverity = (value: boolean | number) => {
    return value ? 'success' : 'danger';
};

const editarProducto = (prod: Producto) => {
    selectedProductoId.value = prod.id ?? null;
    updateProductoDialog.value = true;
};

const confirmarDeleteProducto = (prod: Producto) => {
    producto.value = prod;
    deleteProductoDialog.value = true;
};

function handleProductoUpdated() {
    loadProductos();
}

function handleProductoDeleted() {
    loadProductos();
}

onMounted(loadProductos);
</script>

<template>
    <DataTable
        ref="dt"
        v-model:selection="selectedProductos"
        :value="productos"
        :paginator="true"
        :rows="pagination.perPage"
        :totalRecords="pagination.total"
        :loading="loading"
        :lazy="true"
        @page="onPage"
        :rowsPerPageOptions="[15, 20, 25]"
        dataKey="id"
        scrollable scrollHeight="574px"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} productos"
        class="w-full"
    >
        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">PRODUCTOS</h4>
                <div class="flex flex-wrap gap-2">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar producto..." />
                    </IconField>
                    <MultiSelect v-model="selectedColumns" :options="optionalColumns" optionLabel="header"
                        display="chip" placeholder="Seleccionar" />
                    <Select v-model="selectedEstadoProducto" :options="estadoProductoOptions" optionLabel="name" placeholder="Estado" />
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadProductos" />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" />
        <Column field="name" header="Nombre" sortable style="min-width: 20rem" />
        <Column v-if="isColumnSelected('details')" field="details" header="Detalles" sortable
            style="min-width: 41rem">
        </Column>
        <Column field="Categoria_name" header="Categoría" sortable style="min-width: 15rem" />
        <Column field="Almacen_name" header="Almacén" sortable style="min-width: 15rem"/>
        <Column field="creacion" header="Creación" sortable style="min-width: 13rem" />
        <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem"/>
        <Column field="state" header="Estado" sortable>
            <template #body="{ data }">
                <Tag :value="data.state ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.state)" />
            </template>
        </Column>
        <Column field="accions" header="Acciones" :exportable="false" style="min-width: 8rem">
            <template #body="{ data }">
                <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editarProducto(data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmarDeleteProducto(data)" />
            </template>
        </Column>
    </DataTable>

    <DeleteProducto
        v-model:visible="deleteProductoDialog"
        :producto="producto"
        @deleted="handleProductoDeleted"
    />
    <UpdateProducto
        v-model:visible="updateProductoDialog"
        :productoId="selectedProductoId"
        @updated="handleProductoUpdated"
    />
</template>
