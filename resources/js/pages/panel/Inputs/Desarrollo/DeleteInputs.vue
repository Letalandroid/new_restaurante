<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

interface InputData {
    id: number;
    name: string;
}

const props = defineProps<{
    visible: boolean;
    input: InputData | null;
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

async function deleteInput(): Promise<void> {
    if (!props.input) return;
    try {
        await axios.delete(`/insumo/${props.input.id}`);
        emit('deleted');
        closeDialog();
        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Insumo eliminado correctamente',
            life: 3000
        });
    } catch (error: any) {
        console.error(error);
        let errorMessage = 'Error eliminando el insumo';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        }
        toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
    }
}
</script>

<template>
    <Dialog v-model:visible="localVisible" :style="{ width: '90vw', maxWidth: '450px' }" header="Confirmar" :modal="true">
        <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl" />
            <span v-if="input">¿Estás seguro de eliminar el insumo <b>{{ input.name }}</b>?</span>
        </div>
        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Sí" icon="pi pi-check" @click="deleteInput" />
        </template>
    </Dialog>
</template>

