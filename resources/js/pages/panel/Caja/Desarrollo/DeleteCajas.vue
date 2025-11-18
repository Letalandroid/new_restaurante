<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

interface Caja {
    id: number | string;
    numero_cajas?: string | number;
    [key: string]: any;
}

/*interface DeleteResponse {
    state: boolean;
    message: string;
}*/

const props = defineProps<{
    visible: boolean;
    caja: Caja | null;
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

async function deleteCaja(): Promise<void> {
    if (!props.caja) return;
    try {
        await axios.delete(`/caja/${props.caja.id}`);
            emit('deleted');
            closeDialog();
            toast.add({
                severity: 'success',
                summary: 'Éxito',
                detail: 'Caja eliminada correctamente',
                life: 3000
            });
    } catch (error: any) {
        console.error(error);
        let errorMessage = 'Error eliminando la caja';
        if ((error as AxiosError).response) {
            errorMessage = ((error as AxiosError).response?.data as any)?.message || errorMessage;
        }
        toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
    }
}

</script>

<template>
    <Dialog v-model:visible="localVisible" :style="{ width: '90vw', maxWidth: '350px' }" header="Confirmar" :modal="true"
        @update:visible="closeDialog">
        <div class="flex items-center gap-5">
            <i class="pi pi-exclamation-triangle !text-7xl" />
                <span v-if="caja">¿Estás seguro de eliminar la caja N° <b>{{ caja.numero_cajas }}</b>?</span>
        </div>
        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Sí" icon="pi pi-check" @click="deleteCaja" />
        </template>
    </Dialog>
</template>
