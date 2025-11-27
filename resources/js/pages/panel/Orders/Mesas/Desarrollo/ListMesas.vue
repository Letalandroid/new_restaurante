<!-- eslint-disable vue/block-lang -->
<script setup>
import { router } from '@inertiajs/core';
import axios from 'axios';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import { Head } from '@inertiajs/vue3';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Toolbar from 'primevue/toolbar';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import { useToast } from 'primevue/usetoast';
import { computed, onMounted, ref, watch } from 'vue';
const isButtonDisabled = ref(false);

import AddCliente from './AddCliente.vue';

// NUEVAS VARIABLES PARA PRODUCTOS
const products = ref([]);
const searchQueryProduct = ref('');
const currentPageProduct = ref(1);
const perPageProduct = ref(7);
const totalPagesProduct = ref(1);

// Initialize toast
const toast = useToast();
const showInsumosDialog = ref(false); // Para mostrar el dialogo de insumos
const mesas = ref([]);
const searchQuery = ref('');
const selectedFloor = ref(null); // null significa 'Todos los pisos'
const selectedArea = ref(null); // null significa 'Todas las áreas'
const floors = ref([]); // Array para almacenar los pisos
const areas = ref([]); // Array para almacenar las áreas
const showOrderForm = ref(false);
const formulariopedido = ref(true);
const showOrderHistorial = ref(false);
const selectedMesa = ref(null);
const platos = ref([]);
const historialPlatos = ref([]);
const historialLoading = ref(false);
const historialPagination = ref({
    page: 1,
    perPage: 7,
    total: 0,
});
const globalFilterValue = ref('');
const order = ref({
    mesaId: null,
    tablenum: null,
    platos: [],
    productos: [], // SEPARAR PLATOS Y PRODUCTOS
    idOrder: null,
});

// Estado para el formulario
const showReciboForm = ref(false);
const recibo = ref({
    codigoCliente: '',
    tipoRecibo: '', // 'Factura' o 'Boleta'
    tipoPago: '', // 'Tarjeta', 'Transferencia', 'Efectivo', 'Yape', 'Plin'
    total: 0,
});

const clientesOptions = ref([]); // Resultados de la búsqueda de clientes
const searchTerm = ref(''); // Término de búsqueda ingresado por el usuario
const showResults = ref(false); // Para mostrar/ocultar los resultados
const selectedCliente = ref(null); // Cliente seleccionado

// Función para manejar la búsqueda de clientes
const handleSearch = async () => {
    if (searchTerm.value.trim()) {
        showResults.value = true;
        try {
            const response = await axios.get('/cliente', {
                params: {
                    codigo: searchTerm.value, // Se pasa el término de búsqueda
                    state: 1
                },
            });

            if (response.data && Array.isArray(response.data.data)) {
                clientesOptions.value = response.data.data; // Guardamos los resultados en clientesOptions
            }
        } catch (error) {
            console.error('Error en la búsqueda:', error);
        }
    } else {
        // Si no hay texto en el campo de búsqueda, reiniciamos el tipo de recibo
        recibo.value.tipoRecibo = '';  // Reiniciar el tipo de recibo
        clientesOptions.value = []; // Limpiamos los resultados si no hay texto
        showResults.value = false; // Ocultamos los resultados
    }
};

// Función para seleccionar un cliente de la lista
const selectCliente = (cliente) => {
    selectedCliente.value = cliente; // Asignamos el cliente seleccionado
    searchTerm.value = cliente.codigo + ' - ' + cliente.name +' '+cliente.lastname; // Mostramos el código y nombre en el campo de búsqueda
    clientesOptions.value = []; // Limpiamos las opciones después de la selección
    showResults.value = false; // Ocultamos los resultados

    // Establecer el tipo de recibo basado en el tipo de cliente
    if (cliente.client_type_id === 1) {
        recibo.value.tipoRecibo = 'Boleta'; // Si es persona, se selecciona Boleta
    } else if (cliente.client_type_id === 2) {
        recibo.value.tipoRecibo = 'Factura'; // Si es empresa, se selecciona Factura
    }
};
const selectedPlato = ref(null); // Para almacenar el plato seleccionado

const goToCerrarCaja = () => {
    const url = '/caja/cerrar'
    router.visit(url);
};

const showReciboToolbar = ref(false);
// Variables de estado para la paginación
const currentPage = ref(1); // Página actual
const perPage = ref(7); // Platos por página
const totalPages = ref(1); // Total de páginas
const loadHistorialPlatos = async () => {
    if (!order.value.idOrder) return;

    historialLoading.value = true;
    try {
        const response = await axios.get(`/order-dishes`, {
            params: {
                idOrder: order.value.idOrder,
                page: historialPagination.value.page,
                per_page: historialPagination.value.perPage,
                search: globalFilterValue.value,
                state: selectedState.value,
            },
        });

        historialPlatos.value = response.data.data;
        historialPagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar historial de platos:', error);
    } finally {
        historialLoading.value = false;
    }
};

// CORREGIDO: Función para cargar productos (SOLO productos)
const loadProducts = async (search = '') => {
    try {
        const response = await axios.get(`/producto`, {
            params: {
                search: search,
                page: currentPageProduct.value,
                per_page: perPageProduct.value,
                state: 1
            }
        });

        console.log('Productos cargados:', response.data.data);

        // Obtener pedidos pendientes para calcular stock virtual SOLO PARA PRODUCTOS
        const pedidosPendientesResponse = await axios.get('/order-dishes', {
            params: {
                state: 'pendiente'
            }
        });

        const productosEnPedidosPendientes = pedidosPendientesResponse.data.data.filter(
            item => item.idProduct !== null && item.state === 'pendiente'
        );

        // Filtrar productos con stock disponible > 0 y calcular stock virtual
        const productosDisponibles = response.data.data.map((producto) => {
            // Calcular cuántos de este producto están en pedidos pendientes
            const cantidadEnPedidosPendientes = productosEnPedidosPendientes
                .filter(item => item.idProduct === producto.id)
                .reduce((total, item) => total + item.quantity, 0);

            // Stock virtual = stock real - productos en pedidos pendientes
            const stockVirtual = parseFloat(producto.stock_quantity) - cantidadEnPedidosPendientes;

            return {
                ...producto,
                stock_quantity: Math.max(0, stockVirtual), // Mostrar stock virtual (no negativo)
                stock_real: parseFloat(producto.stock_quantity) // Guardar stock real
            };
        }).filter(producto => producto.stock_quantity > 0); // Solo mostrar productos con stock virtual > 0

        products.value = productosDisponibles;
        totalPagesProduct.value = response.data.meta.last_page;
        
        console.log('Productos disponibles (con stock virtual):', productosDisponibles);
    } catch (error) {
        console.error('Error cargando productos:', error);
    }
};

