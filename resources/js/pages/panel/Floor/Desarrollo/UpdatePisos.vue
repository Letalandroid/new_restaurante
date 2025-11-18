<script setup lang="ts">
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';

interface Piso {
    name: string;
    description: string;
    state: boolean;
}

interface ServerErrors {
    name?: string[];
    description?: string[];
}

const props = defineProps<{
    visible: boolean;
    pisoId: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

const serverErrors = ref<ServerErrors>({});
const submitted = ref<boolean>(false);
const toast = useToast();
const loading = ref<boolean>(false);

const dialogVisible = ref<boolean>(props.visible);
watch(() => props.visible, (val) => (dialogVisible.value = val));
watch(dialogVisible, (val) => emit('update:visible', val));

watch(
    () => props.visible,
    (newVal) => {
        if (newVal && props.pisoId) {
            fetchPiso();
        }
    }
);

const piso = ref<Piso>({
    name: '',
    description: '',
    state: false
});

const fetchPiso = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await axios.get(`/piso/${props.pisoId}`);
        const data = response.data.floor;

        piso.value.name = data.name;
        piso.value.description = data.description || '';
        piso.value.state = data.state;
    } catch (error: any) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar el piso',
            life: 3000
        });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const updatePiso = async (): Promise<void> => {
    submitted.value = true;
    serverErrors.value = {};

    try {
        const pisoData = {
            name: piso.value.name,
            description: piso.value.description,
            state: piso.value.state === true
        };

        await axios.put(`/piso/${props.pisoId}`, pisoData);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Piso actualizado correctamente',
            life: 3000
        });

        dialogVisible.value = false;
        emit('updated');
    } catch (error: any) {
        if (error.response && error.response.data?.errors) {
            serverErrors.value = error.response.data.errors;
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
                detail: 'No se pudo actualizar el piso',
                life: 3000
            });
        }
        console.error(error);
    }
};
</script>

<template>
    <Dialog
        v-model:visible="dialogVisible"
        header="Editar Piso"
        modal
        :closable="true"
        :closeOnEscape="true"
        :style="{ width: '90%', maxWidth: '550px' }"
        class="max-w-full sm:max-w-lg"
    >
        <div class="flex flex-col gap-6 p-2 sm:p-4 overflow-y-auto max-h-[80vh]">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-10 col-span-8">
                    <label for="name" class="block font-bold mb-3">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <InputText
                        v-model="piso.name"
                        required
                        maxlength="150"
                        fluid
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.name }"
                    />
                    <small v-if="serverErrors.name" class="text-red-500">{{ serverErrors.name[0] }}</small>
                </div>

                <div class="sm:col-span-2 col-span-4">
                    <label for="state" class="block font-bold mb-2">
                        Estado <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-3 flex-wrap sm:flex-nowrap">
                        <Checkbox v-model="piso.state" fluid :binary="true" inputId="state" />
                        <Tag
                            :value="piso.state ? 'Activo' : 'Inactivo'"
                            :severity="piso.state ? 'success' : 'danger'"
                        />
                    </div>
                </div>

                <div class="col-span-12">
                    <label for="description" class="block font-bold mb-3">Descripción</label>
                    <Textarea
                        v-model="piso.description"
                        maxlength="255"
                        rows="4"
                        autoResize
                        fluid
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.description }"
                    />
                    <small v-if="serverErrors.description" class="text-red-500">
                        {{ serverErrors.description[0] }}
                    </small>
                </div>
            </div>
        </div>
        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updatePiso" :loading="loading" />
        </template>
    </Dialog>
</template>

<style scoped></style>
