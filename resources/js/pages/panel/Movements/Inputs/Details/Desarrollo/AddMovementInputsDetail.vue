<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Nuevo Item" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
            <Button label="Volver" icon="pi pi-arrow-left" severity="secondary" class="mr-2" @click="goBack" />
        </template>
    </Toolbar>

    <Dialog v-model:visible="inputDialog" :style="{ width: '500px' }" header="NUEVO SERVICIO" :modal="true" @hide="onDialogHide">
        <div class="flex flex-col gap-6">
            <!-- Selector de Tipo -->
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12">
                    <label class="mb-2 block font-bold">Tipo de Item <span class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <div class="flex items-center">
                            <RadioButton 
                                v-model="itemType" 
                                inputId="insumo" 
                                name="itemType" 
                                value="insumo" 
                                @change="clearSelection"
                            />
                            <label for="insumo" class="ml-2">Insumo</label>
                        </div>
                        <div class="flex items-center">
                            <RadioButton 
                                v-model="itemType" 
                                inputId="producto" 
                                name="itemType" 
                                value="producto" 
                                @change="clearSelection"
                            />
                            <label for="producto" class="ml-2">Producto</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Búsqueda de Items -->
            <div class="relative w-full">
                <!-- InputText para búsqueda -->
                <InputText 
                    v-model="searchTerm" 
                    @input="handleSearch" 
                    :placeholder="itemType === 'insumo' ? 'Buscar insumo...' : 'Buscar producto...'" 
                    class="w-full" 
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
                <Button icon="pi pi-times" @click="clearSelection" />
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
            <Button label="Guardar" icon="pi pi-check" @click="saveMovement" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Button from 'primevue/button';
import InputDate from 'primevue/datepicker';
import Dialog from 'primevue/dialog';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import RadioButton from 'primevue/radiobutton';
import Toolbar from 'primevue/toolbar';
import { useToast } from 'primevue/usetoast';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/core';

// Interfaces
interface Item {
    id: number;
    name: string;
    quantityUnitMeasure: string;
    unitMeasure: string;
    stock?: number;
}

interface MovementInput {
    inputName: string;
    batch: string;
    quantity: number | null;
    expirationDate: Date | null;
    totalPrice: number | null;
    unitPrice: string;
}

interface ServerErrors {
    [key: string]: string[];
}
function onDialogHide(): void {
    hideDialog();
}
const { id } = usePage().props;
const toast = useToast();
const inputDialog = ref(false);

const emit = defineEmits<{
    (e: 'movementsinputAgregado'): void;
}>();

const serverErrors = ref<ServerErrors>({}); // Para manejar errores de validación
const searchTerm = ref<string>('');
const showResults = ref<boolean>(false);
const itemsOptions = ref<Item[]>([]);
const timeoutId = ref<ReturnType<typeof setTimeout> | null>(null);
const selectedItem = ref<Item | null>(null);
const itemType = ref<'insumo' | 'producto'>('insumo');

const movementInput = ref<MovementInput>({
    inputName: '', // Aquí se almacenará el nombre del insumo seleccionado
    batch: '',
    quantity: null,
    expirationDate: null,
    totalPrice: null,
    unitPrice: '',
});

// Watcher para calcular el precio unitario
watch([() => movementInput.value.quantity, () => movementInput.value.totalPrice], () => {
    if (movementInput.value.quantity && movementInput.value.totalPrice) {
        movementInput.value.unitPrice = (movementInput.value.totalPrice / movementInput.value.quantity).toFixed(2);
    }
});

