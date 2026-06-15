<script setup>
import {
  CircleCheckIcon,
  InfoIcon,
  Loader2Icon,
  OctagonXIcon,
  TriangleAlertIcon,
  XIcon,
} from "@lucide/vue";
import { Toaster as Sonner } from "vue-sonner";
import { cn } from "@/lib/utils";

const props = defineProps({
  id: { type: String, required: false },
  invert: { type: Boolean, required: false },
  theme: { type: String, required: false },
  position: { type: String, required: false },
  closeButtonPosition: { type: String, required: false },
  hotkey: { type: Array, required: false },
  richColors: { type: Boolean, required: false },
  expand: { type: Boolean, required: false },
  duration: { type: Number, required: false },
  gap: { type: Number, required: false },
  visibleToasts: { type: Number, required: false },
  closeButton: { type: Boolean, required: false },
  toastOptions: { type: Object, required: false },
  class: { type: String, required: false },
  style: { type: null, required: false },
  offset: { type: [Object, String, Number], required: false },
  mobileOffset: { type: [Object, String, Number], required: false },
  dir: { type: String, required: false },
  swipeDirections: { type: Array, required: false },
  icons: { type: Object, required: false },
  containerAriaLabel: { type: String, required: false },
});
</script>

<template>
  <Sonner
    :class="cn('toaster group', props.class)"
    :style="{
      '--normal-bg': 'var(--popover)',
      '--normal-text': 'var(--popover-foreground)',
      '--normal-border': 'var(--border)',
      '--border-radius': 'var(--radius)',
    }"
    :toast-options="{
      classNames: {
        toast: 'group toast group-[.toaster]:bg-surface group-[.toaster]:text-text group-[.toaster]:border-border group-[.toaster]:shadow-lg group-[.toaster]:rounded-xl group-[.toaster]:p-4 group-[.toaster]:border group-[.toaster]:flex group-[.toaster]:items-center group-[.toaster]:gap-3 group-[.toaster]:font-sans',
        title: 'group-[.toast]:font-semibold group-[.toast]:text-text group-[.toast]:text-sm font-sans',
        description: 'group-[.toast]:text-text-secondary group-[.toast]:text-xs font-sans',
        actionButton: 'group-[.toast]:bg-primary group-[.toast]:text-white group-[.toast]:hover:bg-primary-dark group-[.toast]:px-3 group-[.toast]:py-1.5 group-[.toast]:rounded-md group-[.toast]:text-xs group-[.toast]:font-medium transition-colors font-sans',
        cancelButton: 'group-[.toast]:bg-surface-3 group-[.toast]:text-text group-[.toast]:hover:bg-surface-3/80 group-[.toast]:px-3 group-[.toast]:py-1.5 group-[.toast]:rounded-md group-[.toast]:text-xs group-[.toast]:font-medium transition-colors font-sans',
        success: 'group-[.toaster]:border-accent-green/20 group-[.toaster]:bg-emerald-50/50 group-[.toaster]:text-emerald-950 dark:group-[.toaster]:bg-emerald-950/20 dark:group-[.toaster]:text-emerald-300 dark:group-[.toaster]:border-emerald-800/40',
        error: 'group-[.toaster]:border-accent-red/20 group-[.toaster]:bg-red-50/50 group-[.toaster]:text-red-950 dark:group-[.toaster]:bg-red-950/20 dark:group-[.toaster]:text-red-300 dark:group-[.toaster]:border-red-800/40',
        warning: 'group-[.toaster]:border-accent-yellow/20 group-[.toaster]:bg-amber-50/50 group-[.toaster]:text-amber-950 dark:group-[.toaster]:bg-amber-950/20 dark:group-[.toaster]:text-amber-300 dark:group-[.toaster]:border-amber-800/40',
        info: 'group-[.toaster]:border-primary/20 group-[.toaster]:bg-indigo-50/50 group-[.toaster]:text-indigo-950 dark:group-[.toaster]:bg-indigo-950/20 dark:group-[.toaster]:text-indigo-300 dark:group-[.toaster]:border-indigo-800/40',
      }
    }"
    v-bind="props"
  >
    <template #success-icon>
      <CircleCheckIcon class="size-5 text-accent-green" />
    </template>
    <template #info-icon>
      <InfoIcon class="size-5 text-primary" />
    </template>
    <template #warning-icon>
      <TriangleAlertIcon class="size-5 text-accent-yellow" />
    </template>
    <template #error-icon>
      <OctagonXIcon class="size-5 text-accent-red" />
    </template>
    <template #loading-icon>
      <div>
        <Loader2Icon class="size-5 animate-spin text-primary" />
      </div>
    </template>
    <template #close-icon>
      <XIcon class="size-4 text-text-secondary hover:text-text transition-colors" />
    </template>
  </Sonner>
</template>