const filteredProducts = computed(() => {
    if (!searchQueryProduct.value) {
        return products.value;
    }
    return products.value.filter((producto) => 
        producto.name.toLowerCase().includes(searchQueryProduct.value.toLowerCase())
    );
});

const loadPlatos = async (search = '') => {
    try {
        // Construir la URL con el parámetro de búsqueda y paginación
        const response = await axios.get(`/plato?search=${search}&page=${currentPage.value}&per_page=${perPage.value}`, {params:{state:1}});

        // Filtrar los platos para que solo se muestren aquellos con quantity > 0
        const platosDisponibles = response.data.data.filter((plato) => plato.quantity > 0);

        // Asignar solo los platos con cantidad disponible
        platos.value = platosDisponibles;

        // Obtener el total de páginas
        totalPages.value = response.data.meta.last_page;
    } catch (error) {
        console.error('Error cargando platos:', error);
    }
};

const showOrderFormForMesa = async (mesaId, tablenum) => {
    console.log('Mesa ID:', mesaId);
    console.log('Tablenum:', tablenum);

    selectedMesa.value = mesas.value.find((mesa) => mesa.id === mesaId);
    order.value.mesaId = selectedMesa.value.id;
    order.value.tablenum = tablenum;
    tableNumber.value = tablenum;
    showOrderForm.value = true;
    showOrderHistorial.value = true;
    showReciboToolbar.value = true;

    // CARGAR AMBOS
    loadPlatos();
    loadProducts();

    try {
        // Buscar en la API si hay un pedido activo para esta mesa
        const { data } = await axios.get('/orders');
        const ordenActiva = data.data.find((orden) => orden.idMesa === mesaId && orden.state !== 'finalizado');

        if (ordenActiva) {
            order.value.idOrder = ordenActiva.id;
            await loadHistorialPlatos();
        } else {
            order.value.idOrder = null;
            historialPlatos.value = [];
            historialPagination.value.total = 0;
        }
    } catch (error) {
        console.error('Error obteniendo pedido activo de la mesa:', error);
    }
};

const getSeverity = (state) => {
    switch (state) {
        case 'pendiente':
            return 'warning';
        case 'servido':
        case 'completado':
            return 'success';
        case 'cancelado':
            return 'danger';
        case 'en preparación':
            return 'info';
        case 'en entrega':
            return 'primary';
        default:
            return null;
    }
};

// CORREGIDO: Función para agregar producto al pedido
const agregarProductoAlPedido = (producto) => {
    const productoExistente = order.value.productos.find((pedido) => 
        pedido.id === producto.id
    );

    if (productoExistente) {
        productoExistente.cantidad = Math.min(productoExistente.cantidad + 1, parseFloat(producto.stock_quantity));
    } else {
        order.value.productos.push({
            id: producto.id,
            name: producto.name,
            price: producto.priceSale, // Usar priceSale
            stock: parseFloat(producto.stock_quantity), // Usar stock_quantity
            cantidad: 1,
            tipo: 'producto'
        });
    }
    console.log('Productos en el pedido:', order.value.productos);
};
const agregarAlPedido = (plato) => {
    // Verifica si el plato ya está en el pedido
    const platoExistente = order.value.platos.find((pedido) => pedido.id === plato.id);

    if (platoExistente) {
        // Si ya existe, solo actualiza la cantidad
        platoExistente.cantidad = Math.min(platoExistente.cantidad + 1, plato.stock);
    } else {
        // Si no existe, agregarlo al pedido con cantidad 1
        order.value.platos.push({
            id: plato.id,
            name: plato.name,
            price: plato.price,
            stock: plato.quantity, // stock máximo
            cantidad: 1, // cantidad inicial en el pedido
        });
    }

    // Imprimir la estructura de los platos en el pedido
    console.log('Platos en el pedido:', order.value.platos);
};

// Función para mostrar los insumos de un plato en el Dialog
const verInsumos = (plato) => {
    console.log(plato); // Verifica qué tiene `plato`
    selectedPlato.value = plato;
    showInsumosDialog.value = true;
};

// Función para cancelar la visualización de insumos (cerrar el dialogo)
const cancelarInsumos = () => {
    showInsumosDialog.value = false; // Cerrar el dialogo
};
// Función para cargar las mesas con los filtros
const loadMesas = async () => {
    try {
        let url = '/mesa'; // URL base de la API

        // Agregar parámetros de filtro a la URL si se seleccionan valores diferentes de "Todos"
        const params = [];
        if (selectedFloor.value && selectedFloor.value !== 'all') {
            params.push(`idFloor=${selectedFloor.value}`);
        }
        if (selectedArea.value && selectedArea.value !== 'all') {
            params.push(`idArea=${selectedArea.value}`);
        }
        if (searchQuery.value) {
            params.push(`search=${searchQuery.value}`); // Filtro por número de mesa
        }
        if (params.length > 0) {
            url += `?${params.join('&')}`;
        }

        // Realizar la solicitud GET con los parámetros adecuados
        const response = await axios.get(url);

        // Filtrar mesas para que solo se muestren las mesas con state: true
        mesas.value = response.data.data.filter(mesa => mesa.state);

        // Filtrar áreas según el piso seleccionado
        updateAreas();
    } catch (error) {
        console.error('Error cargando mesas:', error);
    }
};

// Función para actualizar las áreas basadas en el piso seleccionado
const updateAreas = () => {
    if (selectedFloor.value === 'all' || !selectedFloor.value) {
        areas.value = [
            { label: 'Todas las áreas', value: 'all' },
            ...mesas.value.reduce((uniqueAreas, mesa) => {
                if (!uniqueAreas.some((area) => area.value === mesa.idArea)) {
                    uniqueAreas.push({ label: mesa.area_name, value: mesa.idArea });
                }
                return uniqueAreas;
            }, []),
        ];
    } else {
        areas.value = [
            { label: 'Todas las áreas', value: 'all' },
            ...mesas.value
                .filter((mesa) => mesa.idFloor === selectedFloor.value)
                .reduce((uniqueAreas, mesa) => {
                    if (!uniqueAreas.some((area) => area.value === mesa.idArea)) {
                        uniqueAreas.push({ label: mesa.area_name, value: mesa.idArea });
                    }
                    return uniqueAreas;
                }, []),
        ];
    }

    // Ordenar las áreas por idArea de menor a mayor
    areas.value.sort((a, b) => a.value - b.value);
};

