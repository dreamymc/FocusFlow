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
  const toColumn = event.to.getAttribute('data-column-id');
  const fromColumn = event.from.getAttribute('data-column-id');
  
  // Only emit if it actually moved to a different column or different position
  emit('task-moved', {
    taskId,
    fromColumn,
    toColumn,
    newIndex: event.newIndex,
    oldIndex: event.oldIndex
  });
};
</script>

<template>
  <div class="w-[280px] shrink-0 flex flex-col max-h-[80vh]">
    <!-- Column Header -->
    <div
      class="flex items-center justify-between px-3 py-2 border-l-4 mb-3"
      :style="{ borderLeftColor: column.color }"
    >
      <span class="font-display font-semibold text-sm text-text">
        {{ column.label }}
      </span>
      <span class="w-5 h-5 rounded-full bg-surface-3 text-text-secondary text-xs flex items-center justify-center font-medium">
        {{ localTasks.length }}
      </span>
    </div>

    <!-- Column Body (Draggable Area) -->
    <VueDraggable
      v-model="localTasks"
      group="tasks"
      :disabled="readOnly"
      @end="onDragEnd"
      :data-column-id="column.id"
      class="flex-1 overflow-y-auto bg-surface-3/30 border border-transparent rounded-xl p-2 min-h-[450px] transition-all duration-200"
      ghost-class="opacity-40"
      drag-class="rotate-[1deg]"
    >
      <!-- Task Cards -->
      <div
        v-for="task in localTasks"
        :key="task.id"
        :data-task-id="task.id"
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
        class="h-full flex items-center justify-center p-8 text-center"
      >
        <span class="text-xs text-text-muted italic select-none">No tasks here</span>
      </div>
    </VueDraggable>

    <!-- Column Footer: Add Task Button -->
    <div v-if="!readOnly && isMember" class="mt-2">
      <button
        @click="emit('create-task', column.id)"
        class="w-full flex items-center justify-center py-2 px-3 rounded-lg border border-dashed border-border hover:border-border-strong text-xs font-medium text-text-muted hover:text-text hover:bg-surface-2 transition-all duration-200 cursor-pointer"
      >
        + Add task
      </button>
    </div>
  </div>
</template>
