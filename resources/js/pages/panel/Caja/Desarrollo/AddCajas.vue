<template>
    <!-- Toolbar responsive -->
    <Toolbar class="mb-6">
        <template #start>
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto justify-center sm:justify-start">
                <Button 
                    label="Agregar Cajas" 
                    icon="pi pi-plus" 
                    severity="secondary" 
                    class="w-full sm:w-auto" 
                    @click="openNew" 
                />
            </div>
        </template>
    </Toolbar>

    <!-- Diálogo responsive -->
    <Dialog 
        v-model:visible="cajaDialog" 
        :style="{ width: '90%', maxWidth: '500px' }" 
        header="Registro de cajas" 
        :modal="true"
    >
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Número de cajas -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2 text-sm sm:text-base">
                        Ingresa número de cajas a crear: 
                        <span class="text-red-500">*</span>
                    </label>
                    <InputText 
                        v-model.trim="caja.numero_cajas" 
                        type="number" 
                        fluid 
                        maxlength="100"
                        class="w-full"
                    />
                    <small 
                        v-if="submitted && !caja.numero_cajas" 
                        class="text-red-500 block mt-1 text-xs sm:text-sm"
                    >
                        El número de cajas es obligatorio.
                    </small>
                    <small 
                        v-else-if="serverErrors.numero_cajas" 
                        class="text-red-500 block mt-1 text-xs sm:text-sm"
                    >
                        {{ serverErrors.numero_cajas[0] }}
                    </small>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarCaja" />
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
import { useToast } from 'primevue/usetoast';

interface Caja {
    numero_cajas: string;
}

interface ServerErrors {
    [key: string]: string[];
}

const toast = useToast();
const submitted = ref<boolean>(false);
const cajaDialog = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});
const emit = defineEmits<{
    (e: 'caja-agregada'): void;
}>();

const caja = ref<Caja>({
    numero_cajas: '',
});

function resetCaja() {
    caja.value = {
        numero_cajas: '',
    };
    serverErrors.value = {};
    submitted.value = false;
}

function openNew() {
    resetCaja();
    cajaDialog.value = true;
}

function hideDialog() {
    cajaDialog.value = false;
    resetCaja();
}

function guardarCaja() {
    submitted.value = true;
    serverErrors.value = {};

    axios.post('/caja', caja.value)
        .then(() => {
            toast.add({ severity: 'success', summary: 'Éxito', detail: 'Caja registrada', life: 3000 });
            hideDialog();
            emit('caja-agregada');
        })
        .catch((error: AxiosError) => {
            if (error.response && error.response.status === 422) {
                serverErrors.value = (error.response.data as any).errors || {};
            } else {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: 'No se pudo registrar la caja',
                    life: 3000
                });
            }
        });
}
</script>
