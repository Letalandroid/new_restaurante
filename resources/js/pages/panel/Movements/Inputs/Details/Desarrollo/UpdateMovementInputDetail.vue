<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import InputDate from 'primevue/datepicker';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import RadioButton from 'primevue/radiobutton';
import { useToast } from 'primevue/usetoast';

// Tipos de datos
interface Input {
    id: number;
    name: string;
    priceBuy?: string;
    priceSale?: string;
    quantityUnitMeasure?: string;
    idAlmacen?: number;
    almacen_name?: string;
    description?: string;
    unitMeasure?: string;
    state?: boolean;
}

interface Product {
    id: number;
    name: string;
    quantityUnitMeasure?: string;
    unitMeasure?: string;
    stock?: number;
}

interface MovementInputDetail {
    id: number;
    idMovementInput: number;
    idInput: number | null;
    idProduct: number | null;
    quantity: string;
    totalPrice: string;
    priceUnit: string;
    batch: string;
    expirationDate: string;
    input?: Input | null;
    product?: Product | null;
}

const inputDialog = ref(false);

const props = defineProps<{
    visible: boolean;
    movementInputId: number | null;
    detailId: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

const toast = useToast();
const serverErrors = ref<Record<string, string[]>>({});
const loading = ref(false);

const dialogVisible = ref(props.visible);
watch(
    () => props.visible,
    (val) => (dialogVisible.value = val),
);
watch(dialogVisible, (val) => emit('update:visible', val));

const movementInput = ref({
    batch: '',
    idMovementInput: '',
    quantity: 0,
    totalPrice: 0,
    unitPrice: '',
    expirationDate: null as Date | null,
});

// Variables para items
const searchTerm = ref('');
const showResults = ref(false);
const itemsOptions = ref<(Input | Product)[]>([]);
const timeoutId = ref<number | null>(null);
const selectedItem = ref<Input | Product | null>(null);
const itemType = ref<'insumo' | 'producto'>('insumo');

const clearSearch = () => {
    searchTerm.value = '';
};

watch(
    () => props.visible,
    async (val) => {
        if (val && props.movementInputId) {
            await fetchMovementInput();
        }
    },
);

const fetchMovementInput = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get(`/items/movimientos/detalle/${props.movementInputId}`);

        // Buscar solo el detalle que quieres editar
        const detail = data.data.find((item: MovementInputDetail) => item.id === props.detailId);

        if (detail) {
            movementInput.value = {
                batch: detail.batch,
                quantity: parseFloat(detail.quantity),
                expirationDate: detail.expirationDate ? new Date(detail.expirationDate) : null,
                totalPrice: parseFloat(detail.totalPrice),
                unitPrice: detail.priceUnit,
                idMovementInput: detail.idMovementInput.toString(),
            };

            // Determinar el tipo de item y cargar datos
            if (detail.input) {
                selectedItem.value = detail.input;
                searchTerm.value = detail.input.name;
                itemType.value = 'insumo';
            } else if (detail.product) {
                selectedItem.value = detail.product;
                searchTerm.value = detail.product.name;
                itemType.value = 'producto';
            }

            if (detail.expirationDate) {
                const parts = detail.expirationDate.split('-');
                const day = parseInt(parts[0], 10);
                const month = parseInt(parts[1], 10) - 1;
                const year = parseInt(parts[2], 10);
                movementInput.value.expirationDate = new Date(year, month, day);
            } else {
                movementInput.value.expirationDate = null;
            }
        }
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar los datos del item',
            life: 3000,
        });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

// Watcher para calcular el precio unitario
watch([() => movementInput.value.quantity, () => movementInput.value.totalPrice], () => {
    if (movementInput.value.quantity && movementInput.value.totalPrice) {
        movementInput.value.unitPrice = (movementInput.value.totalPrice / movementInput.value.quantity).toFixed(2);
    }
});

// Función de búsqueda para items (insumos o productos)
const handleSearch = async () => {
    if (timeoutId.value) clearTimeout(timeoutId.value);
    showResults.value = true;
    timeoutId.value = setTimeout(async () => {
        try {
            if (searchTerm.value.trim()) {
                let response;
                
                if (itemType.value === 'insumo') {
                    // Búsqueda de insumos
                    response = await axios.get('/insumo', {
                        params: {
                            search: searchTerm.value,
                            state: 1
                        },
                    });
                } else {
                    // Búsqueda de productos
                    response = await axios.get('/producto', {
                        params: {
                            search: searchTerm.value
                        },
                    });
                }

                if (response.data && Array.isArray(response.data.data)) {
                    itemsOptions.value = response.data.data;
                }
            } else {
                itemsOptions.value = [];
            }
        } catch (error) {
            console.error('Error en la búsqueda:', error);
        }
    }, 300);
};

