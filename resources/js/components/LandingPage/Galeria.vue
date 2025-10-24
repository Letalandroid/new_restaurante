<template>
  <section 
    id="galeria" 
    class="flex flex-col min-h-screen items-center justify-center 
           px-6 py-16 
           bg-gradient-to-r from-yellow-50 to-orange-50
           transition-colors duration-500"
  >
    <!-- Título -->
    <div class="mb-12 text-center">
      <h2 class="mb-4 text-3xl font-bold text-neutral-900 dark:text-white">
        Galería de Sabores
      </h2>
      <p class="mx-auto mt-4 max-w-2xl text-lg text-neutral-600 dark:text-gray-300">
        Explora algunos de nuestros platos más representativos, preparados con ingredientes frescos y el auténtico toque peruano.
      </p>
    </div>

    <!-- Grid de imágenes -->
    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4 max-w-6xl w-full">
      <div
        v-for="(foto, index) in galeria"
        :key="index"
        class="relative group overflow-hidden rounded-2xl shadow-md hover:shadow-xl 
               bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-700
               transition-all duration-500"
      >
        <!-- Imagen -->
        <img
          :src="foto.image"
          :alt="foto.title"
          class="w-full h-64 object-cover transform group-hover:scale-110 transition-transform duration-500"
        />

        <!-- Capa hover -->
        <div
          class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 
                 flex flex-col justify-center items-center transition-opacity duration-500"
        >
          <h3 class="text-white text-lg font-semibold">{{ foto.title }}</h3>
          <p class="text-gray-200 text-sm mt-2 max-w-[80%] text-center">
            {{ foto.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
const galeria = [
  {
    title: 'Ceviche Clásico',
    description: 'Fresco, picante y con el sabor inconfundible del ají limo y el limón peruano.',
    image: '/imagenes/cevicheclasico.png',
  },
  {
    title: 'Lomo Saltado',
    description: 'Jugoso, con papas doradas y ese toque criollo que enamora a todos.',
    image: '/imagenes/lomosaltado.png',
  },
  {
    title: 'Ají de Gallina',
    description: 'Suave, cremoso y lleno de historia en cada bocado.',
    image: '/imagenes/ajidegallina.jpg',
  },
  {
    title: 'Anticuchos',
    description: 'Un clásico peruano con el mejor adobo y el toque perfecto de carbón.',
    image: '/imagenes/anticucho.jpg',
  },
  {
    title: 'Suspiro Limeño',
    description: 'Un postre tradicional que combina lo dulce y lo suave a la perfección.',
    image: '/imagenes/suspirolimeño.jpg',
  },
  {
    title: 'Chicha Morada',
    description: 'Refrescante bebida a base de maíz morado, canela y clavo.',
    image: '/imagenes/chichamorada.jpg',
  },
  {
    title: 'Papa a la Huancaína',
    description: 'Papas bañadas en la deliciosa salsa de ají amarillo con queso fresco.',
    image: '/imagenes/papahuancaina.jpg',
  },
  {
    title: 'Tacu Tacu con Lomo',
    description: 'El sabor del norte con un toque criollo irresistible.',
    image: '/imagenes/tacu-tacu-con-lomo-saltado.jpg',
  },
];
</script>
