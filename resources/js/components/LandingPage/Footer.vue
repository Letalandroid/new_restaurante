<template>
  <footer 
    class="bg-gradient-to-r from-yellow-50 to-orange-50 
           dark:from-gray-900 dark:to-gray-800 
           py-12 text-neutral-800 dark:text-gray-300 
           border-t border-gray-200 dark:border-gray-700 
           transition-colors duration-500"
  >
    <div class="max-w-[1280px] mx-auto grid grid-cols-1 gap-10 px-6 md:grid-cols-4 lg:px-20">
      
      <!-- Logo y descripción -->
      <div>
        <h3 class="mb-3 text-2xl font-bold text-yellow-700 dark:text-yellow-400">
          Sabor Andino
        </h3>
        <p class="text-sm text-neutral-600 dark:text-gray-400">
          Somos un restaurante familiar dedicado a ofrecer los mejores sabores del Perú, 
          con ingredientes frescos y el auténtico toque casero que nos distingue.
        </p>
      </div>

      <!-- Redes Sociales -->
      <div>
        <h3 class="mb-3 text-2xl font-bold text-yellow-700 dark:text-yellow-400">Síguenos</h3>
        <div class="space-y-2 text-sm">
          <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" 
             class="flex items-center gap-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-500">
            <Facebook class="w-5 h-5" /> Facebook
          </a>
          <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" 
             class="flex items-center gap-2 text-pink-500 hover:text-pink-700 dark:text-pink-400 dark:hover:text-pink-500">
            <Instagram class="w-5 h-5" /> Instagram
          </a>
          <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer" 
             class="flex items-center gap-2 text-gray-800 hover:text-black dark:text-gray-300 dark:hover:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M16 8.04a4.92 4.92 0 0 0 3.1 1.07V6.2a3.26 3.26 0 0 1-2.75-2.8h-2.4v10.92a2.28 2.28 0 1 1-2.26-2.3 2.24 2.24 0 0 1 1.06.27V9.8a4.84 4.84 0 0 0-1.06-.13 4.68 4.68 0 1 0 4.31 4.67V8.04Z"/></svg>
            TikTok
          </a>
          <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" 
             class="flex items-center gap-2 text-red-600 hover:text-red-800 dark:text-red-500 dark:hover:text-red-600">
            <Youtube class="w-5 h-5" /> YouTube
          </a>
        </div>
      </div>

      <!-- Enlaces rápidos -->
      <div>
        <h4 class="mb-3 text-xl font-semibold text-yellow-700 dark:text-yellow-400">Explora</h4>
        <ul class="space-y-2 text-neutral-600 dark:text-gray-400">
          <li><a href="#inicio" class="hover:underline">Inicio</a></li>
          <li><a href="#servicios" class="hover:underline">Servicios</a></li>
          <li><a href="#galeria" class="hover:underline">Galeria</a></li>
          <li><a href="#testimonios" class="hover:underline">Testimonios</a></li>
          <li><a href="#faqs" class="hover:underline">FAQS</a></li>
          <li><a href="#contacto" class="hover:underline">Contacto</a></li>
        </ul>
      </div>

      <!-- Contacto -->
      <div>
        <h4 class="mb-3 text-xl font-semibold text-yellow-700 dark:text-yellow-400">Contáctanos</h4>
        <ul class="space-y-2 text-neutral-600 dark:text-gray-400">
          <li>📍 Av. Los Sauces 345, Chulucanas, Piura, Perú</li>
          <li>📞 +51 987 654 321 / +51 912 345 678</li>
          <li>✉️ reservas@saborandino.pe</li>
          <li>🕑 Lun - Dom: 11:00 am - 11:00 pm</li>
        </ul>
      </div>

    </div>

    <!-- Línea inferior -->
    <div class="mt-10 border-t border-neutral-300 dark:border-gray-700 pt-6 text-center text-sm text-neutral-500 dark:text-gray-400">
      © 2025 Sabor Andino. Todos los derechos reservados 🍽️
    </div>
  </footer>
</template>

<script setup lang="ts">
import { Facebook, Instagram, Youtube } from 'lucide-vue-next';
</script>
