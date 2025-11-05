<script setup lang="ts">
import axios from 'axios';
import { debounce } from 'lodash';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import Dialog from 'primevue/dialog';
import { onMounted, ref, watch } from 'vue';
import Calendar from 'primevue/calendar';  

const SYSTEM_URL = "https://restauranttj-main-rwzwgj.laravel.cloud/";

// Variables para la vista previa
const showPdfDialog = ref<boolean>(false);
const pdfUrl = ref<string | null>(null);
const inputs = ref<any[]>([]);
const loading = ref<boolean>(false);
const globalFilterValue = ref<string>('');
const deleteInputDialog = ref<boolean>(false);
const currentPage = ref<number>(1);
const selectedEstadoInput = ref<any>(null);
const selectedInputs = ref<null>(null);
const pagination = ref({
    currentPage: 1,
    perPage: 15,
    total: 0,
});

const refreshCount = ref<number>(0); // Variable que se incrementa cuando se agrega un insumo

// Rango de fechas
const dateRange = ref<Date[] | null>(null); // Variable para el rango de fechas seleccionado
const today = ref<Date>(new Date()); // Fecha máxima permitida


// Cargar los datos de los kardex
const loadKardexInputs = async () => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            start_date: dateRange.value && dateRange.value[0] ? dateRange.value[0].toISOString().split('T')[0] : null,  // Fecha de inicio
            end_date: dateRange.value && dateRange.value[1] ? dateRange.value[1].toISOString().split('T')[0] : null,  // Fecha de fin
        };

        const response = await axios.get('/items/karde', { params });
        inputs.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar insumos:', error);
    } finally {
        loading.value = false;
    }
};

const props = defineProps<{
    refresh: number;
}>();

// Recarga la tabla cuando `refreshCount` cambia
watch(refreshCount, loadKardexInputs);
watch(() => props.refresh, loadKardexInputs);
watch(
    () => selectedEstadoInput.value,
    () => {
        currentPage.value = 1;
        loadKardexInputs();
    },
);
watch(deleteInputDialog, (val) => {
    console.log('Dialogo eliminar visible:', val);
});

const onPage = (event: { page: number; rows: number }) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadKardexInputs();
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadKardexInputs();
}, 500);

onMounted(loadKardexInputs);

// Watcher para el rango de fechas
watch(dateRange, () => {
    // Verificar si ambas fechas están seleccionadas
    if (dateRange.value && dateRange.value[0] && dateRange.value[1]) {
        pagination.value.currentPage = 1;  // Resetear la página a la primera
        loadKardexInputs();  // Realizar la búsqueda automáticamente cuando ambos valores estén seleccionados
    }
});

let lastDoc: jsPDF | null = null; // Variable global

const generatePDF = (row: any) => {
    const doc = new jsPDF();

    // Hora y fecha actual
    const now = new Date();
    const formatTime = (date: Date) => date.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    const formatDate = (date: Date) => date.toLocaleDateString('es-PE');
    const currentDate = formatDate(now);
    const currentTime = formatTime(now);

    // Verificar si hay código y precio total
    const hasCode = row.code !== undefined && row.code !== null && row.code !== '';
    const hasTotalPrice = row.totalPrice !== undefined && row.totalPrice !== null && row.totalPrice !== '';

    const isInput = row.idInput !== null && row.idInput !== undefined;
    const itemType = isInput ? 'INSUMO' : 'PRODUCTO';
    const itemName = isInput ? row.nameInput : row.nameProduct;

    // Nombre empresa centrado
    doc.setFontSize(15);
    doc.text('RESTAURANTE E.I.R.L', 105, 15, { align: 'center' });

    // Fecha y hora actual
    doc.setFontSize(9);
    doc.text(`Fecha: ${currentDate}    Hora: ${currentTime}`, 105, 21, { align: 'center' });

    // Línea separadora
    doc.setLineWidth(0.5);
    doc.line(10, 24, 200, 24);

    // Título
    doc.setFontSize(13);
    doc.text('COMPROBANTE DE KARDEX', 105, 33, { align: 'center' });

    // Datos a la izquierda
    doc.setFontSize(10);
    let yStart = 42;
    doc.text(`Tipo de Item: ${itemType}`, 13, yStart);
    yStart += 6;
    if (hasCode) {
        doc.text(`Código: ${row.code}`, 13, yStart);
        yStart += 6;
    }
    doc.text(`Usuario: ${row.username || ''}`, 13, yStart);
    yStart += 6;
    doc.text(`Tipo de movimiento: ${row.movement_type || ''}`, 13, yStart);
    yStart += 6;
    doc.text(`${isInput ? 'Insumo' : 'Producto'}: ${itemName || ''}`, 13, yStart);
    yStart += 6;
    // Fecha (de la fila) a la derecha
    doc.text(`Fecha: ${row.created_at || ''}`, 150, 42);

    // Línea horizontal antes de la tabla
    doc.line(10, yStart + 5, 200, yStart + 5);

    // Construir columnas y valores de la tabla
    const tableHead: string[][] = [['Cantidad', 'Unidad']];
    const tableBody: (string | number)[][] = [[
        row.quantity !== undefined && row.quantity !== null && row.quantity !== '' ? row.quantity : '0.00',
        `${row.quantityUnitMeasure ?? ''} ${row.unitMeasure ?? ''}`,
    ]];

    if (hasTotalPrice) {
        tableHead[0].push('Precio Total');
        tableBody[0].push(row.totalPrice);
    }

    // Tabla
    autoTable(doc, {
        startY: yStart + 10,
        head: tableHead,
        body: tableBody,
        theme: 'plain',
        headStyles: {
            fontStyle: 'bold',
            halign: 'center',
            fillColor: [255,255,255],
            textColor: [0,0,0],
        },
        bodyStyles: { halign: 'center' },
        tableLineColor: [0,0,0],
        tableLineWidth: 0.5,
    });

    // Línea horizontal después de la tabla
    const tableEndY = (doc as any).lastAutoTable?.finalY ?? yStart + 20;
    doc.line(10, tableEndY, 200, tableEndY);

    // Mostrar Total Movimiento solo si hay precio total
    if (hasTotalPrice) {
        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.text(
            `Total Movimiento: S/. ${row.totalPrice}`,
            200,
            tableEndY + 10,
            { align: 'right' }
        );
        doc.setFont('helvetica', 'normal');
    }

    // Pie de página
    const pageCount = doc.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(9);
        doc.text(`Página ${i} de ${pageCount}`, 10, 290, { align: 'left' });
        doc.text(SYSTEM_URL, 200, 290, { align: 'right' });
    }

    lastDoc = doc;
    const pdfBlob = doc.output('blob');
    pdfUrl.value = URL.createObjectURL(pdfBlob);
    showPdfDialog.value = true;
};