onMounted(async () => {
    try {
        const response = await axios.get('/mesa');
        // Filtrar las mesas con state: true
        mesas.value = response.data.data.filter(mesa => mesa.state);

        // Obtener los pisos únicos basados en idFloor y ordenar de menor a mayor
        floors.value = [
            { label: 'Todos los pisos', value: 'all' },
            ...mesas.value.reduce((uniqueFloors, mesa) => {
                if (!uniqueFloors.some((floor) => floor.value === mesa.idFloor)) {
                    uniqueFloors.push({ label: mesa.floor_name, value: mesa.idFloor });
                }
                return uniqueFloors;
            }, []),
        ];

        // Ordenar los pisos por idFloor de menor a mayor
        floors.value.sort((a, b) => a.value - b.value);

        // Actualizar áreas después de cargar las mesas
        updateAreas();
    } catch (error) {
        console.error('Error cargando mesas:', error);
    }
});
// Variable para almacenar el texto de búsqueda
const searchQueryPlato = ref('');
// Watch para detectar cambios en los filtros (piso, área y búsqueda)
watch([selectedFloor, selectedArea, searchQuery], () => {
    loadMesas(); // Recargar las mesas cada vez que se selecciona un filtro o se actualiza la búsqueda
});
// Watch para detectar cambios en el texto de búsqueda y cargar platos con la paginación
watch(searchQueryPlato, (newSearchQuery) => {
    currentPage.value = 1; // Reiniciar la página a la primera cuando se cambie la búsqueda
    loadPlatos(newSearchQuery);
});

watch(searchQueryProduct, (newSearchQuery) => {
    currentPageProduct.value = 1;
    loadProducts(newSearchQuery);
});

watch(currentPage, () => {
    loadPlatos(searchQueryPlato.value);
});

watch(currentPageProduct, () => {
    loadProducts(searchQueryProduct.value);
});

watch(selectedFloor, () => {
    selectedArea.value = null; // Resetear área seleccionada
    updateAreas(); // Actualizar áreas
});

const goBack = () => {
    const url = `/ordenes/`;
    router.visit(url);
};

const goBackOrder = () => {
    // Limpiar el pedido (vaciar platos y otros datos)
    order.value.platos = [];
    order.value.productos = []; // Limpiar productos también
    order.value.mesaId = null;
    order.value.tablenum = null;

    // Limpiar historial de platos
    historialPlatos.value = [];
    historialPagination.value.total = 0;

    // Limpiar el formulario de recibo
    recibo.value.codigoCliente = '';
    recibo.value.tipoRecibo = ''; // Limpiar tipo de recibo (Factura o Boleta)
    recibo.value.tipoPago = ''; // Limpiar tipo de pago (Tarjeta, Transferencia, etc.)
    recibo.value.operationCode = ''; // Limpiar código de operación
    recibo.value.total = 0; // Limpiar total
 // Vaciar el campo de búsqueda
    searchTerm.value = ''; // Limpiar el término de búsqueda
    // Ocultar los formularios y volver a mostrar las mesas
    showOrderForm.value = false;
    showOrderHistorial.value = false;
    showReciboForm.value = false;
    showReciboToolbar.value = false;

    loadMesas(); // Recargar las mesas
};


const tableNumber = ref(1);

// CORREGIDO: Función para verificar si un producto está agregado
const isProductoAgregado = (producto) => {
    return order.value.productos.some((pedido) => 
        pedido.id === producto.id
    );
};

const isPlatoAgregado = (plato) => {
    return order.value.platos.some((pedido) => pedido.id === plato.id);
};

// CORREGIDO: Función para ajustar cantidad
const adjustQuantity = (item, increment, tipo) => {
    const array = tipo === 'platillo' ? order.value.platos : order.value.productos;
    const index = array.findIndex((i) => i.id === item.id);
    
    if (item.cantidad === 1 && increment === -1) {
        eliminarDelPedido(index, tipo);
    } else {
        item.cantidad = Math.min(Math.max(item.cantidad + increment, 1), item.stock);
    }
};

// Computed property para filtrar los platos según el texto de búsqueda
const filteredPlatos = computed(() => {
    if (!searchQueryPlato.value) {
        return platos.value; // Si no hay búsqueda, mostrar todos los platos
    }
    return platos.value.filter((plato) => plato.name.toLowerCase().includes(searchQueryPlato.value.toLowerCase()));
});

// CORREGIDO: Función para eliminar del pedido
const eliminarDelPedido = (index, tipo) => {
    if (tipo === 'platillo') {
        order.value.platos.splice(index, 1);
    } else {
        order.value.productos.splice(index, 1);
    }
};

const capitalizeFirstLetter = (str) => {
    if (!str) return ''; // Retorna vacío si no hay texto
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

const cancelOrder = () => {
    // Limpiar el pedido (vaciar platos y otros datos)
    order.value.platos = [];
    order.value.productos = [];
};

// CORREGIDO: Función para registrar la orden
const realizarPedido = async () => {
    if (isButtonDisabled.value) return; // Si el botón está deshabilitado, no hacer nada

    // Deshabilitar el botón
    isButtonDisabled.value = true;

    const userId = await fetchUserId();
    try {
        // Verificar que haya al menos un plato o producto
        if (order.value.platos.length === 0 && order.value.productos.length === 0) {
            toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'Debe seleccionar al menos un item', life: 3000 });
            return;
        }

        const data = {
            idCustomer: 1,
            idUser: userId,
            idTable: order.value.mesaId,
            state: 'pendiente',
            platos: order.value.platos.map((plato) => ({
                id: plato.id,
                cantidad: plato.cantidad,
                price: plato.price,
                // NO enviar 'tipo' aquí - el backend espera solo datos de platos
            })),
            productos: order.value.productos.map((producto) => ({
                id: producto.id,
                cantidad: producto.cantidad,
                price: producto.price,
                // NO enviar 'tipo' aquí - el backend espera solo datos de productos
            })),
        };

        const response = await axios.post('/orders', data);

        toast.add({ severity: 'success', summary: 'Éxito', detail: response.data.message, life: 3000 });

        // Guardar el nuevo ID del pedido y recargar historial
        order.value.idOrder = response.data.order.id; // ← backend debe devolver el pedido creado
        order.value.platos = [];
        order.value.productos = [];
        await loadHistorialPlatos(); // ← recarga historial automáticamente

        await loadPlatos(); // Esto actualiza la lista de platos disponibles (por ejemplo, restando los que se pidieron)
        await loadProducts();
    } catch (error) {
        console.error('Error completo:', error);
        const errorMessage = error.response?.data?.message || 'Hubo un problema al registrar la orden';
        toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
    } finally {
        // Habilitar el botón después de 1 segundo
        setTimeout(() => {
            isButtonDisabled.value = false;
        }, 1000);
    }
};

