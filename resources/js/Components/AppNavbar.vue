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

defineProps({
  title: {
    type: String,
    default: ''
  }
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const currentWorkspace = computed(() => page.props.currentWorkspace);

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
  <header class="h-14 bg-surface border-b border-border flex items-center justify-between px-6 sticky top-0 z-20 select-none">
    <!-- Page Title -->
    <div>
      <h1 class="font-display font-semibold text-lg text-text leading-tight">{{ title }}</h1>
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
          <button class="w-8 h-8 rounded-full bg-primary text-white font-semibold flex items-center justify-center text-xs tracking-wider border border-border shrink-0 select-none cursor-pointer focus:outline-none hover:opacity-90 transition-opacity">
            {{ userInitials }}
          </button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-56 mt-1.5">
          <DropdownMenuLabel>
            <div class="flex flex-col space-y-1">
              <p class="text-sm font-semibold text-text leading-none">{{ user?.name }}</p>
              <p class="text-xs text-text-secondary leading-none">{{ user?.email }}</p>
            </div>
          </DropdownMenuLabel>
          <DropdownMenuSeparator />
          <DropdownMenuItem @select="logout" class="text-red-600 focus:text-red-600 focus:bg-red-50 cursor-pointer">
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