// Función para seleccionar un item de la lista
const selectItem = (item: Input | Product) => {
    selectedItem.value = item;
    searchTerm.value = item.name;
    itemsOptions.value = [];
    showResults.value = false;
};

// Función para limpiar la selección
const clearSelection = () => {
    selectedItem.value = null;
    searchTerm.value = '';
    itemsOptions.value = [];
    showResults.value = false;
};

// Función cuando cambia el tipo de item
const onItemTypeChange = () => {
    clearSelection();
};

function hideDialog() {
    dialogVisible.value = false;
    inputDialog.value = false;

    clearSearch();
    selectedItem.value = null;
    showResults.value = false;
    itemType.value = 'insumo'; // Resetear a insumo por defecto
}

const saveMovement = async () => {
    loading.value = true;
    try {
        if (!movementInput.value.batch || !movementInput.value.quantity || !movementInput.value.expirationDate || !movementInput.value.totalPrice || !movementInput.value.unitPrice || !selectedItem.value) {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Todos los campos son obligatorios.',
                life: 3000,
            });
            return;
        }

        const formData: any = {
            idMovementInput: movementInput.value.idMovementInput,
            quantity: movementInput.value.quantity,
            totalPrice: movementInput.value.totalPrice,
            priceUnit: movementInput.value.unitPrice,
            batch: movementInput.value.batch,
            expirationDate: movementInput.value.expirationDate
                ? movementInput.value.expirationDate.toISOString().split('T')[0]
                : null,
        };

        // Asignar SOLO el campo correspondiente según el tipo
        if (itemType.value === 'insumo') {
            formData.idInput = selectedItem.value.id;
            delete formData.idProduct;
        } else {
            formData.idProduct = selectedItem.value.id;
            delete formData.idInput;
        }

        console.log('Datos a actualizar (limpios):', formData);
        console.log('Tipo de item:', itemType.value);

        const response = await axios.put(`/items/movimientos/detalle/${props.detailId}`, formData);

        if (response.data.state) {
            toast.add({
                severity: 'success',
                summary: 'Éxito',
                detail: `${itemType.value === 'insumo' ? 'Insumo' : 'Producto'} actualizado correctamente.`,
                life: 3000,
            });
            emit('updated');
            hideDialog();

            // Solo actualizar kardex si es un insumo y hay idInput válido
            setTimeout(() => {
                updateKardex().catch((err) => 
                    console.error('Error en kardex background:', err)
                );
            }, 100);
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo actualizar el detalle del movimiento.',
                life: 3000,
            });
        }
    } catch (error: any) {
        // Manejo de errores mejorado
        if (error.response && error.response.data && error.response.data.message) {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: error.response.data.message,
                life: 3000,
            });
        } else if (error.response && error.response.data && error.response.data.errors) {
            const errors = error.response.data.errors;
            const errorMessages = Object.values(errors).flat().join(', ');
            toast.add({
                severity: 'error',
                summary: 'Error de validación',
                detail: errorMessages,
                life: 5000,
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Ocurrió un error al actualizar el detalle del movimiento.',
                life: 3000,
            });
        }
        console.error('Error completo:', error);
    } finally {
        loading.value = false;
    }
};

const updateKardex = async () => {
    try {
        const idMovementInput = movementInput.value.idMovementInput;
        const idInput = itemType.value === 'insumo' ? selectedItem.value?.id : null;
        const idProduct = itemType.value === 'producto' ? selectedItem.value?.id : null;

        let url = `/items/karde?idMovementInput=${idMovementInput}`;
        
        if (itemType.value === 'insumo' && idInput) {
            url += `&idInput=${idInput}`;
        } else if (itemType.value === 'producto' && idProduct) {
            url += `&idProduct=${idProduct}`;
        } else {
            console.warn('No se puede actualizar kardex: tipo de item no válido');
            return;
        }

        const response = await axios.get(url);

        if (!response.data.data || response.data.data.length === 0) {
            console.warn('No se encontró registro en kardex para actualizar');
            return;
        }

        const kardexId = response.data.data[0].id;

        if (!kardexId) {
            console.error('ID del kardex no encontrado');
            return;
        }

        const formDataKardex: any = {
            idMovementInput: idMovementInput,
            totalPrice: movementInput.value.totalPrice,
        };

        if (itemType.value === 'insumo') {
            formDataKardex.idInput = idInput;
        } else if (itemType.value === 'producto') {
            formDataKardex.idProduct = idProduct;
        }

        console.log('Actualizando kardex:', formDataKardex);

        await axios.put(`/items/karde/${kardexId}`, formDataKardex);
        console.log('Kardex actualizado correctamente');
    } catch (error) {
        console.error('Error al actualizar kardex:', error);
    }
};
</script>



