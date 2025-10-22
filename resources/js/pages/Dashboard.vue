<script setup lang="ts">
import AppLayout from '@/layout/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Calendar from 'primevue/calendar';
import Chart from 'primevue/chart';
import Button from 'primevue/button';
import Column from 'primevue/column';
import Message from 'primevue/message';
import DataTable from 'primevue/datatable';
import { onMounted, ref, watch } from 'vue';
import Password from './settings/Password.vue';

// Tipos de datos
interface User {
  id: number;
  name: string;
  email: string;
  [key: string]: any;
}

interface Totales {
  total_customers: number;
  total_orders: number;
  total_dishes: number;
  total_income: string;
  total_employees: number; // Nuevo campo
  total_products: number; // Nuevo campo
}

interface PaymentMethodStats {
  Efectivo: number;
  Transferencia: number;
  Yape: number;
  Plin: number;
  Tarjeta: number;
}

interface DashboardData {
  totales: Totales;
  total_in_range: Totales;
  payment_method_stats: PaymentMethodStats;
  frequent_tables?: number[];
}

interface FrequentTable {
  tablenum: number;
  puesto: number;
}

interface PageProps {
  mustReset: boolean;
  auth: {
    user: User;
  };
  [key: string]: any; // Esto permite propiedades adicionales dinámicas
}

// Obtener página y props
const page = usePage<PageProps>();
const mustReset = page.props.mustReset;
const user = page.props.auth.user;

// Función para determinar el saludo
const getGreeting = (): string => {
  const hours = new Date().getHours();
  if (hours < 12) {
    return 'Buenos días';
  } else if (hours < 18) {
    return 'Buenas tardes';
  } else {
    return 'Buenas noches';
  }
};

// Mensajes motivacionales
const motivationalMessages: string[] = [
  "¡Estás haciendo un gran trabajo, sigue así!",
  "¡Hoy es un buen día para alcanzar tus metas!",
  "¡La perseverancia te lleva lejos, nunca pares!",
  "¡Cada paso te acerca más a tu éxito!"
];

// Función para obtener un mensaje aleatorio
function getMotivationalMessage(): string {
  const randomIndex = Math.floor(Math.random() * motivationalMessages.length);
  return motivationalMessages[randomIndex];
}

const motivationalMessage = getMotivationalMessage();

const dashboardData = ref<DashboardData>({
  totales: {
    total_customers: 0,
    total_orders: 0,
    total_dishes: 0,
    total_income: '0.00',
    total_employees: 0, // Inicializar nuevo campo
    total_products: 0, // Inicializar nuevo campo
  },
  total_in_range: {
    total_customers: 0,
    total_orders: 0,
    total_dishes: 0,
    total_income: '0.00',
    total_employees: 0, // Inicializar nuevo campo
    total_products: 0, // Inicializar nuevo campo
  },
  payment_method_stats: {
    Efectivo: 0,
    Transferencia: 0,
    Yape: 0,
    Plin: 0,
    Tarjeta: 0,
  },
});

const frequentTables = ref<FrequentTable[]>([]);

// Rango de fechas (Calendar usa Date[])
const dateRange = ref<Date[] | null>(null);

// Datos para el gráfico
const paymentMethodLabels = ['Efectivo', 'Transferencia', 'Yape', 'Plin', 'Tarjeta'];
const paymentMethodData = ref<number[]>([0, 0, 0, 0, 0]);

const paymentMethodChartData = ref({
  labels: paymentMethodLabels,
  datasets: [
    {
      label: 'Métodos de Pago',
      data: paymentMethodData.value,
      backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
      borderColor: '#fff',
      borderWidth: 1,
    },
  ],
});

// Actualizar gráfico cuando cambian los datos
watch(
  () => dashboardData.value.payment_method_stats,
  (newStats) => {
    paymentMethodData.value = [
      newStats.Efectivo,
      newStats.Transferencia,
      newStats.Yape,
      newStats.Plin,
      newStats.Tarjeta
    ];
    paymentMethodChartData.value.datasets[0].data = paymentMethodData.value;
  },
  { immediate: true }
);

