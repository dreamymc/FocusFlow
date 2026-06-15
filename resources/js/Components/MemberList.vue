<script setup>
import { usePermissions } from '../Composables/usePermissions';
import { computed } from 'vue';

const props = defineProps({
  members: {
    type: Array,
    required: true,
  }
});

const { isAdmin } = usePermissions();

const getRoleBadgeClasses = (role) => {
  if (role === 'admin') {
    return 'bg-primary-light text-primary border border-primary/20';
  } else if (role === 'member') {
    return 'bg-secondary-light text-secondary border border-secondary/20';
  }
  return 'bg-surface-3 text-text-secondary border border-border';
};

const getInitials = (name) => {
  if (!name) return '?';
  return name.trim().split(/\s+/).map(n => n[0]).slice(0, 2).join('').toUpperCase();
};
</script>

<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-border text-left">
      <thead>
        <tr class="text-xs text-text-muted uppercase tracking-wider font-semibold font-mono">
          <th class="py-3 px-4">Member</th>
          <th class="py-3 px-4">Email</th>
          <th class="py-3 px-4">Role</th>
          <th v-if="isAdmin" class="py-3 px-4 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-border">
        <tr v-for="member in members" :key="member.id" class="text-sm hover:bg-surface-2 transition-colors">
          <td class="py-4 px-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-primary-light text-primary flex items-center justify-center font-semibold font-display text-xs">
              {{ getInitials(member.name) }}
            </div>
            <span class="font-medium text-text">{{ member.name }}</span>
          </td>
          <td class="py-4 px-4 text-text-secondary">
            {{ member.email }}
          </td>
          <td class="py-4 px-4">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getRoleBadgeClasses(member.role)">
              {{ member.role }}
            </span>
          </td>
          <td v-if="isAdmin" class="py-4 px-4 text-right">
            <!-- Basic clean selector that looks like a dropdown -->
            <select
              :value="member.role"
              disabled
              class="rounded-md border border-border bg-surface px-2 py-1 text-xs text-text-secondary focus:border-primary focus:outline-none cursor-not-allowed opacity-60"
              title="Role changes are not available in this version"
            >
              <option value="admin">Admin</option>
              <option value="member">Member</option>
              <option value="viewer">Viewer</option>
            </select>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
