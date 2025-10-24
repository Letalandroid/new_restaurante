<template>
  <nav class="fixed top-0 left-0 z-50 w-full bg-white shadow-md dark:bg-gray-900">
    <div class="mx-auto flex max-w-[1280px] items-center justify-between px-6 py-3">
      <!-- Logo con enlace -->
      <a href="#inicio" class="flex items-center space-x-2">
        <img src="/imagenes/logo.png" alt="Logo" class="h-10 w-10 cursor-pointer" />
        <span class="text-lg font-bold text-orange-600 dark:text-orange-400 cursor-pointer">
          Restaurante Sabor Andino
        </span>
      </a>

      <!-- Menú de escritorio -->
      <div class="hidden items-center space-x-6 md:flex">
        <NavigationMenu>
          <NavigationMenuList class="flex space-x-1">
            <NavigationMenuItem v-for="link in links" :key="link.text">
              <NavigationMenuLink
                :href="link.href"
                class="text-gray-800 hover:text-blue-800 transition dark:text-gray-200 dark:hover:text-blue-400"
              >
                {{ link.text }}
              </NavigationMenuLink>
            </NavigationMenuItem>
          </NavigationMenuList>
        </NavigationMenu>

        <!-- Ícono de cuenta -->
        <a href="/login" class="ml-2 text-gray-800 hover:text-blue-800 dark:text-gray-200 dark:hover:text-blue-400">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5">
            <path fill-rule="evenodd" d="M3.757 4.5c.18.217.376.42.586.608.153-.61.354-1.175.596-1.678A5.53 5.53 0 0 0 3.757 4.5ZM8 1a6.994 6.994 0 0 0-7 7 7 7 0 1 0 7-7Zm0 1.5c-.476 0-1.091.386-1.633 1.427-.293.564-.531 1.267-.683 2.063A5.48 5.48 0 0 0 8 6.5a5.48 5.48 0 0 0 2.316-.51c-.152-.796-.39-1.499-.683-2.063C9.09 2.886 8.476 2.5 8 2.5Zm3.657 2.608a8.823 8.823 0 0 0-.596-1.678c.444.298.842.659 1.182 1.07-.18.217-.376.42-.586.608Zm-1.166 2.436A6.983 6.983 0 0 1 8 8a6.983 6.983 0 0 1-2.49-.456 10.703 10.703 0 0 0 .202 2.6c.72.231 1.49.356 2.288.356.798 0 1.568-.125 2.29-.356a10.705 10.705 0 0 0 .2-2.6Zm1.433 1.85a12.652 12.652 0 0 0 .018-2.609c.405-.276.78-.594 1.117-.947a5.48 5.48 0 0 1 .44 2.262 7.536 7.536 0 0 1-1.575 1.293Zm-2.172 2.435a9.046 9.046 0 0 1-3.504 0c.039.084.078.166.12.244C6.907 13.114 7.523 13.5 8 13.5s1.091-.386 1.633-1.427c.04-.078.08-.16.12-.244Zm1.31.74a8.5 8.5 0 0 0 .492-1.298c.457-.197.893-.43 1.307-.696a5.526 5.526 0 0 1-1.8 1.995Zm-6.123 0a5.526 5.526 0 0 1-1.8-1.995c.414.266.85.499 1.307.696.14.44.294.866.492 1.298ZM2.267 9.328a5.481 5.481 0 0 1 .44-2.262c.338.353.712.67 1.117.947a12.65 12.65 0 0 0 .018 2.609A7.536 7.536 0 0 1 2.267 9.328Z"/>
          </svg>
        </a>

 
      </div>

      <!-- Botón hamburguesa para móvil -->
      <button @click="menuOpen = !menuOpen" class="text-gray-800 focus:outline-none md:hidden dark:text-gray-200">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <!-- Menú móvil -->
    <div v-if="menuOpen" class="px-6 pb-4 md:hidden">
      <NavigationMenu>
        <NavigationMenuList class="flex w-full flex-col items-start space-y-2">
          <NavigationMenuItem v-for="link in links" :key="link.text + '-mobile'">
            <NavigationMenuLink
              :href="link.href"
              class="text-gray-800 hover:text-blue-800 dark:text-gray-200 dark:hover:text-blue-400"
            >
              {{ link.text }}
            </NavigationMenuLink>
          </NavigationMenuItem>

          <!-- Ícono de cuenta en móvil -->
          <NavigationMenuItem>
            <a href="/login" class="flex items-center space-x-2 text-gray-800 hover:text-blue-800  gap-2 rounded-lg p-2 dark:text-gray-200 dark:hover:text-blue-400">
             
              <span>Intranet</span>
            </a>
          </NavigationMenuItem>

        </NavigationMenuList>
      </NavigationMenu>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Moon, Sun} from 'lucide-vue-next';

import {
  NavigationMenu,
  NavigationMenuList,
  NavigationMenuItem,
  NavigationMenuLink,
} from '@/components/ui/navigation-menu'
// Estado del menú móvil
const menuOpen = ref(false)

// Estado del dark mode
const isDark = ref(false)

onMounted(() => {
  // Siempre inicia en modo claro
  document.documentElement.classList.remove("dark")
  localStorage.setItem("theme", "light")
})

function toggleDarkMode() {
  isDark.value = !isDark.value
  if (isDark.value) {
    document.documentElement.classList.add("dark")
    localStorage.setItem("theme", "dark")
  } else {
    document.documentElement.classList.remove("dark")
    localStorage.setItem("theme", "light")
  }
}

// Links del menú
const links = [
  { text: 'Inicio', href: '#inicio' },
  { text: 'Servicios', href: '#servicios' },
  { text: 'Galeria', href: '#galeria' },
  { text: 'Testimonios', href: '#testimonios' },
  { text: 'FAQS', href: '#faqs' },
  { text: 'Contacto', href: '#contacto' },
]
</script>
