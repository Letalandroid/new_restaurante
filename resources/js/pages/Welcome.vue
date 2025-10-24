<template>
    <Navbar />

    <Inicio />

    <Services />

    <Galeria />

    <Testimonios />

    <FaqAccordion />

    <Contacto />
    <Navbar />
    <Footer />
</template>

<script setup lang="ts">
import Contacto from '@/components/LandingPage/Contacto.vue';
import FaqAccordion from '@/components/LandingPage/FaqAccordion.vue';
import Footer from '@/components/LandingPage/Footer.vue';
import Inicio from '@/components/LandingPage/Inicio.vue';
import Navbar from '@/components/LandingPage/Navbar.vue';
import Services from '@/components/LandingPage/Services.vue';
import Testimonios from '@/components/LandingPage/Testimonios.vue';
import Galeria from '@/components/LandingPage/Galeria.vue';
</script>

<style>
html {
    scroll-behavior: smooth;
    
}
a.text-gray-800.hover\:text-blue-800.transition.dark\:text-gray-200.dark\:hover\:text-blue-400 {
    padding: 10px;
}
</style>
