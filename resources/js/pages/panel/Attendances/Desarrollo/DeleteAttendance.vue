<script setup lang="ts">
import { ref, watch } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

interface Attendance {
    id: number;
    employee_name?: string;
    work_date: string;
    [key: string]: any;
}

const props = defineProps<{
    visible: boolean;
    attendance: Attendance | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'deleted'): void;
}>();

const toast = useToast();
const localVisible = ref<boolean>(false);

watch(() => props.visible, (val) => localVisible.value = val);

function closeDialog() {
    emit('update:visible', false);
}

async function deleteAttendance(): Promise<void> {
    if (!props.attendance) return;

    try {
        await axios.delete(`/asistencia/${props.attendance.id}`);
        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Asistencia eliminada correctamente',
            life: 3000
        });
        emit('deleted');
        closeDialog();
    } catch (error: any) {
        console.error(error);
        let errorMessage = 'Error eliminando la asistencia';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        }
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: errorMessage,
            life: 3000
        });
    }
}
</script>

<template>
    <Dialog
        v-model:visible="localVisible"
        :modal="true"
        :style="{ width: '90%', maxWidth: '450px' }"
        header="Confirmar eliminación"
        @update:visible="closeDialog"
    >
        <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl" />
            <span v-if="attendance">
                ¿Estás seguro de eliminar la asistencia de <b>{{ attendance.employee_name }}</b> del día <b>{{ attendance.work_date }}</b>?
            </span>
        </div>

        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="closeDialog" />
            <Button label="Sí" icon="pi pi-check" severity="danger" @click="deleteAttendance" />
        </template>
    </Dialog>
</template>
