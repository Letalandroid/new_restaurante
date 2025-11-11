<script setup lang="ts">
import { ref } from 'vue';
import axios, { AxiosError } from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import { useToast } from 'primevue/usetoast';
import ToolsReservation from './ToolsReservation.vue';

//Toast de notificaciones
const toast = useToast();

//Refs y estados
const submitted = ref<boolean>(false);
const configDialog = ref<boolean>(false);
const serverErrors = ref<{ [key: string]: string[] }>({});

//Datos del formulario
const configuracion = ref({
    waiting_minutes: 2, // Valor por defecto
    auto_expire: true
});

//Evento emitido al padre
const emit = defineEmits<{
    (e: 'configuracion-guardada'): void
}>();

//Reiniciar formulario
function resetForm() {
    configuracion.value = {
        waiting_minutes: 2,
        auto_expire: true
    };
    serverErrors.value = {};
    submitted.value = false;
}

//Abrir el diálogo
async function openDialog() {
    resetForm();

    try {
        const response = await axios.get('/reservation-settings');
        if (response.data?.config) {
            configuracion.value = {
                waiting_minutes: response.data.config.waiting_minutes || 2,
                auto_expire: response.data.config.auto_expire || false
            };
        }
    } catch (error) {
        console.error(error);
        console.warn('No hay configuración previa o error al obtenerla.');
    }

    configDialog.value = true;
}

//Cerrar diálogo
function hideDialog() {
    configDialog.value = false;
    resetForm();
}

//Guardar configuración
async function guardarConfiguracion() {
    submitted.value = true;
    serverErrors.value = {};

    if (!configuracion.value.waiting_minutes) return;

    try {
        const payload = {
            waiting_minutes: configuracion.value.waiting_minutes,
            auto_expire: configuracion.value.auto_expire
        };

        await axios.post('/reservation-settings', payload);

        toast.add({
            severity: 'success',
            summary: 'Configuración guardada',
            detail: 'El tiempo límite de espera fue actualizado correctamente.',
            life: 3000
        });

        hideDialog();
        emit('configuracion-guardada');
    } catch (error) {
        const axiosError = error as AxiosError<any>;
        if (axiosError.response && axiosError.response.status === 422) {
            serverErrors.value = axiosError.response.data.errors || {};
            toast.add({
                severity: 'error',
                summary: 'Error de validación',
                detail: 'Revisa los campos e intenta nuevamente.',
                life: 4000
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo guardar la configuración.',
                life: 4000
            });
        }
        console.error(error);
    }
}
</script>

<template>
  <Toolbar class="mb-6 flex flex-wrap justify-between items-center">
    <template #start>
      <Button
        label="Configurar Tiempo de Espera"
        icon="pi pi-clock"
        severity="secondary"
        class="w-full sm:w-auto"
        @click="openDialog"
      />
    </template>
    <template #end>
            <!-- ToolsPresentation para los botones de exportar e importar -->
            <ToolsReservation/>       
        </template>
  </Toolbar>

  <Dialog
    v-model:visible="configDialog"
    :style="{ width: '95vw', maxWidth: '600px' }"
    header="Configuración del Tiempo de Espera"
    :modal="true"
    class="p-2 sm:p-4"
  >
    <div class="flex flex-col gap-6">
      <div class="grid grid-cols-12 gap-4">
        <!-- Tiempo de espera -->
        <div class="col-span-12 md:col-span-6">
          <label class="block font-bold mb-2 sm:mb-3 text-sm sm:text-base">
            Tiempo de Espera (minutos)
            <span class="text-red-500">*</span>
          </label>
          <InputNumber
            v-model="configuracion.waiting_minutes"
            :min="1"
            showButtons
            inputClass="w-full"
            suffix=" min"
            class="w-full"
          />
          <small
            v-if="submitted && !configuracion.waiting_minutes"
            class="text-red-500 block mt-1 text-sm"
          >
            El tiempo de espera es obligatorio.
          </small>
          <small
            v-if="serverErrors.waiting_minutes"
            class="text-red-500 block mt-1 text-sm"
          >
            {{ serverErrors.waiting_minutes[0] }}
          </small>
        </div>

        <!-- Auto Expirar -->
        <div class="col-span-12 md:col-span-6 flex flex-row items-center gap-3 mt-4 md:mt-8">
          <Checkbox
            v-model="configuracion.auto_expire"
            :binary="true"
            inputId="auto_expire"
            class="scale-110"
          />
          <label
            for="auto_expire"
            class="font-medium text-sm sm:text-base leading-tight"
          >
            Expirar reservaciones automáticamente
          </label>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <template #footer>
      <div class="flex flex-col sm:flex-row gap-2 w-full sm:justify-end">
        <Button
          label="Cancelar"
          icon="pi pi-times"
          text
          @click="hideDialog"
          class="w-full sm:w-auto"
        />
        <Button
          label="Guardar"
          icon="pi pi-check"
          @click="guardarConfiguracion"
          class="w-full sm:w-auto"
        />
      </div>
    </template>
  </Dialog>
</template>
