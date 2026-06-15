import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
  const page = usePage();
  const role = computed(() => page.props.userRole);

  const isAdmin  = computed(() => role.value === 'admin');
  const isMember = computed(() => ['admin', 'member'].includes(role.value));
  const isViewer = computed(() => role.value === 'viewer');

  // Compute permissions map once per role change
  const permissions = computed(() => ({
    'create-tasks':       isMember.value,
    'edit-tasks':         isMember.value,
    'move-tasks':         isMember.value,
    'delete-tasks':       isMember.value,
    'manage-projects':    isMember.value,
    'invite-members':     isAdmin.value,
    'manage-workspace':   isAdmin.value,
    'access-billing':     isAdmin.value,
    'manage-members':     isAdmin.value,
  }));

  /**
   * Check if the user has permission to perform a workspace action.
   * Note: When using inside script setup, wrap in computed() to maintain reactivity:
   * `const canEdit = computed(() => can('edit-tasks'))`
   * 
   * @param {'create-tasks'|'edit-tasks'|'move-tasks'|'delete-tasks'|'manage-projects'|'invite-members'|'manage-workspace'|'access-billing'|'manage-members'} action
   * @returns {boolean}
   */
  const can = (action) => permissions.value[action] ?? false;

  return { role, isAdmin, isMember, isViewer, can };
}
