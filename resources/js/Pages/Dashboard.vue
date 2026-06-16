<script setup>
import { computed, ref, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const isLoading = ref(true);
onMounted(() => {
  setTimeout(() => {
    isLoading.value = false;
  }, 450);
});

const props = defineProps({
  stats: {
    type: Object,
    default: null
  },
  recentTasks: {
    type: Array,
    default: () => []
  }
});

const page = usePage();
const currentWorkspace = computed(() => page.props.currentWorkspace);

// Status Badge Styling Helper
const getStatusBadgeClass = (status) => {
  return {
    'backlog': 'bg-surface-3 text-text-secondary border-border',
    'in_progress': 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20 dark:border-blue-500/30',
    'in_review': 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20 dark:border-amber-500/30',
    'done': 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20 dark:border-emerald-500/30'
  }[status] || 'bg-surface-3 text-text-secondary border-border';
};
</script>

<template>
  <AuthenticatedLayout title="Dashboard">
    <!-- Loading Skeletons -->
    <div v-if="isLoading" class="space-y-8 animate-pulse">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Card: Total Tasks Skeleton (spans 2) -->
        <div class="md:col-span-2 bg-surface border border-border rounded-2xl p-8 flex items-center justify-between shadow-sm">
          <div class="space-y-3 flex-1">
            <div class="h-3 bg-surface-3 rounded w-1/4"></div>
            <div class="h-10 bg-surface-3 rounded w-1/5"></div>
          </div>
          <div class="w-14 h-14 bg-surface-3 rounded-xl"></div>
        </div>
        <!-- Card: In Progress Skeleton (spans 1) -->
        <div class="md:col-span-1 bg-surface border border-border rounded-2xl p-6 flex items-center justify-between shadow-sm">
          <div class="space-y-2 flex-1">
            <div class="h-3 bg-surface-3 rounded w-1/3"></div>
            <div class="h-8 bg-surface-3 rounded w-1/4"></div>
          </div>
          <div class="w-12 h-12 bg-surface-3 rounded-lg"></div>
        </div>
        <!-- Card: Completed Today Skeleton (spans 1) -->
        <div class="md:col-span-1 bg-surface border border-border rounded-2xl p-6 flex items-center justify-between shadow-sm">
          <div class="space-y-2 flex-1">
            <div class="h-3 bg-surface-3 rounded w-1/3"></div>
            <div class="h-8 bg-surface-3 rounded w-1/4"></div>
          </div>
          <div class="w-12 h-12 bg-surface-3 rounded-lg"></div>
        </div>
      </div>
      
      <!-- Recent Activity Skeleton -->
      <div class="bg-surface border border-border rounded-2xl p-8 space-y-6 shadow-sm">
        <div class="space-y-2 mb-4">
          <div class="h-4 bg-surface-3 rounded w-1/4"></div>
          <div class="h-3 bg-surface-3 rounded w-1/3"></div>
        </div>
        <div class="relative pl-6 ml-4 border-l-2 border-border space-y-6 py-2">
          <div v-for="i in 4" :key="i" class="relative flex items-center justify-between py-1">
            <div class="absolute -left-[33px] w-4 h-4 rounded-full bg-surface border-2 border-border flex items-center justify-center"></div>
            <div class="space-y-2 flex-1">
              <div class="h-4 bg-surface-3 rounded w-1/3"></div>
              <div class="h-3 bg-surface-3 rounded w-1/6"></div>
            </div>
            <div class="w-16 h-6 bg-surface-3 rounded-full"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State: No Workspace -->
    <div v-else-if="!currentWorkspace" class="flex flex-col items-center justify-center min-h-[65vh] bg-surface border border-border rounded-2xl p-12 text-center shadow-lg max-w-2xl mx-auto my-4 transition-all duration-300">
      <!-- Custom elegant SVG line-art -->
      <div class="relative mb-8 group">
        <!-- Soft background glow -->
        <div class="absolute inset-0 bg-primary/5 blur-3xl rounded-full scale-150"></div>
        <svg class="relative w-48 h-48 mx-auto text-primary transition-transform duration-700 group-hover:scale-105" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
          <!-- Outer orbital path -->
          <circle cx="100" cy="100" r="85" stroke="url(#gradient-geometric)" stroke-width="1.5" stroke-dasharray="6 6" class="opacity-30" />
          <!-- Inner orbital path -->
          <circle cx="100" cy="100" r="65" stroke="url(#gradient-geometric)" stroke-width="1" class="opacity-40" />
          
          <!-- Central dynamic workspaces structure -->
          <rect x="75" y="75" width="50" height="50" rx="14" stroke="currentColor" stroke-width="2" class="opacity-95 shadow-sm" />
          
          <!-- Connected nodes -->
          <rect x="40" y="45" width="36" height="36" rx="10" stroke="currentColor" stroke-width="1.5" stroke-dasharray="3 3" class="opacity-60" />
          <rect x="124" y="119" width="36" height="36" rx="10" stroke="currentColor" stroke-width="1.5" stroke-dasharray="3 3" class="opacity-60" />
          
          <!-- Flow lines -->
          <path d="M75 100H58C53 100 49 96 49 91V81" stroke="url(#gradient-geometric)" stroke-width="1.5" stroke-linecap="round" class="opacity-70" />
          <path d="M125 100H142C147 100 151 104 151 109V119" stroke="url(#gradient-geometric)" stroke-width="1.5" stroke-linecap="round" class="opacity-70" />
          
          <!-- Colored Accent Orbs -->
          <circle cx="49" cy="81" r="4" fill="#8B5CF6" class="animate-pulse" />
          <circle cx="151" cy="109" r="4" fill="#8B5CF6" class="animate-pulse" />
          <circle cx="100" cy="100" r="3" fill="#6366F1" />
          
          <!-- Tech grid ticks -->
          <line x1="100" y1="15" x2="100" y2="25" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="opacity-40" />
          <line x1="100" y1="175" x2="100" y2="185" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="opacity-40" />
          <line x1="15" y1="100" x2="25" y2="100" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="opacity-40" />
          <line x1="175" y1="100" x2="185" y2="100" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="opacity-40" />

          <defs>
            <linearGradient id="gradient-geometric" x1="15" y1="15" x2="185" y2="185" gradientUnits="userSpaceOnUse">
              <stop stop-color="#6366F1" />
              <stop offset="1" stop-color="#8B5CF6" />
            </linearGradient>
          </defs>
        </svg>
      </div>
      
      <h2 class="font-display-title text-text mb-3 tracking-tight">Create your first Workspace</h2>
      <p class="text-text-secondary text-sm max-w-md mb-8 leading-relaxed">
        Workspaces are the collaborative hubs of FocusFlow. Team members, tasks, and project timelines all live here. Create one to begin your productivity flow.
      </p>
      
      <Link
        href="/workspaces/create"
        class="shimmer-btn inline-flex items-center justify-center bg-primary hover:bg-primary-dark text-white font-semibold px-8 py-3 rounded-lg shadow-lg shadow-primary/25 hover:shadow-primary/30 active:scale-95 transition-all cursor-pointer text-sm tracking-wide gap-2 border border-primary-dark"
      >
        <span>Create Workspace</span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
      </Link>
    </div>

    <!-- Main Dashboard Content -->
    <div v-else class="space-y-8 animate-fade-in">
      <!-- Metric Cards Row -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Card: Total Tasks -->
        <div class="md:col-span-2 bg-gradient-to-br from-primary/10 to-primary-light/30 border border-primary/20 dark:border-primary/30 rounded-2xl p-8 shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between group">
          <div class="space-y-2">
            <span class="label-uppercase-tracked block text-text-secondary">Total Tasks</span>
            <p class="text-4xl font-display font-extrabold text-text leading-none transition-transform duration-300 group-hover:translate-x-1">{{ stats?.totalTasks ?? 0 }}</p>
          </div>
          <div class="w-14 h-14 bg-primary text-white rounded-xl flex items-center justify-center shadow-md shadow-primary/20 transition-transform duration-300 group-hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.375c1.08 0 1.958-.87 1.958-1.958V13.5m-6.75-2.25H12a1.875 1.875 0 0 0 0-3.75H9v3.75Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H3.75A1.125 1.125 0 0 0 2.625 3.375v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V14.25Z" />
            </svg>
          </div>
        </div>

        <!-- Card: In Progress -->
        <div class="md:col-span-1 bg-surface border border-border rounded-2xl p-6 shadow-sm hover:shadow-md hover:border-blue-500/30 transition-all duration-300 flex items-center justify-between group">
          <div class="space-y-2">
            <span class="label-uppercase-tracked block text-text-secondary">In Progress</span>
            <p class="text-3xl font-display font-extrabold text-text leading-none transition-transform duration-300 group-hover:translate-x-1">{{ stats?.activeTasks ?? 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-surface-2 border border-border rounded-xl flex items-center justify-center text-blue-500 dark:text-blue-400 shadow-sm transition-transform duration-300 group-hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
          </div>
        </div>

        <!-- Card: Completed Today -->
        <div class="md:col-span-1 bg-surface border border-border rounded-2xl p-6 shadow-sm hover:shadow-md hover:border-emerald-500/30 transition-all duration-300 flex items-center justify-between group">
          <div class="space-y-2">
            <span class="label-uppercase-tracked block text-text-secondary">Completed Today</span>
            <p class="text-3xl font-display font-extrabold text-text leading-none transition-transform duration-300 group-hover:translate-x-1">{{ stats?.completedToday ?? 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-surface-2 border border-border rounded-xl flex items-center justify-center text-emerald-500 dark:text-emerald-400 shadow-sm transition-transform duration-300 group-hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Recent Activity Section -->
      <div class="bg-surface border border-border rounded-2xl p-8 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
          <div class="space-y-1">
            <h2 class="text-lg font-display font-bold text-text">Recent Task Activity</h2>
            <p class="text-xs text-text-secondary font-sans">Keep track of the latest updates and assignments across your projects.</p>
          </div>
        </div>
        
        <div v-if="recentTasks.length === 0" class="flex flex-col items-center justify-center py-16 text-center bg-surface-2/40 border border-dashed border-border rounded-xl">
          <div class="w-12 h-12 bg-surface rounded-xl flex items-center justify-center text-text-muted mb-4 border border-border">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.375c1.08 0 1.958-.87 1.958-1.958V13.5m-6.75-2.25H12a1.875 1.875 0 0 0 0-3.75H9v3.75Z" />
            </svg>
          </div>
          <h3 class="text-sm font-semibold text-text mb-1">No tasks assigned</h3>
          <p class="text-xs text-text-secondary max-w-xs leading-normal">You have no tasks yet. Ask your team to assign you some or start by creating a task.</p>
        </div>
        
        <div v-else class="relative pl-6 ml-4 border-l-2 border-border space-y-6 py-2">
          <div
            v-for="task in recentTasks"
            :key="task.id"
            class="relative flex items-center justify-between gap-4 group transition-all duration-200"
          >
            <!-- Timeline Bullet -->
            <div 
              class="absolute -left-[33px] w-4 h-4 rounded-full bg-surface border-2 flex items-center justify-center shadow-sm transition-transform duration-300 group-hover:scale-110 z-10"
              :class="{
                'border-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.2)]': task.status === 'done',
                'border-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.2)]': task.status === 'in_progress',
                'border-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.2)]': task.status === 'in_review',
                'border-text-muted': task.status === 'backlog'
              }"
            >
              <div 
                class="w-1.5 h-1.5 rounded-full animate-pulse"
                :class="{
                  'bg-emerald-500': task.status === 'done',
                  'bg-blue-500': task.status === 'in_progress',
                  'bg-amber-500': task.status === 'in_review',
                  'bg-text-muted': task.status === 'backlog'
                }"
              ></div>
            </div>

            <!-- Task Info -->
            <div class="min-w-0 flex-1">
              <span class="text-sm font-semibold text-text group-hover:text-primary transition-colors block leading-snug truncate">
                {{ task.title }}
              </span>
              <div class="flex items-center gap-2 mt-0.5">
                <span class="text-[11px] text-text-secondary font-medium tracking-tight font-sans">
                  Project: <span class="font-semibold text-text-secondary">{{ task.project_name }}</span>
                </span>
              </div>
            </div>

            <!-- Status Badge -->
            <div class="shrink-0 flex items-center">
              <span
                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border tracking-wider uppercase transition-colors duration-200"
                :class="getStatusBadgeClass(task.status)"
              >
                {{ task.status_label }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
