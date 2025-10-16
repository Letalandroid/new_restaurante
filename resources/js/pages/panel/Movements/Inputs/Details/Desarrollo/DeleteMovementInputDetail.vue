<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

// Tipado de las props
interface Input {
    id: number;
    name: string;
}

interface Product {
    id: number;
    name: string;
}

interface MovementInput {
    id: number;
    idMovementInput: number;
    input: Input | null;
    product: Product | null;
}

const props = defineProps<{
    visible: boolean;
    movementInput: MovementInput | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'deleted'): void;
}>();

const toast = useToast();
const localVisible = ref<boolean>(props.visible);

// Sincroniza localVisible con el prop visible
watch(() => props.visible, (val) => {
    localVisible.value = val;
});
// Cuando localVisible cambie, emite el cambio para actualizar el v-model del padre
watch(localVisible, (val) => {
    emit('update:visible', val);
});

function closeDialog(): void {
    emit('update:visible', false);
}

// Función para obtener el nombre del item
function getItemName(): string {
    if (!props.movementInput) return '';
    return props.movementInput.input?.name || props.movementInput.product?.name || '';
}

// Función para obtener el tipo de item
function getItemType(): string {
    if (!props.movementInput) return '';
    if (props.movementInput.input) return 'insumo';
    if (props.movementInput.product) return 'producto';
    return '';
}

async function deleteInput(): Promise<void> {
    if (!props.movementInput) return;
    console.log('ID a eliminar:', props.movementInput.id);

    try {
        await axios.delete(`/insumos/movimientos/detalle/${props.movementInput.id}`);
        
        // Solo eliminar del kardex si es un insumo
        if (getItemType() === 'insumo') {
            deleteInputKardex();
        }
        
        emit('deleted');
        closeDialog();
        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: `${getItemType() === 'insumo' ? 'Insumo' : 'Producto'} eliminado correctamente del movimiento`,
            life: 3000
        });

    } catch (error: any) {
        console.error(error);
        let errorMessage = `Error al eliminar el ${getItemType() === 'insumo' ? 'insumo' : 'producto'} del movimiento`;
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        }
        toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
    }
}

async function deleteInputKardex(): Promise<void> {
    if (!props.movementInput) return;
    const { idMovementInput } = props.movementInput;  
    const idInput = props.movementInput.input?.id;   

    try {
        const response = await axios.get(`/insumos/karde?idMovementInput=${idMovementInput}&idInput=${idInput}`);
        
        const id = response.data.data[0].id;

        const deleteResponse = await axios.delete(`/insumos/karde/${id}`);
        console.log('Respuesta del servidor:', deleteResponse);  // Verifica la respuesta

        emit('deleted');
        closeDialog();

    } catch (error: any) {
        console.error('Error al eliminar kardex:', error);
    }
}


</script>


<template>
    <Dialog v-model:visible="localVisible" :style="{ width: '450px', 'z-index': 9999 }" header="Confirmar" :modal="true">
        <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl" />
            <span v-if="movementInput">
                ¿Estás seguro de eliminar el 
                <b>{{ getItemType() === 'insumo' ? 'insumo' : 'producto' }} {{ getItemName() }}</b> 
                del movimiento?
            </span>
        </div>
        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Sí" icon="pi pi-check" @click="deleteInput" />
        </template>
    </Dialog>
</template>

