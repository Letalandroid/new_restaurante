<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

interface Holiday {
    id: number;
    name?: string;
    date?: string;
    [key: string]: any;
}

const props = defineProps<{
    visible: boolean;
    holiday: Holiday | null;
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

async function deleteHoliday(): Promise<void> {
    if (!props.holiday) return;

    try {
        await axios.delete(`/asistencias/feriado/${props.holiday.id}`);
        emit('deleted');
        closeDialog();

        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Feriado eliminado correctamente',
            life: 3000
        });

    } catch (error) {
        console.error(error);
        let errorMessage = 'Error eliminando el feriado';
        if ((error as AxiosError).response) {
            errorMessage = ((error as AxiosError).response?.data as any)?.message || errorMessage;
        }
        toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
    }
}
</script>

<template>
    <Dialog
        v-model:visible="localVisible"
        :style="{ width: '90%', maxWidth: '450px' }"
        header="Confirmar eliminación"
        :modal="true"
        @update:visible="closeDialog"
    >
        <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl text-yellow-500" />
            <span v-if="holiday">
                ¿Estás seguro de eliminar el feriado <b>{{ holiday.name }}</b>
                del día <b>{{ holiday.date }}</b>?
            </span>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Eliminar" icon="pi pi-check" severity="danger" @click="deleteHoliday" />
        </template>
    </Dialog>
</template>
