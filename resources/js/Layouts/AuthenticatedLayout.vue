<script setup>
import { Head } from '@inertiajs/vue3';
import AppSidebar from '@/Components/AppSidebar.vue';
import AppNavbar from '@/Components/AppNavbar.vue';

defineProps({
  title: {
    type: String,
    default: ''
  }
});
</script>

<template>
  <div class="min-h-screen bg-surface-2 flex">
    <Head :title="title" />
    
    <!-- Sidebar -->
    <AppSidebar />

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col ml-[256px] min-w-0">
      <!-- Top Navbar -->
      <AppNavbar :title="title" />

      <!-- Main Slot Content -->
      <main class="flex-1 p-6 md:p-8 max-w-7xl w-full mx-auto overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>
