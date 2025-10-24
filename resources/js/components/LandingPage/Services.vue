<template>
  <section
    id="servicios"
    class="flex flex-col items-center justify-center min-h-screen md:h-screen py-16 
           bg-white dark:bg-gray-900 transition-colors duration-500"
  >
    <div class="max-w-6xl mx-auto px-6 text-center w-full">
      <!-- Título -->
      <h2 class="text-4xl font-bold text-gray-800 dark:text-white">
        Nuestros Servicios
      </h2>
      <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
        En <span class="font-semibold text-yellow-700 dark:text-yellow-400">Sabor Andino</span> nos apasiona ofrecerte una experiencia gastronómica completa, 
        con atención personalizada, ingredientes frescos y el auténtico sabor peruano.
      </p>

      <!-- Grid de servicios -->
      <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="service in services"
          :key="service.title"
          class="flex flex-col items-center rounded-2xl border border-gray-200 dark:border-gray-700 
                 bg-white dark:bg-gray-800 p-8 shadow-md hover:shadow-xl 
                 transform hover:-translate-y-2 transition-all duration-300"
        >
          <!-- Icono -->
          <div class="flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
            <component :is="service.icon" class="h-8 w-8 text-yellow-600 dark:text-yellow-400" />
          </div>

          <h3 class="mt-6 text-xl font-semibold text-gray-800 dark:text-white">
            {{ service.title }}
          </h3>
          <p class="mt-3 text-gray-600 dark:text-gray-300 text-center">
            {{ service.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import {
  UtensilsCrossed,
  Coffee,
  Bike,
  PartyPopper,
  Salad,
  ShoppingBag,
} from 'lucide-vue-next'

const services = [
  {
    title: 'Comida Tradicional Peruana',
    description: 'Deléitate con los platos más representativos del Perú: ceviche, lomo saltado, ají de gallina y mucho más.',
    icon: UtensilsCrossed,
  },
  {
    title: 'Desayunos y Cafetería',
    description: 'Empieza el día con energía: jugos naturales, café artesanal, panes frescos y opciones saludables.',
    icon: Coffee,
  },
  {
    title: 'Delivery',
    description: 'Disfruta nuestros sabores desde casa. Pide por teléfono o en línea y recibe tu pedido caliente y puntual.',
    icon: Bike,
  },
  {
    title: 'Eventos y Celebraciones',
    description: 'Celebra tus momentos especiales con nosotros. Ofrecemos menús personalizados y atención exclusiva.',
    icon: PartyPopper,
  },
  {
    title: 'Comidas Saludables',
    description: 'Platos balanceados y nutritivos, preparados con ingredientes frescos y de origen local.',
    icon: Salad,
  },
  {
    title: 'Tienda de Productos Andinos',
    description: 'Llévate a casa productos artesanales y tradicionales del Perú: salsas, granos, hierbas y más.',
    icon: ShoppingBag,
  },
]
</script>
