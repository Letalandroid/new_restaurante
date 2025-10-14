<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Nuevo producto" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #end>
            <!-- ToolsProduct para los botones de exportar e importar -->
            <ToolsProduct @import-success="loadProducto"/>       
        </template>
    </Toolbar>

    <Dialog v-model:visible="productoDialog" :style="{ width: '700px' }" header="Registro de productos" :modal="true">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Nombre -->
                <div class="col-span-10">
                    <label class="block font-bold mb-2">Nombre <span class="text-red-500">*</span></label>
                    <InputText v-model.trim="producto.name" fluid maxlength="100" />
                    <small v-if="submitted && !producto.name" class="text-red-500">El nombre es obligatorio.</small>
                    <small v-else-if="submitted && producto.name.length < 2" class="text-red-500">El nombre debe tener al menos 2 caracteres.</small>
                    <small v-else-if="serverErrors.name" class="text-red-500">{{ serverErrors.name[0] }}</small>
                </div>

                <!-- Estado -->
                <div class="col-span-2">
                    <label class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="producto.state" :binary="true" />
                        <Tag :value="producto.state ? 'Activo' : 'Inactivo'" fluid :severity="producto.state ? 'success' : 'danger'" />
                        <small v-if="submitted && producto.state === null" class="text-red-500">El estado es obligatorio.</small>
                        <small v-else-if="serverErrors.state" class="text-red-500">{{ serverErrors.state[0] }}</small>
                    </div>
                </div>

                <!-- Detalles -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Detalle</label>
                    <Textarea v-model="producto.details" autoResize rows="3" fluid maxlength="500" />
                    <small v-if="serverErrors.details" class="text-red-500">{{ serverErrors.details[0] }}</small>
                </div>

                <!-- Categoría -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">Categoría <span class="text-red-500">*</span></label>
                    <Dropdown
                        v-model="producto.idCategory" 
                        :options="categorias" 
                        optionLabel="label" 
                        optionValue="value" 
                        fluid
                        placeholder="Seleccione categoría" 
                        filter
                        filterBy="label"
                        filterPlaceholder="Buscar categoria..."  
                        style="width: 325px;"
                    />
                    <small v-if="submitted && !producto.idCategory" class="text-red-500">La categoría es obligatoria.</small>
                    <small v-else-if="serverErrors.idCategory" class="text-red-500">{{ serverErrors.idCategory[0] }}</small>
                </div>

                <!-- Almacén con Dropdown con búsqueda -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">Almacén <span class="text-red-500">*</span></label>
                    <Dropdown 
                        v-model="producto.idAlmacen" 
                        :options="almacenes" 
                        optionLabel="label" 
                        optionValue="value" 
                        fluid
                        placeholder="Seleccione almacén" 
                        filter
                        filterBy="label"
                        filterPlaceholder="Buscar almacen..." 
                        style="width: 325px;"
                    />
                    <small v-if="submitted && !producto.idAlmacen" class="text-red-500">El almacén es obligatorio.</small>
                    <small v-else-if="serverErrors.idAlmacen" class="text-red-500">{{ serverErrors.idAlmacen[0] }}</small>
                </div>

                <!-- Precio de Venta -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">Precio de Venta <span class="text-red-500">*</span></label>
                    <InputNumber 
                        v-model="producto.priceSale" 
                        mode="currency" 
                        currency="PEN" 
                        locale="es-PE" 
                        fluid
                        :min="0"
                    />
                    <small v-if="submitted && producto.priceSale === null" class="text-red-500">El precio de venta es obligatorio.</small>
                    <small v-else-if="serverErrors.priceSale" class="text-red-500">{{ serverErrors.priceSale[0] }}</small>
                </div>

                <!-- Stock -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">Stock <span class="text-red-500">*</span></label>
                    <InputNumber 
                        v-model="producto.stock" 
                        fluid
                        :min="1"
                        :max="1000000"
                    />
                    <small v-if="submitted && producto.stock === null" class="text-red-500">El stock es obligatorio.</small>
                    <small v-else-if="serverErrors.stock" class="text-red-500">{{ serverErrors.stock[0] }}</small>
                </div>

                <!-- Cantidad de Medida -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">Cantidad de Medida <span class="text-red-500">*</span></label>
                    <InputNumber 
                        v-model="producto.quantityUnitMeasure" 
                        fluid
                        :min="0"
                        :fractionDigits="2"
                    />
                    <small v-if="submitted && producto.quantityUnitMeasure === null" class="text-red-500">La cantidad de medida es obligatoria.</small>
                    <small v-else-if="serverErrors.quantityUnitMeasure" class="text-red-500">{{ serverErrors.quantityUnitMeasure[0] }}</small>
                </div>

                <!-- Unidad de Medida -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">Unidad de Medida <span class="text-red-500">*</span></label>
                    <Dropdown 
                        v-model="producto.unitMeasure" 
                        :options="unidadesMedida" 
                        optionLabel="label" 
                        optionValue="value" 
                        fluid
                        placeholder="Seleccione unidad" 
                        style="width: 325px;"
                    />
                    <small v-if="submitted && !producto.unitMeasure" class="text-red-500">La unidad de medida es obligatoria.</small>
                    <small v-else-if="serverErrors.unitMeasure" class="text-red-500">{{ serverErrors.unitMeasure[0] }}</small>
                </div>

                <!-- Foto -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">Foto</label>
                    <div class="flex flex-col gap-3">
                        <input 
                            type="file" 
                            accept="image/jpg,image/jpeg,image/png"
                            @change="onFileSelected"
                            class="w-full p-2 border border-gray-300 rounded"
                            ref="fileInput"
                        />
                        
                        <!-- Vista previa centrada en el modal -->
                        <div v-if="imagePreview" class="mt-2">
                            <label class="block font-medium mb-2">Vista previa:</label>
                            <div class="bg-white rounded-lg shadow-md p-4 flex flex-col items-center justify-center">
                            <img
                            :src="imagePreview"
                            alt="Vista previa de la foto"
                            class="max-h-[70vh] object-contain rounded"
                            />
                            <Button
                            label="Quitar foto"
                            icon="pi pi-times"
                            severity="danger"
                            text
                            size="small"
                            @click="removeFoto"
                            class="mt-4"
                            />
                        </div>
                        </div>
                        
                        <small class="text-gray-500">Formatos: JPG, JPEG, PNG (Máx. 5MB)</small>
                        <small v-if="serverErrors.foto" class="text-red-500">{{ serverErrors.foto[0] }}</small>
                    </div>
                </div>

            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarProducto" />
        </template>
    </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import Dropdown from 'primevue/dropdown';
