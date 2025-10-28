<template> 
  <section 
    id="contacto" 
    class="flex min-h-screen flex-col items-center justify-center 
           bg-white dark:bg-gray-900 
           px-4 sm:px-6 py-12 sm:py-16 transition-colors duration-500"
  >
    <!-- Título -->
    <div class="mb-8 sm:mb-12 text-center">
      <h2 class="mb-3 sm:mb-4 text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white">
        Reservaciones
      </h2>
      <p class="mx-auto mt-3 sm:mt-4 max-w-2xl text-base sm:text-lg text-neutral-600 dark:text-gray-300 px-2">
        Reserva en Sabor Andino y disfruta de la mejor experiencia gastronómica.
      </p>
    </div>

    <!-- Opciones de contacto -->
    <div class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 max-w-4xl w-full px-2">
      <div
        v-for="contacto in contactos"
        :key="contacto.title"
        class="flex items-start gap-3 sm:gap-4 p-4 sm:p-6 
               bg-white dark:bg-gray-800 
               rounded-xl sm:rounded-2xl shadow-md hover:shadow-lg 
               border border-gray-200 dark:border-gray-700
               transition"
      >
        <!-- Icono -->
        <div class="flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center 
                    rounded-full bg-yellow-100 dark:bg-gray-700 flex-shrink-0">
          <component :is="contacto.icon" class="h-5 w-5 sm:h-6 sm:w-6 text-yellow-600 dark:text-yellow-400" />
        </div>

        <!-- Texto -->
        <div class="min-w-0 flex-1">
          <h3 class="text-base sm:text-lg font-semibold text-gray-800 dark:text-white truncate">
            {{ contacto.title }}
          </h3>
          <p class="mt-1 text-sm sm:text-base text-gray-600 dark:text-gray-300 break-words" v-html="contacto.info"></p>
        </div>
      </div>
    </div>

    <!-- Botón que abre el modal -->
    <Dialog :open="reservaDialog" @update:open="onDialogOpen">
      <DialogTrigger as-child>
        <button 
          class="mt-8 sm:mt-12 bg-yellow-600 text-white px-5 py-2.5 sm:px-6 sm:py-3 rounded-lg hover:bg-yellow-700 shadow text-sm sm:text-base">
          Hacer Reservación
        </button>
      </DialogTrigger>

      <DialogContent
        class="max-w-[95vw] sm:max-w-4xl bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100
               rounded-xl sm:rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 p-4 sm:p-6 mx-2"
      >
        <DialogHeader>
          <DialogTitle class="text-lg sm:text-xl">Realizar Reservación</DialogTitle>
          <DialogDescription class="text-sm sm:text-base">
            Completa los siguientes pasos para realizar tu reserva.
          </DialogDescription>
        </DialogHeader>

        <!-- Stepper -->
        <div class="mt-4 sm:mt-6">
          <Stepper v-model:value="activeStep" linear>
            <!-- Lista de pasos (barra superior) - Mejorado para móviles -->
            <StepList class="flex overflow-x-auto pb-2 -mx-1 px-1">
              <Step :value="1" class="text-xs sm:text-sm whitespace-nowrap min-w-0 flex-1">Personas</Step>
              <Step :value="2" class="text-xs sm:text-sm whitespace-nowrap min-w-0 flex-1">Fecha</Step>
              <Step :value="3" class="text-xs sm:text-sm whitespace-nowrap min-w-0 flex-1">Hora</Step>
              <Step :value="4" class="text-xs sm:text-sm whitespace-nowrap min-w-0 flex-1">Datos</Step>
            </StepList>

            <!-- Panels -->
            <StepPanels>
              <!-- Panel: Personas -->
              <StepPanel :value="1">
                <div class="flex flex-col items-center">
                  <h3 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4 text-center">Elige la cantidad de personas</h3>

                  <!-- Cantidad seleccionada -->
                  <div v-if="reserva.number_people" class="mb-4 sm:mb-6 p-2 sm:p-3 bg-blue-50 dark:bg-blue-900 rounded-lg">
                    <span class="text-base sm:text-lg font-medium text-blue-700 dark:text-blue-300">
                      {{ numeroALetras(reserva.number_people) }} personas
                    </span>
                  </div>

                  <!-- Botones de cantidad -->
                  <div class="grid grid-cols-3 gap-2 sm:gap-3 mb-3 sm:mb-4 w-full max-w-xs">
                    <button
                      v-for="num in mostrarNumeros"
                      :key="num"
                      @click="seleccionarPersonas(num)"
                      :class="[
                        'p-3 sm:p-4 rounded-lg border-2 text-sm sm:text-lg font-semibold transition-all',
                        reserva.number_people === num
                          ? 'bg-yellow-600 text-white border-yellow-600'
                          : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-yellow-500'
                      ]"
                    >
                      {{ num }}
                    </button>
                  </div>

                  <!-- Botón para mostrar más números -->
                  <button
                    v-if="!mostrarTodos"
                    @click="mostrarTodos = true"
                    class="text-yellow-600 dark:text-yellow-400 hover:underline mb-3 sm:mb-4 text-sm"
                  >
                    + Ver más opciones
                  </button>

                  <div class="flex justify-end w-full mt-4 sm:mt-6">
                    <Button
                      label="Siguiente"
                      @click="activeStep = 2"
                      :disabled="!reserva.number_people"
                      icon="pi pi-arrow-right"
                      class="text-sm sm:text-base"
                    />
                  </div>
                </div>
              </StepPanel>

              <!-- Panel: Fecha -->
              <StepPanel :value="2">
                <div class="flex flex-col items-center">
                  <h3 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4 text-center">Selecciona una fecha</h3>

                  <!-- Fecha seleccionada -->
                  <div v-if="reserva.date" class="mb-4 sm:mb-6 p-2 sm:p-3 bg-blue-50 dark:bg-blue-900 rounded-lg">
                    <span class="text-base sm:text-lg font-medium text-blue-700 dark:text-blue-300">
                      {{ formatearFecha(reserva.date) }}
                    </span>
                  </div>

                  <!-- Calendario sin columna de semana -->
                  <Calendar
                    v-model="reserva.date"
                    :minDate="minDate"
                    inline
                    :showWeek="false"
                    class="w-full calendar-sin-semana max-w-xs sm:max-w-none"
                    dateFormat="dd/mm/yy"
                  />

                  <div class="flex justify-between w-full mt-4 sm:mt-6">
                    <Button
                      label="Regresar"
                      @click="activeStep = 1"
                      icon="pi pi-arrow-left"
                      text
                      class="text-sm sm:text-base"
                    />
                    <Button
                      label="Siguiente"
                      @click="activeStep = 3"
                      :disabled="!reserva.date"
                      icon="pi pi-arrow-right"
                      class="text-sm sm:text-base"
                    />
                  </div>
                </div>
              </StepPanel>

              <!-- Panel: Hora -->
              <StepPanel :value="3">
                <div class="flex flex-col items-center">
                  <h3 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4 text-center">Selecciona una hora</h3>

                  <!-- Hora seleccionada -->
                  <div v-if="reserva.hour" class="mb-4 sm:mb-6 p-2 sm:p-3 bg-blue-50 dark:bg-blue-900 rounded-lg">
                    <span class="text-base sm:text-lg font-medium text-blue-700 dark:text-blue-300">
                      {{ formatearHoraConPM(reserva.hour) }}
                    </span>
                  </div>

                  <!-- Botones de hora con formato PM - Mejorado para móviles -->
                  <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 sm:gap-3 w-full max-w-xs sm:max-w-none">
                    <button
                      v-for="hora in horasDisponiblesFiltradas"
                      :key="hora"
                      @click="reserva.hour = hora"
                      :class="[
                        'p-2 sm:p-3 rounded-lg border-2 text-xs sm:text-sm font-semibold transition-all',
                        reserva.hour === hora
                          ? 'bg-yellow-600 text-white border-yellow-600'
                          : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-yellow-500'
                      ]"
                    >
                      {{ formatearHoraConPM(hora) }}
                    </button>
                  </div>

                  <div class="flex justify-between w-full mt-4 sm:mt-6">
                    <Button
                      label="Regresar"
                      @click="activeStep = 2"
                      icon="pi pi-arrow-left"
                      text
                      class="text-sm sm:text-base"
                    />
                    <Button
                      label="Siguiente"
                      @click="activeStep = 4"
                      :disabled="!reserva.hour"
                      icon="pi pi-arrow-right"
                      class="text-sm sm:text-base"
                    />
                  </div>
                </div>
              </StepPanel>

              <!-- Panel: Datos -->
              <StepPanel :value="4">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
                  <!-- Formulario de datos -->
                  <div class="lg:col-span-2">
                    <h3 class="text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Ya casi terminas</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                      <div>
                        <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Nombre *</label>
                        <InputText
                          v-model="reserva.name"
                          placeholder="Ingresa tu nombre"
                          class="w-full text-sm sm:text-base"
                          :class="{ 'p-invalid': serverErrors.name }"
                        />
                        <small v-if="serverErrors.name" class="text-red-500 text-xs">{{ serverErrors.name[0] }}</small>
                      </div>

                      <div>
                        <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Apellido *</label>
                        <InputText
                          v-model="reserva.lastname"
                          placeholder="Ingresa tu apellido"
                          class="w-full text-sm sm:text-base"
                          :class="{ 'p-invalid': serverErrors.lastname }"
                        />
                        <small v-if="serverErrors.lastname" class="text-red-500 text-xs">{{ serverErrors.lastname[0] }}</small>
                      </div>

                      <div>
                        <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Email *</label>
                        <InputText
                          v-model="reserva.email"
                          type="email"
                          placeholder="correo@ejemplo.com"
                          class="w-full text-sm sm:text-base"
                          :class="{ 'p-invalid': serverErrors.email }"
                        />
                        <small v-if="serverErrors.email" class="text-red-500 text-xs">{{ serverErrors.email[0] }}</small>
                      </div>

                      <div>
                        <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Teléfono *</label>
                        <InputText
                          v-model="reserva.phone"
                          maxlength="9"
                          placeholder="999999999"
                          class="w-full text-sm sm:text-base"
                          :class="{ 'p-invalid': serverErrors.phone }"
                        />
                        <small v-if="serverErrors.phone" class="text-red-500 text-xs">{{ serverErrors.phone[0] }}</small>
                      </div>

                      <div class="md:col-span-2">
                        <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1 sm:mb-2 text-sm sm:text-base">Tipo de Comprobante *</label>
                        <!-- Select nativo -->
                        <select 
                          v-model="reserva.client_type_id"
                          @change="onTipoClienteChange"
                          class="w-full p-2 sm:p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm sm:text-base"
                          :class="{ 'border-red-500': serverErrors.client_type_id }"
                        >
                          <option value="" disabled selected>Seleccione tipo de comprobante</option>
                          <option 
                            v-for="tipo in tiposCliente" 
                            :key="tipo.id" 
                            :value="tipo.id"
                          >
                            {{ getNombreComprobante(tipo.name) }}
                          </option>
                        </select>
                        <small v-if="serverErrors.client_type_id" class="text-red-500 text-xs">{{ serverErrors.client_type_id[0] }}</small>
                      </div>

                      <!-- Campo de código (oculto inicialmente) -->
                      <div v-if="reserva.client_type_id" class="md:col-span-2">
                        <label class="block text-gray-700 dark:text-gray-200 font-medium mb-1 sm:mb-2 text-sm sm:text-base">
                          {{ reserva.client_type_id === 1 ? 'DNI (8 dígitos)' : 'RUC (11 dígitos)' }} *
                        </label>
                        <InputText
                          v-model="reserva.codigo"
                          :maxlength="reserva.client_type_id === 1 ? 8 : 11"
                          :placeholder="reserva.client_type_id === 1 ? 'Ingresa tu DNI' : 'Ingresa tu RUC'"
                          class="w-full text-sm sm:text-base"
                          :class="{ 'p-invalid': serverErrors.codigo }"
                        />
                        <small v-if="serverErrors.codigo" class="text-red-500 text-xs">{{ serverErrors.codigo[0] }}</small>
                      </div>
                    </div>
                  </div>

                  <!-- Resumen de la reserva -->
                  <div class="lg:col-span-1">
                    <div class="bg-gray-50 dark:bg-gray-800 p-4 sm:p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                      <h4 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4">Resumen de tu reserva</h4>

                      <div class="space-y-2 sm:space-y-3">
                        <div class="flex justify-between">
                          <span class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">Personas:</span>
                          <span class="font-semibold text-sm sm:text-base">{{ reserva.number_people }}</span>
                        </div>

                        <div class="flex justify-between">
                          <span class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">Fecha:</span>
                          <span class="font-semibold text-sm sm:text-base">{{ formatearFecha(reserva.date) }}</span>
                        </div>

                        <div class="flex justify-between">
                          <span class="text-gray-600 dark:text-gray-400 text-sm sm:text-base">Hora:</span>
                          <span class="font-semibold text-sm sm:text-base">{{ formatearHoraConPM(reserva.hour) }}</span>
                        </div>

                        <hr class="my-2 sm:my-3 border-gray-300 dark:border-gray-600">

                        <div class="flex justify-between text-base sm:text-lg">
                          <span class="font-semibold">Total:</span>
                          <span class="font-bold text-yellow-600">Gratis</span>
                        </div>
                      </div>

                      <div class="mt-4 sm:mt-6 space-y-2 sm:space-y-3">
                        <Button
                          label="Confirmar Reservación"
                          @click="confirmarReserva"
                          icon="pi pi-check"
                          class="w-full text-sm sm:text-base"
                          severity="success"
                          :loading="loading"
                          :disabled="!reserva.client_type_id || !reserva.codigo"
                        />
                        <Button
                          label="Regresar al Inicio"
                          @click="activeStep = 1"
                          icon="pi pi-arrow-left"
                          class="w-full text-sm sm:text-base"
                          text
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </StepPanel>
            </StepPanels>
          </Stepper>
        </div>

        <!-- Mensaje de éxito/error -->
        <div v-if="mensaje.text" class="mt-3 sm:mt-4">
          <Message 
            :severity="mensaje.tipo" 
            :closable="false"
            :class="mensaje.tipo === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'"
            class="text-sm"
          >
            {{ mensaje.text }}
          </Message>
        </div>
      </DialogContent>
    </Dialog>
  </section>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from 'primevue/usetoast'
