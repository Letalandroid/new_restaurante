<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import { debounce } from 'lodash';
import Dialog from 'primevue/dialog';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import DeleteMovementInput from './DeleteMovementInput.vue';
import UpdateMovementInput from './UpdateMovementInput.vue';

interface Movement {
  id: number;
  code: string;
  movement_type: number;
  supplier_name: string;
  payment_type: string;
  issue_date: string;
  execution_date: string;
  sub?: number;
  igv?: number;
  total?: number;
  created_at?: string;
  updated_at?: string;
}

interface MovementDetail {
  quantity: number;
  priceUnit: number;
  batch: string;
  totalPrice: number;
  input?: {
    name: string;
    unitMeasure: string;
  } | null;
  product?: {
    name: string;
    unitMeasure: string;
  } | null;
}

interface Pagination {
  currentPage: number;
  perPage: number;
  total: number;
}

const detailModalVisible = ref<boolean>(false);
const movementDetails = ref<Movement>({} as Movement);
const movementDetailsDetails = ref<MovementDetail[]>([]);
const subtotal = ref<number>(0);
const igv = ref<number>(0);
const total = ref<number>(0);
const showPdfDialog = ref<boolean>(false);
const pdfUrl = ref<string | null>(null);
const movementInputs = ref<Movement[]>([]);
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const deleteMovementInputDialog = ref<boolean>(false);
const updateMovementInputDialog = ref<boolean>(false);
const selectedMovementInputId = ref<number | null>(null);
const movementInput = ref<Movement>({} as Movement);
const currentPage = ref<number>(1);
const selectedMovementInputs = ref<Movement[] | null>(null);
const selectedSupplier = ref<any>(null);
const selectedEstadoMovementInput = ref<any>(null);

const pagination = ref<Pagination>({
  currentPage: 1,
  perPage: 15,
  total: 0
});

const refreshCount = ref<number>(0);
let lastDoc: jsPDF | null = null;

// Función para generar PDF
const generatePDF = () => {
  const doc = new jsPDF();
  const now = new Date();

  const formatTime = (date: Date) => date.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
  const formatDate = (date: Date) => date.toLocaleDateString('es-PE');
  const currentDate = formatDate(now);
  const currentTime = formatTime(now);

  doc.setFontSize(15);
  doc.text('RESTAURANTE E.I.R.L', 105, 15, { align: 'center' });

  doc.setFontSize(9);
  doc.text(`Fecha: ${currentDate}    Hora: ${currentTime}`, 105, 21, { align: 'center' });

  doc.setLineWidth(0.5);
  doc.line(10, 24, 200, 24);

  doc.setFontSize(13);
  doc.text('COMPROBANTE DE COMPRA', 105, 33, { align: 'center' });

  doc.setFontSize(10);
  doc.text(`${movementDetails.value.code}`, 105, 39, { align: 'center' });

  doc.line(10, 47, 200, 47);

  let yStart = 52;
  doc.text(`Proveedor: ${movementDetails.value.supplier_name}`, 13, yStart);
  yStart += 6;
  doc.text(`Tipo de Movimiento: ${getMovementTypeLabel(movementDetails.value.movement_type)}`, 13, yStart);
  yStart += 6;
  doc.text(`Tipo de Pago: ${movementDetails.value.payment_type}`, 13, yStart);
  yStart += 6;
  doc.text(`Fecha de Emisión: ${movementDetails.value.issue_date}`, 13, yStart);
  doc.text(`Fecha de Impresión: ${currentDate}`, 150, yStart);

  yStart += 6;
  doc.text(`Fecha de Ejecución: ${movementDetails.value.execution_date}`, 13, yStart);
  doc.text(`Hora de Impresión: ${currentTime}`, 150, yStart);

  doc.line(10, yStart + 5, 200, yStart + 5);

  const tableHead = [['Cantidad', 'Nombre', 'Unidad', 'Precio Unitario', 'Lote', 'Total']];
  const tableBody = movementDetailsDetails.value.map(item => [
    item.quantity,
    item.input?.name || item.product?.name || '-',
    item.input?.unitMeasure || item.product?.unitMeasure || '-',
    item.priceUnit,
    item.batch,
    item.totalPrice
  ]);

  autoTable(doc, {
    startY: yStart + 10,
    head: tableHead,
    body: tableBody,
    theme: 'plain',
    headStyles: { fontStyle: 'bold', halign: 'center', fillColor: [255, 255, 255], textColor: [0, 0, 0] },
    bodyStyles: { halign: 'center' },
    tableLineColor: [0, 0, 0],
    tableLineWidth: 0.5,
  });

  const tableEndY = (doc as any).lastAutoTable.finalY + 5;
  doc.line(10, tableEndY, 200, tableEndY);

  if (subtotal.value) {
    doc.text(`Subtotal: ${formatCurrency(subtotal.value)}`, 170, tableEndY + 10);
    doc.text(`IGV: ${formatCurrency(igv.value)}`, 170, tableEndY + 20);
    doc.text(`M. Total: ${formatCurrency(total.value)}`, 170, tableEndY + 30);
  }

  const pageCount = doc.getNumberOfPages();
  for (let i = 1; i <= pageCount; i++) {
    doc.setPage(i);
    doc.setFontSize(9);
    doc.text(`Página ${i} de ${pageCount}`, 10, 290, { align: 'left' });
    doc.text("https://restauranttj-main-rwzwgj.laravel.cloud/", 200, 290, { align: 'right' });
  }

  lastDoc = doc;
  const pdfBlob = doc.output('blob');
  pdfUrl.value = URL.createObjectURL(pdfBlob);
  showPdfDialog.value = true;
};

