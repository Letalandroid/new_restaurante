<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Nuevo Piso" icon="pi pi-plus" severity="secondary" class="mr-2 w-full sm:w-auto" @click="openNew" />
        </template>
        <template #end>
            <!-- ToolsFloor para los botones de exportar e importar -->
            <ToolsFloor @import-success="loadPiso"/>       
        </template>
    </Toolbar>

    <Dialog
        v-model:visible="pisoDialog"
        :style="{ width: '90%', maxWidth: '550px' }"
        header="Registro de Piso"
        :modal="true"
        class="max-w-full sm:max-w-lg"
    >
        <div class="flex flex-col gap-6 p-2 sm:p-4">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-10 col-span-8">
                    <label for="name" class="block font-bold mb-3">Nombre <span class="text-red-500">*</span></label>
                    <InputText
                        id="name"
                        v-model.trim="piso.name"
                        required
                        maxlength="100"
                        fluid
                        class="w-full"
                        :class="{ 'p-invalid': submitted && !piso.name || serverErrors.name }"
                    />
                    <small v-if="submitted && !piso.name" class="text-red-500">El nombre es obligatorio.</small>
                    <small v-else-if="submitted && piso.name && piso.name.length < 2" class="text-red-500">
                        El nombre debe tener al menos 2 caracteres.
                    </small>
                    <small v-else-if="serverErrors.name" class="text-red-500">{{ serverErrors.name[0] }}</small>
                </div>

                <div class="sm:col-span-2 col-span-4">
                    <label for="state" class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="piso.state" :binary="true" inputId="state" />
                        <Tag :value="piso.state ? 'Activo' : 'Inactivo'" :severity="piso.state ? 'success' : 'danger'" />
                    </div>
                </div>

                <div class="col-span-12">
                    <label for="description" class="block font-bold mb-3">Descripción</label>
                    <Textarea
                        id="description"
                        fluid
                        v-model="piso.description"
                        maxlength="255"
                        rows="4"
                        autoResize
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.description }"
                    />
                    <small v-if="serverErrors.description" class="text-red-500">{{ serverErrors.description[0] }}</small>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarPiso" :loading="loading" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import Toolbar from 'primevue/toolbar';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import ToolsFloor from './toolsFloor.vue';

interface Piso {
    name: string;
    description: string;
    state: boolean;
}

interface ServerErrors {
    [key: string]: string[];
}

const toast = useToast();
const submitted = ref<boolean>(false);
const loading = ref<boolean>(false);
const pisoDialog = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});

const emit = defineEmits<{
    (e: 'piso-agregado'): void;
}>();

const piso = ref<Piso>({
    name: '',
    description: '',
    state: true
});

// Método para recargar la lista de pisos
const loadPiso = async (): Promise<void> => {
    try {
        const response = await axios.get('/piso');
        console.log(response.data);
        emit('piso-agregado');
    } catch (error: any) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar los pisos', life: 3000 });
        console.error(error);
    }
};

function resetPiso(): void {
    piso.value = {
        name: '',
        description: '',
        state: true
    };
    serverErrors.value = {};
    submitted.value = false;
    loading.value = false;
}

function openNew(): void {
    resetPiso();
    pisoDialog.value = true;
}

function hideDialog(): void {
    pisoDialog.value = false;
    resetPiso();
}

async function guardarPiso(): Promise<void> {
    submitted.value = true;
    serverErrors.value = {};

    if (!piso.value.name || piso.value.name.length < 2) {
        return;
    }

    loading.value = true;

    try {
        await axios.post('/piso', piso.value);

        toast.add({ severity: 'success', summary: 'Éxito', detail: 'Piso registrado', life: 3000 });
        hideDialog();
        emit('piso-agregado');
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            serverErrors.value = error.response.data.errors || {};
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo registrar el piso',
                life: 3000
            });
        }
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped></style>
