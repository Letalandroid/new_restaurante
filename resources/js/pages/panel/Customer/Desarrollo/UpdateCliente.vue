<script setup lang="ts">
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import Dropdown from 'primevue/dropdown';

interface Cliente {
    id?: number;
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
    codigo_pattern?: string;
}

interface ServerErrors {
    [key: string]: string[];
}

const props = defineProps<{
    visible: boolean;
    clienteId: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

const toast = useToast();
const dialogVisible = ref<boolean>(props.visible);
const loading = ref<boolean>(false);
const submitted = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});

const cliente = ref<Cliente>({
    name: '',
    lastname: '',
    email: '',
    phone: '',
    codigo: '',
    client_type_id: null,
    state: false,
});

const tiposCliente = ref<TipoCliente[]>([]);
const codigoMaxLength = ref<number>(8); // 8 dígitos por defecto para persona natural (DNI)
const codigoPattern = ref<string>(''); // Expresión regular para el código
const codigoPlaceholder = ref<string>('Ingrese su codigo');

watch(() => props.visible, (val) => {
    dialogVisible.value = val;
    if (val && props.clienteId) {
        fetchCliente();
        fetchTiposCliente();
    }
});

watch(dialogVisible, (val) => emit('update:visible', val));

const fetchCliente = async (): Promise<void> => {
    try {
        loading.value = true;
        const res = await axios.get(`/cliente/${props.clienteId}`);
        const data = res.data.customer;
        cliente.value = {
            name: data.name,
            lastname: data.lastname,
            email: data.email,
            phone: data.phone,
            codigo: data.codigo,
            client_type_id: data.client_type_id,
            state: data.state
        };
        // Actualizar la longitud y el patrón del código al cargar el cliente
        onTipoClienteChange();
    } catch (error: any) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar el cliente', life: 3000 });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const fetchTiposCliente = async (): Promise<void> => {
    try {
        const res = await axios.get('/tipo_cliente', { params: { state: 1 } });
        tiposCliente.value = res.data.data;
    } catch (error: any) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar los tipos de cliente', life: 3000 });
        console.error(error);
    }
};

const updateCliente = async (): Promise<void> => {
    submitted.value = true;
    serverErrors.value = {};

    // Validación de campos requeridos
    if (!cliente.value.name || !cliente.value.lastname || !cliente.value.email || !cliente.value.phone || !cliente.value.codigo || !cliente.value.client_type_id) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Todos los campos son obligatorios', life: 3000 });
        return;
    }

    // Validación de longitud de código dependiendo del tipo de cliente
    if (cliente.value.client_type_id === 1 && cliente.value.codigo.length !== 8) { // Persona natural (DNI)
        serverErrors.value.codigo = ['El código debe tener 8 dígitos para persona natural.'];
        codigoPlaceholder.value = 'Ingrese su número de DNI';
        toast.add({ severity: 'error', summary: 'Error', detail: 'El código debe tener 8 dígitos para persona natural.', life: 3000 });
        return;
    }

    if (cliente.value.client_type_id === 2 && cliente.value.codigo.length !== 11) { // Persona jurídica (RUC)
        serverErrors.value.codigo = ['El código debe tener 11 dígitos para persona jurídica.'];
        codigoPlaceholder.value = 'Ingrese su número de RUC (10 o 20 dígitos)';
        toast.add({ severity: 'error', summary: 'Error', detail: 'El código debe tener 11 dígitos para persona jurídica.', life: 3000 });
        return;
    }

    // Validación de email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(cliente.value.email)) {
        serverErrors.value.email = ['El formato del email no es válido.'];
        toast.add({ severity: 'error', summary: 'Error', detail: 'El formato del email no es válido.', life: 3000 });
        return;
    }

    // Validación de teléfono (9 dígitos)
    const phoneRegex = /^\d{9}$/;
    if (!phoneRegex.test(cliente.value.phone)) {
        serverErrors.value.phone = ['El teléfono debe tener exactamente 9 dígitos.'];
        toast.add({ severity: 'error', summary: 'Error', detail: 'El teléfono debe tener exactamente 9 dígitos.', life: 3000 });
        return;
    }

    try {
        const clienteData = {
            name: cliente.value.name,
            lastname: cliente.value.lastname,
            email: cliente.value.email,
            phone: cliente.value.phone,
            codigo: cliente.value.codigo,
            client_type_id: cliente.value.client_type_id,
            state: cliente.value.state
        };

        await axios.put(`/cliente/${props.clienteId}`, clienteData);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Cliente actualizado correctamente',
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
                detail: 'No se pudo actualizar el cliente',
                life: 3000
            });
        }
        console.error(error);
    }
};

