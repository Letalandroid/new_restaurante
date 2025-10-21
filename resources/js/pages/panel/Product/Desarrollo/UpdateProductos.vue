<script setup lang="ts">
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import axios, { AxiosError } from 'axios';

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

interface Option {
    label: string;
    value: number | string;
}

interface ServerErrors {
    [key: string]: string[];
}

const props = defineProps<{
    visible: boolean;
    productoId: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:visible', value: boolean): void;
    (e: 'updated'): void;
}>();

const toast = useToast();
const serverErrors = ref<ServerErrors>({});
const submitted = ref<boolean>(false);
const loading = ref<boolean>(false);
const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const dialogVisible = ref<boolean>(props.visible);
watch(() => props.visible, (val) => dialogVisible.value = val);
watch(dialogVisible, (val) => emit('update:visible', val));

const producto = ref<Producto>({
    name: '',
    details: '',
    state: false,
    idCategory: null,
    idAlmacen: null,
    priceSale: null,
    quantityUnitMeasure: null,
    unitMeasure: '',
    stock: null,
    foto: null
});

// Opciones para unidades de medida
const unidadesMedida = ref<Option[]>([
    { label: 'Kilogramos', value: 'kg' },
    { label: 'Gramos', value: 'g' },
    { label: 'Litros', value: 'litros' },
    { label: 'Mililitros', value: 'ml' },
    { label: 'Unidad', value: 'unidad' },
]);

const categorias = ref<Option[]>([]);
const almacenes = ref<Option[]>([]);

watch(() => props.visible, async (val) => {
    if (val && props.productoId) {
        await fetchProducto();
        await fetchCategorias();
        await fetchAlmacenes();
    }
});

const fetchProducto = async (): Promise<void> => {
    loading.value = true;
    try {
        // 🔹 Reiniciamos antes de cargar un nuevo producto
        imagePreview.value = null;
        producto.value.foto = null;
        if (fileInput.value) {
            fileInput.value.value = '';
        }

        const { data } = await axios.get(`/producto/${props.productoId}`);
        const p = data.product;

        producto.value = {
            name: p.name,
            details: p.details,
            state: !!p.state, // asegurar booleano
            idCategory: p.idCategory,
            idAlmacen: p.idAlmacen,
            priceSale: p.priceSale,
            quantityUnitMeasure: p.quantityUnitMeasure,
            unitMeasure: p.unitMeasure,
            stock: p.stock,
            foto: null
        };

        // 🔹 Solo asigna preview si realmente tiene imagen
        if (p.foto && p.foto !== 'sin imagen') {
            imagePreview.value = `/storage/uploads/fotos/productos/${p.foto}`;
        }

    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo cargar el producto',
            life: 3000
        });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const fetchCategorias = async (): Promise<void> => {
    try {
        const { data } = await axios.get('/categoria', { params: { state: 1 } });
        categorias.value = data.data.map((c: any) => ({ label: c.name, value: c.id }));
    } catch (e) {
        toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'No se pudieron cargar categorías' });
        console.error(e);
    }
};

const fetchAlmacenes = async (): Promise<void> => {
    try {
        const { data } = await axios.get('/almacen', { params: { state: 1 } });
        almacenes.value = data.data.map((a: any) => ({ label: a.name, value: a.id }));
    } catch (e) {
        toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'No se pudieron cargar almacenes' });
        console.error(e);
    }
};

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

const updateProducto = async (): Promise<void> => {
    submitted.value = true;
    serverErrors.value = {};

    try {
        const formData = new FormData();
        formData.append('name', producto.value.name);
        formData.append('details', producto.value.details);
        formData.append('state', producto.value.state? '1' : '0');
        formData.append('idCategory', producto.value.idCategory?.toString() || '');
        formData.append('idAlmacen', producto.value.idAlmacen?.toString() || '');
        formData.append('priceSale', producto.value.priceSale?.toString() || '');
        formData.append('quantityUnitMeasure', producto.value.quantityUnitMeasure?.toString() || '');
        formData.append('unitMeasure', producto.value.unitMeasure);
        formData.append('stock', producto.value.stock?.toString() || '');
        
        if (producto.value.foto) {
            formData.append('foto', producto.value.foto);
        }

        // Usar PUT para edición
        // Nota: Algunos servidores pueden requerir POST para FormData, 
        // pero Laravel normalmente maneja PUT con _method
        await axios.post(`/producto/${props.productoId}`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            },
            params: {
                '_method': 'PUT'
            }
        });

        toast.add({
            severity: 'success',
            summary: 'Actualizado',
            detail: 'Producto actualizado correctamente',
            life: 3000
        });

        dialogVisible.value = false;
        emit('updated');
    } catch (error) {
        const err = error as AxiosError<{ message?: string; errors?: ServerErrors }>;
        if (err.response?.status === 422) {
            serverErrors.value = err.response.data.errors || {};
            toast.add({
                severity: 'error',
                summary: 'Error de validación',
                detail: err.response.data.message || 'Revisa los campos.',
                life: 5000
            });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudo actualizar el producto',
                life: 3000
            });
        }
    }
};
</script>

