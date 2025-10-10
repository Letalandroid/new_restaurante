<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

interface Almacen {
    id: number;
    name?: string;
    [key: string]: any;
}

const props = defineProps<{
    visible: boolean;
    almacen: Almacen | null;
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

function closeDialog() {
    emit('update:visible', false);
}

async function deleteAlmacen(): Promise<void> {
    if (!props.almacen) return;
    try {
        await axios.delete(`/almacen/${props.almacen.id}`);
        emit('deleted');
        closeDialog();
        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Almacén eliminado correctamente',
            life: 3000
        });

    } catch (error) {
        console.error(error);
        let errorMessage = 'Error eliminando el almacen';
        if ((error as AxiosError).response) {
            errorMessage = ((error as AxiosError).response?.data as any)?.message || errorMessage;
        }
        toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
    }
}
</script>

<template>
    <Dialog v-model:visible="localVisible" :style="{ width: '90%', maxWidth: '450px' }" header="Confirmar" :modal="true"
        @update:visible="closeDialog">
        <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl" />
            <span v-if="almacen">¿Estás seguro de eliminar este almacen <b>{{ almacen.name }}</b>?</span>
        </div>
        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Sí" icon="pi pi-check" @click="deleteAlmacen" />
        </template>
    </Dialog>
</template>
