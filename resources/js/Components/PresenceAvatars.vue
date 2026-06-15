<script setup>
import { computed } from 'vue';
import { useTaskUpdates } from '../Composables/useTaskUpdates';
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/Components/ui/tooltip';

const props = defineProps({
  taskId: {
    type: [Number, String],
    required: true,
  },
  currentUserId: {
    type: [Number, String],
    required: true,
  }
});

const { members } = useTaskUpdates(props.taskId);

const otherMembers = computed(() => {
  return members.value.filter(m => Number(m.id) !== Number(props.currentUserId));
});

const getInitials = (name) => {
  if (!name) return '?';
  return name.trim().split(/\s+/).map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const getAvatarColorClass = (userId) => {
  const colors = ['red', 'orange', 'yellow', 'green', 'blue', 'purple', 'pink', 'gray'];
  const index = Math.abs(Number(userId)) % colors.length;
  const color = colors[index];
  return {
    'red': 'bg-accent-red',
    'orange': 'bg-accent-orange',
    'yellow': 'bg-accent-yellow',
    'green': 'bg-accent-green',
    'blue': 'bg-accent-blue',
    'purple': 'bg-accent-purple',
    'pink': 'bg-accent-pink',
    'gray': 'bg-accent-gray'
  }[color] || 'bg-accent-blue';
};

const displayedMembers = computed(() => {
  return otherMembers.value.slice(0, 3);
});

const overflowCount = computed(() => {
  return Math.max(0, otherMembers.value.length - 3);
});
</script>

<template>
  <div v-if="otherMembers.length > 0" class="flex items-center gap-2">
    <TooltipProvider>
      <div class="flex -space-x-2 overflow-hidden">
        <!-- Render Avatars -->
        <Tooltip v-for="member in displayedMembers" :key="member.id">
          <TooltipTrigger as-child>
            <div
              class="inline-flex items-center justify-center w-7 h-7 rounded-full text-white font-semibold text-xs border-2 border-surface select-none"
              :class="getAvatarColorClass(member.id)"
            >
              {{ getInitials(member.name) }}
            </div>
          </TooltipTrigger>
          <TooltipContent>
            <p>{{ member.name }}</p>
          </TooltipContent>
        </Tooltip>

        <!-- Overflow Badge -->
        <div
          v-if="overflowCount > 0"
          class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-surface-3 text-text-secondary font-semibold text-xs border-2 border-surface select-none"
        >
          +{{ overflowCount }}
        </div>
      </div>
    </TooltipProvider>

    <span class="text-xs text-text-muted">
      {{ otherMembers.length }} user{{ otherMembers.length === 1 ? '' : 's' }} also viewing
    </span>
  </div>
</template>
