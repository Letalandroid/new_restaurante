<script setup lang="ts">
import axios from 'axios';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import { ref, watch } from 'vue';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';

interface InputData {
    name: string;
    priceSale: number | null;
    state: boolean;
    idAlmacen: number | null;
    description: string | null;
    unitMeasure: string | null;
    quantity?: number | null;
    quantityUnitMeasure?: number | null;
}

interface AlmacenOption {
    label: string;
    value: number;
}

interface UnitMeasureOption {
    label: string;
    value: string;
}

const props = defineProps<{
    visible: boolean;
    inputId: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

const toast = useToast();
const serverErrors = ref<Record<string, string[]>>({});
const submitted = ref(false);
const loading = ref(false);

const dialogVisible = ref<boolean>(props.visible);
watch(
    () => props.visible,
    (val) => (dialogVisible.value = val)
);
watch(dialogVisible, (val) => emit('update:visible', val));

const input = ref<InputData>({
    name: '',
    priceSale: null,
    state: true,
    idAlmacen: null,
    description: null,
    unitMeasure: null,
    quantity: null,
    quantityUnitMeasure: null,
});

const almacens = ref<AlmacenOption[]>([]);

watch(
    () => props.visible,
    async (val) => {
        if (val && props.inputId) {
            await fetchInput();
            await fetchAlmacens();
        }
    }
);

const fetchInput = async (): Promise<void> => {
    loading.value = true;
    try {
        const { data } = await axios.get(`/insumo/${props.inputId}`);
        const i = data.input;
        input.value = {
            name: i.name,
            priceSale: i.priceSale,
            quantity: i.quantity,
            state: i.state,
            idAlmacen: i.idAlmacen,
            description: i.description,
            unitMeasure: i.unitMeasure,
            quantityUnitMeasure: i.quantityUnitMeasure,
        };
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar los insumos',
            life: 3000,
        });
        console.error(error);
    } finally {
        loading.value = false;
    }
    
};
// Listado de unidades de medida
const unitMeasures = ref<UnitMeasureOption[]>([
    { label: 'Kilogramos', value: 'kg' },
    { label: 'Gramos', value: 'g' },
    { label: 'Litros', value: 'litros' },
    { label: 'Mililitros', value: 'ml' },
    { label: 'Unidad', value: 'unidad' },
]);

const fetchAlmacens = async (): Promise<void> => {
    try {
        const { data } = await axios.get('/almacen', { params: { state: 1 } });
        almacens.value = data.data.map((c: any) => ({ label: c.name, value: c.id }));
    } catch (e) {
        toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'No se pudieron cargar los almacens' });
        console.error(e);
    }
};

const updateInput = async (): Promise<void> => {
    submitted.value = true;
    serverErrors.value = {};

    try {
        const dataToSend = {
            name: input.value.name,
            priceSale: parseFloat(String(input.value.priceSale)),
            state: input.value.state === true,
            idAlmacen: input.value.idAlmacen,
            description: input.value.description,
            unitMeasure: input.value.unitMeasure,
            quantityUnitMeasure: input.value.quantityUnitMeasure,
        };

        console.log('Datos a enviar:', dataToSend);

        await axios.put(`/insumo/${props.inputId}`, dataToSend);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Insumo actualizado correctamente',
            life: 3000,
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
                life: 5000,
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo actualizar el insumo',
                life: 3000,
            });
        }
    }
};
</script>

<template>
    <Dialog 
        v-model:visible="dialogVisible" 
        header="Editar Insumo" 
        modal 
        :closable="true" 
        :closeOnEscape="true" 
        :style="{ width: '90%', maxWidth: '600px' }"
    >
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">

                <!-- Nombre -->
                <div class="col-span-8 sm:col-span-10">
                    <label class="mb-2 block font-bold">Nombre <span class="text-red-500">*</span></label>
                    <InputText 
                        v-model="input.name" 
                        required 
                        maxlength="100" 
                        fluid 
                        :class="{ 'p-invalid': serverErrors.name }" 
                    />
                    <small v-if="serverErrors.name" class="p-error">{{ serverErrors.name[0] }}</small>
                </div>

                <!-- Estado -->
                <div class="col-span-4 sm:col-span-2">
                    <label class="mb-2 block font-bold">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="input.state" :binary="true" />
                        <Tag :value="input.state ? 'Activo' : 'Inactivo'" :severity="input.state ? 'success' : 'danger'" />
                    </div>
                </div>

                <!-- Precio Venta -->
                <div class="col-span-12 sm:col-span-6">
                    <label for="priceSale" class="block font-bold mb-2">Precio de Venta <span class="text-red-500">*</span></label>
                    <InputNumber
                        id="priceSale"
                        v-model="input.priceSale"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        mode="currency"
                        currency="PEN"
                        locale="es-PE"
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.priceSale }"
                    />
                    <small v-if="serverErrors.priceSale" class="p-error">{{ serverErrors.priceSale[0] }}</small>
                </div>

                <!-- Cantidad por medida -->
                <div class="col-span-12 sm:col-span-6">
                    <label for="quantityUnitMeasure" class="block font-bold mb-2">Cantidad por medida <span class="text-red-500">*</span></label>
                    <InputNumber
                        id="quantityUnitMeasure"
                        v-model="input.quantityUnitMeasure"
                        :minFractionDigits="2"
                        :maxFractionDigits="2"
                        class="w-full"
                        :class="{ 'p-invalid': serverErrors.quantityUnitMeasure }"
                    />
                    <small v-if="serverErrors.quantityUnitMeasure" class="p-error">{{ serverErrors.quantityUnitMeasure[0] }}</small>
                </div>

                <!-- Unidad de Medida -->
                <div class="col-span-12 sm:col-span-6">
                    <label class="mb-2 block font-bold">Unidad de Medida <span class="text-red-500">*</span></label>
                    <Select
                        v-model="input.unitMeasure"
                        :options="unitMeasures"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Seleccione Unidad de Medida"
                        class="w-full"
                    />
                    <small v-if="serverErrors.unitMeasure" class="p-error">{{ serverErrors.unitMeasure[0] }}</small>
                </div>

                <!-- Almacén -->
                <div class="col-span-12 sm:col-span-6">
                    <label class="mb-2 block font-bold">Almacen <span class="text-red-500">*</span></label>
                    <Dropdown
                        v-model="input.idAlmacen"
                        :options="almacens"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Seleccione Almacen"
                        filter
                        filterBy="label"
                        filterPlaceholder="Buscar almacen..."
                        class="w-full"
                    />
                    <small v-if="submitted && !input.idAlmacen" class="text-red-500">El Almacen es obligatorio.</small>
                    <small v-else-if="serverErrors.idAlmacen" class="text-red-500">{{ serverErrors.idAlmacen[0] }}</small>
                </div>

                <!-- Descripción -->
                <div class="col-span-12">
                    <label class="mb-2 block font-bold">Descripcion <span class="text-red-500">*</span></label>
                    <InputText 
                        v-model="input.description" 
                        required 
                        maxlength="150" 
                        fluid 
                        :class="{ 'p-invalid': serverErrors.description }" 
                    />
                    <small v-if="serverErrors.description" class="p-error">{{ serverErrors.description[0] }}</small>
                </div>

            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateInput" :loading="loading" />
        </template>
    </Dialog>
</template>