async function fetchUserId() {
    try {
        // Hacemos la solicitud al backend para obtener el user_id
        const { data } = await axios.get('/user-id');

        // Verificamos si la solicitud fue exitosa
        if (data.success) {
            return data.user_id; // Retornamos el user_id
        } else {
            console.error('Error al obtener el user_id');
            return null;
        }
    } catch (e) {
        console.error('Error en la solicitud:', e);
        return null;
    }
}


// Agrega la función para el cambio de página
const onHistorialPageChange = (event) => {
    console.log('Cambio de página en el historial:', event); // Imprimir en consola el evento de cambio de página
    historialPagination.value.page = event.page + 1; // Actualiza la página en la paginación
    loadHistorialPlatos(); // Recarga el historial de platos
};


// Lógica de búsqueda global
const onGlobalSearch = async () => {
    // Reiniciar la página a 1 cuando se realice una búsqueda
    historialPagination.value.page = 1;

    // Llamar a la API con el filtro de búsqueda global
    await loadHistorialPlatos();
};

const onStateChange = async () => {
    await loadHistorialPlatos();
};

const selectedState = ref(null); // Filtro para el estado
const stateOptions = ref([
    { label: 'Todos los estados', value: null },
    { label: 'Pendiente', value: 'pendiente' },
    { label: 'En Preparación', value: 'en preparación' },
    { label: 'En Entrega', value: 'en entrega' },
    { label: 'Completado', value: 'completado' },
    { label: 'Cancelado', value: 'cancelado' },
]);

const cancelDish = async (itemId) => {
    try {
        await axios.put(`/order-dishes/${itemId}`, { state: 'cancelado' });
        toast.add({ severity: 'success', summary: 'Éxito', detail: 'Item cancelado', life: 3000 });

        // Actualiza el historial de platos después de la cancelación
        loadHistorialPlatos();
        loadPlatos();
        loadProducts();
    } catch (error) {
        console.error(error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cancelar el item', life: 3000 });
    }
};
const totalHistorialPedido = computed(() => {
    return historialPlatos.value
        .filter((item) => item.state !== 'cancelado')
        .reduce((total, item) => {
            const precio = parseFloat(item.price) || 0;
            const cantidad = parseFloat(item.quantity) || 0;
            return total + precio * cantidad;
        }, 0)
        .toFixed(2);
});

// NUEVO: Computed para calcular el total del pedido actual
const totalPedidoActual = computed(() => {
    const totalPlatos = order.value.platos.reduce((total, plato) => total + parseFloat(plato.price) * plato.cantidad, 0);
    const totalProductos = order.value.productos.reduce((total, producto) => total + parseFloat(producto.price) * producto.cantidad, 0);
    return (totalPlatos + totalProductos).toFixed(2);
});
const actualizarestadomesa = async ()=>{

    
  // Después de la redirección, actualizar la orden con estado "finalizado"
            const updateOrderData = {
                state: 'finalizado',
            };

            // Intentar actualizar la orden
            await axios.put(`/orders/${order.value.idOrder}`, updateOrderData);
                 window.location.href = `/venta/pdf/${order.value.idOrder}`;
 goBackOrder();

}
const showFormularioRecibo = async () => { // Marca la función como async
    // Reiniciar los valores del formulario antes de mostrarlo
    recibo.value = {
        codigoCliente: '',
        tipoRecibo: '', // 'Factura' o 'Boleta'
        tipoPago: '', // 'Tarjeta', 'Transferencia', 'Efectivo', 'Yape', 'Plin'
        operationCode: '',
    };
    searchTerm.value = ''; // Limpiar el término de búsqueda
    selectedCliente.value = null; // Limpiar el cliente seleccionado
    showResults.value = false; // Ocultar los resultados de la búsqueda
    try {
        // Verificar si el idOrder es nulo
        if (!order.value.idOrder) {
            // Si el idOrder es nulo, mostrar mensaje
            toast.add({
                severity: 'warn',
                summary: 'Primero debe realizar un pedido',
                detail: 'Por favor, registre un pedido antes de generar el recibo.',
                life: 3000,
            });
            return; // Salir de la función si idOrder es nulo
        }

        // Verificar si ya existe una venta con el idOrder
        const response = await axios.get('/venta', {
            params: {
                idOrder: order.value.idOrder, // Enviamos idOrder para buscar
            },
        });

        if (response.data.data.length > 0) {
            // Si existe una venta con este idOrder, mostramos el mensaje de "En breve se mostrará el PDF"
            toast.add({
                severity: 'info',
                summary: 'En breve se mostrará el PDF',
                detail: 'Se está generando el recibo, por favor espera...',
                life: 3000,
            });

            actualizarestadomesa();
       
            
        } else {
            // Si no existe, mostramos el formulario para generar el recibo
            showReciboForm.value = true;
        }
    } catch (error) {
        console.error('Error al verificar la venta:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Hubo un error al procesar la solicitud.',
            life: 3000,
        });
    }
};

