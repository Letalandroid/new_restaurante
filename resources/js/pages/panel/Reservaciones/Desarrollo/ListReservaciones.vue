<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import axios from 'axios';
import { debounce } from 'lodash';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';
import DeleteReservaciones from './DeleteReservaciones.vue';
import UpdateReservaciones from './UpdateReservaciones.vue';
// Agregar Calendar component
import Calendar from 'primevue/calendar';
import ViewCliente from './ViewCliente.vue';

interface Reservation {
    id: number;
    Cliente_name: string;
    number_people: number;
    date: string;
    hour: string;
    reservation_code: string;
    creacion: string;
    actualizacion: string;
    state: boolean | number;
}

interface Pagination {
    currentPage: number;
    perPage: number;
    total: number;
}

interface EstadoOption {
    name: string;
    value: string | number | boolean;
}

interface Filters {
    state: number | null;
}

const dt = ref();
const reservations = ref<Reservation[]>([]);
const selectedReservations = ref<Reservation[] | null>(null);
const selectedDate = ref<Date | null>(null); // Cambiar a Date para usar con Calendar
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const deleteReservationDialog = ref<boolean>(false);
const reservation = ref<Reservation | null>(null);
const selectedReservationId = ref<number | null>(null);
const selectedEstadoReservacion = ref<EstadoOption | null>(null);
const updateReservationDialog = ref<boolean>(false);
const viewClienteDialog = ref(false);
const selectedCliente = ref(null);

const toast = useToast();

const props = defineProps<{
    refresh: number;
}>();

watch(() => props.refresh, () => {
    loadReservation();
});

watch(() => selectedEstadoReservacion.value, () => {
    pagination.value.currentPage = 1;
    loadReservation();
});
function viewCliente(reservation: any) {
    selectedCliente.value = reservation.customer; // cliente ya viene dentro de la reservación
    viewClienteDialog.value = true;
}
// Agregar watch para la fecha
watch(() => selectedDate.value, () => {
    pagination.value.currentPage = 1;
    loadReservation();
});

function editReservation(selected: Reservation) {
    selectedReservationId.value = selected.id;
    updateReservationDialog.value = true;
}

const estadoReservacionOptions = ref<EstadoOption[]>([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 1 },
    { name: 'INACTIVOS', value: 0 },
]);

function handleReservationUpdated() {
    loadReservation();
}

function confirmDeleteReservation(selected: Reservation) {
    reservation.value = selected;
    deleteReservationDialog.value = true;
}

