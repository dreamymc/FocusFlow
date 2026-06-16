<script setup>
import { computed } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import NotificationBell from '@/Components/NotificationBell.vue';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';

import { useSidebar } from '@/Composables/useSidebar';

defineProps({
  title: {
    type: String,
    default: ''
  }
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const currentWorkspace = computed(() => page.props.currentWorkspace);
const { toggle } = useSidebar();

// Simple router helper to support template routes without Ziggy dependency
const route = (name) => {
  const routes = {
    'logout': '/logout'
  };
  return routes[name] || name;
};

const userInitials = computed(() => {
  if (!user.value?.name) return 'U';
  return user.value.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();
});

const logout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <header class="h-14 bg-slate-900/80 backdrop-blur-md border-b border-slate-800/80 flex items-center justify-between px-4 md:px-6 sticky top-0 z-20 select-none">
    <!-- Left Section: Hamburger + Title -->
    <div class="flex items-center gap-3">
      <!-- Hamburger Menu (mobile only) -->
      <button
        @click="toggle"
        class="md:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800 transition-colors cursor-pointer focus:outline-none"
        aria-label="Toggle Sidebar"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
      </button>

      <h1 class="font-display font-semibold text-base md:text-lg text-slate-100 leading-tight">{{ title }}</h1>
    </div>

    <!-- Right Controls -->
    <div class="flex items-center gap-4">
      <!-- Notification Bell -->
      <NotificationBell
        v-if="currentWorkspace"
        :workspace-id="currentWorkspace.id"
      />

      <!-- User Dropdown (shadcn) -->
      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <button class="w-8 h-8 rounded-full bg-gradient-to-tr from-primary to-indigo-400 text-white font-semibold flex items-center justify-center text-[11px] tracking-wider border border-white shadow-sm shrink-0 select-none cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 focus-visible:ring-offset-2 hover:shadow-md hover:scale-105 active:scale-95 transition-all duration-200">
            {{ userInitials }}
          </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-56 mt-2 bg-slate-900 border border-slate-800 rounded-xl shadow-xl p-1.5 z-50">
          <DropdownMenuLabel class="px-3 py-2.5">
            <div class="flex flex-col space-y-0.5">
              <p class="text-sm font-semibold text-slate-100 leading-none">{{ user?.name }}</p>
              <p class="text-xs text-slate-400 leading-none mt-1 truncate">{{ user?.email }}</p>
            </div>
          </DropdownMenuLabel>
          <DropdownMenuSeparator class="bg-slate-800 my-1" />
          <DropdownMenuItem @select="logout" class="flex items-center w-full px-3 py-2.5 text-xs font-semibold text-red-400 rounded-lg hover:bg-slate-800 hover:text-red-500 focus:bg-slate-800 focus:text-red-500 transition-colors duration-150 cursor-pointer outline-none select-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
            </svg>
            Sign Out
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  </header>
</template>
