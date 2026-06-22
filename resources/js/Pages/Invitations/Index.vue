<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  invitations: {
    type: Array,
    default: () => [],
  },
});

const loading = ref(false);
const processingId = ref(null);

const accept = (token) => {
  processingId.value = token;
  loading.value = true;
  router.post('/invitations/accept', { token }, {
    onSuccess: () => {
      toast.success('Invitation accepted!');
      processingId.value = null;
      loading.value = false;
    },
    onError: () => {
      toast.error('Failed to accept invitation. It may be invalid or expired.');
      processingId.value = null;
      loading.value = false;
    },
  });
};

const decline = (id) => {
  processingId.value = id;
  loading.value = true;
  router.delete(`/invitations/${id}`, {
    onSuccess: () => {
      toast.success('Invitation declined.');
      processingId.value = null;
      loading.value = false;
    },
    onError: () => {
      toast.error('Failed to decline invitation. Please try again.');
      processingId.value = null;
      loading.value = false;
    },
  });
};
</script>

<template>
  <AuthenticatedLayout title="Invitations">
    <div>
      <Head title="Invitations" />

      <div class="max-w-3xl mx-auto space-y-6">
        <div>
          <h1 class="text-2xl font-bold text-text font-display">Invitations</h1>
          <p class="text-sm text-text-secondary mt-1">
            Review and respond to workspace invitations.
          </p>
        </div>

        <div v-if="loading && invitations.length === 0" class="space-y-3">
          <div v-for="n in 2" :key="n" class="rounded-xl border border-border bg-surface p-5 flex items-center justify-between gap-4 shadow-sm animate-pulse">
            <div class="flex-1 space-y-2">
              <div class="h-4 w-40 bg-surface-3 rounded"></div>
              <div class="h-3 w-24 bg-surface-3 rounded"></div>
            </div>
            <div class="flex gap-2">
              <div class="h-8 w-16 bg-surface-3 rounded-md"></div>
              <div class="h-8 w-16 bg-surface-3 rounded-md"></div>
            </div>
          </div>
        </div>

        <div v-else-if="invitations.length === 0" class="rounded-xl border border-border bg-surface p-12 text-center">
          <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-surface-2 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-text-muted">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
            </svg>
          </div>
          <h3 class="text-sm font-semibold text-text mb-1">No pending invitations</h3>
          <p class="text-xs text-text-secondary max-w-sm mx-auto">
            When someone invites you to a workspace, it will appear here.
          </p>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="invitation in invitations"
            :key="invitation.id"
            class="rounded-xl border border-border bg-surface p-5 flex items-center justify-between gap-4 shadow-sm"
          >
            <div class="min-w-0 flex-1">
              <h3 class="font-semibold text-text text-sm truncate">
                {{ invitation.workspace_name }}
              </h3>
              <p class="text-xs text-text-secondary mt-0.5">
                Role: <span class="font-medium capitalize">{{ invitation.role }}</span>
                &middot; {{ invitation.created_at }}
              </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <button
                @click="accept(invitation.token)"
                :disabled="loading"
                class="inline-flex items-center whitespace-nowrap rounded-md bg-primary hover:bg-primary-dark text-white px-4 py-2 text-xs font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg v-if="processingId === invitation.token" class="animate-spin -ml-1 mr-1.5 h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Accept
              </button>
              <button
                @click="decline(invitation.id)"
                :disabled="loading"
                class="inline-flex items-center whitespace-nowrap rounded-md border border-border bg-surface hover:bg-surface-2 text-text-secondary px-4 py-2 text-xs font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-border disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Decline
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
