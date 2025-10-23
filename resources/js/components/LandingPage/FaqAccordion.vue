<script setup lang="ts">
import { ref } from "vue"

// Lista de FAQs
const faqs = ref([
  {
    question: "¿Cuáles son los horarios de atención del restaurante?",
    answer: "Atendemos de lunes a domingo de 11:00 a.m. a 11:00 p.m. Los feriados también abrimos en horario regular."
  },
  {
    question: "¿Ofrecen servicio de delivery o recojo en tienda?",
    answer: "Sí, puedes hacer tu pedido por delivery a través de nuestras redes sociales, WhatsApp o plataformas aliadas. También ofrecemos la opción de recojo en tienda sin costo adicional."
  },
  {
    question: "¿Se puede hacer una reserva de mesa?",
    answer: "Por supuesto. Puedes reservar una mesa llamando al restaurante o desde nuestra página web. Recomendamos hacerlo con al menos un día de anticipación, especialmente los fines de semana."
  },
  {
    question: "¿Tienen opciones vegetarianas o veganas?",
    answer: "Sí, contamos con platos vegetarianos y veganos elaborados con productos frescos y locales. Puedes consultarlo directamente en nuestro menú o solicitar recomendaciones al personal."
  },
  {
    question: "¿Puedo organizar eventos o celebraciones en el restaurante?",
    answer: "Sí, ofrecemos espacios para celebraciones, reuniones y eventos especiales. Puedes coordinar con nosotros para personalizar el menú y la decoración según la ocasión."
  },
  {
    question: "¿Aceptan tarjetas de crédito o pagos digitales?",
    answer: "Sí, aceptamos todas las tarjetas de crédito, débito y pagos digitales como Yape y Plin. También puedes pagar en efectivo."
  },
  {
    question: "¿Dónde están ubicados y cómo puedo contactarlos?",
    answer: "Nos encontramos en Jr. Libertad 345, Chulucanas, Morropón, Piura. Puedes llamarnos al (073) 378-123 o escribirnos a contacto@restauranteperuano.pe."
  }
])

// Para manejar qué ítem está abierto
const openIndex = ref<number | null>(null)

const toggle = (index: number) => {
  openIndex.value = openIndex.value === index ? null : index
}
</script>

<template>
  <section 
    id="faqs" 
    class="flex min-h-screen flex-col items-center justify-center 
           bg-gradient-to-b from-orange-50 to-yellow-50 
           dark:from-gray-900 dark:to-gray-800 
           px-6 py-16 transition-colors duration-500"
  >
    <div class="w-full max-w-3xl mx-auto">
      <!-- Título -->
      <h2 class="mb-4 text-3xl font-bold text-neutral-900 dark:text-white text-center">
        Preguntas Frecuentes
      </h2>
      <p class="mb-10 text-center text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
        Encuentra respuestas rápidas sobre nuestros servicios, reservas, opciones de menú y más.
      </p>

      <!-- Lista de FAQs -->
      <div class="space-y-4">
        <div
          v-for="(faq, index) in faqs"
          :key="index"
          class="border rounded-xl shadow-sm 
                 bg-white dark:bg-gray-800 
                 border-gray-200 dark:border-gray-700 
                 overflow-hidden transition"
        >
          <!-- Pregunta -->
          <button
            class="w-full flex justify-between items-center px-5 py-4 
                   text-left font-medium text-gray-800 dark:text-white 
                   hover:bg-orange-50 dark:hover:bg-gray-700 transition"
            @click="toggle(index)"
          >
            {{ faq.question }}
            <span class="ml-2 text-gray-500 dark:text-gray-400">
              <svg
                v-if="openIndex !== index"
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
              </svg>
            </span>
          </button>

          <!-- Respuesta -->
          <transition name="fade">
            <div
              v-show="openIndex === index"
              class="px-5 pb-4 text-gray-600 dark:text-gray-300 border-t border-gray-200 dark:border-gray-700"
            >
              {{ faq.answer }}
            </div>
          </transition>
        </div>
      </div>
    </div>
  </section>
</template>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
