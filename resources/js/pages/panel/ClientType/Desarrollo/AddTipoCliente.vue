<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Nuevo tipo de cliente" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #end>
            <!-- ToolsClientType para los botones de exportar e importar -->
            <ToolsClientType @import-success="loadTipoCliente"/>       
        </template>
    </Toolbar>

    <Dialog v-model:visible="tipoClienteDialog" :style="{ width: '600px' }" header="Registro de tipo de cliente" :modal="true">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-9">
                    <label for="name" class="block font-bold mb-3">Nombre <span class="text-red-500">*</span></label>
                    <InputText id="name" v-model.trim="tipoCliente.name" required maxlength="100" fluid />
                    <small v-if="submitted && !tipoCliente.name" class="text-red-500">El nombre es obligatorio.</small>
                    <small v-else-if="submitted && tipoCliente.name && tipoCliente.name.length < 2" class="text-red-500">
                        El nombre debe tener al menos 2 caracteres.
                    </small>
                    <small v-else-if="serverErrors.name" class="text-red-500">{{ serverErrors.name[0] }}</small>
                </div>

                <div class="col-span-3">
                    <label for="state" class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="tipoCliente.state" :binary="true" inputId="state" />
                        <Tag :value="tipoCliente.state ? 'Activo' : 'Inactivo'"
                             :severity="tipoCliente.state ? 'success' : 'danger'" />
                        <small v-if="submitted && tipoCliente.state === null" class="text-red-500">El estado es obligatorio.</small>
                        <small v-else-if="serverErrors.state" class="text-red-500">{{ serverErrors.state[0] }}</small>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarTipoCliente" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import { defineEmits } from 'vue';
import ToolsClientType from './toolsClientType.vue';

interface TipoCliente {
    name: string;
    state: boolean;
}

interface ServerErrors {
    name?: string[];
    state?: string[];
}

const toast = useToast();
const submitted = ref<boolean>(false);
const tipoClienteDialog = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});
const emit = defineEmits(['tipo-cliente-agregado']);

const tipoCliente = ref<TipoCliente>({
    name: '',
    state: true
});

// Método para recargar la lista de tipos de cliente
const loadTipoCliente = async (): Promise<void> => {
    try {
        const response = await axios.get('/tipos_clientes');
        console.log(response.data);
        emit('tipo-cliente-agregado');
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar los tipos de cliente', life: 3000 });
        console.error(error);
    }
};

function resetTipoCliente(): void {
    tipoCliente.value = {
        name: '',
        state: true
    };
    serverErrors.value = {};
    submitted.value = false;
}

function openNew(): void {
    resetTipoCliente();
    tipoClienteDialog.value = true;
}

function hideDialog(): void {
    tipoClienteDialog.value = false;
    resetTipoCliente();
}

async function guardarTipoCliente(): Promise<void> {
    submitted.value = true;
    serverErrors.value = {};

    try {
        await axios.post('/tipo_cliente', tipoCliente.value);
        toast.add({ severity: 'success', summary: 'Éxito', detail: 'Tipo de cliente registrado', life: 3000 });
        hideDialog();
        emit('tipo-cliente-agregado');
    } catch (error: any) {
        if (error.response && error.response.status === 422) {
            serverErrors.value = error.response.data.errors || {};
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo registrar el tipo de cliente',
                life: 3000
            });
        }
    }
}
</script>
