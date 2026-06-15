<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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
    'backlog': 'bg-slate-100 text-slate-700 border-slate-200',
    'in_progress': 'bg-blue-50 text-blue-700 border-blue-200',
    'in_review': 'bg-amber-50 text-amber-700 border-amber-200',
    'done': 'bg-emerald-50 text-emerald-700 border-emerald-200'
  }[status] || 'bg-slate-100 text-slate-700 border-slate-200';
};
</script>

<template>
  <AuthenticatedLayout title="Dashboard">
    <!-- Empty State: No Workspace -->
    <div v-if="!currentWorkspace" class="flex flex-col items-center justify-center min-h-[60vh] bg-surface border border-border rounded-xl p-8 text-center shadow-sm max-w-2xl mx-auto">
      <div class="w-16 h-16 bg-primary-light rounded-full flex items-center justify-center text-primary mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
          <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.97 5.97 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
      </div>
      <h2 class="text-xl font-display font-bold text-text mb-2">Create your first Workspace</h2>
      <p class="text-text-secondary text-sm max-w-sm mb-8 leading-relaxed">
        Workspaces are where your team collaborates on tasks and projects. Create one to get started.
      </p>
      <Link
        href="/workspaces/create"
        class="inline-flex items-center justify-center bg-primary hover:bg-primary-dark text-white font-semibold px-6 py-2.5 rounded-lg shadow-sm hover:shadow transition-all cursor-pointer text-sm"
      >
        Create Workspace
      </Link>
    </div>

    <!-- Main Dashboard Content -->
    <div v-else class="space-y-8 animate-fade-in">
      <!-- Metric Cards Row -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card: Total Tasks -->
        <div class="bg-surface border border-border rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-between">
          <div class="space-y-1">
            <span class="text-xs font-bold text-text-secondary uppercase tracking-wider">Total Tasks</span>
            <p class="text-3xl font-display font-extrabold text-text leading-none">{{ stats?.totalTasks ?? 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-primary-light rounded-lg flex items-center justify-center text-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.375c1.08 0 1.958-.87 1.958-1.958V13.5m-6.75-2.25H12a1.875 1.875 0 0 0 0-3.75H9v3.75Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H3.75A1.125 1.125 0 0 0 2.625 3.375v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V14.25Z" />
            </svg>
          </div>
        </div>

        <!-- Card: In Progress -->
        <div class="bg-surface border border-border rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-between">
          <div class="space-y-1">
            <span class="text-xs font-bold text-text-secondary uppercase tracking-wider">In Progress</span>
            <p class="text-3xl font-display font-extrabold text-text leading-none">{{ stats?.activeTasks ?? 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
          </div>
        </div>

        <!-- Card: Completed Today -->
        <div class="bg-surface border border-border rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-between">
          <div class="space-y-1">
            <span class="text-xs font-bold text-text-secondary uppercase tracking-wider">Completed Today</span>
            <p class="text-3xl font-display font-extrabold text-text leading-none">{{ stats?.completedToday ?? 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-600">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Recent Activity Section -->
      <div class="bg-surface border border-border rounded-xl p-6 shadow-sm">
        <h2 class="text-base font-display font-bold text-text mb-4">Recent Task Activity</h2>
        
        <div v-if="recentTasks.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
          <p class="text-sm text-text-secondary italic font-sans">No tasks currently assigned to you.</p>
        </div>
        
        <div v-else class="divide-y divide-border/60">
          <div
            v-for="task in recentTasks"
            :key="task.id"
            class="py-3.5 flex items-center justify-between gap-4 font-sans hover:bg-slate-50/40 px-2 rounded-lg transition-colors"
          >
            <div class="min-w-0">
              <span class="text-sm font-semibold text-text truncate block leading-tight">{{ task.title }}</span>
              <span class="text-[11px] text-text-secondary font-medium tracking-tight mt-0.5 block">
                Project: <span class="font-semibold">{{ task.project_name }}</span>
              </span>
            </div>
            <div class="shrink-0 flex items-center">
              <span
                class="px-2.5 py-0.5 rounded-full text-xs font-bold border"
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
