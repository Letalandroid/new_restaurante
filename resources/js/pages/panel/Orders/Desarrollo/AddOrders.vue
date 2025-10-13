<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="Nueva Orden" icon="pi pi-plus" severity="secondary" class="mr-2" @click="verMesas" />
        </template>
        <template #end> </template>
    </Toolbar>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/core';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';

const verMesas = (): void => {
    const url = `/ordenes/mesas/`;
    router.visit(url);
};
</script>