const generarRecibo = async () => { 
    const todosCompletadosOCancelados = historialPlatos.value.every((item) => 
        item.state === 'cancelado' || item.state === 'completado'
    );

    if (!todosCompletadosOCancelados) {
        // Si algún plato o producto no está cancelado o completado, mostrar un toast
        toast.add({
            severity: 'warn',
            summary: 'Órdenes no completadas',
            detail: 'Las órdenes aún no están completadas o canceladas.',
            life: 3000
        });
        return; // Detener la ejecución si no todos los platos están completos o cancelados
    }

    const alMenosUnCompletado = historialPlatos.value.some((item) => 
        item.state === 'completado'
    );

    if (!alMenosUnCompletado) {
        // Si no hay ningún plato completado, mostrar un toast
        toast.add({
            severity: 'warn',
            summary: 'No hay pedidos completados',
            detail: 'Debe haber al menos un pedido completado entre los cancelados.',
            life: 3000
        });
        return;
    }

    const totalHistorial = totalHistorialPedido.value;

    const generalData = {
        documentType: recibo.value.tipoRecibo,
        idSale: null
    };

    // Preparar los datos a enviar
    const data = {
        idCustomer: selectedCliente.value.id, // ID del cliente
        documentType: recibo.value.tipoRecibo, // Tipo de documento (Factura o Boleta)
        paymentType: recibo.value.tipoPago, // Tipo de pago (Tarjeta, Transferencia, etc.)
        operationCode: recibo.value.tipoPago !== 'Efectivo' ? recibo.value.operationCode : null, // Código de operación (si no es Efectivo)
        idOrder: order.value.idOrder,
        stateSunat: 'No Enviado',
    };
    console.log('Datos a enviar:', data);
    console.log('ID del pedido:', order.value.idOrder); // Imprimir el idOrder

    try {
        // Enviar la solicitud POST a la API para registrar la venta
        const response = await axios.post('/sale', data);

        // Verificar si la venta se registró correctamente
        if (response.data.state) {
            toast.add({ severity: 'success', summary: 'Recibo generado', detail: 'El recibo ha sido generado correctamente.', life: 3000 });
            console.log('ID de la venta registrada:', response.data.sale.id); // Imprimir ID de la venta generada
            console.log('Monto total del recibo: S/', totalHistorial); // Imprimir el total del recibo
            generalData.idSale = response.data.sale.id;

            // Después de obtener el ID de la venta, guardar en la tabla sales_orders
            const saleOrderData = {
                idSale: response.data.sale.id, // ID de la venta
                idOrder: order.value.idOrder, // ID del pedido
                subtotal: totalHistorial, // Subtotal calculado
            };
            // Enviar la solicitud POST a la API para registrar la relación entre sale y order
            const saleOrderResponse = await axios.post('/venta', saleOrderData);

            // Verificar si se guardó correctamente
            if (saleOrderResponse.data.state) {
                console.log('Pedido de venta registrado correctamente');
                                showReciboForm.value = false;
                              crearComprobante(generalData.idSale,generalData.documentType)
                                
                             
                                 goBackOrder();
            } else {
                console.error('Error al registrar el pedido de venta');
            }
        } else {
            // Aquí extraemos el mensaje de error específico y lo mostramos en el toast
            const errorMessage = response.data.message || 'Hubo un problema al generar el recibo.';
            toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
        }
    } catch (error) {
        console.error('Error al enviar los datos de la venta:', error);
        if (error.response && error.response.data) {
            // Si el error es generado por la respuesta del backend, lo mostramos
            const errorMessage = error.response.data.message || 'Hubo un error al procesar la solicitud.';
            toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
        } else {
            // Si hay un error genérico en la solicitud
            toast.add({ severity: 'error', summary: 'Error', detail: 'Hubo un error al procesar la solicitud.', life: 3000 });
        }
    }
};



const crearComprobante = async (idSale, prefix) => {
    // Validar que el prefijo sea uno de los valores permitidos (boleta o factura)
    if (prefix !== 'Boleta' && prefix !== 'Factura') {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'El tipo de comprobante debe ser "Boleta" o "Factura".',
            life: 3000
        });
        return;
    }

    try {
        // Datos a enviar
        const data = {
            prefix: prefix,  // El tipo de comprobante (Boleta o Factura)
        };

        // Enviar la solicitud POST a la API de Laravel para generar el comprobante
        const response = await axios.post(`/generate-invoice/${idSale}`, data);

        // Verificar si la respuesta es exitosa
        if (response.data.invoice) {


            console.log('Comprobante generado:', response.data.invoice);
 // Ahora enviar el idSale a la API /envio-sunat
            await axios.get(`/envio-sunat?idSale=${idSale}`)
                .then(() => {
                    console.log('idSale enviado a la API de SUNAT');
                       actualizarestadomesa();
                })
                .catch(error => {
                    console.error('Error al enviar idSale a SUNAT:', error);
              
                });
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Hubo un problema al generar el comprobante.',
                life: 3000
            });
        }
    } catch (error) {
        console.error('Error al crear el comprobante:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Hubo un error al procesar la solicitud.',
            life: 3000
        });
    }
};




// Estado para controlar la visibilidad del dialogo
const finalizarMesaDialog = ref(false);

// Función para finalizar la mesa
const finalizarMesa = async () => {
    try {
        // Actualizar la orden con estado "finalizado"
        const updateOrderData = {
            state: 'finalizado',
        };

        await axios.put(`/orders/${order.value.idOrder}`, updateOrderData);

     toast.add({
                severity: 'success',
                summary: 'Mesa cerrada',
                detail: 'La mesa ha sido cerrada y el historial de pedidos se ha perdido.',
                life: 3000
            });
            // Limpiar los datos de la mesa y cerrar la sesión de pedido
            goBackOrder();

            // Cerrar el dialogo
            finalizarMesaDialog.value = false;
       
    } catch (error) {
        console.error('Error al cerrar la mesa:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Hubo un error al procesar la solicitud.',
            life: 3000
        });
    }
};




</script>

