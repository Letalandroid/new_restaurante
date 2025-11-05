<template>
  <div
    class="card p-4 sm:p-6 md:p-8 shadow-sm max-w-full sm:max-w-lg md:max-w-xl lg:max-w-2xl mx-auto bg-white rounded-lg"
  >
    <h2
      class="text-base sm:text-lg md:text-xl font-semibold mb-4 text-center sm:text-left"
    >
      Subir certificado .pem
    </h2>

    <form
      @submit.prevent="subirArchivo"
      enctype="multipart/form-data"
      class="flex flex-col gap-4"
    >
      <div class="mb-3 w-full">
        <input
          type="file"
          accept=".pem"
          @change="handleArchivo"
          class="form-control w-full text-sm sm:text-base border rounded-md p-2"
          required
        />
        <div v-if="errores.certificado" class="text-red-500 mt-1 text-sm">
          {{ errores.certificado }}
        </div>
      </div>

      <!-- ✅ Botón PrimeVue -->
      <div class="flex justify-center sm:justify-start">
        <Button
          type="submit"
          icon="pi pi-upload"
          label="Subir Certificado"
          class="w-full sm:w-auto"
          :loading="cargando"
          :disabled="cargando"
        />
      </div>

      <div
        v-if="mensaje"
        class="alert alert-success mt-3 text-center sm:text-left text-sm sm:text-base"
      >
        {{ mensaje }}
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import Button from 'primevue/button'

const archivo = ref<File | null>(null)
const errores = ref<{ certificado?: string }>({})
const mensaje = ref<string>('')
const cargando = ref<boolean>(false)

function handleArchivo(e: Event) {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    archivo.value = target.files[0]
  }
}

async function subirArchivo() {
  errores.value = {}
  mensaje.value = ''
  cargando.value = true

  if (!archivo.value) {
    errores.value = { certificado: 'Por favor selecciona un archivo .pem.' }
    cargando.value = false
    return
  }

  const formData = new FormData();
  formData.append('certificado', archivo.value);

  try {
    const response = await axios.post('/enviar-certificado', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    mensaje.value = response.data.message || 'Certificado subido correctamente.'
    archivo.value = null

    const input = document.querySelector('input[type="file"]') as HTMLInputElement | null
    if (input) input.value = ''
  } catch (error: any) {
    console.error('Error al subir certificado:', error)

    if (error.response) {
      if (error.response.status === 422) {
        errores.value = error.response.data.errors || {
          certificado: 'Error de validación del archivo.',
        }
      } else {
        errores.value = {
          certificado: error.response.data.message || 'Error inesperado del servidor.',
        }
      }
    } else {
      errores.value = { certificado: 'No se pudo conectar con el servidor.' }
    }
  } finally {
    cargando.value = false
  }
}
</script>

<style scoped>
.card {
  background: var(--surface-card);
  border-radius: 12px;
  border: 1px solid var(--surface-border);
  color: var(--text-color);
}
</style>
