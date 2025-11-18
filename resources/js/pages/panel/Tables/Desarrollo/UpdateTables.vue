<script setup lang="ts">
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Dropdown from 'primevue/dropdown';  // Importamos Dropdown
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

// Tipos
interface Table {
    name: string;
    tablenum: string;
    capacity: number | null;
    state: boolean;
    idArea: number | null;
    idFloor: number | null;
}

interface Option {
    label: string;
    value: number;
}

interface ServerErrors {
    [key: string]: string[];
}

const props = defineProps<{
    visible: boolean;
    tableId: number | null;
}>();
const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

const toast = useToast();
const serverErrors = ref<ServerErrors>({});
const submitted = ref(false);
const loading = ref(false);

const dialogVisible = ref<boolean>(props.visible);
watch(() => props.visible, (val) => dialogVisible.value = val);
watch(dialogVisible, (val) => emit('update:visible', val));

const table = ref<Table>({
    name: '',
    tablenum: '',
    capacity: null,
    state: true,
    idArea: null,
    idFloor: null
});

const areas = ref<Option[]>([]);
const pisos = ref<Option[]>([]);

watch(() => props.visible, async (val) => {
    if (val && props.tableId) {
        await fetchTable();
        await fetchAreas();
        await fetchFloors();
    }
});

const fetchTable = async (): Promise<void> => {
    loading.value = true;
    try {
        const { data } = await axios.get(`/mesa/${props.tableId}`);
        const t = data.table;
        table.value = {
            name: t.name,
            tablenum: t.tablenum,
            capacity: t.capacity,
            state: t.state,
            idArea: t.idArea,
            idFloor: t.idFloor
        };
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar la mesas',
            life: 3000
        });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const fetchAreas = async (): Promise<void> => {
    try {
        const { data } = await axios.get('/area', { params: { state: 1 } });
        areas.value = data.data.map((c: any) => ({ label: c.name, value: c.id }));
    } catch (e) {
        toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'No se pudieron cargar las areas' });
        console.error(e);
    }
};

const fetchFloors = async (): Promise<void> => {
    try {
        const { data } = await axios.get('/piso', { params: { state: 1 } });
        pisos.value = data.data.map((a: any) => ({ label: a.name, value: a.id }));
    } catch (e) {
        toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'No se pudieron cargar los pisos' });
        console.error(e);
    }
};

const updateTable = async (): Promise<void> => {
    submitted.value = true;
    serverErrors.value = {};

    try {
        const dataToSend = {
            name: table.value.name,
            tablenum: table.value.tablenum,
            capacity: table.value.capacity,
            state: table.value.state === true,
            idArea: table.value.idArea,  // <-- CORREGIDO
            idFloor: table.value.idFloor // <-- CORREGIDO
        };

        await axios.put(`/mesa/${props.tableId}`, dataToSend);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Mesa actualizada correctamente',
            life: 3000
        });

        dialogVisible.value = false;
        emit('updated');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            serverErrors.value = error.response.data.errors;
            toast.add({
                severity: 'error',
                summary: 'Error de validación',
                detail: error.response.data.message || 'Revisa los campos.',
                life: 5000
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo actualizar la mesa',
                life: 3000
            });
        }
    }
};

</script>

<template>
    <Dialog
        v-model:visible="dialogVisible"
        header="Editar Mesa"
        modal
        :closable="true"
        :closeOnEscape="true"
        :style="{ width: '90%', maxWidth: '600px' }"
        class="p-2 sm:p-4"
    >
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <!-- Nombre -->
                <div class="sm:col-span-9">
                    <label class="block font-bold mb-2 text-sm sm:text-base">
                        Nombre <span class="text-red-500">*</span>
                    </label>
                    <InputText
                        v-model="table.name"
                        required
                        maxlength="100"
                        fluid
                        :class="{ 'p-invalid': serverErrors.name }"
                        class="w-full"
                    />
                    <small v-if="serverErrors.name" class="p-error text-xs sm:text-sm">
                        {{ serverErrors.name[0] }}
                    </small>
                </div>

                <!-- Estado -->
                <div class="sm:col-span-3">
                    <label class="block font-bold mb-2 text-sm sm:text-base">
                        Estado <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                        <Checkbox v-model="table.state" :binary="true" fluid />
                        <Tag
                            :value="table.state ? 'Activo' : 'Inactivo'"
                            :severity="table.state ? 'success' : 'danger'"
                            class="text-xs sm:text-sm"
                        />
                    </div>
                </div>

                <!-- Número -->
                <div class="sm:col-span-6">
                    <label class="mb-2 block font-bold text-sm sm:text-base">
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
                    <label class="mb-2 block font-bold text-sm sm:text-base">
                        Capacidad <span class="text-red-500">*</span>
                    </label>
                    <InputText
                        :modelValue="table.capacity !== null ? table.capacity.toString() : ''"
                        @update:modelValue="(val) => table.capacity = val ? parseInt(val.replace(/[^0-9]/g, '')) || null : null"
                        fluid
                        type="text"
                        maxlength="6"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        @keypress="(e) => {
                            const char = e.key;
                            if (!/[0-9]/.test(char)) e.preventDefault(); // Bloquea letras, comas, puntos, símbolos
                        }"
                        class="w-full"
                    />
                    <small v-if="submitted && !table.capacity" class="text-red-500 text-xs sm:text-sm">
                        La capacidad es obligatoria.
                    </small>
                    <small v-else-if="table.capacity! < 1" class="text-red-500 text-xs sm:text-sm">
                        Debe ser al menos 1 persona.
                    </small>
                    <small v-else-if="serverErrors.capacity" class="text-red-500 text-xs sm:text-sm">
                        {{ serverErrors.capacity[0] }}
                    </small>
                </div>

                <!-- Area -->
                <div class="sm:col-span-6">
                    <label class="mb-2 block font-bold text-sm sm:text-base">
                        Área <span class="text-red-500">*</span>
                    </label>
                    <Dropdown
                        v-model="table.idArea"
                        fluid
                        :options="areas"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Seleccione un área"
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
                    <label class="mb-2 block font-bold text-sm sm:text-base">
                        Piso <span class="text-red-500">*</span>
                    </label>
                    <Dropdown
                        v-model="table.idFloor"
                        :options="pisos"
                        fluid
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Seleccione un piso"
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
            <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateTable" :loading="loading" />
        </template>
    </Dialog>
</template>
