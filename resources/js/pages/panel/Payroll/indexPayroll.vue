<template>
    <Head title="Nominas" />
    <AppLayout>
        <div>
            <template v-if="isLoading">
                <Espera/>
            </template>

            <template v-else>
                <div class="card">
                 <AddPayroll 
  @payroll-agregado="refrescarListado"
  :filtros="filtros"
/>

<ListPayroll 
  :refresh="refreshKey" 
  @update-filtros="actualizarFiltros" 
/>
                </div>
            </template>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '@/layout/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import Espera from '@/components/Espera.vue';
import AddPayroll from './Desarrollo/AddPayroll.vue';
import ListPayroll from './Desarrollo/ListPayroll.vue';

const isLoading = ref(true);
const refreshKey = ref(0);

function refrescarListado() {
    refreshKey.value++;
}

onMounted(() => {
    setTimeout(() => {
        isLoading.value = false;
    }, 1000);
});
const filtros = ref({ search: '', paid: '', month: null });

function actualizarFiltros(nuevos: any) {
  filtros.value = nuevos;
}

</script>