// Cargar datos del dashboard
const loadDashboardData = async (startDate?: Date, endDate?: Date) => {
  try {
    console.log("Fechas seleccionadas:", startDate, endDate);
    const params = {
      start_date: startDate ? startDate.toISOString().split('T')[0] : null,
      end_date: endDate ? endDate.toISOString().split('T')[0] : null
    };

    const response = await axios.get<DashboardData>('/datos/dashboard', { params });
    dashboardData.value = response.data;

    frequentTables.value = (response.data.frequent_tables || []).map((table, index) => ({
      tablenum: table,
      puesto: index + 1,
    }));
  } catch (error) {
    console.error('Error al obtener los datos del dashboard:', error);
  }
};

// Botón para actualizar los datos
const onUpdateDataClick = (): void => {
  if (dateRange.value && dateRange.value.length === 2) {
    loadDashboardData(dateRange.value[0], dateRange.value[1]);
  }
};

// Cuando cambia el rango de fechas
const onDateRangeChange = (): void => {
  console.log("Date range changed:", dateRange.value);
  if (dateRange.value && dateRange.value.length === 2) {
    loadDashboardData(dateRange.value[0], dateRange.value[1]);
  }
};

// Cargar datos al montar el componente
onMounted(() => {
  console.log("Component mounted, loading dashboard data...");
  loadDashboardData();
});
</script>

