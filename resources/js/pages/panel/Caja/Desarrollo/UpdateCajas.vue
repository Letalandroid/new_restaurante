<script setup lang="ts">
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import Dropdown from 'primevue/dropdown';

interface Caja {
    state: boolean;
    idVendedor: number | null;
    numero_cajas?: string;
}

interface VendedorOption {
    label: string;
    value: number;
}

interface ServerErrors {
    idVendedor?: string[];
}

const props = defineProps<{
    visible: boolean;
    cajaId: number | null;
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

watch(() => props.visible, (val) => (dialogVisible.value = val));
watch(dialogVisible, (val) => emit('update:visible', val));

const caja = ref<Caja>({
    state: false,
    idVendedor: null
});

const vendedores = ref<VendedorOption[]>([]);

watch(
    () => props.visible,
    async (val) => {
        if (val && props.cajaId) {
            await fetchCaja();
            await fetchVendedores();
        }
    }
);

const fetchCaja = async () => {
    loading.value = true;
    try {
        const { data } = await axios.get(`/caja/${props.cajaId}`);
        const c = data.caja;
        caja.value = {
            state: c.state,
            numero_cajas: c.numero_cajas,
            idVendedor: c.idVendedor,
        };
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar la caja',
            life: 3000
        });
        console.error('Error caja', error)
    } finally {
        loading.value = false;
    }
};

const fetchVendedores = async () => {
    try {
        const { data } = await axios.get('/usuarios', { params: { status: true } });
        vendedores.value = data.data.map((c: any) => ({ label: c.name1, value: c.id }));
    } catch (e) {
        toast.add({
            severity: 'warn',
            summary: 'Advertencia',
            detail: 'No se pudieron cargar los vendedores'
        });
        console.error('Error vendedores', e)
    }
};

const updateCaja = async () => {
    submitted.value = true;
    serverErrors.value = {};

    try {
        const dataToSend = {
            state: caja.value.state === true,
            idVendedor: caja.value.idVendedor
        };

        await axios.put(`/caja/${props.cajaId}`, dataToSend);

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Caja actualizada correctamente',
            life: 3000
        });

        dialogVisible.value = false;
        emit('updated');
    } catch (error: any) {
        if (error.response?.status === 422 && error.response?.data?.message) {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: error.response.data.message,
                life: 3000
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo actualizar la caja',
                life: 3000
            });
        }
    }
};
</script>

<template>
  <Dialog 
    v-model:visible="dialogVisible" 
    header="Editar Caja" 
    modal 
    :closable="true" 
    :closeOnEscape="true"
    :style="{ width: '90%', maxWidth: '450px' }"
  >
    <div class="flex flex-col gap-6">
        <div class="grid grid-cols-12 gap-4">
            <!-- Número de caja -->
            <div class="col-span-7 sm:col-span-9">
                <label class="block font-bold mb-2 text-sm sm:text-base">Número de caja</label>
                <InputText 
                  v-model="caja.numero_cajas" 
                  readonly 
                  fluid 
                  class="w-full"
                  disabled
                />
            </div>

            <!-- Estado -->
            <div class="col-span-5 sm:col-span-3">
                <label class="block font-bold mb-2 text-sm sm:text-base">
                  Estado <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-3 flex-wrap">
                    <Checkbox v-model="caja.state" :binary="true" fluid />
                    <Tag 
                      :value="caja.state ? 'Sin ocupar' : 'Ocupada'" 
                      :severity="caja.state ? 'success' : 'danger'" 
                    />
                </div>
            </div>

            <!-- Vendedor -->
            <div class="col-span-12">
                <label class="block font-bold mb-2 text-sm sm:text-base">
                  Vendedor <span class="text-red-500">*</span>
                </label>
                <Dropdown 
                    v-model="caja.idVendedor"
                    :options="vendedores"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Seleccione vendedor"
                    filter
                    filterBy="label"
                    fluid
                    class="w-full"
                    :class="{ 'p-invalid': serverErrors.idVendedor }" 
                />
                <small v-if="serverErrors.idVendedor" class="p-error text-xs sm:text-sm">
                  {{ serverErrors.idVendedor[0] }}
                </small>
            </div>
        </div>
    </div>

    <template #footer>
        <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
        <Button label="Guardar" icon="pi pi-check" @click="updateCaja" :loading="loading" />
    </template>
  </Dialog>
</template>
