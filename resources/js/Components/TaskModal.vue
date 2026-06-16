<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import PresenceAvatars from './PresenceAvatars.vue';
import { usePermissions } from '../Composables/usePermissions';

const props = defineProps({
  task: { type: Object, default: null },
  projectId: { type: Number, required: true },
  workspaceId: { type: Number, required: true },
  mode: {
    type: String,
    default: 'view',
    validator: (val) => ['view', 'create'].includes(val),
  },
  initialStatus: { type: String, default: 'backlog' },
  members: { type: Array, required: true },
  open: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'task-created', 'task-updated', 'task-deleted']);

const { can } = usePermissions();
const readOnly = computed(() => !can('edit-tasks'));

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id);

const isSaving = ref(false);
const saveStatus = ref('');
const showDeleteConfirm = ref(false);
const hasChangesBeenSaved = ref(false);

// Comments
const comments = ref([]);
const newComment = ref('');
const isSubmittingComment = ref(false);
const commentsLoading = ref(false);

const getInitials = (name) => {
  if (!name) return '?';
  return name.trim().split(/\s+/).map(n => n[0]).slice(0, 2).join('').toUpperCase();
};

const formatCommentDate = (dateStr) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
    + ' at ' + date.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit', hour12: true });
};

const fetchComments = async () => {
  if (!props.task) return;
  commentsLoading.value = true;
  try {
    const response = await fetch(`/api/v1/workspaces/${props.workspaceId}/projects/${props.projectId}/tasks/${props.task.id}/comments`);
    if (response.ok) {
      const result = await response.json();
      comments.value = result.data;
    }
  } catch (e) {
    console.error('Failed to load comments');
  } finally {
    commentsLoading.value = false;
  }
};

let modalChannel = null;

const setupEchoListener = () => {
  if (window.Echo && props.task) {
    if (modalChannel) window.Echo.leaveChannel('workspace.' + props.workspaceId);
    modalChannel = window.Echo.private('workspace.' + props.workspaceId);
    modalChannel.listen('TaskCommented', (e) => {
      if (e.task.id === props.task.id) {
        if (!comments.value.some(c => c.id === e.comment.id)) {
          comments.value.push(e.comment);
        }
      }
    });
  }
};

const cleanupEchoListener = () => {
  if (modalChannel && window.Echo) {
    modalChannel.stopListening('TaskCommented');
    modalChannel = null;
  }
};

onUnmounted(() => {
  cleanupEchoListener();
  triggerAutoSave?.cancel();
  document.body.style.overflow = '';
});

const getCookie = (name) => {
  if (typeof document === 'undefined') return null;
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
  return null;
};

// Forms
const createForm = ref({ title: '', description: '', status: props.initialStatus, priority: 'low', assigneeId: null });
const editForm = ref({ title: '', description: '', status: 'backlog', priority: 'low', assigneeId: null });

const initForms = () => {
  if (props.mode === 'create') {
    createForm.value = { title: '', description: '', status: props.initialStatus, priority: 'low', assigneeId: null };
  } else if (props.task) {
    editForm.value = {
      title: props.task.title || '',
      description: props.task.description || '',
      status: props.task.status?.value || props.task.status || 'backlog',
      priority: props.task.priority?.value || props.task.priority || 'low',
      assigneeId: props.task.assignees?.length > 0 ? props.task.assignees[0].id : null,
    };
    saveStatus.value = '';
    showDeleteConfirm.value = false;
  }
};

const isFormDirty = computed(() => {
  if (!props.task) return false;
  const currentAssigneeId = props.task.assignees?.length > 0 ? props.task.assignees[0].id : null;
  const currentStatus = props.task.status?.value || props.task.status || 'backlog';
  const currentPriority = props.task.priority?.value || props.task.priority || 'low';
  const a1 = editForm.value.assigneeId ? Number(editForm.value.assigneeId) : null;
  const a2 = currentAssigneeId ? Number(currentAssigneeId) : null;
  return editForm.value.title !== (props.task.title || '') ||
    editForm.value.description !== (props.task.description || '') ||
    editForm.value.status !== currentStatus ||
    editForm.value.priority !== currentPriority ||
    a1 !== a2;
});

let lastTaskId = null;

watch(() => [props.task?.id, props.open], () => {
  const currentTaskId = props.task?.id || null;
  const taskChanged = currentTaskId !== lastTaskId;
  lastTaskId = currentTaskId;

  if (props.open) {
    hasChangesBeenSaved.value = false;
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }

  if (taskChanged || !isFormDirty.value) initForms();

  if (props.open && props.task && props.mode === 'view') {
    fetchComments();
    setupEchoListener();
  } else {
    cleanupEchoListener();
  }
}, { immediate: true });

