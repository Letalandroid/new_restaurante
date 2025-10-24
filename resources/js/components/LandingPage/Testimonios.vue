<template>
  <section 
    id="testimonios" 
    class="flex flex-col min-h-screen items-center justify-center 
            bg-white dark:bg-gray-900
           px-6 py-16 transition-colors duration-500"
  >
    <!-- Título -->
    <div class="mb-12 text-center">
      <h2 class="mb-4 text-3xl font-bold text-orange-800 dark:text-orange-300">
        Lo que dicen nuestros clientes
      </h2>
      <p class="mx-auto mt-4 max-w-2xl text-lg text-neutral-700 dark:text-gray-300">
        Conoce las experiencias de quienes ya disfrutaron de nuestros platos.  
        Cada opinión refleja el sabor, la atención y el ambiente que hacen de <strong>Sabor Andino</strong> un lugar único.
      </p>
    </div>

    <!-- Carrusel -->
    <div class="relative w-full max-w-6xl flex items-center">
      <!-- Botón izquierda -->
      <button 
        v-if="testimonios.length > 3"
        @click="scrollLeft"
        class="absolute left-0 z-10 p-2 bg-white dark:bg-gray-800 rounded-full shadow hover:bg-gray-100 dark:hover:bg-gray-700"
      >
        <ChevronLeft class="w-6 h-6 text-gray-700 dark:text-gray-300" />
      </button>

      <!-- Contenedor horizontal -->
      <div 
        ref="carousel" 
        class="flex overflow-x-auto scroll-smooth gap-8 py-4 px-12 scrollbar-hide"
      >
        <div
          v-for="testimonio in testimonios"
          :key="testimonio.name"
          class="flex-shrink-0 w-80 flex flex-col items-center text-center p-8 
                 bg-white dark:bg-gray-800 
                 border border-gray-200 dark:border-gray-700 
                 rounded-2xl shadow-md hover:shadow-xl 
                 transform hover:-translate-y-2 
                 transition-all duration-300"
        >
          <!-- Avatar -->
          <img 
            :src="testimonio.avatar" 
            :alt="testimonio.name" 
            class="w-20 h-20 rounded-full mb-4 border-4 border-orange-100 dark:border-orange-900 shadow"
          />

          <!-- Nombre -->
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
            {{ testimonio.name }}
          </h3>

          <!-- Comentario -->
          <p class="mt-3 text-gray-600 dark:text-gray-300 italic">
            {{ testimonio.comment }}
          </p>

          <!-- Estrellas -->
          <div class="flex justify-center mt-4 text-yellow-400">
            <Star v-for="n in 5" :key="n" class="h-5 w-5 mr-1" />
          </div>
        </div>
      </div>

      <!-- Botón derecha -->
      <button 
        v-if="testimonios.length > 3"
        @click="scrollRight"
        class="absolute right-0 z-10 p-2 bg-white dark:bg-gray-800 rounded-full shadow hover:bg-gray-100 dark:hover:bg-gray-700"
      >
        <ChevronRight class="w-6 h-6 text-gray-700 dark:text-gray-300" />
      </button>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Star, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const testimonios = [
  {
    name: 'María López',
    avatar: 'https://randomuser.me/api/portraits/women/44.jpg',
    comment: '“El lomo saltado fue espectacular, con ese toque casero que se siente en cada bocado. ¡Definitivamente volveré con mi familia!”',
  },
  {
    name: 'José Ramírez',
    avatar: 'https://randomuser.me/api/portraits/men/32.jpg',
    comment: '“Un lugar acogedor, buena música y atención rápida. Probé el ají de gallina y fue el mejor que he comido en mucho tiempo.”',
  },
  {
    name: 'Ana Torres',
    avatar: 'https://randomuser.me/api/portraits/women/68.jpg',
    comment: '“Me encantó la variedad del menú y el sabor auténtico de la comida peruana. Todo muy fresco y bien presentado.”',
  },
  {
    name: 'Luis Fernández',
    avatar: 'https://randomuser.me/api/portraits/men/45.jpg',
    comment: '“Excelente atención y platos abundantes. El ceviche estaba delicioso y el postre fue el broche de oro. ¡Recomendado al 100%!”',
  },
  {
    name: 'Carolina Vargas',
    avatar: 'https://randomuser.me/api/portraits/women/22.jpg',
    comment: '“El arroz chaufa con pollo fue una delicia, con el punto exacto de sabor y textura. ¡Una experiencia culinaria increíble!”',
  },
  {
    name: 'Ricardo Medina',
    avatar: 'https://randomuser.me/api/portraits/men/41.jpg',
    comment: '“El restaurante tiene un ambiente muy agradable. La atención fue rápida y el pollo a la brasa, simplemente espectacular.”',
  },
  {
    name: 'Sofía Rojas',
    avatar: 'https://randomuser.me/api/portraits/women/33.jpg',
    comment: '“Pedí un suspiro a la limeña y quedé fascinada. Los postres son exquisitos y la presentación impecable.”',
  },
  {
    name: 'Miguel Castro',
    avatar: 'https://randomuser.me/api/portraits/men/55.jpg',
    comment: '“Todo fue perfecto: el servicio, la comida y el ambiente. Recomiendo el tacu tacu con lomo, ¡una explosión de sabor!”',
  },
];

 
const carousel = ref<HTMLElement | null>(null);

const scrollLeft = () => {
  if (carousel.value) {
    carousel.value.scrollBy({ left: -300, behavior: 'smooth' });
  }
};

const scrollRight = () => {
  if (carousel.value) {
    carousel.value.scrollBy({ left: 300, behavior: 'smooth' });
  }
};
</script>

<style>
/* Oculta la scrollbar */
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