<template>
    <Dialog v-model:visible="dialogVisible" header="Editar Producto" modal :closable="true" :closeOnEscape="true"
        :style="{ width: '700px' }">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <!-- Nombre -->
                <div class="col-span-10">
                    <label class="block font-bold mb-2">Nombre <span class="text-red-500">*</span></label>
                    <InputText v-model="producto.name" required maxlength="100" fluid
                        :class="{ 'p-invalid': serverErrors.name }" />
                    <small v-if="serverErrors.name" class="p-error">{{ serverErrors.name[0] }}</small>
                </div>

                <!-- Estado -->
                <div class="col-span-2">
                    <label class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="producto.state" :binary="true" fluid />
                        <Tag :value="producto.state ? 'Activo' : 'Inactivo'"
                            :severity="producto.state ? 'success' : 'danger'" />
                    </div>
                </div>

                <!-- Detalle -->
                <div class="col-span-12">
                    <label class="block font-bold mb-2">Detalle</label>
                    <Textarea v-model="producto.details" autoResize maxlength="500" rows="3" fluid
                        :class="{ 'p-invalid': serverErrors.details }" />
                    <small v-if="serverErrors.details" class="p-error">{{ serverErrors.details[0] }}</small>
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
                        :class="{ 'p-invalid': serverErrors.idCategory }"
                    />
                    <small v-if="serverErrors.idCategory" class="p-error">{{ serverErrors.idCategory[0] }}</small>
                </div>

                <!-- Almacén (Dropdown con búsqueda) -->
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
                        :class="{ 'p-invalid': serverErrors.idAlmacen }"
                    />
                    <small v-if="serverErrors.idAlmacen" class="p-error">{{ serverErrors.idAlmacen[0] }}</small>
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
                        :class="{ 'p-invalid': serverErrors.priceSale }"
                    />
                    <small v-if="serverErrors.priceSale" class="p-error">{{ serverErrors.priceSale[0] }}</small>
                </div>

                <!-- Stock -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">Stock <span class="text-red-500">*</span></label>
                    <InputNumber 
                        v-model="producto.stock" 
                        fluid
                        :min="1"
                        :max="1000000"
                        :class="{ 'p-invalid': serverErrors.stock }"
                    />
                    <small v-if="serverErrors.stock" class="p-error">{{ serverErrors.stock[0] }}</small>
                </div>

                <!-- Cantidad de Medida -->
                <div class="col-span-6">
                    <label class="block font-bold mb-2">Cantidad de Medida <span class="text-red-500">*</span></label>
                    <InputNumber 
                        v-model="producto.quantityUnitMeasure" 
                        fluid
                        :min="0"
                        :fractionDigits="2"
                        :class="{ 'p-invalid': serverErrors.quantityUnitMeasure }"
                    />
                    <small v-if="serverErrors.quantityUnitMeasure" class="p-error">{{ serverErrors.quantityUnitMeasure[0] }}</small>
                </div>

                <!-- Unidad de Medida -->
                <div class="col-span-4">
                    <label class="block font-bold mb-2">Unidad de Medida <span class="text-red-500">*</span></label>
                    <Dropdown 
                        v-model="producto.unitMeasure" 
                        :options="unidadesMedida" 
                        optionLabel="label" 
                        optionValue="value" 
                        fluid
                        placeholder="Seleccione unidad" 
                        style="width: 325px"
                        :class="{ 'p-invalid': serverErrors.unitMeasure }"
                    />
                    <small v-if="serverErrors.unitMeasure" class="p-error">{{ serverErrors.unitMeasure[0] }}</small>
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
                        
                        <!-- Vista previa de la imagen -->
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
                        <small v-if="serverErrors.foto" class="p-error">{{ serverErrors.foto[0] }}</small>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateProducto" :loading="loading" />
        </template>
    </Dialog>
</template>