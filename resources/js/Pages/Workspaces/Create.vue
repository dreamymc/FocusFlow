<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';
import ColorIcon from '../../Components/ColorIcon.vue';

const form = useForm({
  name: '',
});

const submit = () => {
  form.post(route('workspaces.store'), {
    onError: () => {
      // Errors handled by form.errors
    }
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Create Workspace" />

    <div class="space-y-6">
      <div class="text-center">
        <h2 class="font-display text-2xl font-bold text-text">Create your workspace</h2>
        <p class="mt-2 text-sm text-text-secondary">
          A workspace is where your team's projects and tasks live.
        </p>
      </div>

      <!-- Preview -->
      <div class="flex flex-col items-center justify-center p-4 bg-surface-2 rounded-lg border border-border">
        <div class="text-xs text-text-muted mb-2 font-mono uppercase tracking-wider">Preview</div>
        <div class="flex items-center gap-3">
          <ColorIcon :name="form.name || '?'" size="lg" />
          <div class="font-display font-semibold text-lg text-text">
            {{ form.name || 'New Workspace' }}
          </div>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label for="name" class="block text-sm font-medium text-text-secondary mb-1">
            Workspace Name
          </label>
          <input
            id="name"
            type="text"
            v-model="form.name"
            class="block w-full rounded-md border border-border px-3 py-2 bg-surface text-text shadow-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
            placeholder="e.g. Acme Corporation, Design Team"
            required
            autofocus
            :disabled="form.processing"
          />
          <div v-if="form.errors.name" class="mt-1 text-xs text-accent-red font-medium">
            {{ form.errors.name }}
          </div>
        </div>

        <div>
          <button
            type="submit"
            class="flex w-full items-center justify-center rounded-md bg-primary hover:bg-primary-dark text-white px-4 py-2 text-sm font-medium transition-colors shadow-sm disabled:opacity-50"
            :disabled="form.processing || !form.name.trim()"
          >
            <span v-if="form.processing">Creating...</span>
            <span v-else class="flex items-center gap-1">
              Create workspace <span class="text-xs">→</span>
            </span>
          </button>
        </div>
      </form>
    </div>
  </GuestLayout>
</template>
