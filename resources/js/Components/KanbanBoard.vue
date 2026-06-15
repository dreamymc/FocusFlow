<script setup>
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import KanbanColumn from './KanbanColumn.vue';

const props = defineProps({
  columns: {
    type: Array,
    required: true,
  },
  members: {
    type: Array,
    required: true,
  },
  readOnly: {
    type: Boolean,
    default: false,
  },
  workspaceId: {
    type: Number,
    required: true,
  }
});

const emit = defineEmits(['task-selected', 'create-task', 'task-moved']);

const localColumns = ref(JSON.parse(JSON.stringify(props.columns)));

watch(() => props.columns, (newVal) => {
  localColumns.value = JSON.parse(JSON.stringify(newVal));
}, { deep: true });

function getCookie(name) {
  if (typeof document === 'undefined') return null;
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
  return null;
}

const handleTaskMoved = async ({ taskId, fromColumn, toColumn, newIndex, oldIndex }) => {
  if (fromColumn === toColumn && newIndex === oldIndex) return;

  const sourceCol = localColumns.value.find(c => c.id === fromColumn);
  const targetCol = localColumns.value.find(c => c.id === toColumn);
  if (!sourceCol || !targetCol) return;

  // Optimistic backup
  const backup = JSON.parse(JSON.stringify(localColumns.value));

  // Find and splice task from source
  const taskIndex = sourceCol.tasks.findIndex(t => t.id === taskId);
  if (taskIndex === -1) return;
  const [task] = sourceCol.tasks.splice(taskIndex, 1);

  // Update locally
  task.status = toColumn;
  targetCol.tasks.splice(newIndex, 0, task);

  // Notify parent of move
  emit('task-moved', { taskId, fromColumn, toColumn, newIndex, oldIndex });

  // Send request
  try {
    const xsrf = getCookie('XSRF-TOKEN');
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (xsrf) {
      headers['X-XSRF-TOKEN'] = xsrf;
    }

    const response = await fetch(`/api/v1/workspaces/${props.workspaceId}/tasks/${taskId}/move`, {
      method: 'PUT',
      headers,
      body: JSON.stringify({ status: toColumn }),
    });

    if (!response.ok) {
      throw new Error('Failed to move task');
    }
  } catch (error) {
    // Revert state
    localColumns.value = backup;
    emit('task-moved', { taskId, fromColumn: toColumn, toColumn: fromColumn, newIndex: oldIndex, oldIndex: newIndex });
    toast.error('Failed to move task. Reverted changes.');
  }
};
</script>

<template>
  <div class="flex items-start gap-6 overflow-x-auto pb-4 scrollbar-thin select-none">
    <KanbanColumn
      v-for="column in localColumns"
      :key="column.id"
      :column="column"
      :read-only="readOnly"
      @task-moved="handleTaskMoved"
      @create-task="status => emit('create-task', status)"
      @task-selected="taskId => emit('task-selected', taskId)"
    />
  </div>
</template>
