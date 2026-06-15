<script setup>
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from '@/Components/ui/sheet';
import PresenceAvatars from './PresenceAvatars.vue';
import { usePermissions } from '../Composables/usePermissions';

const props = defineProps({
  task: {
    type: Object,
    default: null,
  },
  projectId: {
    type: Number,
    required: true,
  },
  workspaceId: {
    type: Number,
    required: true,
  },
  mode: {
    type: String,
    default: 'view',
    validator: (val) => ['view', 'create'].includes(val),
  },
  initialStatus: {
    type: String,
    default: 'backlog',
  },
  members: {
    type: Array,
    required: true,
  },
  open: {
    type: Boolean,
    default: false,
  }
});

const emit = defineEmits(['close', 'task-created', 'task-updated', 'task-deleted']);

const { can } = usePermissions();
const readOnly = computed(() => !can('edit-tasks'));

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id);

const isSaving = ref(false);
const saveStatus = ref(''); // '', 'saving', 'saved', 'error'
const showDeleteConfirm = ref(false);

const getCookie = (name) => {
  if (typeof document === 'undefined') return null;
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
  return null;
};

// Form states
const createForm = ref({
  title: '',
  description: '',
  status: props.initialStatus,
  priority: 'low',
  assigneeId: null,
});

const editForm = ref({
  title: '',
  description: '',
  status: 'backlog',
  priority: 'low',
  assigneeId: null,
});

// Initialize form
const initForms = () => {
  if (props.mode === 'create') {
    createForm.value = {
      title: '',
      description: '',
      status: props.initialStatus,
      priority: 'low',
      assigneeId: null,
    };
  } else if (props.task) {
    editForm.value = {
      title: props.task.title || '',
      description: props.task.description || '',
      status: props.task.status?.value || props.task.status || 'backlog',
      priority: props.task.priority?.value || props.task.priority || 'low',
      assigneeId: props.task.assignees && props.task.assignees.length > 0 ? props.task.assignees[0].id : null,
    };
    saveStatus.value = '';
    showDeleteConfirm.value = false;
  }
};

watch(() => [props.task, props.mode, props.open], () => {
  initForms();
}, { immediate: true, deep: true });

// Custom debounce implementation
function debounce(fn, delay) {
  let timeoutId;
  return function (...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), delay);
  };
}

const isFormDirty = computed(() => {
  if (!props.task) return false;
  const currentAssigneeId = props.task.assignees && props.task.assignees.length > 0 ? props.task.assignees[0].id : null;
  const currentStatus = props.task.status?.value || props.task.status || 'backlog';
  const currentPriority = props.task.priority?.value || props.task.priority || 'low';
  
  const assigneeId1 = editForm.value.assigneeId ? Number(editForm.value.assigneeId) : null;
  const assigneeId2 = currentAssigneeId ? Number(currentAssigneeId) : null;
  
  return editForm.value.title !== (props.task.title || '') ||
         editForm.value.description !== (props.task.description || '') ||
         editForm.value.status !== currentStatus ||
         editForm.value.priority !== currentPriority ||
         assigneeId1 !== assigneeId2;
});

const performAutoSave = async () => {
  if (!props.task || readOnly.value) return;
  saveStatus.value = 'saving';

  try {
    const xsrf = getCookie('XSRF-TOKEN');
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (xsrf) {
      headers['X-XSRF-TOKEN'] = xsrf;
    }

    const response = await fetch(`/api/v1/workspaces/${props.workspaceId}/projects/${props.projectId}/tasks/${props.task.id}`, {
      method: 'PATCH',
      headers,
      body: JSON.stringify({
        title: editForm.value.title,
        description: editForm.value.description,
        status: editForm.value.status,
        priority: editForm.value.priority,
        assignee_ids: editForm.value.assigneeId ? [Number(editForm.value.assigneeId)] : [],
      }),
    });

    if (!response.ok) throw new Error();
    const result = await response.json();
    emit('task-updated', result.data);
    saveStatus.value = 'saved';
  } catch (error) {
    saveStatus.value = 'error';
    toast.error('Failed to auto-save task.');
  }
};

const triggerAutoSave = debounce(performAutoSave, 500);

// Watchers for inline edits in view mode
watch(() => [editForm.value.title, editForm.value.description, editForm.value.status, editForm.value.priority, editForm.value.assigneeId], () => {
  if (props.mode === 'view' && props.task && !readOnly.value && isFormDirty.value) {
    triggerAutoSave();
  }
}, { deep: true });

