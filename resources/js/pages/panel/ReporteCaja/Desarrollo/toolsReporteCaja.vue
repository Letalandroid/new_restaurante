<template>
  <div class="flex flex-col sm:flex-row justify-end items-center gap-3 p-4 w-full">
    <!-- Botón Exportar Excel -->
    <Button 
      variant="outlined"
      size="large"
      class="bg-green-600 hover:bg-green-700 text-white px-5 py-3 text-base rounded-lg shadow-md transition-all duration-200"
      icon="pi pi-file-excel"
      label="Exportar a Excel"
      @click="startDownload('excel')"
      :disabled="loading"
    />

    <!-- Botón Exportar PDF -->
    <Button 
      variant="outlined"
      size="large"
      class="bg-red-600 hover:bg-red-700 text-white px-5 py-3 text-base rounded-lg shadow-md transition-all duration-200"
      icon="pi pi-file-pdf"
      label="Exportar a PDF"
      @click="startDownload('pdf')"
      :disabled="loading"
    />

    <!-- Dialog de descarga -->
    <Dialog v-model:visible="loading" modal :closable="false" header="Descargando" :style="{ width: '90%', maxWidth: '400px' }">
      <div class="flex flex-col items-center justify-center p-4 text-center">
        <ProgressSpinner :style="{ width: '60px', height: '60px' }"/>
        <p class="mt-3 font-semibold text-sm sm:text-base">{{ downloadingText }}</p>
      </div>
    </Dialog>

    <!-- Toast -->
    <Toast />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import ProgressSpinner from 'primevue/progressspinner'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'
import axios from 'axios'

const toast = useToast()
const loading = ref(false)
const downloadingText = ref('')

const startDownload = async (type: 'pdf' | 'excel') => {
  const url = type === 'pdf' 
    ? '/panel/reports/export-pdf-reporteCajas' 
    : '/panel/reports/export-excel-reporteCajas'

  const filename = type === 'pdf' ? 'reporteCajas.pdf' : 'reporteCajas.xlsx'

  try {
    loading.value = true
    downloadingText.value = type === 'pdf' ? 'Descargando PDF...' : 'Descargando Excel...'

    const response = await axios.get(url, { responseType: 'blob' })

    const blob = new Blob([response.data], { type: response.data.type })
    const link = document.createElement('a')
    link.href = URL.createObjectURL(blob)
    link.download = filename
    document.body.appendChild(link)
    link.click()
    link.remove()

    toast.add({ 
      severity: 'success', 
      summary: 'Éxito', 
      detail: `${filename} descargado correctamente.`,
      life: 3000
    })
  } catch (error) {
    console.error(error)
    toast.add({ 
      severity: 'error', 
      summary: 'Error', 
      detail: 'Hubo un error al descargar el archivo.',
      life: 3000
    })
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.p-dialog .p-dialog-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

@media (max-width: 640px) {
  .p-dialog .p-dialog-content p {
    font-size: 0.875rem;
  }
}
</style>