// Keyboard close
const handleKeydown = (e) => {
  if (e.key === 'Escape' && props.open) handleClose();
};
onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));

function debounce(fn, delay) {
  let timeoutId;
  function debounced(...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), delay);
  }
  debounced.cancel = () => clearTimeout(timeoutId);
  return debounced;
}

const performAutoSave = async () => {
  if (!props.task || readOnly.value) return;
  saveStatus.value = 'saving';
  try {
    const xsrf = getCookie('XSRF-TOKEN');
    const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
    if (xsrf) headers['X-XSRF-TOKEN'] = xsrf;

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
    hasChangesBeenSaved.value = true;
  } catch {
    saveStatus.value = 'error';
    toast.error('Failed to auto-save task.');
  }
};

const triggerAutoSave = debounce(performAutoSave, 500);

const handleClose = () => {
  triggerAutoSave.cancel();
  if (hasChangesBeenSaved.value) {
    toast.success('Task updated');
    hasChangesBeenSaved.value = false;
  }
  saveStatus.value = '';
  document.body.style.overflow = '';
  emit('close');
};

watch(() => [editForm.value.title, editForm.value.description, editForm.value.status, editForm.value.priority, editForm.value.assigneeId], () => {
  if (props.mode === 'view' && props.task && !readOnly.value && isFormDirty.value) {
    triggerAutoSave();
  }
}, { deep: true });

const submitCreate = async () => {
  isSaving.value = true;
  try {
    const xsrf = getCookie('XSRF-TOKEN');
    const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
    if (xsrf) headers['X-XSRF-TOKEN'] = xsrf;

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
  } catch {
    toast.error('Failed to create task.');
  } finally {
    isSaving.value = false;
  }
};

const submitDelete = async () => {
  if (!props.task) return;
  isSaving.value = true;
  hasChangesBeenSaved.value = false;
  triggerAutoSave.cancel();
  try {
    const xsrf = getCookie('XSRF-TOKEN');
    const headers = { 'Accept': 'application/json' };
    if (xsrf) headers['X-XSRF-TOKEN'] = xsrf;

    const response = await fetch(`/api/v1/workspaces/${props.workspaceId}/projects/${props.projectId}/tasks/${props.task.id}`, {
      method: 'DELETE',
      headers,
    });
    if (!response.ok) throw new Error();
    emit('task-deleted', props.task.id);
    emit('close');
    toast.success('Task deleted successfully!');
  } catch {
    toast.error('Failed to delete task.');
  } finally {
    isSaving.value = false;
  }
};

const submitComment = async () => {
  if (!newComment.value.trim() || isSubmittingComment.value || !props.task) return;
  isSubmittingComment.value = true;
  try {
    const xsrf = getCookie('XSRF-TOKEN');
    const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
    if (xsrf) headers['X-XSRF-TOKEN'] = xsrf;

    const response = await fetch(`/api/v1/workspaces/${props.workspaceId}/projects/${props.projectId}/tasks/${props.task.id}/comments`, {
      method: 'POST',
      headers,
      body: JSON.stringify({ content: newComment.value }),
    });
    if (!response.ok) throw new Error();
    const result = await response.json();
    if (!comments.value.some(c => c.id === result.data.id)) {
      comments.value.push(result.data);
    }
    newComment.value = '';
    toast.success('Comment posted!');
  } catch {
    toast.error('Failed to post comment.');
  } finally {
    isSubmittingComment.value = false;
  }
};

const statusLabel = (val) => ({ backlog: 'Backlog', in_progress: 'In Progress', in_review: 'In Review', done: 'Done' }[val] || val);
const priorityLabel = (val) => ({ low: 'Low', medium: 'Medium', high: 'High' }[val] || val);

const priorityDotClass = (val) => ({
  low: 'bg-emerald-500',
  medium: 'bg-amber-400',
  high: 'bg-rose-500',
}[val] || 'bg-text-muted');

const statusDotClass = (val) => ({
  backlog: 'bg-text-muted',
  in_progress: 'bg-blue-500',
  in_review: 'bg-violet-500',
  done: 'bg-emerald-500',
}[val] || 'bg-text-muted');
</script>