const submitCreate = async () => {
  isSaving.value = true;
  try {
    const xsrf = getCookie('XSRF-TOKEN');
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (xsrf) {
      headers['X-XSRF-TOKEN'] = xsrf;
    }

    const response = await fetch(`/api/v1/workspaces/${props.workspaceId}/projects/${props.projectId}/tasks`, {
      method: 'POST',
      headers,
      body: JSON.stringify({
        title: createForm.value.title,
        description: createForm.value.description,
        status: createForm.value.status,
        priority: createForm.value.priority,
        assignee_ids: createForm.value.assigneeId ? [Number(createForm.value.assigneeId)] : [],
      }),
    });

    if (!response.ok) throw new Error();
    const result = await response.json();
    emit('task-created', result.data);
    emit('close');
    toast.success('Task created successfully!');
  } catch (error) {
    toast.error('Failed to create task.');
  } finally {
    isSaving.value = false;
  }
};

const submitDelete = async () => {
  if (!props.task) return;
  isSaving.value = true;

  try {
    const xsrf = getCookie('XSRF-TOKEN');
    const headers = {
      'Accept': 'application/json',
    };
    if (xsrf) {
      headers['X-XSRF-TOKEN'] = xsrf;
    }

    const response = await fetch(`/api/v1/workspaces/${props.workspaceId}/projects/${props.projectId}/tasks/${props.task.id}`, {
      method: 'DELETE',
      headers,
    });

    if (!response.ok) throw new Error();
    emit('task-deleted', props.task.id);
    emit('close');
    toast.success('Task deleted successfully!');
  } catch (error) {
    toast.error('Failed to delete task.');
  } finally {
    isSaving.value = false;
  }
};
</script>