const downloadPDF = () => {
  if (lastDoc) {
    lastDoc.save('comprobante-compras.pdf');
  }
};

const closePdfDialog = () => {
  showPdfDialog.value = false;
  if (pdfUrl.value) {
    URL.revokeObjectURL(pdfUrl.value);
    pdfUrl.value = null;
  }
};

const viewMovementDetails = async (movementId: number) => {
  try {
    detailModalVisible.value = true;

    // 1. Obtener datos del movimiento principal
    const response = await axios.get(`/items/movimiento/${movementId}`);
    movementDetails.value = response.data.movement;

    //Extraer valores numéricos de los strings con formato
    if (response.data.movement) {
      const movement = response.data.movement;
      
      // Convertir "S/.1,300.00" → 1300.00
      subtotal.value = extractNumberFromCurrency(movement.sub);
      igv.value = extractNumberFromCurrency(movement.igv);
      total.value = extractNumberFromCurrency(movement.total);
    }

    // 2. Obtener detalles de los items
    const detailsResponse = await axios.get(`/items/movimientos/detalle/${movementId}`);
    movementDetailsDetails.value = detailsResponse.data.data;

    console.log('Datos del movimiento:', {
      movement: response.data.movement,
      subtotal: subtotal.value,
      igv: igv.value,
      total: total.value,
      details: detailsResponse.data.data
    });

  } catch (error) {
    console.error('Error al cargar los detalles del movimiento:', error);
  }
};

//Extraer número de string con formato de moneda
const extractNumberFromCurrency = (currencyString: string): number => {
  if (!currencyString) return 0;
  
  // Remover "S/.", comas y espacios, luego convertir a número
  const numericString = currencyString
    .replace('S/.', '')
    .replace(/,/g, '')
    .trim();
  
  return parseFloat(numericString) || 0;
};



