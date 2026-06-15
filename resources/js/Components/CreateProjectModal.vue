<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/Components/ui/dialog';

const props = defineProps({
  workspaceId: {
    type: Number,
    required: true,
  }
});

const isOpen = ref(false);

const form = useForm({
  name: '',
  description: '',
});

const submit = () => {
  form.post(`/workspaces/${props.workspaceId}/projects`, {
    onSuccess: () => {
      isOpen.value = false;
      form.reset();
      toast.success('Project created successfully!');
    },
    onError: () => {
      toast.error('Failed to create project.');
    }
  });
};
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <button class="inline-flex items-center justify-center rounded-md bg-primary hover:bg-primary-dark text-white px-4 py-2 text-sm font-medium transition-colors shadow-sm gap-1 cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Create Project
      </button>
    </DialogTrigger>
    
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle class="font-display">Create new project</DialogTitle>
        <DialogDescription>
          A project contains tasks and boards for organizing your team's work.
        </DialogDescription>
      </DialogHeader>
      
      <form @submit.prevent="submit" class="space-y-4 py-4">
        <div class="space-y-1">
          <label for="project-name" class="text-sm font-medium text-text-secondary">Project Name</label>
          <input
            id="project-name"
            type="text"
            v-model="form.name"
            class="block w-full rounded-md border border-border px-3 py-2 bg-surface text-text shadow-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
            placeholder="e.g. Website Redesign, Mobile App"
            required
            :disabled="form.processing"
          />
          <div v-if="form.errors.name" class="text-xs text-accent-red mt-1 font-medium">
            {{ form.errors.name }}
          </div>
        </div>

        <div class="space-y-1">
          <label for="project-description" class="text-sm font-medium text-text-secondary">Description (Optional)</label>
          <textarea
            id="project-description"
            v-model="form.description"
            class="block w-full rounded-md border border-border px-3 py-2 bg-surface text-text shadow-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-20 resize-none"
            placeholder="What is this project about?"
            :disabled="form.processing"
          />
          <div v-if="form.errors.description" class="text-xs text-accent-red mt-1 font-medium">
            {{ form.errors.description }}
          </div>
        </div>

        <DialogFooter class="pt-4">
          <button
            type="submit"
            class="inline-flex items-center justify-center rounded-md bg-primary hover:bg-primary-dark text-white px-4 py-2 text-sm font-medium transition-colors shadow-sm disabled:opacity-50"
            :disabled="form.processing || !form.name.trim()"
          >
            <span v-if="form.processing">Creating...</span>
            <span v-else>Create Project</span>
          </button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
