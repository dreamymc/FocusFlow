<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { usePermissions } from '../../Composables/usePermissions';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import ColorIcon from '../../Components/ColorIcon.vue';
import CreateProjectModal from '../../Components/CreateProjectModal.vue';

const props = defineProps({
  workspace: {
    type: Object,
    required: true,
  },
  projects: {
    type: Array,
    required: true,
  }
});

const { can } = usePermissions();
</script>

<template>
  <AuthenticatedLayout title="Projects">
    <div class="space-y-6">
      <!-- Top header bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="font-display text-2xl font-bold text-text">Projects</h1>
          <p class="text-sm text-text-secondary">
            Manage and view the projects running in <span class="font-semibold text-text">{{ workspace.name }}</span>.
          </p>
        </div>
        <div v-if="can('manage-projects')">
          <CreateProjectModal :workspace-id="workspace.id" />
        </div>
      </div>

      <!-- Projects Grid -->
      <div v-if="projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link
          v-for="project in projects"
          :key="project.id"
          :href="`/workspaces/${workspace.id}/projects/${project.id}`"
          class="block rounded-lg border border-border bg-surface p-6 shadow-sm hover:scale-[1.01] hover:shadow-md hover:border-border-strong transition-all duration-200 cursor-pointer"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
              <ColorIcon :name="project.name" :id="project.id" size="md" />
              <div class="min-w-0">
                <h3 class="font-display font-semibold text-text truncate text-base">{{ project.name }}</h3>
                <p class="text-xs text-text-secondary truncate mt-0.5" v-if="project.description">
                  {{ project.description }}
                </p>
              </div>
            </div>
            
            <!-- Task Count Badge -->
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-light text-primary border border-primary/10">
              {{ project.tasks_count }} {{ project.tasks_count === 1 ? 'task' : 'tasks' }}
            </span>
          </div>
        </Link>
      </div>

      <!-- Empty State -->
      <div v-else class="flex flex-col items-center justify-center border border-dashed border-border rounded-xl p-12 bg-surface text-center">
        <!-- SVG Icon Illustration -->
        <div class="w-16 h-16 rounded-full bg-primary-light flex items-center justify-center mb-4 text-primary">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-.778.099-1.533.284-2.253" />
          </svg>
        </div>
        <h3 class="font-display font-semibold text-lg text-text mb-1">No projects yet</h3>
        <p class="text-sm text-text-secondary max-w-sm mb-6">
          No projects in this workspace yet. Create a project to get started!
        </p>
        <div v-if="can('manage-projects')">
          <CreateProjectModal :workspace-id="workspace.id" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
