<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
import { toast } from 'vue-sonner';
import KanbanColumn from './KanbanColumn.vue';

// Map to track active timeouts per task ID to prevent memory leaks and race conditions
const activeTimeouts = new Map();

onBeforeUnmount(() => {
  activeTimeouts.forEach(clearTimeout);
  activeTimeouts.clear();
});

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
  const updated = JSON.parse(JSON.stringify(newVal));
  // Preserve shaking status from current localColumns
  localColumns.value.forEach(col => {
    col.tasks.forEach(t => {
      if (t.shaking) {
        for (const newCol of updated) {
          const newT = newCol.tasks.find(nt => nt.id === t.id);
          if (newT) {
            newT.shaking = true;
          }
        }
      }
    });
  });
  localColumns.value = updated;
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

    toast.success('Task moved successfully');
  } catch (error) {
    // Revert state
    let backupTask = null;
    for (const column of backup) {
      const task = column.tasks.find(t => t.id === taskId);
      if (task) {
        backupTask = task;
        break;
      }
    }
    if (backupTask) {
      backupTask.shaking = true;
    }

    localColumns.value = backup;
    emit('task-moved', { taskId, fromColumn: toColumn, toColumn: fromColumn, newIndex: oldIndex, oldIndex: newIndex });

    // Clear any existing active timeout for this task to avoid race conditions
    if (activeTimeouts.has(taskId)) {
      clearTimeout(activeTimeouts.get(taskId));
    }

    // Call setTimeout with a duration of 800ms to clear the shaking flag from the task object
    const timeoutId = setTimeout(() => {
      if (backupTask) {
        backupTask.shaking = false;
      }
      for (const col of localColumns.value) {
        const t = col.tasks.find(task => task.id === taskId);
        if (t) {
          t.shaking = false;
        }
      }
      activeTimeouts.delete(taskId);
    }, 800);

    activeTimeouts.set(taskId, timeoutId);

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
