<script setup>
import { computed } from 'vue';

const props = defineProps({
  task: {
    type: Object,
    required: true,
  },
  readOnly: {
    type: Boolean,
    default: false,
  }
});

const emit = defineEmits(['task-clicked']);

let isDragging = false;
let startX = 0;
let startY = 0;

const onMouseDown = (e) => {
  isDragging = false;
  startX = e.clientX;
  startY = e.clientY;
};

const onMouseUp = (e) => {
  const diffX = Math.abs(e.clientX - startX);
  const diffY = Math.abs(e.clientY - startY);
  if (diffX > 5 || diffY > 5) {
    isDragging = true;
  }
};

const onClick = (e) => {
  if (isDragging) {
    e.preventDefault();
    e.stopPropagation();
    return;
  }
  emit('task-clicked', props.task.id);
};

const getInitials = (name) => {
  if (!name) return '?';
  return name.trim().split(/\s+/).map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const priorityBorderClass = computed(() => {
  if (!props.task.priority) return 'border-l-border';
  const p = typeof props.task.priority === 'object' ? props.task.priority.value : props.task.priority;
  return {
    'high': 'border-l-accent-red',
    'medium': 'border-l-accent-yellow',
    'low': 'border-l-accent-green'
  }[p] || 'border-l-border';
});

const isOverdue = (dateString) => {
  if (!dateString) return false;
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const due = new Date(dateString);
  return due < today;
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
};
</script>

<template>
  <div
    class="bg-slate-900 rounded-xl border border-t-slate-800 border-r-slate-800 border-b-slate-800 border-l-4 p-3.5 shadow-sm select-none transition-all duration-300"
    :class="[
      priorityBorderClass,
      readOnly
        ? 'cursor-default'
        : 'cursor-grab active:cursor-grabbing hover:shadow-lg hover:shadow-primary/5 hover:translate-y-[-2px] hover:border-slate-750',
      task.shaking ? 'animate-shake' : ''
    ]"
    @mousedown="onMouseDown"
    @mouseup="onMouseUp"
    @click="onClick"
  >
    <!-- Top row: Title -->
    <div class="flex items-start justify-between gap-2">
      <h4 class="text-sm font-medium text-slate-200 line-clamp-2 leading-snug">
        {{ task.title }}
      </h4>
    </div>

    <!-- Bottom row: Task ID, Due Date & Assignees -->
    <div class="flex items-center justify-between mt-3 pt-2 border-t border-slate-800/80">
      <div class="flex items-center gap-2">
        <span class="font-mono text-[10px] text-slate-500">
          TASK-{{ task.id }}
        </span>
        <span
          v-if="task.due_date"
          class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium border transition-colors duration-150"
          :class="isOverdue(task.due_date)
            ? 'text-red-400 bg-red-950/30 border-red-900/30 font-semibold'
            : 'text-slate-300 bg-slate-950/60 border-slate-800'"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-2.5 h-2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
          </svg>
          {{ formatDate(task.due_date) }}
        </span>
      </div>

      <!-- Assignees avatar group -->
      <div v-if="task.assignees && task.assignees.length > 0" class="flex -space-x-1.5 overflow-hidden items-center">
        <div
          v-for="(assignee, index) in task.assignees.slice(0, 3)"
          :key="assignee.id || index"
          class="w-5 h-5 rounded-full bg-slate-800 text-slate-200 flex items-center justify-center font-semibold text-[9px] font-display border-2 border-slate-900 shrink-0"
          :style="{ zIndex: 10 - index }"
          :title="assignee.name"
        >
          {{ getInitials(assignee.name) }}
        </div>
        <div
          v-if="task.assignees.length > 3"
          class="w-5 h-5 rounded-full bg-slate-950 text-slate-400 flex items-center justify-center font-medium text-[8px] font-display border-2 border-slate-900 shrink-0"
          :style="{ zIndex: 5 }"
          :title="`${task.assignees.length - 3} more`"
        >
          +{{ task.assignees.length - 3 }}
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes shake {
  0%, 100% {
    transform: translateX(0);
  }
  20%, 60% {
    transform: translateX(-4px);
  }
  40%, 80% {
    transform: translateX(4px);
  }
}

.animate-shake {
  animation: shake 0.4s ease-in-out;
  border-color: var(--color-accent-red) !important;
}
</style>
