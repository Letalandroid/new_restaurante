<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button 
                label="Nuevo almacen" 
                icon="pi pi-plus" 
                severity="secondary" 
                class="mr-2 w-full sm:w-auto" 
                @click="openNew" 
            />
        </template>
        <template #end>
            <!-- ToolsAlmacen para los botones de exportar e importar -->
            <ToolsAlmacen @import-success="loadAlmacen"/>       
        </template>
    </Toolbar>

    <Dialog 
        v-model:visible="almacenDialog" 
        header="Registro de almacenes" 
        :modal="true"
        :closable="true"
        :style="{ width: '90%', maxWidth: '600px' }"
    >
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Nombre -->
                <div class="col-span-8 sm:col-span-9">
                    <label for="name" class="block font-bold mb-3">
                        Nombres <span class="text-red-500">*</span>
                    </label>
                    <InputText 
                        id="name" 
                        v-model.trim="almacen.name" 
                        required 
                        maxlength="100" 
                        fluid 
                    />
                    <small v-if="submitted && !almacen.name" class="text-red-500">
                        El nombre es obligatorio.
                    </small>
                    <small v-else-if="submitted && almacen.name && almacen.name.length < 2" class="text-red-500">
                        El nombre debe tener al menos 2 caracteres.
                    </small>
                    <small v-else-if="serverErrors.name" class="text-red-500">
                        {{ serverErrors.name[0] }}
                    </small>
                </div>

                <!-- Estado -->
                <div class="col-span-4 sm:col-span-3">
                    <label for="state" class="block font-bold mb-2">
                        Estado <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                        <Checkbox v-model="almacen.state" :binary="true" inputId="state" />
                        <Tag :value="almacen.state ? 'Activo' : 'Inactivo'" 
                             :severity="almacen.state ? 'success' : 'danger'" 
                        />
                        <small v-if="submitted && almacen.state === null" class="text-red-500">
                            El estado es obligatorio.
                        </small>
                        <small v-else-if="serverErrors.state" class="text-red-500">
                            {{ serverErrors.state[0] }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer con botones -->
        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text class="w-full sm:w-auto" @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" class="w-full sm:w-auto" @click="guardaralmacen" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import ToolsAlmacen from './toolsAlmacen.vue';

interface Almacen {
    name: string;
    state: boolean;
}

interface ServerErrors {
    [key: string]: string[];
}

const toast = useToast();
const submitted = ref<boolean>(false);
const almacenDialog = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});
const emit = defineEmits<{
    (e: 'almacen-agregado'): void;
}>();

const almacen = ref<Almacen>({
    name: '',
    state: true
});

// Método para recargar la lista de almacenes
const loadAlmacen = async () => {
    try {
        const response = await axios.get('/almacen');  // Solicitud GET para obtener los almacenes
        console.log(response.data);
        emit('almacen-agregado');  // Notificación a componente padre
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar los almacenes', life: 3000 });
        console.error(error);
    }
}

function resetAlmacen() {
    almacen.value = {
        name: '',
        state: true
    };
    serverErrors.value = {};
    submitted.value = false;
}

function openNew() {
    resetAlmacen();
    almacenDialog.value = true;
}

function hideDialog() {
    almacenDialog.value = false;
    resetAlmacen();
}

function guardaralmacen() {
    submitted.value = true;
    serverErrors.value = {};

    axios.post('/almacen', almacen.value)
        .then(() => {
            toast.add({ severity: 'success', summary: 'Éxito', detail: 'Almacén registrado', life: 3000 });
            hideDialog();
            emit('almacen-agregado');
        })
        .catch((error: AxiosError) => {
           if (error.response && error.response.status === 422) {
                serverErrors.value = (error.response.data as any).errors || {};
            } else {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'No se pudo registrar el almacén',
                    life: 3000
                });
            }
        });
}
</script>
