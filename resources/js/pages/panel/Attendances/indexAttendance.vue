<template>
    <Head title="Asistencias" />
    <AppLayout>
        <div>
            <template v-if="isLoading">
                <Espera/>
            </template>

            <template v-else>
                <div class="card">
<AddAsistencias 
  @attendance-registered="refrescarListado"
  :selected-status="selectedStatus"
  :global-filter-value="globalFilterValue"
  :selected-date-range="selectedDateRange"
/>

<ListAsistencias 
  :refresh="refreshKey"
  @filters-changed="handleFiltersChanged"
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
import ListAsistencias from './Desarrollo/ListAttendance.vue';
import AddAsistencias from './Desarrollo/AddAttendance.vue';

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
const selectedStatus = ref(null)
const globalFilterValue = ref('')
const selectedDateRange = ref(null)

function handleFiltersChanged(filters: any) {
  selectedStatus.value = filters.selectedStatus
  globalFilterValue.value = filters.globalFilterValue
  selectedDateRange.value = filters.selectedDateRange
}

</script>
