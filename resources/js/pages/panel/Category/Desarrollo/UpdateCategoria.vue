<script setup lang="ts">
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import Tag from 'primevue/tag';
import Checkbox from 'primevue/checkbox';

interface Categoria {
    name: string;
    state: boolean;
}

interface ServerErrors {
    name?: string[];
    state?: string[];
}

const props = defineProps<{
    visible: boolean;
    categoriaId: number | null;
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
watch(() => props.visible, (val) => dialogVisible.value = val);
watch(dialogVisible, (val) => emit('update:visible', val));

watch(() => props.visible, (newVal) => {
    if (newVal && props.categoriaId) {
        fetchCategoria();
    }
});

const categoria = ref<Categoria>({
    name: '',
    state: false
});

const fetchCategoria = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await axios.get(`/categoria/${props.categoriaId}`);
        const data = response.data.category;

        categoria.value.name = data.name;
        categoria.value.state = data.state;
    } catch (error: any) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar la categoría',
            life: 3000
        });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const updateCategoria = async (): Promise<void> => {
    submitted.value = true;
    serverErrors.value = {};

    try {
        const categoriaData = {
            name: categoria.value.name,
            state: categoria.value.state === true,
        };

        await axios.put(`/categoria/${props.categoriaId}`, categoriaData);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Categoría actualizada correctamente',
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
                detail: 'No se pudo actualizar la categoría',
                life: 3000
            });
        }
        console.error(error);
    }
};
</script>

<template>
    <!-- Dialog de edición -->
    <Dialog 
        v-model:visible="dialogVisible" 
        header="Editar Categoría" 
        modal 
        :closable="true" 
        :closeOnEscape="true"
        :style="{ width: '90vw', maxWidth: '600px' }"
    >
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Nombre -->
                <div class="col-span-8 sm:col-span-9">
                    <label for="name" class="block font-bold mb-3">Nombre <span class="text-red-500">*</span></label>
                    <InputText
                        v-model="categoria.name"
                        required
                        maxlength="100"
                        fluid
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.name }"
                    />
                    <small v-if="serverErrors.name" class="p-error">{{ serverErrors.name[0] }}</small>
                </div>

                <!-- Estado -->
                <div class="col-span-4 sm:col-span-3">
                    <label for="state" class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                        <Checkbox v-model="categoria.state" :binary="true" inputId="state" />
                        <Tag 
                            :value="categoria.state ? 'Activo' : 'Inactivo'" 
                            :severity="categoria.state ? 'success' : 'danger'" 
                            class="text-xs sm:text-sm"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer adaptativo -->
        <template #footer>
            <div class="flex flex-col sm:flex-row gap-2 sm:justify-end w-full">
                <Button 
                    label="Cancelar" 
                    icon="pi pi-times" 
                    text 
                    class="w-full sm:w-auto" 
                    @click="dialogVisible = false" 
                />
                <Button 
                    label="Guardar" 
                    icon="pi pi-check" 
                    class="w-full sm:w-auto" 
                    @click="updateCategoria" 
                    :loading="loading" 
                />
            </div>
        </template>
    </Dialog>
</template>
