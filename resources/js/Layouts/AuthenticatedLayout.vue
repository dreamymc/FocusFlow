<script setup>
import { Head } from '@inertiajs/vue3';
import AppSidebar from '@/Components/AppSidebar.vue';
import AppNavbar from '@/Components/AppNavbar.vue';
import { Toaster } from '@/Components/ui/sonner';
import { useSidebar } from '@/Composables/useSidebar';

defineProps({
  title: {
    type: String,
    default: ''
  }
});

const { isOpen, close } = useSidebar();
</script>

<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex">
    <Head :title="title" />
    
    <!-- Sidebar -->
    <AppSidebar />

    <!-- Mobile Sidebar Backdrop Overlay -->
    <div 
      v-if="isOpen" 
      @click="close" 
      class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-20 md:hidden transition-opacity duration-200"
    ></div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col ml-0 md:ml-64 min-w-0">
      <!-- Top Navbar -->
      <AppNavbar :title="title" />

      <!-- Main Slot Content -->
      <main class="flex-1 p-6 md:p-8 max-w-7xl w-full mx-auto overflow-y-auto">
        <Transition name="page" mode="out-in">
          <slot :key="$page.url" />
        </Transition>
      </main>
    </div>
    <Toaster />
  </div>
</template>

<style>
.page-enter-active,
.page-leave-active {
  transition: opacity 150ms ease, transform 150ms ease;
}

.page-enter-from {
  opacity: 0;
  transform: translateY(4px);
}

.page-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
