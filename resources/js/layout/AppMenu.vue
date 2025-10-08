<script lang="ts" setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppMenuItem from './AppMenuItem.vue';

// Tipos para usuario y permisos
interface User {
    permissions?: string[];
}

interface Auth {
    user?: User;
}

// Obtenemos los props de la página
const page = usePage<{ auth: Auth }>();

// Computed para permisos del usuario
const permissions = computed<string[]>(() => page.props.auth.user?.permissions ?? []);

// Función para verificar permisos
const hasPermission = (perm: string): boolean => permissions.value.includes(perm);

// Tipos del menú
interface MenuItem {
    label: string;
    icon?: string;
    to?: string;
    items?: MenuItem[];
}
// Computed para el modelo del menú
const model = computed<MenuItem[]>(() => [
    {
        label: 'Home',
        items: [
            { label: 'Dashboard', icon: 'pi pi-fw pi-home', to: '/dashboard' }
        ]
    },
    {
        label: 'Gestión de Productos',
        items: [
            hasPermission('ver productos') && { label: 'Productos', icon: 'pi pi-fw pi-box', to: '/productos' },
            hasPermission('ver categorias') && { label: 'Categorías', icon: 'pi pi-fw pi-tags', to: '/categorias' },
            hasPermission('ver almacenes') && { label: 'Almacenes', icon: 'pi pi-fw pi-building', to: '/almacenes' },
            hasPermission('ver proveedores') && { label: 'Proveedores', icon: 'pi pi-fw pi-truck', to: '/proveedores' },
            hasPermission('ver insumos') && { label: 'Insumos', icon: 'pi pi-fw pi-box', to: '/insumos' },
        ].filter(Boolean) as MenuItem[],
    },
    {
        label: 'Gestión de Cocina',
        items: [
            hasPermission('ver dishes') && { label: 'Platos', icon: 'pi pi-fw pi-apple', to: '/platos' },
        ].filter(Boolean) as MenuItem[],
    },
    {
        label: 'Gestión de Comercio',
        items: [
            hasPermission('ver mesas') && { label: 'Ventas', icon: 'pi pi-fw pi-shop', to: '/ventas' },
            hasPermission('ver cajas') && { label: 'Cajas', icon: 'pi pi-fw pi-cart-plus', to: '/cajas' },
            hasPermission('ver ordenes') && { label: 'Ordenes', icon: 'pi pi-fw pi-list', to: '/ordenes' },
            hasPermission('ver mesas') && { label: 'Lista de Mesas', icon: 'pi pi-fw pi-table', to: '/ordenes/mesas' },
        ].filter(Boolean) as MenuItem[],
        
    },
    {
      label: 'Gestión de Clientes',
      items: [
       hasPermission('ver proveedores') && { label: 'Proveedores', icon: 'pi pi-fw pi-truck', to: '/proveedores' },
      hasPermission('ver almacenes') && { label: 'Almacenes', icon: 'pi pi-fw pi-building', to: '/almacenes' },
      (hasPermission('ver clientes') || hasPermission('ver tipos_clientes')) && {
        label: 'Cliente',
        icon: 'pi pi-fw pi-users',
        items: [
          hasPermission('ver clientes') && { label: 'Clientes', icon: 'pi pi-fw pi-users', to: '/clientes' },
          //hasPermission('ver tipos_clientes') && { label: 'Tipo de Clientes', icon: 'pi pi-fw pi-id-card', to: '/tipo_clientes' },
        ].filter(Boolean) as MenuItem[],
      },
(hasPermission('ver movimientos') || hasPermission('ver movimientos')) && {
        label: 'Movimientos',
        icon: 'pi pi-fw pi-box layout-menuitem-icon',
        items: [
          hasPermission('ver facturas insumos') && { label: 'Compras de Insumos', icon: 'pi pi-fw pi-users', to: '/insumos/movimientos' },
                    hasPermission('ver kardex insumos') && { label: 'Kardex de Insumos', icon: 'pi pi-fw pi-users', to: '/insumos/kardex' },

        ].filter(Boolean) as MenuItem[],
      },
      ].filter(Boolean) as MenuItem[],
    },
    {
        label: 'Gestión de Infraestructura',
        items: [
            hasPermission('ver pisos') && { label: 'Pisos', icon: 'pi pi-fw pi-list', to: '/pisos' },
            hasPermission('ver areas') && { label: 'Areas', icon: 'pi pi-fw pi-map', to: '/areas' },
            hasPermission('ver mesas') && { label: 'Mesas', icon: 'pi pi-fw pi-table', to: '/mesas' },
        ].filter(Boolean) as MenuItem[],
    },
    {
  label: 'Usuarios y Seguridad',
  items: [
    hasPermission('ver usuarios') && { label: 'Gestión de Usuarios', icon: 'pi pi-fw pi-user-edit', to: '/usuario' },
    hasPermission('ver roles') && { label: 'Roles', icon: 'pi pi-fw pi-shield', to: '/roles' },
    (hasPermission('ver empleados') || hasPermission('ver tipos_empleados')) && {
      label: 'Empleado',
      icon: 'pi pi-fw pi-id-card',
      items: [
        hasPermission('ver empleados') && { label: 'Empleados', icon: 'pi pi-fw pi-id-card', to: '/empleados' },
        hasPermission('ver tipos_empleados') && { label: 'Tipo de empleados', icon: 'pi pi-fw pi-sitemap', to: '/tipo_empleados' },
      ].filter(Boolean) as MenuItem[],
    },
    hasPermission('ver presentaciones') && { label: 'Presentaciones', icon: 'pi pi-fw pi-check-square', to: '/presentaciones' },
  ].filter(Boolean) as MenuItem[],
},
{
  label: 'Reportes y Finanzas',
  items: [
    hasPermission('ver reporte_cajas') && { label: 'Reporte de Caja', icon: 'pi pi-fw pi-file', to: '/reporte-cajas' },
    // ...otros reportes/finanzas
  ].filter(Boolean) as MenuItem[],
}
].filter(section => section.items && section.items.length > 0) as MenuItem[]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="i">
            <app-menu-item :item="item" :index="i" />
        </template>
    </ul>
</template>

<style scoped lang="scss">
/* Estilos personalizados opcionales */
</style>
