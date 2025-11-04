<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

interface Reservacion {
    id: number;
    reservation_code: string;
    customer?: {
        name: string;
        lastname: string;
    };
    Cliente_name?: string;
    [key: string]: any;
}

const props = defineProps<{
    visible: boolean;
    reservacion: Reservacion | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'deleted'): void;
}>();

const toast = useToast();
const localVisible = ref<boolean>(false);

watch(() => props.visible, (newVal) => {
    localVisible.value = newVal;
});

function closeDialog(): void {
    emit('update:visible', false);
}

async function deleteReservacion(): Promise<void> {
    if (!props.reservacion) return;
    try {
        await axios.delete(`/reservacion/${props.reservacion.id}`);
        emit('deleted');
        closeDialog();
        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Reservación eliminada correctamente',
            life: 3000
        });
    } catch (error: any) {
        console.error(error);
        let errorMessage = 'Error eliminando la reservación';
        if (error.response) {
            errorMessage = error.response.data.message || errorMessage;
        }
        toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
    }
}
</script>

<template>
    <Dialog 
        v-model:visible="localVisible" 
        :style="{ width: '500px' }" 
        header="Confirmar Eliminación" 
        :modal="true"
        @update:visible="closeDialog"
    >
        <div class="flex items-start gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl text-orange-500 mt-1" />
            <div v-if="reservacion">
                <p class="mb-2 text-color">¿Estás seguro de eliminar la siguiente reservación?</p>
                <div 
                    class="p-3 rounded border"
                    :class="['surface-ground', 'border-surface-300', 'dark:border-surface-700']"
                >
                    <p class="text-color"><strong>Código:</strong> {{ reservacion.reservation_code }}</p>
                    <p class="text-color"><strong>Cliente:</strong> 
                        {{ reservacion.Cliente_name || (reservacion.customer ? `${reservacion.customer.name} ${reservacion.customer.lastname}` : 'N/A') }}
                    </p>
                    <p class="text-color"><strong>Fecha:</strong> {{ reservacion.date }}</p>
                    <p class="text-color"><strong>Hora:</strong> {{ reservacion.hour }}</p>
                    <p class="text-color"><strong>Personas:</strong> {{ reservacion.number_people }}</p>
                </div>
                <p class="mt-3 font-semibold text-red-500">Esta acción no se puede deshacer.</p>
            </div>
        </div>
        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Eliminar" icon="pi pi-trash" @click="deleteReservacion" />
        </template>
    </Dialog>
</template>