import ToolsProduct from './toolsProduct.vue';

interface Producto {
    name: string;
    details: string;
    state: boolean;
    idCategory: number | null;
    idAlmacen: number | null;
    priceSale: number | null;
    quantityUnitMeasure: number | null;
    unitMeasure: string;
    stock: number | null;
    foto: File | null;
}

interface OpcionSelect {
    label: string;
    value: number | string;
}

interface ServerErrors {
    [key: string]: string[];
}

const toast = useToast();
const submitted = ref<boolean>(false);
const productoDialog = ref<boolean>(false);
const serverErrors = ref<ServerErrors>({});
const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const emit = defineEmits<{
    (e: 'producto-agregado'): void;
}>();

const producto = ref<Producto>({
    name: '',
    details: '',
    state: true,
    idCategory: null,
    idAlmacen: null,
    priceSale: null,
    quantityUnitMeasure: null,
    unitMeasure: '',
    stock: null,
    foto: null
});

// Opciones para unidades de medida
const unidadesMedida = ref<OpcionSelect[]>([
    { label: 'Kilogramos', value: 'kg' },
    { label: 'Gramos', value: 'g' },
    { label: 'Litros', value: 'litros' },
    { label: 'Mililitros', value: 'ml' },
    { label: 'Unidad', value: 'unidad' },
]);

// Método para recargar la lista de productos
const loadProducto = async (): Promise<void> => {
    try {
        const response = await axios.get('/producto');  // Aquí haces una solicitud GET para obtener los productos
        console.log(response.data);
        // Realiza lo que necesites con la respuesta, como actualizar el listado en un componente superior
        emit('producto-agregado');  // Si quieres que un componente padre reciba la notificación de la actualización
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar los productos', life: 3000 });
        console.error(error);
    }
};

const categorias = ref<OpcionSelect[]>([]);
const almacenes = ref<OpcionSelect[]>([]);

function onFileSelected(event: Event): void {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        producto.value.foto = file;
        
        // Crear vista previa
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
}

function removeFoto(): void {
    producto.value.foto = null;
    imagePreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function resetProducto(): void {
    producto.value = {
        name: '',
        details: '',
        state: true,
        idCategory: null,
        idAlmacen: null,
        priceSale: null,
        quantityUnitMeasure: null,
        unitMeasure: '',
        stock: null,
        foto: null
    };
    imagePreview.value = null;
    serverErrors.value = {};
    submitted.value = false;
    
    // Resetear el input file
    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function openNew(): void {
    resetProducto();
    fetchCategorias();
    fetchAlmacenes();
    productoDialog.value = true;
}

function hideDialog(): void {
    productoDialog.value = false;
    resetProducto();
}

async function fetchCategorias(): Promise<void> {
    try {
        const { data } = await axios.get('/categoria', { params: { state: 1 } });
        categorias.value = data.data.map((c: any) => ({ label: c.name, value: c.id }));
    } catch (e) {
        toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'No se pudieron cargar categorías' });
        console.error(e);
    }
}

async function fetchAlmacenes(): Promise<void> {
    try {
        const { data } = await axios.get('/almacen', { params: { state: 1 } });
        almacenes.value = data.data.map((a: any) => ({ label: a.name, value: a.id }));
    } catch (e) {
        toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'No se pudieron cargar almacenes' });
        console.error(e);
    }
}

function guardarProducto(): void {
    submitted.value = true;
    serverErrors.value = {};

    const formData = new FormData();
    formData.append('name', producto.value.name);
    formData.append('details', producto.value.details);
    formData.append('state', producto.value.state ? '1' : '0');
    formData.append('idCategory', producto.value.idCategory?.toString() || '');
    formData.append('idAlmacen', producto.value.idAlmacen?.toString() || '');
    formData.append('priceSale', producto.value.priceSale?.toString() || '');
    formData.append('quantityUnitMeasure', producto.value.quantityUnitMeasure?.toString() || '');
    formData.append('unitMeasure', producto.value.unitMeasure);
    formData.append('stock', producto.value.stock?.toString() || '');
    
    if (producto.value.foto) {
        formData.append('foto', producto.value.foto);
    }

    axios.post('/producto', formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    .then(() => {
        toast.add({ severity: 'success', summary: 'Éxito', detail: 'Producto registrado', life: 3000 });
        hideDialog();
        emit('producto-agregado');
    })
    .catch((error) => {
    if (error.response?.status === 422) {
        console.log(error.response.data.errors); // 👈 muestra los errores exactos en consola
        serverErrors.value = error.response.data.errors || {};
    } else {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo registrar el producto',
            life: 3000
        });
    }
});
}
</script>