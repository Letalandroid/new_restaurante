<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Card from 'primevue/card';
import Divider from 'primevue/divider';
import Avatar from 'primevue/avatar';
import Skeleton from 'primevue/skeleton';

interface Cliente {
    id: number;
    name: string;
    lastname: string;
    email: string;
    phone: string;
    codigo: string;
    Cliente_Tipo: string;
    state: boolean | number;
    creacion: string;
    actualizacion: string;
}

const props = defineProps<{
    visible: boolean;
    cliente: Cliente | null;
}>();

const emits = defineEmits(['update:visible']);

const loading = ref(false); //Estado de carga

//Muestra loading cada vez que se abre el modal
watch(
    () => props.visible,
    (val) => {
        if (val) {
            loading.value = true;
            setTimeout(() => {
                loading.value = false;
            }, 1000); //tiempo simulado de carga
        }
    }
);

const closeDialog = () => {
    emits('update:visible', false);
};

// Función para obtener las iniciales del cliente
const getInitials = (name: string, lastname: string) => {
    return `${name?.charAt(0) || ''}${lastname?.charAt(0) || ''}`.toUpperCase();
};

// Función para formatear fecha
const formatDate = (dateString: string) => {
    return dateString || '-';
};

// Estado visual del cliente
const estadoCliente = computed(() => {
    if (!props.cliente) return { texto: '', tipo: '' };
    return props.cliente.state
        ? { texto: 'ACTIVO', tipo: 'success' }
        : { texto: 'INACTIVO', tipo: 'danger' };
});
</script>

<template>
    <Dialog 
        :visible="props.visible"
        @update:visible="emits('update:visible', $event)"
        modal 
        header="Detalles del Cliente" 
        :style="{ width: '500px', maxWidth: '90vw' }"
        :closable="false"
        class="view-cliente-dialog"
    >
        <!-- 🌀 Loading antes de mostrar datos -->
        <div v-if="loading" class="loading-container">
            <div class="flex flex-column align-items-center gap-3 p-4">
                <Skeleton shape="circle" size="4rem" class="mb-2"></Skeleton>
                <Skeleton width="10rem" height="1.5rem" class="mb-2"></Skeleton>
                <Skeleton width="8rem" height="1rem" class="mb-4"></Skeleton>
                <Skeleton width="100%" height="2rem" class="mb-2"></Skeleton>
                <Skeleton width="100%" height="2rem" class="mb-2"></Skeleton>
            </div>
        </div>

        <!-- Datos del cliente -->
        <div v-else-if="props.cliente" class="client-data-container">
            <div class="client-header text-center mb-4">
                <Avatar 
                    :label="getInitials(props.cliente.name, props.cliente.lastname)" 
                    class="client-avatar shadow-2"
                    size="xlarge"
                    :style="{
                        backgroundColor: props.cliente.state ? 'var(--green-500)' : 'var(--red-500)',
                        color: 'var(--surface-0)',
                        border: '2px solid var(--surface-border)'
                    }"
                />
                <div>
                    <h2 class="client-name m-0 text-900">
                        {{ props.cliente.name }} {{ props.cliente.lastname }}
                    </h2>
                    <Tag 
                        :value="estadoCliente.texto" 
                        :severity="estadoCliente.tipo" 
                        class="mt-2 status-tag"
                    />
                </div>
            </div>

            <Divider />

            <Card class="mb-3">
                <template #title>
                    <div class="flex align-items-center gap-2">
                        <i class="pi pi-id-card text-primary"></i>
                        <span>Información de Contacto</span>
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-nogutter client-info-grid">
                        <div class="col-6 info-item">
                            <div class="info-label">
                                <i class="pi pi-envelope mr-2 text-color-secondary"></i>
                                Email
                            </div>
                            <div class="info-value text-900">{{ props.cliente.email }}</div>
                        </div>
                        <div class="col-6 info-item">
                            <div class="info-label">
                                <i class="pi pi-phone mr-2 text-color-secondary"></i>
                                Teléfono
                            </div>
                            <div class="info-value text-900">{{ props.cliente.phone }}</div>
                        </div>
                        <div class="col-6 info-item">
                            <div class="info-label">
                                <i class="pi pi-qrcode mr-2 text-color-secondary"></i>
                                Código
                            </div>
                            <div class="info-value text-900 font-mono">{{ props.cliente.codigo }}</div>
                        </div>
                        <div class="col-6 info-item">
                            <div class="info-label">
                                <i class="pi pi-tag mr-2 text-color-secondary"></i>
                                Tipo
                            </div>
                            <div class="info-value text-900">
                                <Tag :value="props.cliente.Cliente_Tipo" severity="info" />
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <Card>
                <template #title>
                    <div class="flex align-items-center gap-2">
                        <i class="pi pi-database text-primary"></i>
                        <span>Información del Sistema</span>
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-nogutter client-info-grid">
                        <div class="col-12 info-item">
                            <div class="info-label">
                                <i class="pi pi-calendar-plus mr-2 text-color-secondary"></i>
                                Fecha de Creación
                            </div>
                            <div class="info-value text-900">{{ formatDate(props.cliente.creacion) }}</div>
                        </div>
                        <div class="col-12 info-item">
                            <div class="info-label">
                                <i class="pi pi-calendar-edit mr-2 text-color-secondary"></i>
                                Última Actualización
                            </div>
                            <div class="info-value text-900">{{ formatDate(props.cliente.actualizacion) }}</div>
                        </div>
                        <div class="col-12 info-item">
                            <div class="info-label">
                                <i class="pi pi-key mr-2 text-color-secondary"></i>
                                ID del Cliente
                            </div>
                            <div class="info-value text-900 font-mono">#{{ props.cliente.id }}</div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Footer -->
        <template #footer>
            <div class="flex justify-content-end w-full">
                <Button 
                    label="Cerrar" 
                    icon="pi pi-times" 
                    @click="closeDialog" 
                    class="p-button-outlined"
                />
            </div>
        </template>
    </Dialog>
</template>

<style scoped>
.client-logo {
    width: 70px;
    height: 70px;
    object-fit: contain;
    border-radius: 50%;
}

.client-avatar {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 1rem;
    transition: transform 0.2s ease-in-out;
}
.client-avatar:hover {
    transform: scale(1.05);
}

.client-name {
    font-size: 1.5rem;
    font-weight: 600;
}

.status-tag {
    font-weight: 600;
    padding: 0.5rem 1rem;
}

.loading-container {
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.client-info-grid {
    gap: 1rem 0;
}

.info-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--surface-200);
}

.info-label {
    font-size: 0.875rem;
    color: var(--text-color-secondary);
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
}

.info-value {
    font-size: 1rem;
    font-weight: 500;
}

:deep(.p-card) {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--surface-200);
}

:deep(.p-divider) {
    margin: 1.5rem 0;
}
</style>