// Función de búsqueda para items (insumos o productos)
const handleSearch = async (): Promise<void> => {
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
const selectItem = (item: Item): void => {
    selectedItem.value = item;
    searchTerm.value = item.name;
    itemsOptions.value = [];
    showResults.value = false;
};

// Función para limpiar la selección
const clearSelection = (): void => {
    selectedItem.value = null;
    searchTerm.value = '';
    itemsOptions.value = [];
    showResults.value = false;
};

function openNew(): void {
    inputDialog.value = true;
}

function goBack(): void {
    const url = `/insumos/movimientos`;
    router.visit(url);
}

async function fetchUserId(): Promise<number | null> {
    try {
        // Hacemos la solicitud al backend para obtener el user_id
        const { data } = await axios.get('/user-id');

        // Verificamos si la solicitud fue exitosa
        if (data.success) {
            return data.user_id; // Retornamos el user_id
        } else {
            console.error("Error al obtener el user_id");
            return null;
        }
    } catch (e) {
        console.error("Error en la solicitud:", e);
        return null;
    }
}

const saveMovement = async (): Promise<void> => {
    try {
        // Validación frontend
        if (!selectedItem.value) {
            toast.add({ 
                severity: 'error', 
                summary: 'Error', 
                detail: 'Por favor seleccione un item', 
                life: 3000 
            });
            return;
        }

        const expirationDate = new Date(movementInput.value.expirationDate!);

        // Preparar datos base
        const requestData: any = {
            idMovementInput: id,
            quantity: movementInput.value.quantity,
            totalPrice: movementInput.value.totalPrice,
            priceUnit: movementInput.value.unitPrice,
            batch: movementInput.value.batch,
            expirationDate: expirationDate,
        };

        // Asignar SOLO el campo correspondiente según el tipo (NO enviar el otro campo)
        if (itemType.value === 'insumo') {
            requestData.idInput = selectedItem.value?.id;
            // NO enviar idProduct cuando es insumo
        } else {
            requestData.idProduct = selectedItem.value?.id;
            // NO enviar idInput cuando es producto
        }

        // Enviar los datos al backend
        const response = await axios.post('/insumos/movimientos/detalle', requestData);

        if (response.data.state) {
            toast.add({ 
                severity: 'success', 
                summary: 'Éxito', 
                detail: `${itemType.value === 'insumo' ? 'Insumo' : 'Producto'} agregado correctamente al Movimiento`, 
                life: 3000 
            });
            emit('movementsinputAgregado');
            
            // Solo registrar en kardex si es un insumo
            if (itemType.value === 'insumo') {
                enviarkardexinputs(selectedItem.value!.id, movementInput.value.totalPrice!); 
            }
            
            hideDialog();
        }
    } catch (error: any) {
        // Manejo de errores mejorado
        if (error.response && error.response.data && error.response.data.message) {
            toast.add({ 
                severity: 'error', 
                summary: 'Error', 
                detail: error.response.data.message, 
                life: 3000 
            });
        } else if (error.response && error.response.data && error.response.data.errors) {
            // Mostrar errores de validación específicos
            const errors = error.response.data.errors;
            const errorMessages = Object.values(errors).flat().join(', ');
            toast.add({ 
                severity: 'error', 
                summary: 'Error de validación', 
                detail: errorMessages, 
                life: 5000 
            });
        } else {
            toast.add({ 
                severity: 'error', 
                summary: 'Error', 
                detail: 'Hubo un error al guardar los datos', 
                life: 3000 
            });
        }
        console.error('Error completo:', error);
    }
};

const enviarkardexinputs = async (idInput: number, totalPrice: number): Promise<void> => {
    const userId = await fetchUserId(); // Esperar a obtener el user_id

    if (!userId) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo obtener el user_id' });
        return; // Si no se obtiene el user_id, no continuar con el registro
    }

    try {
        // Obtener los detalles del movimiento de insumo
        const movementResponse = await axios.get(`/insumos/movimientos/detalle/${id}`);
        
        // Extraer el code y payment_type desde el movimiento
        const movementInputResponse = movementResponse.data.data[0].movementInput;
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        const code = movementInputResponse.code;
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        const payment_type = movementInputResponse.payment_type;

        // Crear los datos para el kardex
        const movementDataKardex = {
            idUser: userId,
            idInput: idInput, // Recibido como parámetro
            idMovementInput: id, // Asegúrate de que este id esté correctamente definido
            movement_type: "0", // Asegúrate de que movement_type esté presente en el movimiento
            totalPrice: totalPrice, // Recibido como parámetro
        };

        // Enviar los datos para registrar el Kardex
        const response = await axios.post('/insumos/karde', movementDataKardex);
        console.log('Kardex registrado correctamente:', response.data);
    } catch (error: any) {
        console.error('Error al registrar el kardex:', error);
        if (error.response && error.response.data && error.response.data.errors) {
            console.error('Errores de validación:', error.response.data.errors);
        } else {
            console.error('Error desconocido:', error);
        }
    }
};

function hideDialog(): void {
    inputDialog.value = false;
    // Restablecer el formulario después de agregar el insumo
    movementInput.value = {
        inputName: '',
        batch: '',
        quantity: null,
        expirationDate: null,
        totalPrice: null,
        unitPrice: '',
    };
    clearSearch();
    selectedItem.value = null;
    itemType.value = 'insumo'; // Resetear a insumo por defecto
}
const clearSearch = (): void => {
    searchTerm.value = ''; // Vaciar el campo de búsqueda
};
</script>