<template>
  <Sheet :open="open" @update:open="val => !val && emit('close')">
    <SheetContent class="w-full sm:max-w-[540px] overflow-y-auto bg-surface border-l border-border p-6 shadow-xl">
      
      <!-- CREATE MODE -->
      <div v-if="mode === 'create'" class="space-y-6">
        <SheetHeader>
          <SheetTitle class="font-display text-xl font-bold text-text">Create Task</SheetTitle>
          <SheetDescription>Add a new task to your project board.</SheetDescription>
        </SheetHeader>

        <form @submit.prevent="submitCreate" class="space-y-4 pt-4">
          <!-- Title -->
          <div class="space-y-1">
            <label for="task-title" class="text-sm font-medium text-text-secondary">Task Title</label>
            <input
              id="task-title"
              type="text"
              v-model="createForm.title"
              class="block w-full rounded-md border border-border px-3 py-2 bg-surface text-text shadow-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
              placeholder="e.g. Design homepage hero section"
              required
              :disabled="isSaving"
            />
          </div>

          <!-- Description -->
          <div class="space-y-1">
            <label for="task-desc" class="text-sm font-medium text-text-secondary">Description</label>
            <textarea
              id="task-desc"
              v-model="createForm.description"
              class="block w-full rounded-md border border-border px-3 py-2 bg-surface text-text shadow-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none h-28 resize-none"
              placeholder="Provide a detailed task description..."
              :disabled="isSaving"
            />
          </div>

          <!-- Grid selectors -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Status -->
            <div class="space-y-1">
              <label for="task-status" class="text-sm font-medium text-text-secondary">Status</label>
              <select
                id="task-status"
                v-model="createForm.status"
                class="block w-full rounded-md border border-border px-3 py-2 bg-surface text-text shadow-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                required
                :disabled="isSaving"
              >
                <option value="backlog">Backlog</option>
                <option value="in_progress">In Progress</option>
                <option value="in_review">In Review</option>
                <option value="done">Done</option>
              </select>
            </div>

            <!-- Priority -->
            <div class="space-y-1">
              <label for="task-priority" class="text-sm font-medium text-text-secondary">Priority</label>
              <select
                id="task-priority"
                v-model="createForm.priority"
                class="block w-full rounded-md border border-border px-3 py-2 bg-surface text-text shadow-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                required
                :disabled="isSaving"
              >
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
          </div>

          <!-- Assignee -->
          <div class="space-y-1">
            <label for="task-assignee" class="text-sm font-medium text-text-secondary">Assignee</label>
            <select
              id="task-assignee"
              v-model="createForm.assigneeId"
              class="block w-full rounded-md border border-border px-3 py-2 bg-surface text-text shadow-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
              :disabled="isSaving"
            >
              <option :value="null">Unassigned</option>
              <option v-for="member in members" :key="member.id" :value="member.id">
                {{ member.name }}
              </option>
            </select>
          </div>

          <!-- Submit Button -->
          <div class="pt-4 flex justify-end gap-3 border-t border-border/40">
            <button
              type="button"
              @click="emit('close')"
              class="inline-flex items-center justify-center rounded-md border border-border bg-surface px-4 py-2 text-sm font-medium text-text hover:bg-surface-2 transition-colors cursor-pointer"
              :disabled="isSaving"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="inline-flex items-center justify-center rounded-md bg-primary hover:bg-primary-dark text-white px-4 py-2 text-sm font-medium transition-colors shadow-sm cursor-pointer disabled:opacity-50"
              :disabled="isSaving || !createForm.title.trim()"
            >
              <span v-if="isSaving">Creating...</span>
              <span v-else>Create Task</span>
            </button>
          </div>
        </form>
      </div>

      <!-- VIEW/EDIT MODE -->
      <div v-else-if="mode === 'view' && task" class="space-y-6">
        <SheetHeader>
          <div class="flex items-center justify-between border-b border-border/40 pb-2">
            <span class="font-mono text-xs text-text-muted">TASK-{{ task.id }}</span>
            
            <!-- Saving Status Indicator -->
            <span class="text-xs font-medium text-text-muted">
              <span v-if="saveStatus === 'saving'" class="text-primary animate-pulse">Saving...</span>
              <span v-else-if="saveStatus === 'saved'" class="text-accent-green">Saved ✓</span>
              <span v-else-if="saveStatus === 'error'" class="text-accent-red">Save failed</span>
            </span>
          </div>
          
          <!-- Presence Channel Avatars -->
          <div class="pt-2">
            <PresenceAvatars :task-id="task.id" :current-user-id="currentUserId" />
          </div>
        </SheetHeader>

        <!-- Inline Editable Title -->
        <div class="space-y-1">
          <input
            type="text"
            v-model="editForm.title"
            class="block w-full font-display text-xl font-bold text-text bg-transparent border border-transparent hover:border-border/60 focus:border-primary focus:bg-surface rounded px-2 py-1 focus:outline-none transition-all"
            required
            :disabled="readOnly"
          />
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-border/40">
          
          <!-- Left Column (Description) -->
          <div class="md:col-span-2 space-y-2">
            <label class="text-xs font-semibold text-text-secondary uppercase tracking-wider font-mono">Description</label>
            <textarea
              v-model="editForm.description"
              class="block w-full rounded-md border border-transparent hover:border-border/60 focus:border-border bg-transparent focus:bg-surface p-2 text-sm text-text h-40 resize-none focus:outline-none transition-all"
              placeholder="Add a detailed description for this task..."
              :disabled="readOnly"
            />
          </div>

          <!-- Right Column (Metadata Selectors) -->
          <div class="space-y-4 bg-surface-2 rounded-xl p-4 border border-border">
            <!-- Status -->
            <div class="space-y-1">
              <label for="edit-status" class="text-xs font-semibold text-text-secondary uppercase tracking-wider font-mono">Status</label>
              <select
                id="edit-status"
                v-model="editForm.status"
                class="block w-full rounded-md border border-border bg-surface px-2 py-1 text-xs text-text focus:border-primary focus:outline-none"
                :disabled="readOnly"
              >
                <option value="backlog">Backlog</option>
                <option value="in_progress">In Progress</option>
                <option value="in_review">In Review</option>
                <option value="done">Done</option>
              </select>
            </div>

            <!-- Priority -->
            <div class="space-y-1">
              <label for="edit-priority" class="text-xs font-semibold text-text-secondary uppercase tracking-wider font-mono">Priority</label>
              <select
                id="edit-priority"
                v-model="editForm.priority"
                class="block w-full rounded-md border border-border bg-surface px-2 py-1 text-xs text-text focus:border-primary focus:outline-none"
                :disabled="readOnly"
              >
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>

            <!-- Assignee -->
            <div class="space-y-1">
              <label for="edit-assignee" class="text-xs font-semibold text-text-secondary uppercase tracking-wider font-mono">Assignee</label>
              <select
                id="edit-assignee"
                v-model="editForm.assigneeId"
                class="block w-full rounded-md border border-border bg-surface px-2 py-1 text-xs text-text focus:border-primary focus:outline-none"
                :disabled="readOnly"
              >
                <option :value="null">Unassigned</option>
                <option v-for="member in members" :key="member.id" :value="member.id">
                  {{ member.name }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Delete Task (Members only) -->
        <div v-if="can('delete-tasks')" class="pt-6 border-t border-border/40 flex justify-between items-center">
          <div v-if="!showDeleteConfirm">
            <button
              @click="showDeleteConfirm = true"
              class="text-xs font-medium text-accent-red hover:underline cursor-pointer"
            >
              Delete Task
            </button>
          </div>
          <div v-else class="flex items-center gap-3">
            <span class="text-xs text-text font-medium">Are you sure?</span>
            <button
              @click="submitDelete"
              class="text-xs font-medium bg-accent-red hover:bg-accent-red/90 text-white rounded px-2.5 py-1.5 cursor-pointer"
              :disabled="isSaving"
            >
              Delete
            </button>
            <button
              @click="showDeleteConfirm = false"
              class="text-xs font-medium text-text-secondary hover:underline cursor-pointer"
              :disabled="isSaving"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>

    </SheetContent>
  </Sheet>
</template>
