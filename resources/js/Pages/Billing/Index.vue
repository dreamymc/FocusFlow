<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  workspace: {
    type: Object,
    required: true
  },
  subscription: {
    type: Object,
    default: null
  },
  onGracePeriod: {
    type: Boolean,
    default: false
  },
  plan: {
    type: String,
    required: true // 'pro' or 'free'
  }
});

const form = useForm({});

const submitPortal = () => {
  form.post(`/workspaces/${props.workspace.id}/billing/portal`);
};
</script>

<template>
  <AuthenticatedLayout title="Billing">
    <div class="space-y-8 max-w-4xl">
      <!-- Title & Header -->
      <div class="space-y-1">
        <h1 class="text-2xl font-bold font-display text-text-primary tracking-tight">Billing & Subscriptions</h1>
        <p class="text-sm text-text-secondary">
          Manage your workspace subscription plans, billing details, and invoice history.
        </p>
      </div>

      <!-- Current Plan Card -->
      <div class="bg-surface border-2 rounded-xl p-6 md:p-8 transition-all duration-300 shadow-sm hover:shadow-md"
           :class="plan === 'pro' ? 'border-primary' : 'border-border'">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
          <div class="space-y-4 flex-1">
            <div class="flex items-center gap-3">
              <h2 class="text-xl font-bold font-display text-text-primary">Current Workspace Plan</h2>
              
              <!-- Badges -->
              <span v-if="plan === 'free'" class="text-xs font-semibold text-text-secondary bg-slate-100 border border-slate-200 px-2.5 py-1 rounded-full uppercase tracking-wider">
                Free
              </span>
              <div v-else class="flex items-center gap-2">
                <span class="text-xs font-semibold text-primary bg-primary-light border border-primary/20 px-2.5 py-1 rounded-full uppercase tracking-wider">
                  Pro
                </span>
                <span class="text-xs font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-md flex items-center gap-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                  Active
                </span>
              </div>
            </div>

            <!-- Features Description -->
            <div class="space-y-3">
              <p class="text-sm text-text-secondary">
                {{ plan === 'pro' 
                  ? 'Your workspace has access to all premium FocusFlow features for unlimited collaboration.'
                  : 'Get started with basic tracking features. Upgrade to unlock the full potential of FocusFlow.' 
                }}
              </p>
              
              <!-- Feature Checklist -->
              <ul class="space-y-2.5 pt-1.5">
                <template v-if="plan === 'free'">
                  <li class="flex items-center gap-2.5 text-sm text-text-secondary">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Up to 3 projects</span>
                  </li>
                  <li class="flex items-center gap-2.5 text-sm text-text-secondary">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Up to 10 tasks</span>
                  </li>
                  <li class="flex items-center gap-2.5 text-sm text-text-secondary">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span>Basic WebSockets</span>
                  </li>
                </template>
                <template v-else>
                  <li class="flex items-center gap-2.5 text-sm text-text-secondary">
                    <svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span class="font-medium">Unlimited projects</span>
                  </li>
                  <li class="flex items-center gap-2.5 text-sm text-text-secondary">
                    <svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span class="font-medium">Unlimited tasks</span>
                  </li>
                  <li class="flex items-center gap-2.5 text-sm text-text-secondary">
                    <svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span class="font-medium">Real-time collaboration</span>
                  </li>
                  <li class="flex items-center gap-2.5 text-sm text-text-secondary">
                    <svg class="w-4 h-4 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <span class="font-medium">Priority support</span>
                  </li>
                </template>
              </ul>
            </div>
          </div>

          <!-- Actions Column -->
          <div class="flex flex-col justify-center min-w-[200px]">
            <form @submit.prevent="submitPortal" class="w-full">
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg text-sm font-semibold transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2"
                :class="plan === 'pro' 
                  ? 'bg-white hover:bg-slate-50 text-text-primary border border-border shadow-sm focus:ring-primary' 
                  : 'bg-primary hover:bg-primary-dark text-white shadow-sm focus:ring-primary hover:scale-[1.02] active:scale-[0.98]'"
              >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <template v-if="form.processing">Connecting...</template>
                <template v-else-if="plan === 'pro'">Manage Subscription</template>
                <template v-else>Upgrade to Pro</template>
              </button>
            </form>
          </div>
        </div>

        <!-- Grace Period Notification -->
        <div v-if="plan === 'pro' && onGracePeriod" class="mt-6 flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg">
          <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <div class="space-y-1">
            <h4 class="text-sm font-semibold text-amber-800">Subscription Cancelled</h4>
            <p class="text-xs text-amber-700 leading-normal">
              Your Pro plan ends on <span class="font-bold">{{ subscription?.ends_at }}</span>. Renew to keep uninterrupted premium access to all projects and real-time collaboration.
            </p>
          </div>
        </div>
      </div>

      <!-- Plan Comparison Section (Only shown if on Free Plan) -->
      <div v-if="plan === 'free'" class="space-y-4 pt-4 border-t border-border">
        <h3 class="text-lg font-bold font-display text-text-primary">Compare Plans</h3>
        <div class="overflow-x-auto border border-border rounded-xl">
          <table class="w-full text-left border-collapse bg-surface text-sm">
            <thead>
              <tr class="bg-slate-50/75 border-b border-border">
                <th class="p-4 font-semibold text-text-primary">Features</th>
                <th class="p-4 font-semibold text-text-primary">Free</th>
                <th class="p-4 font-semibold text-text-primary text-primary">Pro</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-border">
              <tr>
                <td class="p-4 font-medium text-text-primary">Projects</td>
                <td class="p-4 text-text-secondary">Up to 3</td>
                <td class="p-4 text-primary font-semibold">Unlimited</td>
              </tr>
              <tr>
                <td class="p-4 font-medium text-text-primary">Tasks per Project</td>
                <td class="p-4 text-text-secondary">Up to 10</td>
                <td class="p-4 text-primary font-semibold">Unlimited</td>
              </tr>
              <tr>
                <td class="p-4 font-medium text-text-primary">Real-Time Sync</td>
                <td class="p-4 text-text-secondary">Basic polling</td>
                <td class="p-4 text-primary font-semibold">Instant WebSockets</td>
              </tr>
              <tr>
                <td class="p-4 font-medium text-text-primary">Collaboration</td>
                <td class="p-4 text-text-secondary">Single user</td>
                <td class="p-4 text-primary font-semibold">Multi-user workspace</td>
              </tr>
              <tr>
                <td class="p-4 font-medium text-text-primary">Support</td>
                <td class="p-4 text-text-secondary">Community</td>
                <td class="p-4 text-primary font-semibold">Priority email</td>
              </tr>
              <tr class="bg-slate-50/20">
                <td class="p-4 font-medium text-text-primary">Price</td>
                <td class="p-4 text-text-secondary">$0 / month</td>
                <td class="p-4 text-primary font-bold">$15 / month</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
