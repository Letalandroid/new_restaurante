<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';
//import Tag from 'primevue/tag';
import { debounce } from 'lodash';
import UpdateReporteCaja from './UpdateReporteCaja.vue';

// Tipos
interface Reporte {
    id: number;
    numero_cajas: string;
    vendedorNombre: string;
    monto_efectivo: number | null;
    monto_tarjeta: number | null;
    monto_yape: number | null;
    monto_transferencia: number | null;
    creacion: string;
    actualizacion: string;
    fecha_salida?: string | null;
}

interface Pagination {
    currentPage: number;
    perPage: number;
    total: number;
}

interface ApiResponse {
    data: Reporte[];
    meta: {
        current_page: number;
        total: number;
    };
}

// Variables reactivas
const reportes = ref<Reporte[]>([]);
const loading = ref<boolean>(false);
const selectedReporte = ref<Reporte[] | null>(null);
const globalFilterValue = ref<string>('');
const updateReporteDialog = ref<boolean>(false);
const selectedReporteId = ref<number | null>(null);
const pagination = ref<Pagination>({
    currentPage: 1,
    perPage: 15,
    total: 0
});

// Cargar reportes
const loadReportes = async (): Promise<void> => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
        };
        const response = await axios.get<ApiResponse>('/reporte_caja', { params });
        reportes.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar reportes:', error);
    } finally {
        loading.value = false;
    }
};

// Evento de paginación
const onPage = (event: { page: number; rows: number }): void => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadReportes();
};

// Búsqueda global con debounce
const onGlobalSearch = debounce((): void => {
    pagination.value.currentPage = 1;
    loadReportes();
}, 500);

// Formatear moneda
const formatCurrency = (value: number | string | null): string => {
    if (value != null) {
        return 'S/. ' + parseFloat(value as string).toFixed(2);
    }
    return '';
};

// Abrir modal de edición
const editarReporte = (reporte: Reporte): void => {
    selectedReporteId.value = reporte.id;
    updateReporteDialog.value = true;
};

// Manejar actualización
function handleReporteUpdated(): void {
    loadReportes();
}

onMounted(loadReportes);
</script>

<template>
    <DataTable
        ref="dt"
        v-model:selection="selectedReporte"
        :value="reportes"
        :paginator="true"
        :rows="pagination.perPage"
        :totalRecords="pagination.total"
        :loading="loading"
        :lazy="true"
        @page="onPage"
        :rowsPerPageOptions="[15, 20, 25]"
        dataKey="id"
        scrollable scrollHeight="574px"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} reportes"
        class="w-full overflow-x-auto text-sm sm:text-base"
    >
        <template #header>
            <div class="flex flex-col sm:flex-row flex-wrap gap-3 sm:gap-2 items-start sm:items-center justify-between">
                <h2 class="m-0">REPORTE DE CAJAS</h2>
                <div class="flex flex-col sm:flex-row flex-wrap gap-2 w-full sm:w-auto">
                    <InputText 
                        v-model="globalFilterValue" 
                        @input="onGlobalSearch" 
                        placeholder="Buscar por vendedor o N° de caja..." 
                        class="w-full sm:w-80 md:w-96"
                    />
                    <Button 
                        icon="pi pi-refresh" 
                        outlined 
                        rounded 
                        aria-label="Refresh" 
                        @click="loadReportes" 
                        class="w-full sm:w-auto"
                    />
                </div>
            </div>
        </template>

        <Column field="numero_cajas" header="N° Caja" sortable />
        <Column field="vendedorNombre" header="Vendedor" sortable />
        <!--Columnas puestas al prefijo de S/.-->
        <Column field="monto_efectivo" header="Monto Efectivo" sortable>
            <template #body="{ data }">
            {{ formatCurrency(data.monto_efectivo) }}
            </template>
        </Column>
        <Column field="monto_tarjeta" header="Monto Tarjeta" sortable>
            <template #body="{ data }">
            {{ formatCurrency(data.monto_tarjeta) }}
            </template>
        </Column>
        <Column field="monto_yape" header="Monto Yape/Plin" sortable>
            <template #body="{ data }">
            {{ formatCurrency(data.monto_yape) }}
            </template>
        </Column>
        <Column field="monto_transferencia" header="Monto Transferencia" sortable>
            <template #body="{ data }">
            {{ formatCurrency(data.monto_transferencia) }}
            </template>
        </Column>
        <Column field="creacion" header="Fecha Apertura" sortable />
        <Column field="actualizacion" header="Fecha Modificación" sortable />
        <Column field="fecha_salida" header="Fecha Cierre" sortable>
          <template #body="{ data }">
            <span>{{ data.fecha_salida || 'Sin cerrar' }}</span>
          </template>
        </Column>
        <Column field="acciones" header="Acción" :exportable="false">
            <template #body="{ data }">
                <Button icon="pi pi-pencil" outlined rounded @click="editarReporte(data)" class="w-8 h-8 sm:w-10 sm:h-10" />
            </template>
        </Column>
    </DataTable>

    <UpdateReporteCaja 
      v-model:visible="updateReporteDialog" 
      :reporteId="selectedReporteId" 
      @updated="handleReporteUpdated" 
    />
</template>