const closePdfDialog = () => {
    showPdfDialog.value = false;
    if (pdfUrl.value) {
        URL.revokeObjectURL(pdfUrl.value);
        pdfUrl.value = null;
    }
};

const downloadPDF = () => {
    if (lastDoc) {
        // Usa lo que quieras como nombre dinámico:
        lastDoc.save('comprobante-kardex.pdf');
    }
};
</script>

<template>
    <DataTable
        ref="dt"
        v-model:selection="selectedInputs"
        :value="inputs"
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
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} Kardexes de Items"
    >
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h4 class="m-0">Kardex de Items</h4>
                <div class="flex flex-wrap gap-2">
                    <div class="flex gap-2">
                        <Calendar 
                            v-model="dateRange" 
                            selectionMode="range" 
                            placeholder="Rango de fechas" 
                            class="w-full"
                            dateFormat="yy-mm-dd"
                            :maxDate="today"
                            showIcon
                        />
                    </div>
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar por código..." />
                    </IconField>

                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadKardexInputs" />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>


<Column field="username" header="Usuario" sortable style="min-width: 7rem;" />
     
              <Column field="movement_type" header="Movimiento" sortable style="min-width: 7rem" />
            <Column field="item_type" header="Tipo" sortable style="min-width: 6rem">
                <template #body="{ data }">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                        :class="data.idInput ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'">
                        {{ data.idInput ? 'INSUMO' : 'PRODUCTO' }}
                    </span>
                </template>
            </Column>
            <Column field="name" header="Item" sortable style="min-width: 12rem">
                <template #body="{ data }">
                    <span>{{ data.nameInput || data.nameProduct || 'N/A' }}</span>
                </template>
            </Column>
<Column field="quantity" header="Cantidad" sortable style="min-width: 7rem" />
              <Column header="Unidad" sortable style="min-width: 7rem">
            <template #body="{ data }">
                <span>{{ data.quantityUnitMeasure }} {{ data.unitMeasure }}</span>
            </template>
        </Column>
          <Column field="code" header="Código" sortable style="min-width: 7rem" />
            <Column field="totalPrice" header="Precio Total" sortable style="min-width: 9rem" />
               <Column field="created_at" header="Fecha" sortable style="min-width: 13rem" />
        <Column field="accions" header="Acciones" :exportable="false" style="min-width: 8rem">
            <template #body="{ data }">
                <Button icon="pi pi-file-pdf" outlined rounded class="mr-2" @click="generatePDF(data)" />
            </template>
        </Column>
    </DataTable>
<Dialog v-model:visible="showPdfDialog" header="Vista previa del comprobante" :style="{ width: '95vw', maxWidth: '800px' }" modal :closable="true" @hide="closePdfDialog">
    <template #default>
        <iframe
            v-if="pdfUrl"
            :src="pdfUrl"
            width="100%"
            height="700px"
            style="border: none;"
        ></iframe>
        <div class="flex justify-end mt-2">
            <Button label="Descargar PDF" icon="pi pi-download" @click="downloadPDF" />
        </div>
    </template>
</Dialog>
</template>
