import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
  const page = usePage();
  const role = computed(() => page.props.userRole);

  const isAdmin  = computed(() => role.value === 'admin');
  const isMember = computed(() => ['admin', 'member'].includes(role.value));
  const isViewer = computed(() => role.value === 'viewer');

  const can = (action) => {
    const permissions = {
      'create-tasks':       isMember.value,
      'edit-tasks':         isMember.value,
      'move-tasks':         isMember.value,
      'delete-tasks':       isMember.value,
      'manage-projects':    isMember.value,
      'invite-members':     isAdmin.value,
      'manage-workspace':   isAdmin.value,
      'access-billing':     isAdmin.value,
      'manage-members':     isAdmin.value,
    };
    return permissions[action] ?? false;
  };

  return { role, isAdmin, isMember, isViewer, can };
}
