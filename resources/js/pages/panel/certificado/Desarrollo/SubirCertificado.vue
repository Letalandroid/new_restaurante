<template>
  <div class="card p-4 shadow-sm">
    <h2 class="text-lg font-semibold mb-4">Subir certificado .pem</h2>

    <form @submit.prevent="subirArchivo" enctype="multipart/form-data">
      <div class="mb-3">
        <input
          type="file"
          accept=".pem"
          @change="handleArchivo"
          class="form-control"
          required
        />
        <div v-if="errores.certificado" class="text-danger mt-1">
          {{ errores.certificado }}
        </div>
      </div>

      <!-- ✅ Botón PrimeVue -->
      <Button
        type="submit"
        icon="pi pi-upload"
        label="Subir Certificado"
        class="w-full md:w-auto"
        :loading="cargando"
        :disabled="cargando"
      />

      <div v-if="mensaje" class="alert alert-success mt-3">
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