<template>
  <!-- Portal-style backdrop -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
      >
        <!-- Backdrop -->
        <div
          class="absolute inset-0 bg-black/50 backdrop-blur-sm"
          @click="handleClose"
        />

        <!-- Modal Panel -->
        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 scale-95 translate-y-2"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-2"
        >
          <div
            v-if="open"
            class="relative w-full max-w-3xl max-h-[90vh] flex flex-col bg-surface border border-border/60 rounded-2xl shadow-[0_32px_80px_-12px_rgba(0,0,0,0.4)] overflow-hidden"
            role="dialog"
            aria-modal="true"
          >

            <!-- ─── CREATE MODE ─── -->
            <template v-if="mode === 'create'">
              <!-- Header -->
              <div class="flex items-center justify-between px-6 py-5 border-b border-border/50 flex-shrink-0">
                <div>
                  <h2 class="font-display text-lg font-bold text-text tracking-tight">Create Task</h2>
                  <p class="text-xs text-text-muted mt-0.5">Add a new task to your project board.</p>
                </div>
                <button
                  @click="handleClose"
                  class="p-2 rounded-lg text-text-secondary bg-surface-3 hover:text-text hover:bg-surface-2 hover:border-border border border-border/40 transition-all duration-150 outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                  aria-label="Close modal"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <!-- Body -->
              <div class="overflow-y-auto flex-1 px-6 py-5">
                <form id="create-task-form" @submit.prevent="submitCreate" class="space-y-5">
                  <div class="space-y-1.5">
                    <label for="task-title" class="label-uppercase-tracked block">Task Title</label>
                    <input
                      id="task-title"
                      type="text"
                      v-model="createForm.title"
                      class="block w-full rounded-xl border border-border bg-surface-2 px-4 py-2.5 text-sm text-text placeholder-text-muted/60 hover:border-border-strong focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all outline-none"
                      placeholder="e.g. Design homepage hero section"
                      required
                      :disabled="isSaving"
                    />
                  </div>

                  <div class="space-y-1.5">
                    <label for="task-desc" class="label-uppercase-tracked block">Description</label>
                    <textarea
                      id="task-desc"
                      v-model="createForm.description"
                      class="block w-full rounded-xl border border-border bg-surface-2 px-4 py-2.5 text-sm text-text-secondary placeholder-text-muted/60 hover:border-border-strong focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all h-28 resize-none outline-none"
                      placeholder="Provide a detailed task description..."
                      :disabled="isSaving"
                    />
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                      <label for="task-status" class="label-uppercase-tracked block">Status</label>
                      <div class="relative">
                        <select id="task-status" v-model="createForm.status" class="task-select" required :disabled="isSaving">
                          <option value="backlog">Backlog</option>
                          <option value="in_progress">In Progress</option>
                          <option value="in_review">In Review</option>
                          <option value="done">Done</option>
                        </select>
                        <div class="select-chevron"><svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg></div>
                      </div>
                    </div>
                    <div class="space-y-1.5">
                      <label for="task-priority" class="label-uppercase-tracked block">Priority</label>
                      <div class="relative">
                        <select id="task-priority" v-model="createForm.priority" class="task-select" required :disabled="isSaving">
                          <option value="low">Low</option>
                          <option value="medium">Medium</option>
                          <option value="high">High</option>
                        </select>
                        <div class="select-chevron"><svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg></div>
                      </div>
                    </div>
                  </div>

                  <div class="space-y-1.5">
                    <label for="task-assignee" class="label-uppercase-tracked block">Assignee</label>
                    <div class="relative">
                      <select id="task-assignee" v-model="createForm.assigneeId" class="task-select" :disabled="isSaving">
                        <option :value="null">Unassigned</option>
                        <option v-for="member in members" :key="member.id" :value="member.id">{{ member.name }}</option>
                      </select>
                      <div class="select-chevron"><svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg></div>
                    </div>
                  </div>
                </form>
              </div>

              <!-- Footer -->
              <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-border/50 flex-shrink-0">
                <button type="button" @click="handleClose" class="btn-ghost" :disabled="isSaving">Cancel</button>
                <button type="submit" form="create-task-form" class="btn-primary" :disabled="isSaving || !createForm.title.trim()">
                  <svg v-if="isSaving" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                  <span>{{ isSaving ? 'Creating...' : 'Create Task' }}</span>
                </button>
              </div>
            </template>

            <!-- ─── VIEW MODE ─── -->
            <template v-else-if="mode === 'view' && task">
              <!-- Modal Header -->
              <div class="flex items-center justify-between px-6 py-4 border-b border-border/50 flex-shrink-0">
                <div class="flex items-center gap-3">
                  <span class="font-mono text-[11px] text-text-muted tracking-wider bg-surface-3 border border-border/60 rounded-md px-2.5 py-1 select-none">TASK-{{ task.id }}</span>

                  <!-- Save status -->
                  <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-y-0.5" enter-to-class="opacity-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0" mode="out-in">
                    <span v-if="saveStatus === 'saving'" key="saving" class="inline-flex items-center gap-1.5 text-[11px] text-primary font-medium">
                      <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                      Saving…
                    </span>
                    <span v-else-if="saveStatus === 'saved'" key="saved" class="inline-flex items-center gap-1 text-[11px] text-emerald-500 font-medium">
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                      Saved
                    </span>
                    <span v-else-if="saveStatus === 'error'" key="error" class="inline-flex items-center gap-1 text-[11px] text-rose-500 font-medium">
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                      Save failed
                    </span>
                  </Transition>
                </div>

                <div class="flex items-center gap-2">
                  <PresenceAvatars :key="task.id" :task-id="task.id" :current-user-id="currentUserId" />
                  <button
                    @click="handleClose"
                    class="p-2 rounded-lg text-text-secondary bg-surface-3 hover:text-text hover:bg-surface-2 hover:border-border border border-border/40 transition-all duration-150 outline-none focus-visible:ring-2 focus-visible:ring-primary/40"
                    aria-label="Close modal"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Scrollable body -->
              <div class="overflow-y-auto flex-1">
                <div class="p-6 space-y-6">

                  <!-- Title — tabindex=-1 prevents auto-focus on open -->
                  <div>
                    <input
                      type="text"
                      v-model="editForm.title"
                      tabindex="-1"
                      class="block w-full font-display text-xl font-bold text-text bg-transparent border border-transparent rounded-xl px-3 py-2 hover:bg-surface-3 hover:border-border/50 focus:bg-surface-2 focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 outline-none placeholder-text-muted/50"
                      :disabled="readOnly"
                      placeholder="Task Title"
                    />
                  </div>

                  <!-- Two-column layout -->
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Left: Description + Comments -->
                    <div class="md:col-span-2 space-y-6">
                      <!-- Description -->
                      <div class="space-y-1.5">
                        <label class="label-uppercase-tracked block">Description</label>
                        <textarea
                          v-model="editForm.description"
                          class="block w-full rounded-xl border border-transparent bg-transparent px-3 py-2.5 text-sm text-text-secondary placeholder-text-muted/50 h-36 resize-none transition-all duration-200 hover:bg-surface-3 hover:border-border/50 focus:bg-surface-2 focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none"
                          placeholder="Add a detailed description for this task..."
                          :disabled="readOnly"
                        />
                      </div>

                      <!-- Comments -->
                      <div class="space-y-3 pt-4 border-t border-border/40">
                        <h4 class="text-xs font-semibold text-text-secondary font-display tracking-wide uppercase">Comments</h4>

                        <div v-if="comments.length > 0" class="space-y-3 max-h-60 overflow-y-auto pr-1">
                          <div v-for="comment in comments" :key="comment.id" class="flex gap-3 text-sm">
                            <div class="flex-shrink-0 w-7 h-7 rounded-full bg-gradient-to-br from-primary/20 to-secondary/20 text-primary flex items-center justify-center font-bold text-[10px] border border-primary/20 select-none">
                              {{ getInitials(comment.user?.name) }}
                            </div>
                            <div class="flex-1 bg-surface-3 border border-border/40 rounded-xl px-3.5 py-2.5 space-y-1">
                              <div class="flex items-center justify-between">
                                <span class="font-semibold text-text text-xs">{{ comment.user?.name || 'Unknown' }}</span>
                                <span class="text-[10px] text-text-muted font-mono">{{ formatCommentDate(comment.created_at) }}</span>
                              </div>
                              <p class="text-text-secondary text-xs leading-relaxed whitespace-pre-line">{{ comment.content }}</p>
                            </div>
                          </div>
                        </div>
                        <div v-else-if="commentsLoading" class="text-xs text-text-muted italic animate-pulse">Loading comments…</div>
                        <div v-else class="text-xs text-text-muted italic">No comments yet. Be the first to start the conversation!</div>

                        <!-- Add comment -->
                        <form @submit.prevent="submitComment" class="flex gap-2.5 items-end pt-2">
                          <textarea
                            v-model="newComment"
                            class="block flex-1 rounded-xl border border-border/70 bg-surface-2 px-3.5 py-2.5 text-xs text-text placeholder-text-muted/60 hover:border-border-strong focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all h-16 resize-none outline-none"
                            placeholder="Share feedback or update progress..."
                            :disabled="isSubmittingComment"
                          />
                          <button
                            type="submit"
                            class="btn-primary h-16 px-4 text-xs flex-shrink-0"
                            :disabled="isSubmittingComment || !newComment.trim()"
                          >
                            <svg v-if="isSubmittingComment" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                            <span v-else>Post</span>
                          </button>
                        </form>
                      </div>
                    </div>

                    <!-- Right: Metadata -->
                    <div class="space-y-4 bg-surface-3 border border-border/40 rounded-xl p-4 self-start">
                      <div class="space-y-1.5">
                        <label for="edit-status" class="label-uppercase-tracked flex items-center gap-1.5">
                          <span class="w-2 h-2 rounded-full flex-shrink-0" :class="statusDotClass(editForm.status)"></span>
                          Status
                        </label>
                        <div class="relative">
                          <select id="edit-status" v-model="editForm.status" class="task-select" :disabled="readOnly">
                            <option value="backlog">Backlog</option>
                            <option value="in_progress">In Progress</option>
                            <option value="in_review">In Review</option>
                            <option value="done">Done</option>
                          </select>
                          <div class="select-chevron"><svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg></div>
                        </div>
                      </div>

                      <div class="space-y-1.5">
                        <label for="edit-priority" class="label-uppercase-tracked flex items-center gap-1.5">
                          <span class="w-2 h-2 rounded-full flex-shrink-0" :class="priorityDotClass(editForm.priority)"></span>
                          Priority
                        </label>
                        <div class="relative">
                          <select id="edit-priority" v-model="editForm.priority" class="task-select" :disabled="readOnly">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                          </select>
                          <div class="select-chevron"><svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg></div>
                        </div>
                      </div>

                      <div class="space-y-1.5">
                        <label for="edit-assignee" class="label-uppercase-tracked block">Assignee</label>
                        <div class="relative">
                          <select id="edit-assignee" v-model="editForm.assigneeId" class="task-select" :disabled="readOnly">
                            <option :value="null">Unassigned</option>
                            <option v-for="member in members" :key="member.id" :value="member.id">{{ member.name }}</option>
                          </select>
                          <div class="select-chevron"><svg class="h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg></div>
                        </div>
                      </div>

                      <!-- Delete -->
                      <div v-if="can('delete-tasks')" class="pt-3 border-t border-border/40">
                        <div v-if="!showDeleteConfirm">
                          <button @click="showDeleteConfirm = true" class="text-xs font-semibold text-rose-500 hover:text-rose-400 transition-colors cursor-pointer outline-none">
                            Delete Task
                          </button>
                        </div>
                        <div v-else class="space-y-2">
                          <p class="text-xs text-text-secondary font-medium">Are you sure?</p>
                          <div class="flex gap-2">
                            <button @click="submitDelete" class="text-xs font-semibold bg-rose-500 hover:bg-rose-600 text-white rounded-lg px-3 py-1.5 transition-all cursor-pointer" :disabled="isSaving">Delete</button>
                            <button @click="showDeleteConfirm = false" class="text-xs font-semibold text-text-muted hover:text-text-secondary transition-colors cursor-pointer" :disabled="isSaving">Cancel</button>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </template>

          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
@reference "../../css/app.css";

.task-select {
  @apply appearance-none block w-full rounded-lg border border-border bg-surface-2 pl-3 pr-10 py-2 text-xs text-text hover:border-border-strong focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all cursor-pointer disabled:cursor-not-allowed disabled:opacity-60 outline-none;
}

.select-chevron {
  @apply pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3;
}

.btn-primary {
  @apply inline-flex items-center justify-center gap-2 rounded-xl bg-primary hover:bg-primary-dark text-white px-5 py-2.5 text-sm font-semibold transition-all shadow-md shadow-primary/20 hover:shadow-primary/30 cursor-pointer disabled:opacity-50 disabled:shadow-none outline-none focus-visible:ring-4 focus-visible:ring-primary/20;
}

.btn-ghost {
  @apply inline-flex items-center justify-center rounded-xl border border-border bg-transparent px-4 py-2.5 text-sm font-medium text-text-secondary hover:bg-surface-3 hover:text-text transition-all cursor-pointer outline-none focus-visible:ring-4 focus-visible:ring-border/50;
}

.label-uppercase-tracked {
  @apply text-[10px] font-bold text-text-muted uppercase tracking-widest;
}
</style>