const formatCurrency = (value: number | null) => {
  if (value != null) {
    // ✅ Usar formato consistente con tu API: "S/.1,300.00"
    return 'S/.' + value.toLocaleString('es-PE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }
  return 'S/.0.00';
};

const loadMovementInputs = async () => {
  loading.value = true;
  try {
    const params = {
      page: pagination.value.currentPage,
      per_page: pagination.value.perPage,
      search: globalFilterValue.value,
      supplier: selectedSupplier.value?.value,
      state: selectedEstadoMovementInput.value?.value ?? '',
    };
    const response = await axios.get('/items/movimiento', { params });
    movementInputs.value = response.data.data;
    pagination.value.currentPage = response.data.meta.current_page;
    pagination.value.total = response.data.meta.total;
  } catch (error) {
    console.error('Error al cargar los movimientos de entrada:', error);
  } finally {
    loading.value = false;
  }
};

const props = defineProps<{
  refresh: number;
}>();

watch(refreshCount, loadMovementInputs);
watch(() => props.refresh, loadMovementInputs);
watch(() => selectedEstadoMovementInput.value, () => {
  currentPage.value = 1;
  loadMovementInputs();
});

const onPage = (event: any) => {
  pagination.value.currentPage = event.page + 1;
  pagination.value.perPage = event.rows;
  loadMovementInputs();
};

const onGlobalSearch = debounce(() => {
  pagination.value.currentPage = 1;
  loadMovementInputs();
}, 500);



const editarMovementInput = (movement: Movement) => {
  selectedMovementInputId.value = movement.id;
  updateMovementInputDialog.value = true;
};

const confirmarDeleteMovementInput = (movement: Movement) => {
  movementInput.value = movement;
  console.log(movementInput.value);
  deleteMovementInputDialog.value = true;
};

function handleMovementInputUpdated() {
  loadMovementInputs();
}

function handleMovementInputDeleted() {
  loadMovementInputs();
}

onMounted(loadMovementInputs);

const getMovementTypeLabel = (value: number) => {
  const movementTypes: Record<number, string> = {
    1: 'Factura',
    2: 'Guía',
    3: 'Boleta',
  };
  return movementTypes[value] || 'Desconocido';
};

const closeDetailModal = () => {
  detailModalVisible.value = false;
};

const maximized = ref<boolean>(false);
</script>

<template>
    <DataTable ref="dt" v-model:selection="selectedMovementInputs" :value="movementInputs" dataKey="id" :paginator="true"
        :rows="pagination.perPage" :totalRecords="pagination.total" :loading="loading" :lazy="true" @page="onPage"
        :rowsPerPageOptions="[15, 20, 25]" scrollable scrollHeight="574px"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} Movimientos de Items">
        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">Movimientos de Items</h4>
                <div class="flex flex-wrap gap-2">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar por codigo..." />
                    </IconField>
                    
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadMovementInputs" />
                </div>
            </div>
        </template>
        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>

        <Column field="code" header="Código" sortable style="min-width: 7rem" />
        <Column field="movement_type" header="Tipo" sortable style="min-width: 5rem">
  <template #body="{ data }">
    <span>
      {{ getMovementTypeLabel(data.movement_type) }}
    </span>
  </template>
</Column>
        <Column field="supplier_name" header="Proveedor" sortable style="min-width: 14rem" />
        <Column field="payment_type" header="Pago" sortable style="min-width: 6rem" />
        <Column field="issue_date" header="Emisión" sortable style="min-width: 8rem" />
        <Column field="execution_date" header="Ejecución" sortable style="min-width: 7rem" />
        <Column field="sub" header="Sub" sortable style="min-width: 5rem" />
        <Column field="igv" header="IGV" sortable style="min-width: 5rem" />
        <Column field="total" header="Total" sortable style="min-width: 5rem" />

       
        <Column field="created_at" header="Creación" sortable style="min-width: 13rem" />
        <Column field="updated_at" header="Actualización" sortable style="min-width: 13rem"/>
        <Column field="accions" header="Acciones" :exportable="false" style="min-width: 12rem">
            <template #body="{ data }">
                <Button icon="pi pi-eye" outlined rounded class="mr-2" @click="viewMovementDetails(data.id)" />
                <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editarMovementInput(data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmarDeleteMovementInput(data)" />
            </template>
        </Column>
    </DataTable>

    <DeleteMovementInput
        v-model:visible="deleteMovementInputDialog"
        :movementInput="movementInput"
        @deleted="handleMovementInputDeleted"
    />
    <UpdateMovementInput
        v-model:visible="updateMovementInputDialog"
        :movementInputId="selectedMovementInputId"
        @updated="handleMovementInputUpdated"
    />
<!-- Modal de detalle -->
  <Dialog
    v-model:visible="detailModalVisible"
    header="Detalle del Movimiento"
    :modal="true"
    :closable="true"
    :style="maximized ? { width: '100vw', height: '100vh', maxWidth: '100vw', maxHeight: '100vh' } : { width: '50vw' }"
    >
    <!-- CAMBIO: Slot de header personalizado para agregar el botón de ampliar -->
    <template #header>
      <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <h4>Detalle del Movimiento</h4>
        <div style="display: flex; gap: 8px;">
          <Button icon="pi pi-window-maximize" class="ml-2" @click="maximized = !maximized" />
        </div>
      </div>
    </template>
    <!-- /CAMBIO header -->

    <div>
      <!-- Detalles del movimiento ...igual... -->

      <div class="p-grid p-fluid">
        <div class="p-col-12 p-md-6 detail-item">
          <div><strong>Código:</strong> {{ movementDetails.code }}</div>
          <div><strong>Fecha de Emisión:</strong> {{ movementDetails.issue_date }}</div>
          <div><strong>Fecha de Ejecución:</strong> {{ movementDetails.execution_date }}</div>
        </div>
        <div class="p-col-12 p-md-6 detail-item">
          <div><strong>Proveedor:</strong> {{ movementDetails.supplier_name }}</div>
          <div><strong>Tipo de Movimiento:</strong> {{ getMovementTypeLabel(movementDetails.movement_type) }}</div>
          <div><strong>Tipo de Pago:</strong> {{ movementDetails.payment_type }}</div>
        </div>
      </div>
      <hr />
      <DataTable :value="movementDetailsDetails" :paginator="true" :rows="10" :scrollable="true">
        <Column field="quantity" header="Cantidad" sortable />
        
        <!-- Muestra nombre dinámico: input o product -->
        <Column header="Nombre" sortable>
          <template #body="{ data }">
            {{ data.input?.name || data.product?.name || '-' }}
          </template>
        </Column>

        <!-- Muestra unidad dinámica -->
        <Column header="Unidad" sortable>
          <template #body="{ data }">
            {{ data.input?.unitMeasure || data.product?.unitMeasure || '-' }}
          </template>
        </Column>

        <Column field="priceUnit" header="Precio Unitario" sortable />
        <Column field="batch" header="Lote" sortable />
        <Column field="totalPrice" header="Total" sortable />
      </DataTable>
      <hr />
      <div class="totals">
        <span><strong>Subtotal:</strong> {{ formatCurrency(subtotal) }}</span>
        <span><strong>IGV:</strong> {{ formatCurrency(igv) }}</span>
        <span><strong>Total:</strong> {{ formatCurrency(total) }}</span>
      </div>
      <hr />
      <!-- CAMBIO: Botones juntos -->
      <div class="p-d-flex p-jc-between">
        <span>Mostrando 1 a {{ movementDetailsDetails.length }} de {{ movementDetailsDetails.length }} Items comprados</span>
        <div class="action-buttons">
          <Button label="Imprimir" icon="pi pi-file-pdf" @click="generatePDF" />
          <Button label="Volver" icon="pi pi-arrow-left" @click="closeDetailModal" />
        </div>
      </div>
      <!-- /CAMBIO -->
    </div>
  </Dialog>

  <!-- Modal de vista previa PDF -->
  <Dialog v-model:visible="showPdfDialog" header="Vista previa de impresión" :modal="true" :style="{ width: '800px' }" @hide="closePdfDialog">
    <template #default>
      <iframe v-if="pdfUrl" :src="pdfUrl" width="100%" height="700px" style="border: none;"></iframe>
      <div class="flex justify-end mt-2">
        <Button label="Descargar PDF" icon="pi pi-download" @click="downloadPDF" />
      </div>
    </template>
  </Dialog>

</template>
<style scoped>
/* Estilo para el modal */
.p-dialog .p-dialog-header {
    font-weight: bold;
    font-size: 1.25rem;
}

.p-grid {
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
}

.p-col-12 {
    margin-bottom: 15px;
}

.p-col-12.p-md-6 {
    margin-bottom: 15px;
}

.detail-item {
    margin-bottom: 50px;
}

.totals {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    font-weight: bold;
    font-size: 1.1rem;
}

/* CAMBIO: Botones juntos con separación */
.action-buttons {
    display: flex;
    gap: 10px;
}

.p-d-flex {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
/* Si quieres que el dialogo no tenga márgenes internos cuando está maximizado */
.p-dialog[style*="100vw"] .p-dialog-content {
  height: calc(100vh - 4rem); /* Ajusta según el header/footer del modal */
  overflow: auto;
}
</style>