const onTipoClienteChange = (): void => {
    const tipoCliente = tiposCliente.value.find(t => t.id === cliente.value.client_type_id);
    if (tipoCliente) {
        // Ajustar el patrón y longitud de código dependiendo del tipo de cliente
        if (tipoCliente.codigo_pattern) {
            codigoPattern.value = tipoCliente.codigo_pattern;
        }

        if (cliente.value.client_type_id === 1) {
            codigoMaxLength.value = 8; // Persona natural (DNI)
            codigoPlaceholder.value = 'Ingrese su número de DNI';
        } else if (cliente.value.client_type_id === 2) {
            codigoMaxLength.value = 11; // Persona jurídica (RUC)
            codigoPlaceholder.value = 'Ingrese su número de RUC';
        } else {
            // Para otros tipos de clientes, puedes ajustar la longitud y el patrón
            codigoMaxLength.value = 10;
            codigoPlaceholder.value = 'Ingrese su código';
        }
    }
};
</script>

<template>
    <Dialog v-model:visible="dialogVisible" header="Editar Cliente" modal :closable="true" 
        :style="{ width: '85vw', maxWidth: '550px' }">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Campos de nombre y apellido -->
                <div class="col-span-12 md:col-span-6">
                    <label class="block font-bold mb-2">Nombre <span class="text-red-500">*</span></label>
                    <InputText
                        v-model="cliente.name"
                        required
                        placeholder="Ingrese el nombre del cliente"
                        maxlength="150"
                        fluid
                        :class="{ 'p-invalid': serverErrors.name }"
                    />
                    <small v-if="serverErrors.name" class="text-red-500">{{ serverErrors.name[0] }}</small>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="block font-bold mb-2">Apellido <span class="text-red-500">*</span></label>
                    <InputText
                        v-model="cliente.lastname"
                        required
                        placeholder="Ingrese el apellido del cliente"
                        maxlength="150"
                        fluid
                        :class="{ 'p-invalid': serverErrors.lastname }"
                    />
                    <small v-if="serverErrors.lastname" class="text-red-500">{{ serverErrors.lastname[0] }}</small>
                </div>

                <!-- Campos de email y teléfono -->
                <div class="col-span-12 md:col-span-6">
                    <label class="block font-bold mb-2">Email <span class="text-red-500">*</span></label>
                    <InputText
                        v-model="cliente.email"
                        required
                        placeholder="Ingrese el email del cliente"
                        type="email"
                        fluid
                        :class="{ 'p-invalid': serverErrors.email }"
                    />
                    <small v-if="serverErrors.email" class="text-red-500">{{ serverErrors.email[0] }}</small>
                </div>

                <div class="col-span-12 md:col-span-6">
                    <label class="block font-bold mb-2">Teléfono <span class="text-red-500">*</span></label>
                    <InputText
                        v-model="cliente.phone"
                        required
                        placeholder="Ingrese el teléfono (9 dígitos)"
                        maxlength="9"
                        fluid
                        :class="{ 'p-invalid': serverErrors.phone }"
                    />
                    <small v-if="serverErrors.phone" class="text-red-500">{{ serverErrors.phone[0] }}</small>
                </div>

                <!-- Tipo de Cliente -->
                <div class="col-span-12 md:col-span-12">
                    <label class="block font-bold mb-2">Tipo de Cliente <span class="text-red-500">*</span></label>
                    <Dropdown
                        v-model="cliente.client_type_id"
                        :options="tiposCliente"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Seleccione tipo de cliente"
                        filter
                        filterBy="name"
                        filterPlaceholder="Buscar tipo de cliente..."
                        class="w-full"
                        fluid
                        :class="{ 'p-invalid': serverErrors.client_type_id }"
                        @change="onTipoClienteChange"
                    />
                    <small v-if="serverErrors.client_type_id" class="text-red-500">{{ serverErrors.client_type_id[0] }}</small>
                </div>

                <!-- Código y Estado -->
                <div class="col-span-12 md:col-span-10">
                    <label class="block font-bold mb-2">Código <span class="text-red-500">*</span></label>
                    <InputText
                        v-model="cliente.codigo"
                        required
                        fluid
                        :placeholder="codigoPlaceholder"
                        :maxlength="codigoMaxLength"
                        :pattern="codigoPattern"
                        :class="{ 'p-invalid': serverErrors.codigo }"
                    />
                    <small v-if="serverErrors.codigo" class="text-red-500">{{ serverErrors.codigo[0] }}</small>
                </div>
                
                <div class="col-span-12 md:col-span-2 flex flex-col justify-end">
                    <label class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="cliente.state" :binary="true" />
                        <Tag :value="cliente.state ? 'Activo' : 'Inactivo'" :severity="cliente.state ? 'success' : 'danger'" />
                    </div>
                </div>

            </div>
        </div>
        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateCliente" :loading="loading" />
        </template>
    </Dialog>
</template>