<template>
    <Head title="Lista de mesas" />
    <div>
        <Toolbar v-if="showReciboToolbar" class="mb-4">
            <template #start>
                <Button label="Salir del Pedido" icon="pi pi-times" severity="secondary" class="mr-2" @click="goBackOrder" />
            </template>
            <template #end>
                <Button label="Generar Recibo" icon="pi pi-file" severity="primary" class="mr-2" @click="showFormularioRecibo" />
            </template>
        </Toolbar>

        <Toolbar v-if="!showReciboToolbar" class="mb-4">
            <template #start>
                <Button label="Volver" icon="pi pi-chevron-left" severity="secondary" class="mr-2" @click="goBack" />
                <Button 
                    label="Cerrar Caja" 
                    icon="pi pi-lock" 
                    severity="danger" 
                    @click="goToCerrarCaja" 
                />
            </template>

            <template #end>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 w-full sm:w-auto mt-4 sm:mt-0">
                    <!-- Dropdown for Floor -->
                    <Dropdown
                        v-model="selectedFloor"
                        :options="floors"
                        option-label="label"
                        option-value="value"
                        placeholder="Selecciona un Piso"
                        filter
                        filterBy="label"
                        class="w-full sm:w-60 lg:w-68"
                    />

                    <!-- Dropdown for Area -->
                    <Dropdown
                        v-model="selectedArea"
                        :options="areas"
                        option-label="label"
                        option-value="value"
                        placeholder="Selecciona una Area"
                        filter
                        filterBy="label"
                        class="w-full sm:w-60 lg:w-68"
                    />

                    <!-- Search Input -->
                    <InputText 
                        v-model="searchQuery" 
                        debounce="300" 
                        placeholder="Buscar por numero" 
                        class="w-full sm:w-48 lg:w-56" 
                    />
                </div>
            </template>
        </Toolbar>

        <!-- Section to display mesas -->
        <div v-if="!showOrderForm" class="grid grid-cols-12 gap-4">
            <div v-for="mesa in mesas" :key="mesa.id" class="col-span-12 lg:col-span-6 xl:col-span-3">
                <div class="card mb-0">
                    <div class="mb-4 flex justify-between">
                        <div>
                            <span class="mb-4 block font-medium text-muted-color">Mesa Nº</span>
                            <div class="text-xl font-medium text-surface-900 dark:text-surface-0">{{ mesa.tablenum }}</div>
                        </div>
                        <div
                            class="flex items-center justify-center bg-blue-100 rounded-border dark:bg-blue-400/10"
                            style="width: 2.5rem; height: 2.5rem"
                            @click="showOrderFormForMesa(mesa.id, mesa.tablenum)"
                        >
                            <!-- Condición para mostrar icono según el estado de la mesa -->
                            <i
                                v-if="mesa.order_status === 'disponible'"
                                class="pi pi-plus !text-xl text-blue-500"
                                style="cursor: pointer !important"
                            ></i>
                            <i v-else class="pi pi-clock !text-xl text-yellow-500" style="cursor: not-allowed !important"></i>
                        </div>
                    </div>
                    <div class="flex space-x-4">
                        <span class="text-muted-color">{{ mesa.floor_name }} - {{ mesa.area_name }} - {{ mesa.capacity }} personas</span>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div v-if="showReciboForm" class="card mb-4 p-3 sm:p-4">
                <h3 class="mb-3 sm:mb-4 text-lg sm:text-xl font-semibold">Generar Recibo</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <!-- Código de Cliente con búsqueda personalizada -->
                    <div class="col-span-1 sm:col-span-2">
                        <label for="codigoCliente" class="block mb-2 text-sm sm:text-base">Código de Cliente:</label>
                        <div class="relative w-full">
                            <!-- InputText para búsqueda de cliente -->
                            <InputText 
                                v-model="searchTerm" 
                                @input="handleSearch" 
                                placeholder="Buscar código de cliente..." 
                                class="w-full" 
                                maxlength="11" 
                            />

                            <!-- Resultados de búsqueda -->
                            <div
                                v-if="showResults && clientesOptions.length > 0"
                                class="absolute z-50 mt-2 max-h-60 w-full overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 p-2 shadow-lg"
                            >
                                <div
                                    v-for="cliente in clientesOptions"
                                    :key="cliente.id"
                                    @click="selectCliente(cliente)"
                                    class="cursor-pointer rounded p-2 hover:bg-primary-100 hover:text-primary-800"
                                >
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-gray-800 text-sm sm:text-base">{{ cliente.codigo }} - {{ cliente.name }} {{ cliente.lastname }}</span>
                                        <span class="text-xs sm:text-sm text-gray-600">{{ cliente.client_type_id === 1 ? 'Persona natural' : 'Persona juridica' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Mensaje de no encontrados -->
                            <div
                                v-if="showResults && searchTerm && clientesOptions.length === 0"
                                class="absolute z-50 mt-2 w-full rounded-lg border border-gray-200 bg-gray-50 p-4 text-center text-gray-600 shadow-lg"
                            >
                                <p class="text-sm sm:text-base mb-2">No se encontraron resultados.</p>
                                <AddCliente />
                            </div>
                        </div>
                    </div>

                    <!-- Tipo de Recibo (Automático según el cliente seleccionado) -->
                    <div class="col-span-1 sm:col-span-1">
                        <label for="tipoRecibo" class="block mb-2 text-sm sm:text-base">Tipo de Recibo:</label>
                        <Dropdown
                            v-model="recibo.tipoRecibo"
                            :options="['Factura', 'Boleta']"
                            placeholder="Seleccione tipo"
                            :disabled="true"
                            class="w-full"
                        />
                    </div>

                    <!-- Tipo de Pago -->
                    <div class="col-span-1 sm:col-span-1">
                        <label for="tipoPago" class="block mb-2 text-sm sm:text-base">Tipo de Pago:</label>
                        <Dropdown
                            v-model="recibo.tipoPago"
                            :options="['Tarjeta', 'Transferencia', 'Efectivo', 'Yape', 'Plin']"
                            placeholder="Seleccione tipo de pago"
                            class="w-full"
/>
                    </div>

                    <!-- Código de Operación (Solo si no es Efectivo) -->
                    <div v-if="recibo.tipoPago !== 'Efectivo'" class="col-span-1 sm:col-span-2">
                        <label for="operationCode" class="block mb-2 text-sm sm:text-base">Código de Operación:</label>
                        <InputText 
                            v-model="recibo.operationCode" 
                            placeholder="Ingrese el código de operación" 
                            class="w-full" 
                        />
                    </div>

                    <!-- Total -->
                    <div class="col-span-1 sm:col-span-2">
                        <label for="total" class="block mb-2 text-sm sm:text-base">Total:</label>
                        <InputText 
                            id="total" 
                            v-model="recibo.total" 
                            :value="totalHistorialPedido" 
                            disabled 
                            class="w-full" 
                        />
                    </div>
                </div>

                <div class="mt-4 flex flex-col sm:flex-row justify-end gap-2 sm:gap-0 sm:space-x-2">
                    <Button 
                        label="Generar" 
                        icon="pi pi-check" 
                        severity="success" 
                        class="w-full sm:w-auto order-2 sm:order-1" 
                        @click="generarRecibo" 
                    />
                    <Button 
                        label="Cancelar" 
                        icon="pi pi-times" 
                        severity="secondary" 
                        class="w-full sm:w-auto order-1 sm:order-2 mb-2 sm:mb-0" 
                        @click="showReciboForm = false" 
                    />
                </div>
            </div>

            <div v-if="showOrderForm" class="card flex flex-col p-3 sm:p-4">
                <div v-if="formulariopedido" class="grid flex-grow grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Columna 1: Mesa y platos disponibles -->
                    <div class="col-span-1 lg:border-r lg:pr-4 pb-4 lg:pb-0">
                        <h2 class="mb-3 sm:mb-4 text-base sm:text-lg font-semibold uppercase">
                            <strong>Mesa Nº {{ order.tablenum || 'Cargando...' }}</strong>
                        </h2>

                        <TabView>
                            <!-- TAB DE PLATILLOS -->
                            <TabPanel header="Platillos">
                                <InputText v-model="searchQueryPlato" placeholder="Buscar platillo..." class="mb-4 w-full" />

                                <div class="platos-disponibles max-h-96 overflow-y-auto">
                                    <div v-for="plato in filteredPlatos" :key="plato.id" class="plato-item mb-4 p-2 border-b">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="text-lg font-medium">{{ capitalizeFirstLetter(plato.name) }} - S/ {{ plato.price }} </span>
                                                <div class="text-xs text-gray-500">Stock: {{ plato.quantity }}</div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <Button label="Detalles" icon="pi pi-info-circle" class="p-button-text" @click="verInsumos(plato)" />
                                                <Button
                                                    label="Añadir"
                                                    icon="pi pi-plus"
                                                    class="p-button-success"
                                                    :disabled="isPlatoAgregado(plato)"
                                                    @click="agregarAlPedido(plato)"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="paginacion mt-4 flex items-center justify-center space-x-6">
                                    <span v-if="currentPage > 1" class="cursor-pointer text-blue-500" @click="currentPage--">Anterior</span>
                                    <span class="text-sm text-gray-600"> Página {{ currentPage }} de {{ totalPages }} </span>
                                    <span v-if="currentPage < totalPages" class="cursor-pointer text-blue-500" @click="currentPage++">Siguiente</span>
                                </div>
                            </TabPanel>

                            <!-- En el TabPanel de Productos -->
                            <TabPanel header="Productos">
                                <InputText v-model="searchQueryProduct" placeholder="Buscar producto..." class="mb-4 w-full" />

                                <div class="productos-disponibles max-h-96 overflow-y-auto">
                                    <div v-for="producto in filteredProducts" :key="producto.id" class="producto-item mb-4 p-2 border-b">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="text-lg font-medium">{{ capitalizeFirstLetter(producto.name) }} - S/ {{ producto.priceSale }} </span>
                                                <div class="text-xs text-gray-500">Stock: {{ producto.stock_quantity }}</div>
                                            </div>
                                            <div class="flex space-x-2">
                                                <Button
                                                    label="Añadir"
                                                    icon="pi pi-plus"
                                                    class="p-button-success"
                                                    :disabled="isProductoAgregado(producto)"
                                                    @click="agregarProductoAlPedido(producto)"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="paginacion mt-4 flex items-center justify-center space-x-6">
                                    <span v-if="currentPageProduct > 1" class="cursor-pointer text-blue-500" @click="currentPageProduct--">Anterior</span>
                                    <span class="text-sm text-gray-600"> Página {{ currentPageProduct }} de {{ totalPagesProduct }} </span>
                                    <span v-if="currentPageProduct < totalPagesProduct" class="cursor-pointer text-blue-500" @click="currentPageProduct++">Siguiente</span>
                                </div>
                            </TabPanel>
                        </TabView>
                    </div>

                    <!-- Columna 2: Pedido y total CORREGIDO -->
                    <div class="col-span-1 flex flex-col justify-between pl-4">
                        <h2 class="mb-4 text-lg font-semibold uppercase">
                            <strong>Pedido</strong>
                        </h2>
                        <div class="pedido flex-grow max-h-96 overflow-y-auto">
                            <!-- Mostrar platos del pedido -->
                            <div v-for="(plato, index) in order.platos" :key="'plato-' + plato.id" class="mb-2 p-2 border-b flex items-center justify-between">
                                <div>
                                    <span>{{ capitalizeFirstLetter(plato.name) }} - S/ {{ plato.price }}</span>
                                    <div class="text-xs text-gray-500">🍽️ Platillo</div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Button icon="pi pi-minus" class="p-button-text p-button-danger" @click="adjustQuantity(plato, -1, 'platillo')" />
                                    <span>{{ plato.cantidad }}</span>
                                    <Button icon="pi pi-plus" class="p-button-text p-button-success" @click="adjustQuantity(plato, 1, 'platillo')" />
                                    <span class="text-xs text-gray-500">de {{ plato.stock }}</span>
                                </div>
                                <Button icon="pi pi-trash" class="p-button-text p-button-danger ml-2" @click="eliminarDelPedido(index, 'platillo')" />
                            </div>

                            <!-- Mostrar productos del pedido -->
                            <div v-for="(producto, index) in order.productos" :key="'producto-' + producto.id" class="mb-2 p-2 border-b flex items-center justify-between">
                                <div>
                                    <span>{{ capitalizeFirstLetter(producto.name) }} - S/ {{ producto.price }}</span>
                                    <div class="text-xs text-gray-500">📦 Producto</div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <Button icon="pi pi-minus" class="p-button-text p-button-danger" @click="adjustQuantity(producto, -1, 'producto')" />
                                    <span>{{ producto.cantidad }}</span>
                                    <Button icon="pi pi-plus" class="p-button-text p-button-success" @click="adjustQuantity(producto, 1, 'producto')" />
                                    <span class="text-xs text-gray-500">de {{ producto.stock }}</span>
                                </div>
                                <Button icon="pi pi-trash" class="p-button-text p-button-danger ml-2" @click="eliminarDelPedido(index, 'producto')" />
                            </div>
                        </div>

                        <!-- Total y botones -->
                        <div class="total mt-4 font-semibold uppercase">
                            <strong>Total: </strong> S/ {{ totalPedidoActual }}
                        </div>

                        <!-- Botones debajo del total usando PrimeVue -->
                        <div class="mt-3 sm:mt-4 flex flex-col sm:flex-row gap-2 sm:gap-4">
                            <Button 
                                label="Realizar Pedido" 
                                icon="pi pi-check" 
                                severity="success" 
                                class="w-full sm:w-1/2" 
                                @click="realizarPedido" 
                            />
                            <Button 
                                label="Vaciar" 
                                icon="pi pi-times" 
                                severity="danger" 
                                class="w-full sm:w-1/2" 
                                @click="cancelOrder" 
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="showOrderHistorial" class="card p-3 sm:p-4">
                <DataTable
                    v-if="order.idOrder"
                    :value="historialPlatos"
                    :loading="historialLoading"
                    :lazy="true"
                    :paginator="true"
                    :rows="historialPagination.perPage"
                    :first="(historialPagination.page - 1) * historialPagination.perPage"
                    :totalRecords="historialPagination.total"
                    dataKey="id"
                    scrollable
                    :scrollHeight="scrollHeight"
                    class="mt-4 sm:mt-6"
                    @page="onHistorialPageChange"
                    responsiveLayout="scroll"
                >
                    <template #header>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-2">
                        <h4 class="m-0">HISTORIAL DE LA ORDEN</h4>
                            <div class="flex flex-col sm:flex-row gap-2 sm:gap-2 w-full sm:w-auto">
                                <IconField class="w-full sm:w-48 mb-2 sm:mb-0">
                                    <InputText 
                                        v-model="globalFilterValue" 
                                        @input="onGlobalSearch" 
                                        placeholder="Buscar..." 
                                        class="w-full" 
                                    />
                                </IconField>
                                <Dropdown
                                    v-model="selectedState"
                                    :options="stateOptions"
                                    option-label="label"
                                    option-value="value"
                                    placeholder="Selecciona un Estado"
                                    class="w-full sm:w-48 mb-2 sm:mb-0"
                                    @change="onStateChange"
                                />
                                <Button 
                                    icon="pi pi-refresh" 
                                    outlined 
                                    rounded 
                                    aria-label="Refresh" 
                                    class="w-full sm:w-auto"
                                    @click="loadHistorialPlatos" 
                                />
                            </div>
                        </div>
                    </template>

                    <!-- Columna para mostrar tipo de item -->
                    <Column header="Tipo" style="min-width: 6rem">
                        <template #body="{ data }">
                            <Tag 
                                :value="data.idDish ? 'Platillo' : 'Producto'" 
                                :severity="data.idDish ? 'info' : 'success'" 
                            />
                        </template>
                    </Column>

                    <Column field="name" header="Nombre" />
                    <Column field="productName" header="Producto" />
                    <Column field="quantity" header="Cantidad" />
                    <Column field="price" header="Precio Unit.">
                        <template #body="{ data }"> S/ {{ parseFloat(data.price).toFixed(2) }} </template>
                    </Column>
                    <Column header="Subtotal" style="min-width: 8rem">
                        <template #body="{ data }"> S/ {{ (parseFloat(data.quantity) * parseFloat(data.price)).toFixed(2) }} </template>
                    </Column>

                    <Column field="state" header="Estado" :style="columnStyle">
                        <template #body="{ data }">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2">
                                <Tag :value="data.state" :severity="getSeverity(data.state)" class="text-xs sm:text-sm" />
                                <Button
                                    v-if="data.state === 'pendiente'"
                                    label="Cancelar"
                                    icon="pi pi-times"
                                    severity="danger"
                                    class="p-button-text text-xs sm:text-sm mt-1 sm:mt-0 w-full sm:w-auto"
                                    @click="cancelDish(data.id)"
                                />
                            </div>
                        </template>
                    </Column>
                    <Column field="creacion" header="Fecha" :style="columnStyle">
                        <template #body="{ data }">
                            <span class="text-sm sm:text-base">{{ data.creacion }}</span>
                        </template>
                    </Column>
                </DataTable>
                <!-- Mostrar total del historial de la orden, excluyendo los cancelados -->
                <div class="p-4 text-right font-semibold">
                    <span>Total: </span>
                    <span>S/ {{ totalHistorialPedido }}</span>
                </div>
            </div>
            <!-- Dialog para ver los insumos del plato seleccionado -->
            <Dialog v-model:visible="showInsumosDialog" :header="'Insumos de ' + (selectedPlato?.name || 'Cargando...')" modal width="50%">
                <p class="mb-4 font-semibold">Lista de Insumos:</p>
                <ul class="list-inside list-disc">
                    <li v-for="insumo in selectedPlato?.insumos" :key="insumo.id" class="mb-2">
                        <span class="font-medium">{{ insumo.name }} - </span>
                        <span>{{ insumo.quantityUnitMeasure }} {{ insumo.unitMeasure }}</span>
                    </li>
                </ul>
                <template #footer>
                    <Button label="Cerrar" icon="pi pi-times" severity="secondary" @click="cancelarInsumos" />
                </template>
            </Dialog>
        </div>

        <!-- El Dialog que muestra el mensaje de advertencia -->
        <Dialog 
            v-model:visible="finalizarMesaDialog" 
            header="Confirmar Cierre de Mesa" 
            modal
            :style="{ width: '90vw', maxWidth: '40rem' }"
            :breakpoints="{ '960px': '75vw', '641px': '90vw' }"
        >
            <p class="mb-4 text-sm sm:text-base">¿Estás seguro de que deseas cerrar esta mesa? La mesa volverá a estar disponible, y se perderá el historial de pedidos.</p>
            <template #footer>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-0 sm:space-x-2 w-full">
                    <Button 
                        label="Cancelar" 
                        icon="pi pi-times" 
                        severity="secondary" 
                        class="w-full sm:w-auto" 
                        @click="finalizarMesaDialog = false" 
                    />
                    <Button 
                        label="Confirmar" 
                        icon="pi pi-check" 
                        severity="danger" 
                        class="w-full sm:w-auto" 
                        @click="finalizarMesa" 
                    />
                </div>
            </template>
        </Dialog>
    </div>
</template>
