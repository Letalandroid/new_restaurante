<script setup lang="ts">
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import axios, { AxiosError } from 'axios';
import { useToast } from 'primevue/usetoast';
import Tag from 'primevue/tag';
import Checkbox from 'primevue/checkbox';

interface Area {
    name: string;
    state: boolean;
}

const props = defineProps<{
    visible: boolean;
    AreaId: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

const serverErrors = ref<Record<string, string[]>>({});
const submitted = ref<boolean>(false);
const toast = useToast();
const loading = ref<boolean>(false);
const dialogVisible = ref<boolean>(props.visible);

watch(() => props.visible, (val) => (dialogVisible.value = val));
watch(dialogVisible, (val) => emit('update:visible', val));

const area = ref<Area>({
    name: '',
    state: false
});

const fetchArea = async () : Promise<void> => {
    if (!props.AreaId) return;
    loading.value = true;
    try {
        const response = await axios.get(`/area/${props.AreaId}`);
        const data = response.data.areas;
        area.value.name = data.name;
        area.value.state = data.state;
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar el área',
            life: 3000
        });
        console.error('Error fetching area:', error);
    } finally {
        loading.value = false;
    }
};

watch(() => props.visible, (newVal) => {
        if (newVal && props.AreaId) {
            fetchArea();
        }
    });

const updateArea = async (): Promise<void> => {
    submitted.value = true;
    serverErrors.value = {};
    if (!props.AreaId) return;
    try {
        const areaData = {
            name: area.value.name,
            state: area.value.state
        };

        await axios.put(`/area/${props.AreaId}`, areaData);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Área actualizada correctamente',
            life: 3000
        });

        dialogVisible.value = false;
        emit('updated');
    } catch (error: any) {
        const axiosError = error as AxiosError;
        if (axiosError.response?.status === 422 && axiosError.response.data) {
            serverErrors.value = (axiosError.response.data as any).errors || {};
            toast.add({
                severity: 'error',
                summary: 'Error de validación',
                detail: 'Revisa los campos e intenta nuevamente.',
                life: 5000
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo actualizar el área',
                life: 3000
            });
        }
        console.error(error);
    }
};
</script>

<template>
    <Dialog v-model:visible="dialogVisible" header="Editar Área" modal :closable="true" :closeOnEscape="true"
        :style="{ width: '90%', maxWidth: '550px' }">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <div class="sm:col-span-10 col-span-8">
                    <label for="name" class="block font-bold mb-3">Nombre <span class="text-red-500">*</span></label>
                    <InputText
                        id="name"
                        v-model="area.name"
                        required
                        maxlength="100"
                        fluid
                        :class="{ 'p-invalid': serverErrors.name }"
                    />
                    <small v-if="serverErrors.name" class="p-error">{{ serverErrors.name[0] }}</small>
                </div>

                <div class="sm:col-span-2 col-span-4">
                    <label for="state" class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="area.state" :binary="true" inputId="state" />
                        <Tag :value="area.state ? 'Activo' : 'Inactivo'" :severity="area.state ? 'success' : 'danger'" />
                    </div>
                </div>
            </div>
        </div>
        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateArea" :loading="loading" />
        </template>
    </Dialog>
</template>

<style scoped>
</style>