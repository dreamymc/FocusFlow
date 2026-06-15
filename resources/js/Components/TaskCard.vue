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

const firstAssignee = computed(() => {
  return props.task.assignees && props.task.assignees.length > 0
    ? props.task.assignees[0]
    : null;
});

const getInitials = (name) => {
  if (!name) return '?';
  return name.trim().split(/\s+/).map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const priorityColorClass = computed(() => {
  if (!props.task.priority) return null;
  const p = typeof props.task.priority === 'object' ? props.task.priority.value : props.task.priority;
  return {
    'high': 'bg-accent-red',
    'medium': 'bg-accent-yellow',
    'low': 'bg-accent-green'
  }[p] || null;
});

const priorityLabel = computed(() => {
  if (!props.task.priority) return '';
  return typeof props.task.priority === 'object' ? props.task.priority.label : props.task.priority;
});
</script>

<template>
  <div
    class="bg-surface rounded-lg border border-border p-3 shadow-sm select-none transition-all duration-200"
    :class="[
      readOnly
        ? 'cursor-default'
        : 'cursor-grab active:cursor-grabbing hover:shadow-md hover:translate-y-[-1px] hover:border-border-strong'
    ]"
    @mousedown="onMouseDown"
    @mouseup="onMouseUp"
    @click="onClick"
  >
    <!-- Top row: Priority dot & Title -->
    <div class="flex items-start justify-between gap-2">
      <h4 class="text-sm font-medium text-text line-clamp-2 leading-snug pr-4">
        {{ task.title }}
      </h4>
      <div
        v-if="priorityColorClass"
        class="w-2.5 h-2.5 rounded-full shrink-0 mt-1"
        :class="priorityColorClass"
        :title="`Priority: ${priorityLabel}`"
      />
    </div>

    <!-- Bottom row: Task ID & Assignee -->
    <div class="flex items-center justify-between mt-3 pt-2 border-t border-border/40">
      <span class="font-mono text-[10px] text-text-muted">
        TASK-{{ task.id }}
      </span>

      <!-- Assignee avatar initials -->
      <div
        v-if="firstAssignee"
        class="w-5 h-5 rounded-full bg-primary-light text-primary flex items-center justify-center font-semibold text-[9px] font-display"
        :title="firstAssignee.name"
      >
        {{ getInitials(firstAssignee.name) }}
      </div>
    </div>
  </div>
</template>
