<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'; // Inertia.js hook
import axios from 'axios';
import { debounce } from 'lodash';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import { onMounted, ref, watch } from 'vue';
import DeleteMovementInput from './DeleteMovementInputDetail.vue';
import UpdateMovementInput from './UpdateMovementInputDetail.vue';

// Tipos de datos para movimientos e insumos
interface Input {
    id: number;
    name: string;
    quantityUnitMeasure?: string;
    unitMeasure?: string;
}

interface MovementInput {
    id: number;
    idMovementInput: number;
    quantity: string | number;
    input: Input;
    priceUnit: string | number;
    batch: string;
    totalPrice: string | number;
}

const movementInputs = ref<MovementInput[]>([]);
const loading = ref(false);
const globalFilterValue = ref('');
const deleteMovementInputDialog = ref(false);
const updateMovementInputDialog = ref(false);
const selectedMovementInputId = ref<number | null>(null);
const selectedDetailId = ref<number | null>(null);
const movementInput = ref<MovementInput | null>(null);
const currentPage = ref(1);
const selectedSupplier = ref<any>(null);
const selectedMovementInputs = ref<MovementInput[] | null>(null);
const selectedEstadoMovementInput = ref<any>(null);
const pagination = ref({
    currentPage: 1,
    perPage: 15,
    total: 0,
});

const { id } = usePage().props;

const refreshCount = ref(0); // Variable que se incrementa cuando se agrega un movimiento




const subtotal = ref(0);
const igv = ref(0);
const total = ref(0);

const loadMovementInputs = async () => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            supplier: selectedSupplier?.value,
            state: selectedEstadoMovementInput.value?.value ?? '',
        };

        // Realizamos la solicitud a la API con el 'id' en la URL
        const response = await axios.get(`/insumos/movimientos/detalle/${id}`, { params });

        // Asignamos los datos de la respuesta a la variable reactiva
        movementInputs.value = response.data.data;

        // Aseguramos que los valores de subtotal, total_igv y total sean números
        subtotal.value = parseFloat(response.data.subtotal.replace(/,/g, '')); // Remover las comas y convertir a número
        igv.value = parseFloat(response.data.total_igv.replace(/,/g, ''));
        total.value = parseFloat(response.data.total.replace(/,/g, ''));

        // Actualizamos la paginación
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar los movimientos de entrada:', error);
    } finally {
        loading.value = false;
    }
};

const formatCurrency = (value: number) => {
    if (value != null) {
        // Formatear el número como moneda con 2 decimales y símbolo "S/."
        return 'S/. ' + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
    return '';
};

const props = defineProps<{
    refresh: number;
}>();
// Recarga la tabla cuando `refreshCount` cambia
watch(refreshCount, loadMovementInputs);
watch(() => props.refresh, loadMovementInputs);
watch(
    () => selectedEstadoMovementInput.value,
    () => {
        currentPage.value = 1;
        loadMovementInputs();
    },
);

const onPage = (event: { page: number; rows: number }) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadMovementInputs();
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadMovementInputs();
}, 500);

const editarMovementInput = (movement: MovementInput) => {
    selectedMovementInputId.value = movement.idMovementInput;
    selectedDetailId.value = movement.id; // <-- Nuevo ref para el detalle
    updateMovementInputDialog.value = true;
};

const confirmarDeleteMovementInput = (movement: MovementInput) => {
    movementInput.value = movement;
    console.log(movementInput.value); // Verifica si los datos están correctos
    deleteMovementInputDialog.value = true;
};

function handleMovementInputUpdated() {
    loadMovementInputs();
}

function handleMovementInputDeleted() {
    loadMovementInputs();
}

onMounted(loadMovementInputs);


// Función para formatear la cantidad
function formatQuantity(quantity: string | number) {
    const numericQuantity = parseFloat(quantity as string); // Asegurarnos de que sea un número

    // Si no es un número válido, devolvemos el valor original
    if (isNaN(numericQuantity)) {
        return quantity;
    }

    // Si la cantidad termina en .00, retorna como entero
    if (numericQuantity % 1 === 0) {
        return numericQuantity.toFixed(0); // Elimina los decimales
    } else {
        return numericQuantity.toFixed(2); // Mantiene dos decimales
    }
}
</script>

<style scoped>
/* Personalización de estilos para la tabla */
.p-datatable-footer .p-datatable-footer-cell {
    text-align: center;
}
</style>

<template>
    <DataTable
        ref="dt"
        v-model:selection="selectedMovementInputs"
        :value="movementInputs"
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
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} Insumos Comprados"
    >
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h4 class="m-0">Movimientos de Insumos</h4>
                <div class="flex flex-wrap gap-2">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar insumo..." />
                    </IconField>
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadMovementInputs" />
                </div>
            </div>
            <!-- Totals above the table with spacing -->
            <div class="totals-info" style="margin-top: 10px; text-align: right">
                <span style="margin-right: 15px"><strong>Subtotal:</strong> {{ formatCurrency(subtotal) }}</span>
                <span style="margin-right: 15px"><strong>IGV:</strong> {{ formatCurrency(igv) }}</span>
                <span><strong>Total:</strong> {{ formatCurrency(total) }}</span>
            </div>
        </template>
        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>

        <Column field="quantity" header="Cantidad" sortable style="min-width: 7rem">
            <template #body="{ data }">
                <span>{{ formatQuantity(data.quantity) }}</span>
            </template>
        </Column>

        <Column field="input.name" header="Insumo" sortable style="min-width: 7rem" />

        <Column header="Unidad" sortable style="min-width: 7rem">
            <template #body="{ data }">
                <span>{{ data.input.quantityUnitMeasure }} {{ data.input.unitMeasure }}</span>
            </template>
        </Column>

        <Column field="priceUnit" header="Precio Unitario" sortable style="min-width: 7rem" />
        <Column field="batch" header="Lote" sortable style="min-width: 7rem" />
        <Column field="totalPrice" header="Total" sortable style="min-width: 7rem" />

        <Column field="accions" header="Acciones" :exportable="false" style="min-width: 10rem">
            <template #body="{ data }">
                <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editarMovementInput(data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmarDeleteMovementInput(data)" />
            </template>
        </Column>
    </DataTable>

    <!-- Componente para eliminar movimiento de insumo -->
    <DeleteMovementInput
        v-model:visible="deleteMovementInputDialog"
        :movementInput="movementInput"
        @deleted="handleMovementInputDeleted"
    />

    <!-- Componente para actualizar movimiento de insumo -->
    <UpdateMovementInput
        v-model:visible="updateMovementInputDialog"
        :movementInputId="selectedMovementInputId"
        :detailId="selectedDetailId"
        @updated="handleMovementInputUpdated"
    />
</template>
