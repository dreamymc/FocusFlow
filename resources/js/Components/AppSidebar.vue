<script setup>
import { ref, computed } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { usePermissions } from '@/Composables/usePermissions';
import ColorIcon from '@/Components/ColorIcon.vue';

const page = usePage();
const { role, isAdmin, can } = usePermissions();

// Simple router helper to support template routes without Ziggy dependency
const route = (name) => {
  const routes = {
    'workspaces.switch': '/workspaces/switch',
    'logout': '/logout'
  };
  return routes[name] || name;
};

const workspaces = computed(() => page.props.workspaces || []);
const currentWorkspace = computed(() => page.props.currentWorkspace);
const user = computed(() => page.props.auth.user);

const isWorkspaceDropdownOpen = ref(false);

const toggleWorkspaceDropdown = () => {
  isWorkspaceDropdownOpen.value = !isWorkspaceDropdownOpen.value;
};

const switchWorkspace = (workspaceId) => {
  isWorkspaceDropdownOpen.value = false;
  router.post(route('workspaces.switch'), {
    workspace_id: workspaceId
  }, {
    preserveState: false,
    preserveScroll: true
  });
};

const logout = () => {
  router.post(route('logout'));
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

const isActive = (path) => {
  return page.url === path || page.url.startsWith(path + '/');
};
</script>

<template>
  <aside class="w-[256px] h-screen bg-surface-sidebar border-r border-border fixed left-0 top-0 flex flex-col justify-between z-30 select-none">
    <div class="flex flex-col flex-1 overflow-y-auto">
      <!-- Logo Area -->
      <div class="h-14 border-b border-border flex items-center px-6">
        <Link href="/dashboard" class="flex items-center gap-2">
          <span class="font-display font-extrabold text-xl tracking-tight">
            <span class="text-primary font-bold">Focus</span><span class="text-text-secondary font-medium">Flow</span>
          </span>
        </Link>
      </div>

      <!-- Workspace Switcher -->
      <div v-if="currentWorkspace" class="px-4 py-4 border-b border-border relative">
        <button
          @click="toggleWorkspaceDropdown"
          class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-primary-light/50 border border-transparent hover:border-border transition-all duration-200 cursor-pointer"
        >
          <div class="flex items-center gap-3 min-w-0">
            <ColorIcon :name="currentWorkspace.name" :id="currentWorkspace.id" size="md" />
            <span class="font-sans font-semibold text-sm text-text truncate text-left">{{ currentWorkspace.name }}</span>
          </div>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-text-secondary shrink-0">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
          </svg>
        </button>

        <!-- Dropdown Menu -->
        <div
          v-if="isWorkspaceDropdownOpen"
          class="absolute left-4 right-4 mt-1 bg-surface border border-border rounded-lg shadow-lg py-1.5 z-40 max-h-60 overflow-y-auto"
        >
          <div class="px-3 py-1 text-[11px] font-bold text-text-muted tracking-wider uppercase">Switch Workspace</div>
          <button
            v-for="workspace in workspaces"
            :key="workspace.id"
            @click="switchWorkspace(workspace.id)"
            class="w-full flex items-center gap-3 px-3 py-2 text-left hover:bg-primary-light transition-colors text-sm font-medium cursor-pointer"
            :class="{ 'text-primary bg-primary-light/40 font-semibold': workspace.id === currentWorkspace.id }"
          >
            <ColorIcon :name="workspace.name" :id="workspace.id" size="sm" />
            <span class="truncate">{{ workspace.name }}</span>
          </button>

          <div class="border-t border-border mt-1.5 pt-1.5">
            <Link
              href="/workspaces/create"
              class="w-full flex items-center gap-3 px-3 py-2 text-left hover:bg-primary-light transition-colors text-sm font-medium text-primary cursor-pointer"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
              <span>Create Workspace</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- Nav Section: Projects -->
      <div v-if="currentWorkspace" class="px-4 py-4 flex flex-col gap-1">
        <div class="flex items-center justify-between px-2 mb-1">
          <span class="text-[11px] font-bold text-text-muted tracking-wider uppercase font-sans">Projects</span>
          <Link
            v-if="can('manage-projects')"
            :href="`/workspaces/${currentWorkspace.id}/projects/create`"
            class="p-0.5 rounded hover:bg-primary-light text-text-secondary hover:text-primary transition-colors cursor-pointer"
            title="New Project"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
          </Link>
        </div>

        <!-- Project Links -->
        <div class="flex flex-col gap-0.5">
          <Link
            v-for="project in currentWorkspace.projects"
            :key="project.id"
            :href="`/workspaces/${currentWorkspace.id}/projects/${project.id}`"
            class="flex items-center gap-3 px-2 py-1.5 rounded-md text-sm font-medium text-text-secondary hover:text-text hover:bg-primary-light/50 transition-colors cursor-pointer"
            :class="{ 'text-primary bg-primary-light/50 font-semibold': isActive(`/workspaces/${currentWorkspace.id}/projects/${project.id}`) }"
          >
            <ColorIcon :name="project.name" :id="project.id" size="sm" />
            <span class="truncate">{{ project.name }}</span>
          </Link>
          <div v-if="!currentWorkspace.projects || currentWorkspace.projects.length === 0" class="text-xs text-text-muted px-2 py-1 italic">
            No projects yet
          </div>
        </div>
      </div>

      <!-- Nav Section: Workspace Settings -->
      <div v-if="currentWorkspace" class="px-4 py-2 border-t border-border/60 flex flex-col gap-1">
        <div class="px-2 mb-1">
          <span class="text-[11px] font-bold text-text-muted tracking-wider uppercase font-sans">Workspace</span>
        </div>
        <div class="flex flex-col gap-0.5">
          <Link
            v-if="isAdmin"
            :href="`/workspaces/${currentWorkspace.id}/settings#members`"
            class="flex items-center gap-3 px-3 py-1.5 rounded-md text-sm font-medium text-text-secondary hover:text-text hover:bg-primary-light/50 transition-colors cursor-pointer"
            :class="{ 'text-primary bg-primary-light/50 font-semibold': page.url.endsWith('/settings#members') }"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-text-secondary">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.97 5.97 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
            </svg>
            <span>Members</span>
          </Link>
          
          <Link
            v-if="isAdmin"
            :href="`/workspaces/${currentWorkspace.id}/settings`"
            class="flex items-center gap-3 px-3 py-1.5 rounded-md text-sm font-medium text-text-secondary hover:text-text hover:bg-primary-light/50 transition-colors cursor-pointer"
            :class="{ 'text-primary bg-primary-light/50 font-semibold': page.url.endsWith('/settings') }"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-text-secondary">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.43l-1.003.828c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.43l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            <span>Settings</span>
          </Link>

          <Link
            v-if="isAdmin"
            :href="`/billing`"
            class="flex items-center gap-3 px-3 py-1.5 rounded-md text-sm font-medium text-text-secondary hover:text-text hover:bg-primary-light/50 transition-colors cursor-pointer"
            :class="{ 'text-primary bg-primary-light/50 font-semibold': page.url === '/billing' }"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-text-secondary">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.5 8.25h19V6A2.25 2.25 0 0 0 20.25 3.75H3.75A2.25 2.25 0 0 0 1.5 6v2.25ZM21.5 10.25h-19v6.5A2.25 2.25 0 0 0 4.75 19h14.5a2.25 2.25 0 0 0 2.25-2.25v-6.5ZM17.25 13.5h.008v.008h-.008V13.5Zm-3 0h.008v.008h-.008V13.5Z" />
            </svg>
            <span>Billing</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- User Area -->
    <div class="p-4 border-t border-border flex flex-col gap-2 bg-surface/50">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-full bg-primary text-white font-semibold flex items-center justify-center text-xs tracking-wider border border-border shrink-0 select-none">
          {{ userInitials }}
        </div>
        <div class="flex flex-col min-w-0">
          <span class="text-sm font-semibold text-text truncate leading-tight">{{ user?.name }}</span>
          <span class="text-xs text-text-secondary font-medium truncate uppercase tracking-tight">{{ role || 'No Role' }}</span>
        </div>
      </div>
      <button
        @click="logout"
        class="w-full flex items-center justify-center gap-2 mt-2 px-3 py-1.5 border border-border rounded-lg text-xs font-semibold text-text-secondary hover:text-red-600 hover:bg-red-50 hover:border-red-100 transition-all duration-150 cursor-pointer"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
        </svg>
        <span>Sign Out</span>
      </button>
    </div>
  </aside>
</template>
