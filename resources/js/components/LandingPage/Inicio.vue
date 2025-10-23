<template>
  <!-- Hero / Inicio -->
  <section 
    id="inicio" 
    class="mt-[50px] flex h-screen items-center 
           bg-gradient-to-r from-yellow-50 to-orange-50
           dark:from-gray-900 dark:to-gray-800 transition-colors duration-500"
  >
    <div class="mx-auto grid max-w-[1280px] items-center gap-8 px-6 md:grid-cols-2">

      <!-- Texto principal -->
      <div>
        <h1 class="text-4xl leading-tight font-bold 
                   text-orange-600 dark:text-orange-400 
                   md:text-6xl transition-colors duration-500">
          Bienvenidos a <br />
          <span class="text-orange-800 dark:text-orange-200">
            Restaurante Sabor Andino
          </span>
        </h1>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-300 transition-colors duration-500">
          Disfruta de los mejores platos típicos peruanos preparados con ingredientes frescos y el auténtico sabor casero.  
          Una experiencia culinaria que despierta tus sentidos.
        </p>

        <!-- Call to action -->
        <div class="mt-6 flex space-x-4">
          <Button asChild size="lg">
            <a href="#servicios">Servicios</a>
          </Button>
          <Button asChild variant="outline" size="lg" class="dark:border-gray-500 dark:text-gray-200 dark:hover:bg-gray-700">
            <a href="#contacto">Haz tu Reserva</a>
          </Button>
        </div>
      </div>

      <!-- Carrusel de imágenes con efecto deslizado -->
      <div class="hidden md:flex flex-col items-center relative w-full overflow-hidden rounded-lg">
        <div
          class="flex transition-transform duration-700 ease-in-out"
          :style="{ transform: `translateX(-${currentIndex * 100}%)` }"
        >
          <img
            v-for="(img, index) in images"
            :key="index"
            :src="img"
            alt="Platos del restaurante"
            class="w-full flex-shrink-0 object-cover"
          />
        </div>

        <!-- Botones de navegación -->
        <button 
          @click="prev" 
          class="absolute left-0 top-1/2 -translate-y-1/2 
                 bg-white/70 dark:bg-gray-700/70 
                 p-2 rounded-full shadow 
                 hover:bg-white dark:hover:bg-gray-600 
                 transition-colors"
        >
          &#8592;
        </button>
        <button 
          @click="next" 
          class="absolute right-0 top-1/2 -translate-y-1/2 
                 bg-white/70 dark:bg-gray-700/70 
                 p-2 rounded-full shadow 
                 hover:bg-white dark:hover:bg-gray-600 
                 transition-colors"
        >
          &#8594;
        </button>
      </div>

    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import { Button } from "@/components/ui/button";

const images = [
  "/imagenes/portada.jpg",
  "/imagenes/plato1.jpg",
  "/imagenes/plato2.jpg",
  "/imagenes/plato3.jpg",
  "/imagenes/plato4.jpg",
];

const currentIndex = ref(0);

const prev = () => {
  currentIndex.value = (currentIndex.value - 1 + images.length) % images.length;
};

const next = () => {
  currentIndex.value = (currentIndex.value + 1) % images.length;
};

// Cambio automático cada 5 segundos
let intervalId: number;
onMounted(() => {
  intervalId = window.setInterval(next, 5000);
});
onUnmounted(() => {
  clearInterval(intervalId);
});
</script>
