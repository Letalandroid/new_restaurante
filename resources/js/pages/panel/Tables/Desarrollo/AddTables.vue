<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Nueva Mesa" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #end>
            <!-- ToolsTable para los botones de exportar e importar -->
            <ToolsTable @import-success="loadMesa"/>       
        </template>
    </Toolbar>

    <Dialog
        v-model:visible="tableDialog"
        :style="{ width: '90%', maxWidth: '550px' }"
        header="Registro de Mesas"
        :modal="true"
        class="p-2 sm:p-4"
    >
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <!-- Nombre -->
                <div class="sm:col-span-9">
                    <label class="mb-2 block font-bold">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model.trim="table.name" fluid maxlength="100" class="w-full" />
                    <small v-if="submitted && !table.name" class="text-red-500 text-xs sm:text-sm">
                        El nombre es obligatorio.
                    </small>
                    <small v-else-if="submitted && table.name.length < 2" class="text-red-500 text-xs sm:text-sm">
                        El nombre debe tener al menos 2 caracteres.
                    </small>
                    <small v-else-if="serverErrors.name" class="text-red-500 text-xs sm:text-sm">
                        {{ serverErrors.name[0] }}
                    </small>
                </div>

                <!-- Estado -->
                <div class="sm:col-span-3">
                    <label class="mb-2 block font-bold">
                        Estado <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <Checkbox v-model="table.state" :binary="true" />
                        <Tag
                            :value="table.state ? 'Activo' : 'Inactivo'"
                            fluid
                            :severity="table.state ? 'success' : 'danger'"
                            class="text-xs sm:text-sm"
                        />
                        <small v-if="submitted && table.state === null" class="text-red-500 text-xs sm:text-sm">
                            El estado es obligatorio.
                        </small>
                        <small v-else-if="serverErrors.state" class="text-red-500 text-xs sm:text-sm">
                            {{ serverErrors.state[0] }}
                        </small>
                    </div>
                </div>

                <!-- Número -->
                <div class="sm:col-span-6">
                    <label class="mb-2 block font-bold">
                        Número <span class="text-red-500">*</span>
                    </label>
                    <InputText v-model.trim="table.tablenum" fluid maxlength="50" class="w-full" />
                    <small v-if="submitted && !table.tablenum" class="text-red-500 text-xs sm:text-sm">
                        El número es obligatorio.
                    </small>
                    <small v-else-if="serverErrors.tablenum" class="text-red-500 text-xs sm:text-sm">
                        {{ serverErrors.tablenum[0] }}
                    </small>
                </div>

                <!-- Capacidad -->
                <div class="sm:col-span-6">
                    <label class="mb-2 block font-bold">
                        Capacidad <span class="text-red-500">*</span>
                    </label>
                    <InputText
                        v-model="capacityInput"
                        fluid
                        placeholder="Capacidad"
                        inputmode="numeric"
                        maxlength="6"
                        @keydown="filterInteger"
                        @input="syncCapacity"
                        class="w-full"
                    />
                    <small v-if="submitted && !capacityInput" class="text-red-500 text-xs sm:text-sm">
                        La capacidad es obligatoria.
                    </small>
                    <small v-else-if="Number(capacityInput) < 1" class="text-red-500 text-xs sm:text-sm">
                        Debe ser al menos 1 persona.
                    </small>
                    <small v-else-if="serverErrors.capacity" class="text-red-500 text-xs sm:text-sm">
                        {{ serverErrors.capacity[0] }}
                    </small>
                </div>

                <!-- Area -->
                <div class="sm:col-span-6">
                    <label class="mb-2 block font-bold">
                        Área <span class="text-red-500">*</span>
                    </label>
                    <Dropdown
                        v-model="table.idArea"
                        fluid
                        :options="areas"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Seleccione el área"
                        filter
                        filterPlaceholder="Buscar área"
                        class="w-full"
                    />
                    <small v-if="submitted && !table.idArea" class="text-red-500 text-xs sm:text-sm">
                        El área es obligatoria.
                    </small>
                    <small v-else-if="serverErrors.idArea" class="text-red-500 text-xs sm:text-sm">
                        {{ serverErrors.idArea[0] }}
                    </small>
                </div>

                <!-- Piso -->
                <div class="sm:col-span-6">
                    <label class="mb-2 block font-bold">
                        Piso <span class="text-red-500">*</span>
                    </label>
                    <Dropdown
                        v-model="table.idFloor"
                        :options="pisos"
                        fluid
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Seleccione el piso"
                        filter
                        filterPlaceholder="Buscar piso"
                        class="w-full"
                    />
                    <small v-if="submitted && !table.idFloor" class="text-red-500 text-xs sm:text-sm">
                        El piso es obligatorio.
                    </small>
                    <small v-else-if="serverErrors.idFloor" class="text-red-500 text-xs sm:text-sm">
                        {{ serverErrors.idFloor[0] }}
                    </small>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarTable" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import axios from 'axios';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
