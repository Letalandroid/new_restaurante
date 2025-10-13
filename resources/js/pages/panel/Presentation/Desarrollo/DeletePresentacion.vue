<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

// Tipos
interface Presentacion {
    id: number;
    name: string;
}

const props = defineProps<{
    visible: boolean;
    presentacion: Presentacion | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'deleted'): void;
}>();

const toast = useToast();
const localVisible = ref<boolean>(false);

watch(() => props.visible, (newVal: boolean) => {
    localVisible.value = newVal;
});

function closeDialog(): void {
    emit('update:visible', false);
}

async function deletePresentacion(): Promise<void> {
    if (!props.presentacion) return;
    try {
        await axios.delete(`/presentacion/${props.presentacion.id}`);
        emit('deleted');
        closeDialog();
        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Presentación eliminada correctamente',
            life: 3000
        });
    } catch (error) {
        console.error(error);
        let errorMessage = 'Error eliminando la presentación';
        const err = error as AxiosError<{ message?: string }>;
        if (err.response) {
            errorMessage = err.response.data?.message || errorMessage;
        }
        toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
    }
}
</script>

<template>
    <Dialog v-model:visible="localVisible" :style="{ width: '450px' }" header="Confirmar" :modal="true"
        @update:visible="closeDialog">
        <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl" />
            <span v-if="presentacion">¿Estás seguro de eliminar la presentación llamada: <b>{{ presentacion.name }}</b>?</span>
        </div>
        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Sí" icon="pi pi-check" @click="deletePresentacion" />
        </template>
    </Dialog>
</template>
