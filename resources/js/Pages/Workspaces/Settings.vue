<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '../../Layouts/AuthenticatedLayout.vue';
import MemberList from '../../Components/MemberList.vue';
import InviteMemberModal from '../../Components/InviteMemberModal.vue';

const props = defineProps({
  workspace: {
    type: Object,
    required: true,
  },
  members: {
    type: Array,
    required: true,
  }
});

const generalForm = useForm({
  name: props.workspace.name,
});

const saveGeneral = () => {
  generalForm.put(route('workspaces.update', props.workspace.id), {
    onSuccess: () => {
      toast.success('Workspace name updated!');
    },
    onError: () => {
      toast.error('Failed to update workspace name.');
    }
  });
};

const showDeleteConfirm = ref(false);
const deleteWorkspace = () => {
  toast.info('Workspace deletion is not enabled in this version.');
  showDeleteConfirm.value = false;
};
</script>

<template>
  <AuthenticatedLayout :title="`${workspace.name} Settings`">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Heading -->
      <div>
        <h1 class="font-display text-2xl font-bold text-text">Workspace Settings</h1>
        <p class="text-sm text-text-secondary">
          Manage your workspace details, team members, and permissions.
        </p>
      </div>

      <!-- General Settings Card -->
      <div class="rounded-lg border border-border bg-surface p-6 shadow-sm">
        <h2 class="font-display text-lg font-semibold text-text mb-4">General Settings</h2>
        <form @submit.prevent="saveGeneral" class="space-y-4">
          <div>
            <label for="workspace-name" class="block text-sm font-medium text-text-secondary mb-1">
              Workspace Name
            </label>
            <input
              id="workspace-name"
              type="text"
              v-model="generalForm.name"
              class="block w-full max-w-md rounded-md border border-border px-3 py-2 bg-surface text-text shadow-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
              required
              :disabled="generalForm.processing"
            />
            <div v-if="generalForm.errors.name" class="mt-1 text-xs text-accent-red font-medium">
              {{ generalForm.errors.name }}
            </div>
          </div>
          <div>
            <button
              type="submit"
              class="inline-flex items-center justify-center rounded-md bg-primary hover:bg-primary-dark text-white px-4 py-2 text-sm font-medium transition-colors shadow-sm disabled:opacity-50"
              :disabled="generalForm.processing || !generalForm.name.trim() || generalForm.name === workspace.name"
            >
              <span v-if="generalForm.processing">Saving...</span>
              <span v-else>Save Changes</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Members Card -->
      <div id="members" class="rounded-lg border border-border bg-surface p-6 shadow-sm">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="font-display text-lg font-semibold text-text">Team Members</h2>
            <p class="text-xs text-text-secondary mt-0.5">
              Manage who has access to this workspace and their permission levels.
            </p>
          </div>
          <InviteMemberModal :workspace-id="workspace.id" />
        </div>
        <MemberList :members="members" />
      </div>

      <!-- Danger Zone -->
      <div class="rounded-lg border border-accent-red/30 bg-surface p-6 shadow-sm">
        <h2 class="font-display text-lg font-semibold text-accent-red mb-2">Danger Zone</h2>
        <p class="text-sm text-text-secondary mb-4">
          Once you delete a workspace, there is no going back. Please be certain.
        </p>
        <div class="flex items-center gap-4">
          <button
            v-if="!showDeleteConfirm"
            @click="showDeleteConfirm = true"
            class="inline-flex items-center justify-center rounded-md border border-accent-red text-accent-red hover:bg-accent-red/5 px-4 py-2 text-sm font-medium transition-colors"
          >
            Delete Workspace
          </button>
          <div v-else class="flex items-center gap-2">
            <span class="text-sm text-text font-medium">Are you absolutely sure?</span>
            <button
              @click="deleteWorkspace"
              class="inline-flex items-center justify-center rounded-md bg-accent-red hover:bg-accent-red/90 text-white px-4 py-2 text-sm font-medium transition-colors"
            >
              Yes, delete workspace
            </button>
            <button
              @click="showDeleteConfirm = false"
              class="inline-flex items-center justify-center rounded-md border border-border bg-surface hover:bg-surface-2 text-text px-4 py-2 text-sm font-medium transition-colors"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
