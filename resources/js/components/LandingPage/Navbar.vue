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

        <!-- Botón de modo oscuro/claro -->
        <button
          @click="toggleDarkMode"
          class="p-2 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
          :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
        >
          <Sun v-if="isDark" class="h-5 w-5" />
          <Moon v-else class="h-5 w-5" />
        </button>

        <!-- Ícono de cuenta -->
        <a href="/login" class="ml-3 flex items-center text-gray-800 hover:text-blue-800 dark:text-gray-200 dark:hover:text-cyan-400">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5">
            <path fill-rule="evenodd"
              d="M8 0a8 8 0 100 16A8 8 0 008 0zM5.5 5.5a2.5 2.5 0 115 0 2.5 2.5 0 01-5 0zM8 9c-2.21 0-4 1.57-4 3.5V13h8v-.5C12 10.57 10.21 9 8 9z" />
          </svg>
          <span class="ml-1">Intranet</span>
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

          <!-- Botón de modo oscuro/claro en móvil -->
          <NavigationMenuItem>
            <button
              @click="toggleDarkMode"
              class="flex items-center space-x-2 text-gray-800 hover:text-blue-800 gap-2 rounded-lg p-2 dark:text-gray-200 dark:hover:text-blue-400 w-full text-left"
            >
              <Sun v-if="isDark" class="h-5 w-5" />
              <Moon v-else class="h-5 w-5" />
              <span>{{ isDark ? 'Modo Claro' : 'Modo Oscuro' }}</span>
            </button>
          </NavigationMenuItem>

          <!-- Ícono de cuenta en móvil -->
          <NavigationMenuItem>
            <a href="/login" class="flex items-center space-x-2 text-gray-800 hover:text-blue-800 gap-2 rounded-lg p-2 dark:text-gray-200 dark:hover:text-blue-400">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-5">
                <path fill-rule="evenodd"
                  d="M8 0a8 8 0 100 16A8 8 0 008 0zM5.5 5.5a2.5 2.5 0 115 0 2.5 2.5 0 01-5 0zM8 9c-2.21 0-4 1.57-4 3.5V13h8v-.5C12 10.57 10.21 9 8 9z" />
              </svg>
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
import { Moon, Sun } from 'lucide-vue-next'
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
  // Verificar si ya hay una preferencia guardada
  const savedTheme = localStorage.getItem("theme")
  const hasDarkClass = document.documentElement.classList.contains("app-dark")
  
  if (savedTheme === "dark" || hasDarkClass) {
    enableDarkMode()
  } else {
    enableLightMode()
  }
})

function toggleDarkMode() {
  if (isDark.value) {
    enableLightMode()
  } else {
    enableDarkMode()
  }
}

function enableDarkMode() {
  isDark.value = true
  document.documentElement.classList.add("app-dark")
  localStorage.setItem("theme", "dark")
}

function enableLightMode() {
  isDark.value = false
  document.documentElement.classList.remove("app-dark")
  localStorage.setItem("theme", "light")
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
