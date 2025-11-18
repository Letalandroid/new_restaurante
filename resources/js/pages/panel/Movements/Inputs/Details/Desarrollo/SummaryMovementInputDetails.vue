<template>
    <Panel header="DETALLE DE MOVIMIENTO" class="p-mt-3 w-full">
        <div class="grid grid-cols-12 gap-4">
            <!-- Código y Fecha de Emisión -->
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
                <div class="flex items-center flex-wrap mb-2">
                    <label class="mr-2 font-bold"><strong>Código:</strong></label>
                    <span class="break-words">{{ movement.code }}</span>
                </div>
                <div class="flex items-center flex-wrap">
                    <label class="mr-2 font-bold"><strong>Fecha de Emisión:</strong></label>
                    <span class="break-words">{{ movement.issue_date }}</span>
                </div>
            </div>

            <!-- Proveedor y Fecha de Ejecución -->
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
                <div class="flex items-center flex-wrap mb-2">
                    <label class="mr-2 font-bold"><strong>Proveedor:</strong></label>
                    <span class="break-words">{{ movement.supplier_name }}</span>
                </div>
                <div class="flex items-center flex-wrap">
                    <label class="mr-2 font-bold"><strong>Fecha de Ejecución:</strong></label>
                    <span class="break-words">{{ movement.execution_date }}</span>
                </div>
            </div>

            <!-- Tipo de Movimiento y Tipo de Pago -->
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
                <div class="flex items-center flex-wrap mb-2">
                    <label class="mr-2 font-bold"><strong>Tipo de Movimiento:</strong></label>
                    <span class="break-words">{{ getMovementType(movement.movement_type) }}</span>
                </div>
                <div class="flex items-center flex-wrap">
                    <label class="mr-2 font-bold"><strong>Tipo de Pago:</strong></label>
                    <span class="break-words">{{ movement.payment_type }}</span>
                </div>
            </div>
        </div>
    </Panel>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3'; 
const { id } = usePage().props;
import Panel from 'primevue/panel';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
const toast = useToast();


const movement = ref({
    code: '',
    issue_date: '',
    execution_date: '',
    supplier_name: '',
    movement_type: 0,
    payment_type: ''
});

// Función para obtener el tipo de movimiento
function getMovementType(type: number) {
    switch(type) {
        case 1:
            return 'Factura';
        case 2:
            return 'Guía';
        case 3:
            return 'Boleta';
        default:
            return 'Desconocido';
    }
}

// Función para cargar los datos del movimiento desde la API
async function loadMovementDetails() {
    try {
        const response = await axios.get(`/items/movimiento/${id}`);
        if (response.data.state) {
            movement.value = response.data.movement;
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: 'Movimiento no encontrado.' });
                        window.location.href = '/items/movimientos/';

        }
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Error al cargar los datos del movimiento.' });
                    window.location.href = '/items/movimientos/';
        console.error(error);

    }
}

// Cargar los detalles al montar el componente
onMounted(() => {
    loadMovementDetails();
});
</script>

<style scoped>
.p-field {
    margin-bottom: 1.5rem;
}

p {
    font-size: 1rem;
    margin-top: 0.2rem;
    font-weight: 500;
}
.p-grid.p-fluid {
    display: flex
;
    flex-direction: row;
    flex-wrap: nowrap;
    align-items: center;
    justify-content: space-evenly;
}
</style>