const pagination = ref<Pagination>({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const filters = ref<Filters>({
    state: null
});

function handleReservationDeleted() {
    loadReservation();
}

const loadReservation = async (): Promise<void> => {
    loading.value = true;
    try {
        const params: Record<string, any> = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            state: filters.value.state,
            date: selectedDate.value ? selectedDate.value.toISOString().slice(0, 10) : '' // Cambiar 'fecha' por 'date'
        };

        if (selectedEstadoReservacion.value !== null && selectedEstadoReservacion.value.value !== '') {
            params.state = selectedEstadoReservacion.value.value;
        }

        const response = await axios.get('/reservacion', { params });

        reservations.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error: any) {
        console.error('Error al cargar reservaciones:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar las reservaciones', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const onPage = (event: { page: number; rows: number }) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadReservation();
};

const getSeverity = (value: boolean | number): 'success' | 'danger' | undefined => {
    const boolValue = value === true || value === 1 ;
    return boolValue ? 'success' : value === false || value === 0 ? 'danger' : undefined;
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadReservation();
}, 500);

// Función para limpiar el filtro de fecha
const clearDateFilter = () => {
    selectedDate.value = null;
};

onMounted(() => {
    loadReservation();
});
</script>

<template>
    <DataTable 
        ref="dt" 
        v-model:selection="selectedReservations" 
        :value="reservations" 
        dataKey="id" 
        :paginator="true"
        :rows="pagination.perPage" 
        :totalRecords="pagination.total" 
        :loading="loading" 
        :lazy="true" 
        @page="onPage"
        :rowsPerPageOptions="[15, 20, 25]" 
        scrollable 
        scrollHeight="574px"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} reservaciones"
        class="w-full overflow-x-auto text-sm sm:text-base"
    >

        <template #header>
            <div class="flex flex-col md:flex-row flex-wrap gap-3 md:gap-2 items-start md:items-center justify-between w-full">
                <h4 class="m-0">RESERVACIONES</h4>

                <!-- Contenedor derecho -->
                <div class="flex flex-col md:flex-row items-start md:items-end gap-3 w-full md:w-auto">
                    
                    <!-- Agregar el filtro por fecha -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 w-full md:w-auto">
                        <Calendar 
                            v-model="selectedDate" 
                            dateFormat="dd/mm/yy" 
                            placeholder="Seleccionar fecha"
                            showIcon
                            :showButtonBar="true"
                            class="w-full sm:w-auto"
                        />
                        <Button 
                            v-if="selectedDate" 
                            icon="pi pi-times" 
                            outlined 
                            rounded 
                            severity="secondary"
                            @click="clearDateFilter" 
                            aria-label="Limpiar fecha"
                            class="w-full sm:w-auto"
                        />
                    </div>

                    <!-- BUSCADOR GLOBAL -->
                    <div class="flex flex-col sm:flex-row flex-wrap gap-2 w-full md:w-auto">
                        <IconField class="w-full sm:w-80">
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText 
                                v-model="globalFilterValue" 
                                @input="onGlobalSearch" 
                                placeholder="Buscar por código reserva o cliente..."
                                class="w-full"
                            />
                        </IconField>

                        <Select 
                            v-model="selectedEstadoReservacion" 
                            :options="estadoReservacionOptions" 
                            optionLabel="name"
                            placeholder="Estado" 
                            class="w-full sm:w-auto"
                        />

                        <Button 
                            icon="pi pi-refresh" 
                            outlined 
                            rounded 
                            aria-label="Refresh" 
                            @click="loadReservation" 
                            class="w-full sm:w-auto"
                        />
                    </div>
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
        <Column field="Cliente_name" header="Cliente" sortable style="min-width: 14rem" />
        <Column field="number_people" header="N° Personas" sortable style="min-width: 10rem" />
        <Column field="date" header="Fecha" sortable style="min-width: 10rem" />
        <Column field="hour" header="Hora" sortable style="min-width: 10rem" />
        <Column field="waiting_hour" header="Hora de espera" sortable style="min-width: 10rem" />
        <Column field="reservation_code" header="Código Reserva" sortable style="min-width: 12rem" />
        <Column field="creacion" header="Creación" sortable style="min-width: 13rem" />
        <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem" />
        <Column field="state" header="Estado" sortable style="min-width: 6rem">
            <template #body="{ data }">
                <Tag :value="data.state ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.state)" />
            </template>
        </Column>
        <Column header="Acciones" :exportable="false" style="min-width: 10rem">
            <template #body="slotProps">
                <div class="flex justify-center sm:justify-start gap-2 flex-wrap">
                    <Button 
                        icon="pi pi-user" 
                        outlined 
                        rounded 
                        severity="info" 
                        class="w-9 h-9 sm:w-10 sm:h-10" 
                        @click="viewCliente(slotProps.data)" 
                    />
                    <Button 
                        icon="pi pi-pencil" 
                        outlined 
                        rounded 
                        class="w-9 h-9 sm:w-10 sm:h-10" 
                        @click="editReservation(slotProps.data)" 
                    />
                    <Button 
                        icon="pi pi-trash" 
                        outlined 
                        rounded 
                        severity="danger"
                        class="w-9 h-9 sm:w-10 sm:h-10"
                        @click="confirmDeleteReservation(slotProps.data)" 
                    />
                </div>
            </template>
        </Column>
    </DataTable>

    <DeleteReservaciones 
        v-model:visible="deleteReservationDialog" 
        :reservacion="reservation" 
        @deleted="handleReservationDeleted" 
    />
    <UpdateReservaciones
        v-model:visible="updateReservationDialog" 
        :reservacionId="selectedReservationId"
        @updated="handleReservationUpdated" 
    />
    <ViewCliente 
        v-model:visible="viewClienteDialog" 
        :cliente="selectedCliente" 
    />
</template>