<template>
  <Head title="Dashboard" />
  <div v-if="mustReset">
    <div>
      <Password />
    </div>
  </div>
  <AppLayout v-else>
    <div class="card">
      <!-- Saludo y nombre completo del usuario -->
      <h2>{{ getGreeting() }}, {{ user.name }}!</h2>
      <br>
      <!-- Mensaje motivacional -->
      <Message severity="info">{{ motivationalMessage }}</Message>
    </div>

    <div class="grid grid-cols-4 gap-8">
      <Calendar
        v-model="dateRange"
        selectionMode="range"
        placeholder="Rango de fechas"
        class="w-full"
        dateFormat="yy-mm-dd"
        @change="onDateRangeChange"
      />
      <Button label="Filtrar" @click="onUpdateDataClick" class="p-button p-component" />
    </div>

    <br />
    <div class="grid grid-cols-12 gap-8">
      <!-- Revenue Card -->
      <div class="col-span-12 lg:col-span-6 xl:col-span-3">
        <div class="card mb-0">
          <div class="mb-4 flex justify-between">
            <div>
              <span class="mb-4 block font-medium text-muted-color">Ingresos Totales</span>
              <div class="text-xl font-medium text-surface-900 dark:text-surface-0">
                S/{{ dashboardData.totales.total_income }}
              </div>
            </div>
            <div
              class="flex items-center justify-center bg-orange-100 rounded-border dark:bg-orange-400/10"
              style="width: 2.5rem; height: 2.5rem"
            >
              <i class="pi pi-dollar !text-xl text-orange-500"></i>
            </div>
          </div>
          <span class="font-medium text-primary">S/{{ dashboardData.total_in_range.total_income }}</span>
          <span class="text-muted-color"> filtrados</span>
        </div>
      </div>

      <!-- Customers Card -->
      <div class="col-span-12 lg:col-span-6 xl:col-span-3">
        <div class="card mb-0">
          <div class="mb-4 flex justify-between">
            <div>
              <span class="mb-4 block font-medium text-muted-color">Nº Clientes</span>
              <div class="text-xl font-medium text-surface-900 dark:text-surface-0">
                {{ dashboardData.totales.total_customers }}
              </div>
            </div>
            <div
              class="flex items-center justify-center bg-cyan-100 rounded-border dark:bg-cyan-400/10"
              style="width: 2.5rem; height: 2.5rem"
            >
              <i class="pi pi-users !text-xl text-cyan-500"></i>
            </div>
          </div>
          <span class="font-medium text-primary">{{ dashboardData.total_in_range.total_customers }}</span>
          <span class="text-muted-color"> registrados</span>
        </div>
      </div>

      <!-- Orders Card -->
      <div class="col-span-12 lg:col-span-6 xl:col-span-3">
        <div class="card mb-0">
          <div class="mb-4 flex justify-between">
            <div>
              <span class="mb-4 block font-medium text-muted-color">Nº Ordenes</span>
              <div class="text-xl font-medium text-surface-900 dark:text-surface-0">
                {{ dashboardData.totales.total_orders }}
              </div>
            </div>
            <div
              class="flex items-center justify-center bg-blue-100 rounded-border dark:bg-blue-400/10"
              style="width: 2.5rem; height: 2.5rem"
            >
              <i class="pi pi-shopping-cart !text-xl text-blue-500"></i>
            </div>
          </div>
          <span class="font-medium text-primary">{{ dashboardData.total_in_range.total_orders }}</span>
          <span class="text-muted-color"> filtrados</span>
        </div>
      </div>

      <!-- Dishes Card -->
      <div class="col-span-12 lg:col-span-6 xl:col-span-3">
        <div class="card mb-0">
          <div class="mb-4 flex justify-between">
            <div>
              <span class="mb-4 block font-medium text-muted-color">Nº Platillos</span>
              <div class="text-xl font-medium text-surface-900 dark:text-surface-0">
                {{ dashboardData.totales.total_dishes }}
              </div>
            </div>
            <div
              class="flex items-center justify-center bg-purple-100 rounded-border dark:bg-purple-400/10"
              style="width: 2.5rem; height: 2.5rem"
            >
              <i class="pi pi-comment !text-xl text-purple-500"></i>
            </div>
          </div>
          <span class="font-medium text-primary">{{ dashboardData.total_in_range.total_dishes }}</span>
          <span class="text-muted-color"> filtrados</span>
        </div>
      </div>
      <!-- Employees Card -->
      <div class="col-span-12 lg:col-span-6 xl:col-span-3">
        <div class="card mb-0">
          <div class="mb-4 flex justify-between">
            <div>
              <span class="mb-4 block font-medium text-muted-color">Nº Empleados</span>
              <div class="text-xl font-medium text-surface-900 dark:text-surface-0">
                {{ dashboardData.totales.total_employees }}
              </div>
            </div>
            <div
              class="flex items-center justify-center bg-green-100 rounded-border dark:bg-green-400/10"
              style="width: 2.5rem; height: 2.5rem"
            >
              <i class="pi pi-user !text-xl text-green-500"></i>
            </div>
          </div>
          <span class="font-medium text-primary">{{ dashboardData.total_in_range.total_employees }}</span>
          <span class="text-muted-color"> registrados</span>
        </div>
      </div>

      <!-- Products Card -->
      <div class="col-span-12 lg:col-span-6 xl:col-span-3">
        <div class="card mb-0">
          <div class="mb-4 flex justify-between">
            <div>
              <span class="mb-4 block font-medium text-muted-color">Nº Productos</span>
              <div class="text-xl font-medium text-surface-900 dark:text-surface-0">
                {{ dashboardData.totales.total_products }}
              </div>
            </div>
            <div
              class="flex items-center justify-center bg-red-100 rounded-border dark:bg-red-400/10"
              style="width: 2.5rem; height: 2.5rem"
            >
              <i class="pi pi-box !text-xl text-red-500"></i>
            </div>
          </div>
          <span class="font-medium text-primary">{{ dashboardData.total_in_range.total_products }}</span>
          <span class="text-muted-color"> en inventario</span>
        </div>
      </div>
            <!-- Mesas Frecuentes -->
      <div class="col-span-12 xl:col-span-6">
        <div class="card">
          <div class="mb-4 text-xl font-semibold">Mesas Frecuentes</div>
          <DataTable :value="frequentTables" :rows="5" class="p-datatable-sm">
            <Column field="puesto" header="Puesto" />
            <Column field="tablenum" header="Número de Mesa" />
          </DataTable>
        </div>
      </div>
      <!-- Revenue Stream Chart -->
      <div class="col-span-12 xl:col-span-6">
        <div class="card">
          <div class="mb-4 text-xl font-semibold">FRECUENCIA DE LOS METODOS DE PAGO</div>
          <Chart type="bar" :data="paymentMethodChartData" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
body {
  overflow-x: hidden;
}
.p-datatable-table {
  table-layout: fixed;
  width: 100%;
}
</style>
