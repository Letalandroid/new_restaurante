<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Nuevo cliente" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #end>
            <!-- ToolsCustomer para los botones de exportar e importar -->
            <ToolsCustomer @import-success="loadCliente"/>       
        </template>
    </Toolbar>

    <Dialog v-model:visible="clienteDialog" :style="{ width: '700px' }" header="Registro de cliente" :modal="true">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Campos de nombre y apellido -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">Nombre <span class="text-red-500">*</span></label>
                    <InputText
                        v-model.trim="cliente.name"
                        required
                        placeholder="Ingrese el nombre del cliente"
                        maxlength="150"
                        fluid
                    />
                    <small v-if="submitted && !cliente.name" class="text-red-500">El nombre es obligatorio.</small>
                    <small v-if="serverErrors.name" class="text-red-500">{{ serverErrors.name[0] }}</small>
                </div>

                <div class="col-span-6">
                    <label class="block font-bold mb-2">Apellido <span class="text-red-500">*</span></label>
                    <InputText
                        v-model.trim="cliente.lastname"
                        required
                        placeholder="Ingrese el apellido del cliente"
                        maxlength="150"
                        fluid
                    />
                    <small v-if="submitted && !cliente.lastname" class="text-red-500">El apellido es obligatorio.</small>
                    <small v-if="serverErrors.lastname" class="text-red-500">{{ serverErrors.lastname[0] }}</small>
                </div>

                <!-- Campos de email y teléfono -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">Email <span class="text-red-500">*</span></label>
                    <InputText
                        v-model.trim="cliente.email"
                        required
                        placeholder="Ingrese el email del cliente"
                        type="email"
                        fluid
                    />
                    <small v-if="submitted && !cliente.email" class="text-red-500">El email es obligatorio.</small>
                    <small v-if="serverErrors.email" class="text-red-500">{{ serverErrors.email[0] }}</small>
                </div>

                <div class="col-span-6">
                    <label class="block font-bold mb-2">Teléfono <span class="text-red-500">*</span></label>
                    <InputText
                        v-model.trim="cliente.phone"
                        required
                        placeholder="Ingrese el teléfono (9 dígitos)"
                        maxlength="9"
                        fluid
                    />
                    <small v-if="submitted && !cliente.phone" class="text-red-500">El teléfono es obligatorio.</small>
                    <small v-if="serverErrors.phone" class="text-red-500">{{ serverErrors.phone[0] }}</small>
                </div>

                <div class="col-span-2">
                    <label class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="cliente.state" :binary="true" />
                        <Tag :value="cliente.state ? 'Activo' : 'Inactivo'" :severity="cliente.state ? 'success' : 'danger'" />
                    </div>
                    <small v-if="serverErrors.state" class="text-red-500">{{ serverErrors.state[0] }}</small>
                </div>

                <div class="col-span-10">
                    <label class="block font-bold mb-2">Tipo de Cliente <span class="text-red-500">*</span></label>
                    <Dropdown
                        v-model="cliente.client_type_id"
                        :options="tiposCliente"
                        optionLabel="name"
                        optionValue="id"
                        fluid
                        placeholder="Seleccione tipo de cliente"
                        filter
                        filterBy="name"
                        filterPlaceholder="Buscar tipo de cliente..."
                        class="w-full"
                        @change="onTipoClienteChange"
                    />
                    <small v-if="submitted && !cliente.client_type_id" class="text-red-500">Debe seleccionar un tipo.</small>
                    <small v-if="serverErrors.client_type_id" class="text-red-500">{{ serverErrors.client_type_id[0] }}</small>
                </div>

                <!-- Campo de código (DNI o RUC) que solo aparece después de seleccionar el tipo de cliente -->
                <div v-if="cliente.client_type_id" class="col-span-12">
                    <label class="block font-bold mb-2">Código <span class="text-red-500">*</span></label>
                    <InputText
                        v-model.trim="cliente.codigo"
                        required
                        fluid
                        :maxlength="codigoMaxLength" 
                        :placeholder="codigoPlaceholder"
                    />
                    <small v-if="submitted && !cliente.codigo" class="text-red-500">El código es obligatorio.</small>
                    <small v-if="serverErrors.codigo" class="text-red-500">{{ serverErrors.codigo[0] }}</small>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarCliente" />
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
import Dropdown from 'primevue/dropdown';
import ToolsCustomer from './toolsCustomer.vue';

interface Cliente {
    name: string;
    lastname: string;
    email: string;
    phone: string;
    codigo: string;
    client_type_id: number | null;
    state: boolean;
}

interface TipoCliente {
    id: number;
    name: string;
}

interface ServerErrors {
    [key: string]: string[];
}

const toast = useToast();
const submitted = ref(false);
const clienteDialog = ref(false);
const serverErrors = ref<ServerErrors>({});
const emit = defineEmits(['cliente-agregado']);

const cliente = ref<Cliente>({
    name: '',
    lastname: '',
    email: '',
    phone: '',
    codigo: '',
    client_type_id: null,
    state: true
});

// Variable para controlar la longitud máxima del código
const codigoMaxLength = ref<number>(8);  // Valor inicial para persona natural (8 dígitos para DNI)
const codigoPlaceholder = ref<string>("Ingrese su número de DNI");  // Placeholder inicial para persona natural

// Cargar tipos de cliente
const tiposCliente = ref<TipoCliente[]>([]);

const loadCliente = async (): Promise<void> => {
    try {
        const response = await axios.get('/cliente');  
        console.log(response.data);
        emit('cliente-agregado');  
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar los clientes', life: 3000 });
        console.error(error);
    }
};

function onTipoClienteChange(): void {
    if (cliente.value.client_type_id === 1) { 
        codigoMaxLength.value = 8;  
        codigoPlaceholder.value = "Ingrese su número de DNI"; 
    } else if (cliente.value.client_type_id === 2) { 
        codigoMaxLength.value = 11; 
        codigoPlaceholder.value = "Ingrese su número de RUC (10 o 20)"; 
    }
}

function resetCliente(): void {
    cliente.value = {
        name: '',
        lastname: '',
        email: '',
        phone: '',
        codigo: '',
        client_type_id: null,
        state: true
    };
    serverErrors.value = {};
    submitted.value = false;
}

function openNew(): void {
    resetCliente();
    clienteDialog.value = true;
    fetchTiposCliente();
}

function hideDialog(): void {
    clienteDialog.value = false;
    resetCliente();
}

function fetchTiposCliente(): void {
    axios.get('/tipo_cliente', { params: { state: 1 } })
        .then(res => {
            tiposCliente.value = res.data.data;
        })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar los tipos de cliente', life: 3000 });
        });
}

function guardarCliente(): void {
    submitted.value = true;
    serverErrors.value = {};

    if (!cliente.value.name || !cliente.value.lastname || !cliente.value.email || !cliente.value.phone || !cliente.value.codigo || !cliente.value.client_type_id) return;

    axios.post('/cliente', cliente.value)
        .then(() => {
            toast.add({ severity: 'success', summary: 'Éxito', detail: 'Cliente registrado', life: 3000 });
            hideDialog();
            emit('cliente-agregado');
        })
        .catch(error => {
            if (error.response && error.response.status === 422) {
                serverErrors.value = error.response.data.errors || {};
            } else {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'No se pudo registrar el cliente',
                    life: 3000
                });
            }
        });
}
</script>
