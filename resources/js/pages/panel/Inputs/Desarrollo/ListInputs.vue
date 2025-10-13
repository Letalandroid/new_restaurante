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
import DeleteInput from './DeleteInputs.vue';
import UpdateInput from './UpdateInputs.vue';

interface InputItem {
    id: number;
    name: string;
    priceSale?: number;
    quantityUnitMeasure?: number;
    unitMeasure?: string;
    almacen_name?: string;
    creacion?: string;
    actualizacion?: string;
    state?: boolean | number;
}

interface Pagination {
    currentPage: number;
    perPage: number;
    total: number;
}

const inputs = ref<InputItem[]>([]);
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const deleteInputDialog = ref<boolean>(false);
const updateInputDialog = ref<boolean>(false);
const selectedInputId = ref<number | null>(null);
const input = ref<InputItem | null>(null);
const currentPage = ref<number>(1);
const selectedInputs = ref<InputItem[] | null>(null);
//const selectedColumns = ref<{ field: string; header: string }[]>([]);
const selectedAlmacen = ref<{ name?: string; value?: string } | null>(null);
const selectedEstadoInput = ref<{ name: string; value: string | number | boolean } | null>(null);

const pagination = ref<Pagination>({
    currentPage: 1,
    perPage: 15,
    total: 0
});
const refreshCount = ref<number>(0);

const estadoInputOptions = ref<{ name: string; value: string | number | boolean }[]>([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 1 },
    { name: 'INACTIVOS', value: 0 },
]);



const formatCurrency = (value: number | string | null | undefined): string => {
    if (value != null) {
        return 'S/. ' + parseFloat(value.toString()).toFixed(2);
    }
    return '';
};

const loadInputs = async (): Promise<void> => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            almacen: selectedAlmacen.value?.value,
            state: selectedEstadoInput.value?.value ?? '',
        };
        const response = await axios.get('/insumo', { params });
        inputs.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar insumos:', error);
    } finally {
        loading.value = false;
    }
};

const props = defineProps<{
    refresh: number;
}>();

watch(refreshCount, loadInputs);
watch(() => props.refresh, loadInputs);
watch(() => selectedEstadoInput.value, () => {
    currentPage.value = 1;
    loadInputs();
});
watch(deleteInputDialog, (val) => {
    console.log('Dialogo eliminar visible:', val);
});

const onPage = (event: any): void => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadInputs();
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadInputs();
}, 500);

const getSeverity = (value: boolean | number | undefined): string => {
    return value ? 'success' : 'danger';
};

const editarInput = (prod: InputItem): void => {
    selectedInputId.value = prod.id ?? null;
    updateInputDialog.value = true;
};

const confirmarDeleteInput = (prod: InputItem): void => {
    input.value = prod;
    deleteInputDialog.value = true;
};

function handleInputUpdated(): void {
    loadInputs();
}

function handleInputDeleted(): void {
    loadInputs();
}

onMounted(loadInputs);
</script>

<template>
    <DataTable ref="dt" v-model:selection="selectedInputs" :value="inputs" dataKey="id" :paginator="true"
        :rows="pagination.perPage" :totalRecords="pagination.total" :loading="loading" :lazy="true" @page="onPage"
        :rowsPerPageOptions="[15, 20, 25]" scrollable scrollHeight="574px"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} Insumos">
        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">Insumos</h4>
                <div class="flex flex-wrap gap-2">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar insumo..." />
                    </IconField>
                    
                    <Select v-model="selectedEstadoInput" :options="estadoInputOptions" optionLabel="name" placeholder="Estado" />
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadInputs" />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>

        <Column field="name" header="Nombre" sortable style="min-width: 10rem" />

        <Column field="priceSale" header="Precio Venta" sortable style="min-width: 10rem">
            <template #body="{ data }">
                {{ formatCurrency(data.priceSale) }}
            </template>
        </Column>

        <Column field="quantityUnitMeasure" header="Cantidad" sortable style="min-width: 10rem" />

        <Column field="unitMeasure" header="Unidad de Medida" sortable style="min-width: 10rem">
            <template #body="{ data }">
                {{ data.unitMeasure }}
            </template>
        </Column>

        <Column field="almacen_name" header="Almacen" sortable style="min-width: 10rem" />
        <Column field="creacion" header="Creación" sortable style="min-width: 13rem" />
        <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem" />
        <Column field="state" header="Estado" sortable>
            <template #body="{ data }">
                <Tag :value="data.state ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.state)" />
            </template>
        </Column>
        <Column field="accions" header="Acciones" :exportable="false" style="min-width: 8rem">
            <template #body="{ data }">
                <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editarInput(data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmarDeleteInput(data)" />
            </template>
        </Column>
    </DataTable>

    <DeleteInput
        v-model:visible="deleteInputDialog"
        :input="input"
        @deleted="handleInputDeleted"
    />
    <UpdateInput
        v-model:visible="updateInputDialog"
        :inputId="selectedInputId"
        @updated="handleInputUpdated"
    />
</template>
