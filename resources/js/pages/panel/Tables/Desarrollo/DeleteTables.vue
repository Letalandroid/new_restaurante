<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

// Tipos para props
interface Table {
    id: number;
    tablenum: string;
    [key: string]: any; // Permite otros campos adicionales sin error
}

const props = defineProps<{
    visible: boolean;
    table: Table | null;
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

async function deleteTable(): Promise<void> {
    if (!props.table) return;
    try {
        await axios.delete(`/mesa/${props.table.id}`);
        emit('deleted');
        closeDialog();
        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Mesa eliminada correctamente',
            life: 3000
        });
    } catch (error: any) {
        console.error(error);
        let errorMessage = 'Error eliminando la mesa';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        }
        toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
    }
}
</script>

<template>
    <Dialog v-model:visible="localVisible" :style="{ width: '450px', 'z-index': 9999 }" header="Confirmar" :modal="true">
        <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl" />
            <span v-if="table">¿Estás seguro de eliminar la mesa <b>{{ table.tablenum }}</b>?</span>
        </div>
        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Sí" icon="pi pi-check" @click="deleteTable" />
        </template>
    </Dialog>
</template>