import {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog'

/* PrimeVue Stepper components según docs */
import Stepper from 'primevue/stepper'
import StepList from 'primevue/steplist'
import Step from 'primevue/step'
import StepPanels from 'primevue/steppanels'
import StepPanel from 'primevue/steppanel'

/* Otros PrimeVue */
import Button from 'primevue/button'
import Calendar from 'primevue/calendar'
import InputText from 'primevue/inputtext'
import Message from 'primevue/message'

import { MapPin, Phone, Mail, Clock } from 'lucide-vue-next'

/* Estado */
const toast = useToast()
const activeStep = ref<number>(1)
const mostrarTodos = ref(false)
const minDate = ref(new Date())
const loading = ref(false)
const reservaDialog = ref(false) // Controla la visibilidad del diálogo

const contactos = [
  { title: 'Dirección', icon: MapPin, info: 'Av. Los Sauces 345, Chulucanas, Piura, Perú' },
  { title: 'Teléfonos', icon: Phone, info: `<a href="tel:+51987654321">+51 987 654 321</a> / <a href="tel:+51912345678">+51 912 345 678</a>` },
  { title: 'Correo electrónico', icon: Mail, info: `<a href="mailto:reservas@saborandino.pe">reservas@saborandino.pe</a>` },
  { title: 'Horario de atención', icon: Clock, info: 'Lunes a domingo: 11:00 am - 11:00 pm' },
]

const reserva = reactive({
  number_people: null as number | null,
  date: null as Date | null,
  hour: '',
  name: '',
  lastname: '',
  email: '',
  phone: '',
  client_type_id: null as number | null,
  codigo: ''
})

// Tipos de cliente cargados desde el backend
const tiposCliente = ref<any[]>([])

const mensaje = reactive({
  text: '',
  tipo: '' as 'success' | 'error' | 'info' | 'warn' | undefined
})

const serverErrors = ref<Record<string, string[]>>({})

/* Números para mostrar (1-6 inicialmente, 1-10 cuando se expande) */
const mostrarNumeros = computed(() => {
  return mostrarTodos.value ? [1,2,3,4,5,6,7,8,9,10] : [1,2,3,4,5,6]
})

/* Generar horas disponibles de 12:00 a 17:00 cada 15 minutos */
const horasDisponibles = computed(() => {
  const horas: string[] = []
  for (let hora = 12; hora <= 17; hora++) {
    for (let minuto = 0; minuto < 60; minuto += 15) {
      if (hora === 17 && minuto > 0) break // Solo hasta 17:00
      const horaFormateada = `${hora.toString().padStart(2, '0')}:${minuto.toString().padStart(2, '0')}`
      horas.push(horaFormateada)
    }
  }
  return horas
})

/* Filtrar horas disponibles para ocultar las que ya pasaron */
const horasDisponiblesFiltradas = computed(() => {
  if (!reserva.date) return horasDisponibles.value
  
  const hoy = new Date()
  const fechaReserva = new Date(reserva.date)
  
  // Si la fecha seleccionada es hoy, filtrar horas pasadas
  if (fechaReserva.toDateString() === hoy.toDateString()) {
    return horasDisponibles.value.filter(hora => {
      const [horaStr, minutoStr] = hora.split(':')
      const horaReserva = new Date()
      horaReserva.setHours(parseInt(horaStr), parseInt(minutoStr), 0, 0)
      return horaReserva > hoy
    })
  }
  
  return horasDisponibles.value
})

// Función para mapear nombres de comprobante en el frontend
const getNombreComprobante = (nombreBackend: string): string => {
  const mapeo: { [key: string]: string } = {
    'Persona natural': 'Boleta',
    'Persona jurídica': 'Factura'
  }
  return mapeo[nombreBackend] || nombreBackend
}

// Cargar tipos de cliente desde el backend
const fetchTiposCliente = async (): Promise<void> => {
  try {
    const response = await axios.get('/tipo_cliente', { params: { state: 1 } })
    tiposCliente.value = response.data.data
  } catch (error: any) {
    console.error('Error al cargar tipos de cliente:', error)
    toast.add({ 
      severity: 'error', 
      summary: 'Error', 
      detail: 'No se pudieron cargar los tipos de cliente', 
      life: 3000 
    })
  }
}

// Función para cerrar el diálogo
const closeDialog = (): void => {
  reservaDialog.value = false
  resetearFormulario()
}

// Función que se ejecuta cuando el modal se abre/cierra
const onDialogOpen = (open: boolean): void => {
  reservaDialog.value = open
  if (!open) {
    // Si el modal se cierra, resetear todos los datos
    resetearFormulario()
  }
}

const onTipoClienteChange = (): void => {
  // Limpiar el campo código cuando cambia el tipo de cliente
  reserva.codigo = ''
}

const seleccionarPersonas = (numero: number) => {
  reserva.number_people = numero
}

const numeroALetras = (numero: number | null): string => {
  if (numero === null) return ''
  const numerosEnLetras = [
    'Cero', 'Una', 'Dos', 'Tres', 'Cuatro', 'Cinco',
    'Seis', 'Siete', 'Ocho', 'Nueve', 'Diez'
  ]
  return numerosEnLetras[numero] || numero.toString()
}

const formatearFecha = (fecha: Date | null): string => {
  if (!fecha) return ''
  const opciones: Intl.DateTimeFormatOptions = {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }
  return fecha.toLocaleDateString('es-ES', opciones)
}

/* Formatear hora para mostrar con PM */
const formatearHoraConPM = (hora: string): string => {
  const [horaStr, minutoStr] = hora.split(':')
  const horaNum = parseInt(horaStr)
  
  // Convertir formato 24h a 12h con PM
  if (horaNum === 12) {
    return `12:${minutoStr} PM`
  } else if (horaNum > 12) {
    return `${horaNum - 12}:${minutoStr} PM`
  }
  
  return `${horaNum}:${minutoStr} PM`
}

// Función para resetear completamente el formulario
const resetearFormulario = (): void => {
  Object.assign(reserva, {
    number_people: null,
    date: null,
    hour: '',
    name: '',
    lastname: '',
    email: '',
    phone: '',
    client_type_id: null,
    codigo: ''
  })
  serverErrors.value = {}
  mensaje.text = ''
  mensaje.tipo = undefined
  activeStep.value = 1
  mostrarTodos.value = false
}

const confirmarReserva = async () => {
  // Validar campos requeridos
  if (!reserva.name || !reserva.lastname || !reserva.email || !reserva.phone || 
      !reserva.client_type_id || !reserva.codigo || !reserva.number_people || 
      !reserva.date || !reserva.hour) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Por favor completa todos los campos obligatorios',
      life: 3000
    })
    return
  }

  // Validar longitud del código según el tipo de cliente
  if (reserva.client_type_id === 1 && reserva.codigo.length !== 8) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'El DNI debe tener 8 dígitos',
      life: 3000
    })
    return
  }

  if (reserva.client_type_id === 2 && reserva.codigo.length !== 11) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'El RUC debe tener 11 dígitos',
      life: 3000
    })
    return
  }

  loading.value = true
  serverErrors.value = {}
  mensaje.text = ''
  mensaje.tipo = undefined

  try {
    // Preparar datos para enviar al backend
    const datosReserva = {
      // Datos del cliente
      name: reserva.name,
      lastname: reserva.lastname,
      email: reserva.email,
      phone: reserva.phone,
      codigo: reserva.codigo,
      client_type_id: reserva.client_type_id,
      state: true, // Por defecto activo
      
      // Datos de la reservación
      number_people: reserva.number_people,
      date: reserva.date.toISOString().split('T')[0], // Formato YYYY-MM-DD
      hour: reserva.hour
    }

    // Enviar la reservación al backend
    await axios.post('/reservacionL', datosReserva)

    // Mostrar mensaje de éxito
    mensaje.text = '¡Reserva realizada con éxito! Te hemos enviado un correo de confirmación.'
    mensaje.tipo = 'success'

    toast.add({
      severity: 'success',
      summary: 'Éxito',
      detail: 'Reserva realizada correctamente',
      life: 5000
    })

    // Cerrar el diálogo después de 2 segundos (para que el usuario vea el mensaje de éxito)
    setTimeout(() => {
      closeDialog()
    }, 3000)

  } catch (error: any) {
    console.error('Error al realizar reserva:', error)
    
    if (error.response && error.response.status === 422) {
      // Errores de validación del backend
      serverErrors.value = error.response.data.errors || {}
      mensaje.text = 'Por favor corrige los errores en el formulario'
      mensaje.tipo = 'error'
    } else {
      // Error general
      mensaje.text = 'Hubo un error al realizar la reserva. Por favor intenta nuevamente.'
      mensaje.tipo = 'error'
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'No se pudo realizar la reserva',
        life: 3000
      })
    }
  } finally {
    loading.value = false
  }
}

// Cargar tipos de cliente cuando se monta el componente
onMounted(() => {
  fetchTiposCliente()
})
</script>

<style scoped>
/* Estilos para ocultar la columna de semana en el calendario */
.calendar-sin-semana :deep(.p-datepicker table th:first-child),
.calendar-sin-semana :deep(.p-datepicker table td:first-child) {
  display: none;
}

.calendar-sin-semana :deep(.p-datepicker table) {
  width: 100%;
}

.calendar-sin-semana :deep(.p-datepicker table th),
.calendar-sin-semana :deep(.p-datepicker table td) {
  width: calc(100% / 7);
}

/* Mejoras adicionales para móviles */
@media (max-width: 640px) {
  .calendar-sin-semana :deep(.p-datepicker table th),
  .calendar-sin-semana :deep(.p-datepicker table td) {
    padding: 0.25rem;
  }
  
  .calendar-sin-semana :deep(.p-datepicker .p-datepicker-header) {
    padding: 0.5rem;
  }
}
</style>