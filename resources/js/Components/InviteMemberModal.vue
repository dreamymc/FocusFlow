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
  email: '',
  role: 'member',
});

const submit = () => {
  form.post(route('workspaces.invite', props.workspaceId), {
    onSuccess: () => {
      isOpen.value = false;
      form.reset();
      toast.success('Invitation sent!');
    },
    onError: () => {
      toast.error('Failed to send invitation.');
    }
  });
};
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <button class="inline-flex items-center justify-center rounded-md bg-primary hover:bg-primary-dark text-white px-4 py-2 text-sm font-medium transition-colors shadow-sm">
        Invite member
      </button>
    </DialogTrigger>
    
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle class="font-display">Invite team member</DialogTitle>
        <DialogDescription>
          Send an invitation to join this workspace. They will receive an email.
        </DialogDescription>
      </DialogHeader>
      
      <form @submit.prevent="submit" class="space-y-4 py-4">
        <div class="space-y-1">
          <label for="invite-email" class="text-sm font-medium text-text-secondary">Email Address</label>
          <input
            id="invite-email"
            type="email"
            v-model="form.email"
            class="block w-full rounded-md border border-border px-3 py-2 bg-surface text-text shadow-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
            placeholder="colleague@example.com"
            required
            :disabled="form.processing"
          />
          <div v-if="form.errors.email" class="text-xs text-accent-red mt-1 font-medium">
            {{ form.errors.email }}
          </div>
        </div>

        <div class="space-y-1">
          <label for="invite-role" class="text-sm font-medium text-text-secondary">Role</label>
          <select
            id="invite-role"
            v-model="form.role"
            class="block w-full rounded-md border border-border px-3 py-2 bg-surface text-text shadow-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
            required
            :disabled="form.processing"
          >
            <option value="member">Member (can manage projects & tasks)</option>
            <option value="viewer">Viewer (read-only access)</option>
          </select>
          <div v-if="form.errors.role" class="text-xs text-accent-red mt-1 font-medium">
            {{ form.errors.role }}
          </div>
        </div>

        <DialogFooter class="pt-4">
          <button
            type="submit"
            class="inline-flex items-center justify-center rounded-md bg-primary hover:bg-primary-dark text-white px-4 py-2 text-sm font-medium transition-colors shadow-sm disabled:opacity-50"
            :disabled="form.processing || !form.email.trim()"
          >
            <span v-if="form.processing">Sending...</span>
            <span v-else>Send invitation</span>
          </button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>
