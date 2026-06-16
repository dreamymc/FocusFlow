<script setup>
import { ref, watch, computed } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';
import { usePermissions } from '../Composables/usePermissions';
import TaskCard from './TaskCard.vue';

const props = defineProps({
  column: {
    type: Object,
    required: true,
  },
  readOnly: {
    type: Boolean,
    default: false,
  }
});

const emit = defineEmits(['task-moved', 'create-task', 'task-selected']);

const { isMember } = usePermissions();

const localTasks = ref([...props.column.tasks]);

watch(() => props.column.tasks, (newTasks) => {
  localTasks.value = [...newTasks];
}, { deep: true });

const onDragEnd = (event) => {
  const taskId = Number(event.item.getAttribute('data-task-id'));
  const toColumn = event.to.closest('[data-column-id]')?.getAttribute('data-column-id');
  const fromColumn = event.from.closest('[data-column-id]')?.getAttribute('data-column-id');
  
  // Only emit if it actually moved to a different column or different position
  emit('task-moved', {
    taskId,
    fromColumn,
    toColumn,
    newIndex: event.newIndex,
    oldIndex: event.oldIndex
  });
};

const columnTintClass = computed(() => {
  return {
    'backlog': 'bg-surface-3/30 border-border',
    'in_progress': 'bg-blue-500/5 dark:bg-blue-950/10 border-blue-500/10 dark:border-blue-950/30',
    'in_review': 'bg-amber-500/5 dark:bg-amber-950/10 border-amber-500/10 dark:border-amber-950/30',
    'done': 'bg-emerald-500/5 dark:bg-emerald-950/10 border-emerald-500/10 dark:border-emerald-950/30'
  }[props.column.id] || 'bg-surface-3/30 border-border';
});
</script>

<template>
  <div class="w-[280px] shrink-0 flex flex-col max-h-[80vh]" :data-column-id="column.id">
    <!-- Column Header -->
    <div
      class="flex items-center justify-between px-3 py-2 border-l-4 mb-3"
      :style="{ borderLeftColor: column.color }"
    >
      <span class="font-display font-semibold text-sm text-text">
        {{ column.label }}
      </span>
      <span class="w-5 h-5 rounded-full bg-surface text-text-secondary text-xs flex items-center justify-center font-medium border border-border">
        {{ localTasks.length }}
      </span>
    </div>

    <!-- Column Body (Draggable Area) -->
    <VueDraggable
      v-model="localTasks"
      group="tasks"
      :disabled="readOnly"
      @end="onDragEnd"
      draggable=".task-card-wrapper"
      class="kanban-column-body flex-1 overflow-y-auto border rounded-xl p-2 min-h-[450px] transition-all duration-200"
      :class="columnTintClass"
      ghost-class="sortable-ghost"
      drag-class="drag-active"
    >
      <!-- Task Cards -->
      <div
        v-for="task in localTasks"
        :key="task.id"
        :data-task-id="task.id"
        class="task-card-wrapper"
      >
        <TaskCard
          :task="task"
          :read-only="readOnly"
          class="mb-2"
          @task-clicked="taskId => emit('task-selected', taskId)"
        />
      </div>

      <!-- Empty State inside Column -->
      <div
        v-if="localTasks.length === 0"
        class="flex flex-col items-center justify-center py-12 px-4 text-center border-2 border-dashed border-border rounded-lg select-none"
      >
        <span class="text-xs font-medium text-text-muted">Drop tasks here</span>
      </div>
    </VueDraggable>

    <!-- Column Footer: Add Task Button -->
    <div v-if="!readOnly && isMember" class="mt-2">
      <button
        @click="emit('create-task', column.id)"
        class="w-full flex items-center justify-center py-2 px-3 rounded-lg border border-dashed border-border hover:border-primary-dark/40 hover:bg-primary-light/30 hover:text-primary text-xs font-medium text-text-muted transition-all duration-150 cursor-pointer"
      >
        + Add task
      </button>
    </div>
  </div>
</template>

<style scoped>
.sortable-ghost {
  opacity: 0.4;
}
.kanban-column-body:has(.sortable-ghost) {
  outline: 2px dashed var(--color-primary);
  outline-offset: -2px;
  background-color: rgba(99, 102, 241, 0.05) !important;
}
.drag-active {
  opacity: 0.95;
  transform: rotate(1deg) !important;
  background-color: var(--color-primary-light) !important;
  cursor: grabbing !important;
}
.drag-active :deep(.bg-surface) {
  background-color: var(--color-primary-light) !important;
}
</style>