<template>
    <Dialog
        v-model:visible="dialogVisible"
        header="Editar Movimiento de Item"
        modal
        :closable="true"
        :closeOnEscape="true"
        :style="{ width: '500px' }"
        @hide="hideDialog"
    >
        <div class="flex flex-col gap-6">
            <!-- Selector de Tipo -->
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12">
                    <label class="mb-2 block font-bold">Tipo de Item <span class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <div class="flex items-center">
                            <RadioButton 
                                v-model="itemType" 
                                inputId="edit-insumo" 
                                name="editItemType" 
                                value="insumo" 
                                @change="onItemTypeChange"
                                :disabled="true"
                            />
                            <label for="edit-insumo" class="ml-2">Insumo</label>
                        </div>
                        <div class="flex items-center">
                            <RadioButton 
                                v-model="itemType" 
                                inputId="edit-producto" 
                                name="editItemType" 
                                value="producto" 
                                @change="onItemTypeChange"
                                :disabled="true"
                            />
                            <label for="edit-producto" class="ml-2">Producto</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Búsqueda de Items -->
            <div class="relative w-full">
                <InputText 
                    v-model="searchTerm" 
                    @input="handleSearch" 
                    :placeholder="itemType === 'insumo' ? 'Buscar insumo...' : 'Buscar producto...'" 
                    class="w-full"
                    :disabled="true" 
                />

                <!-- Resultados del autocompletado -->
                <div
                    v-if="showResults && itemsOptions.length > 0"
                    class="absolute z-50 mt-2 max-h-60 w-full overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-2 shadow-lg"
                >
                    <div
                        v-for="item in itemsOptions"
                        :key="item.id"
                        @click="selectItem(item)"
                        class="cursor-pointer rounded p-2 hover:bg-primary-100 hover:text-primary-800"
                    >
                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-800">{{ item.id }} - {{ item.name }}</span>
                            <span class="text-sm text-gray-600">
                                {{ item.quantityUnitMeasure }} {{ item.unitMeasure }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Mensaje de no encontrados -->
                <div
                    v-if="showResults && searchTerm && itemsOptions.length === 0"
                    class="absolute z-50 mt-2 w-full rounded-lg border border-gray-200 bg-gray-50 p-4 text-center text-gray-600 shadow-lg"
                >
                    No se encontraron resultados
                </div>
            </div>

            <!-- Item seleccionado -->
            <div v-if="selectedItem" class="mt-4 flex items-center justify-between rounded-lg bg-primary-50 p-3">
                <span class="font-medium text-primary-800">
                    {{ selectedItem.id }} - {{ selectedItem.name }} - {{ selectedItem.quantityUnitMeasure }} {{ selectedItem.unitMeasure }}
                </span>
            </div>

            <!-- Número de Lote -->
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12">
                    <label class="mb-2 block font-bold">Número de Lote <span class="text-red-500">*</span></label>
                    <InputText v-model="movementInput.batch" required maxlength="20" fluid :class="{ 'p-invalid': serverErrors.batch }" />
                    <small v-if="serverErrors.batch" class="text-red-500">{{ serverErrors.batch[0] }}</small>
                </div>
            </div>

            <!-- Fecha de Vencimiento -->
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-6">
                    <label class="mb-2 block font-bold">Fecha de Vencimiento <span class="text-red-500">*</span></label>
                    <InputDate v-model="movementInput.expirationDate" required class="w-full" dateFormat="dd/mm/yy" showIcon/>
                    <small v-if="serverErrors.expirationDate" class="text-red-500">{{ serverErrors.expirationDate[0] }}</small>
                </div>
            </div>

            <!-- Cantidad -->
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12">
                    <label class="mb-2 block font-bold">Cantidad <span class="text-red-500">*</span></label>
                    <InputNumber v-model="movementInput.quantity" required :min="1" class="w-full" :class="{ 'p-invalid': serverErrors.quantity }" />
                    <small v-if="serverErrors.quantity" class="text-red-500">{{ serverErrors.quantity[0] }}</small>
                </div>
            </div>

            <!-- Precio Total -->
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12">
                    <label class="mb-2 block font-bold">Precio Total <span class="text-red-500">*</span></label>
                    <InputNumber
                        v-model="movementInput.totalPrice"
                        required
                        :min="0"
                        mode="decimal"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.totalPrice }"
                    />
                    <small v-if="serverErrors.totalPrice" class="text-red-500">{{ serverErrors.totalPrice[0] }}</small>
                </div>
            </div>

            <!-- Precio Unitario (Deshabilitado) -->
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12">
                    <label class="mb-2 block font-bold">Precio Unitario</label>
                    <InputText v-model="movementInput.unitPrice" disabled fluid />
                </div>
            </div>
        </div>
        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="saveMovement" :loading="loading" />
        </template>
    </Dialog>
</template>