//import Select from 'primevue/select';
import Tag from 'primevue/tag';
import Toolbar from 'primevue/toolbar';
import { useToast } from 'primevue/usetoast';
import { ref } from 'vue';
import ToolsTable from './toolsTable.vue';
import Dropdown from 'primevue/dropdown';  // Importamos Dropdown

// Interfaces
interface Mesa {
    name: string;
    tablenum: string;
    capacity: number | null;
    state: boolean;
    idArea: number | null;
    idFloor: number | null;
}

interface OptionItem {
    label: string;
    value: number;
}

interface ServerErrors {
    [key: string]: string[];
}

const toast = useToast();
const submitted = ref<boolean>(false);
const tableDialog = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});
const emit = defineEmits(['tables-agregado', 'mesa-agregada']);
// Campo auxiliar para manejar capacidad como texto pero validar número
const capacityInput = ref<string>('');

// Permite solo números enteros en tiempo real
const filterInteger = (event: KeyboardEvent): void => {
    const allowedKeys = ['Backspace', 'Tab', 'ArrowLeft', 'ArrowRight', 'Delete'];
    const isNumber = /^[0-9]$/.test(event.key);

    if (!isNumber && !allowedKeys.includes(event.key)) {
        event.preventDefault(); // Bloquea cualquier otra tecla (letras, puntos, comas, signos)
    }
};

// Sincroniza el valor válido al objeto table
const syncCapacity = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    // Limpia cualquier carácter no numérico (por si se pega texto)
    input.value = input.value.replace(/[^0-9]/g, '');
    capacityInput.value = input.value;
    table.value.capacity = input.value ? parseInt(input.value) : null;
};


const table = ref<Mesa>({
    name: '',
    tablenum: '',
    capacity: null,
    state: true,
    idArea: null,
    idFloor: null
});

// Método para recargar la lista de mesas
const loadMesa = async (): Promise<void> => {
    try {
        const response = await axios.get('/mesa');  // Aquí haces una solicitud GET para obtener las mesas
        console.log(response.data);
        // Realiza lo que necesites con la respuesta, como actualizar el listado en un componente superior
        emit('mesa-agregada');  // Si quieres que un componente padre reciba la notificación de la actualización
    } catch (error: any) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar las mesas', life: 3000 });
        console.error(error);
    }
};

const areas = ref<OptionItem[]>([]);
const pisos = ref<OptionItem[]>([]);

function resetTable(): void {
    table.value = {
        name: '',
        tablenum: '',
        capacity: null,
        state: true,
        idArea: null,
        idFloor: null
    };
    capacityInput.value = '';
    serverErrors.value = {};
    submitted.value = false;
}

function openNew(): void {
    resetTable();
    fetchAreas();
    fetchFloors();
    tableDialog.value = true;
}

function hideDialog(): void {
    tableDialog.value = false;
    resetTable();
}

async function fetchAreas(): Promise<void> {
    try {
        const { data } = await axios.get('/area', { params: { state: 1 } });
        areas.value = data.data.map((c: any) => ({ label: c.name, value: c.id }));
    } catch (e: any) {
        toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'No se pudieron cargar las Areas' });
        console.error(e);
    }
}

async function fetchFloors(): Promise<void> {
    try {
        const { data } = await axios.get('/piso', { params: { state: 1 } });
        pisos.value = data.data.map((a: any) => ({ label: a.name, value: a.id }));
    } catch (e: any) {
        toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'No se pudieron cargar los pisos' });
        console.error(e);
    }
}

function guardarTable(): void {
    submitted.value = true;
    serverErrors.value = {};

    axios
        .post('/mesa', table.value)
        .then(() => {
            toast.add({ severity: 'success', summary: 'Éxito', detail: 'Mesa registrada', life: 3000 });
            hideDialog();
            emit('tables-agregado');
        })
        .catch((error: any) => {
            if (error.response?.status === 422) {
                serverErrors.value = error.response.data.errors || {};
            } else {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'No se pudo registrar la mesa',
                    life: 3000,
                });
            }
        });
}
</script>
