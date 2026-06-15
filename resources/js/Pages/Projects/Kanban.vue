<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { toast } from 'vue-sonner';
import { usePermissions } from '../../Composables/usePermissions';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import ColorIcon from '../../Components/ColorIcon.vue';
import KanbanBoard from '../../Components/KanbanBoard.vue';
import TaskModal from '../../Components/TaskModal.vue';

const props = defineProps({
  project: {
    type: Object,
    required: true,
  },
  workspace: {
    type: Object,
    required: true,
  },
  columns: {
    type: Array,
    required: true,
  },
  members: {
    type: Array,
    required: true,
  }
});

const { isViewer, isMember } = usePermissions();

const localColumns = ref(JSON.parse(JSON.stringify(props.columns)));

watch(() => props.columns, (newVal) => {
  localColumns.value = JSON.parse(JSON.stringify(newVal));
}, { deep: true });

// State for Task Modal
const showTaskModal = ref(false);
const selectedTask = ref(null);
const taskModalMode = ref('view');
const createTaskStatus = ref('backlog');

const openTaskModal = (taskId) => {
  let foundTask = null;
  for (const column of localColumns.value) {
    foundTask = column.tasks.find(t => t.id === taskId);
    if (foundTask) break;
  }
  if (foundTask) {
    selectedTask.value = foundTask;
    taskModalMode.value = 'view';
    showTaskModal.value = true;
  }
};

const openCreateTaskModal = (status) => {
  createTaskStatus.value = status || 'backlog';
  selectedTask.value = null;
  taskModalMode.value = 'create';
  showTaskModal.value = true;
};

const handleTaskMoved = ({ taskId, fromColumn, toColumn, newIndex, oldIndex }) => {
  const sourceCol = localColumns.value.find(c => c.id === fromColumn);
  const targetCol = localColumns.value.find(c => c.id === toColumn);
  if (!sourceCol || !targetCol) return;
  const taskIndex = sourceCol.tasks.findIndex(t => t.id === taskId);
  if (taskIndex === -1) return;
  const [task] = sourceCol.tasks.splice(taskIndex, 1);
  task.status = toColumn;
  targetCol.tasks.splice(newIndex, 0, task);
};

const handleTaskCreated = (newTask) => {
  const status = typeof newTask.status === 'object' ? newTask.status.value : newTask.status;
  const column = localColumns.value.find(c => c.id === status);
  if (column) {
    if (!column.tasks.some(t => t.id === newTask.id)) {
      column.tasks.push(newTask);
    }
  }
};

const handleTaskUpdated = (updatedTask) => {
  let foundColumn = null;
  let taskIndex = -1;
  
  for (const col of localColumns.value) {
    taskIndex = col.tasks.findIndex(t => t.id === updatedTask.id);
    if (taskIndex !== -1) {
      foundColumn = col;
      break;
    }
  }

  if (foundColumn && taskIndex !== -1) {
    const currentStatus = typeof updatedTask.status === 'object' ? updatedTask.status.value : updatedTask.status;
    if (foundColumn.id !== currentStatus) {
      foundColumn.tasks.splice(taskIndex, 1);
      const targetCol = localColumns.value.find(c => c.id === currentStatus);
      if (targetCol) {
        targetCol.tasks.push(updatedTask);
      }
    } else {
      foundColumn.tasks[taskIndex] = updatedTask;
    }
  }
  
  if (selectedTask.value && selectedTask.value.id === updatedTask.id) {
    selectedTask.value = updatedTask;
  }
};

const handleTaskDeleted = (taskId) => {
  for (const col of localColumns.value) {
    const idx = col.tasks.findIndex(t => t.id === taskId);
    if (idx !== -1) {
      col.tasks.splice(idx, 1);
      break;
    }
  }
  if (selectedTask.value && selectedTask.value.id === taskId) {
    selectedTask.value = null;
    showTaskModal.value = false;
  }
};
</script>

<template>
  <AuthenticatedLayout :title="project.name">
    <div class="space-y-6 flex flex-col h-full">
      <!-- Sub-header bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <!-- Breadcrumbs -->
          <div class="flex items-center gap-2 text-xs text-text-secondary font-medium mb-1">
            <Link :href="`/workspaces/${workspace.id}/projects`" class="hover:text-primary transition-colors">
              Projects
            </Link>
            <span class="text-text-muted">/</span>
            <span class="text-text-muted font-normal">{{ project.name }}</span>
          </div>

          <!-- Title header -->
          <div class="flex items-center gap-3">
            <ColorIcon :name="project.name" :id="project.id" size="lg" />
            <h1 class="font-display text-2xl font-bold text-text">{{ project.name }}</h1>
          </div>
        </div>

        <!-- Create Task shortcut (Members+) -->
        <div v-if="isMember && !isViewer">
          <button
            @click="openCreateTaskModal('backlog')"
            class="inline-flex items-center justify-center rounded-md bg-primary hover:bg-primary-dark text-white px-4 py-2 text-sm font-medium transition-colors shadow-sm gap-1 cursor-pointer"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            New Task
          </button>
        </div>
      </div>

      <!-- Kanban Board Container -->
      <div class="flex-1 min-h-0">
        <KanbanBoard
          :columns="localColumns"
          :members="members"
          :read-only="isViewer"
          :workspace-id="workspace.id"
          @task-selected="openTaskModal"
          @create-task="openCreateTaskModal"
          @task-moved="handleTaskMoved"
        />
      </div>
    </div>

    <!-- Task Details & Creation Modal -->
    <TaskModal
      :open="showTaskModal"
      :task="selectedTask"
      :project-id="project.id"
      :workspace-id="workspace.id"
      :mode="taskModalMode"
      :initial-status="createTaskStatus"
      :members="members"
      @close="showTaskModal = false"
      @task-created="handleTaskCreated"
      @task-updated="handleTaskUpdated"
      @task-deleted="handleTaskDeleted"
    />
  </AuthenticatedLayout>
</template>
