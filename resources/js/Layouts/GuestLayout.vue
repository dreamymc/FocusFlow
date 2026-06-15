<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen flex font-sans text-text antialiased">
    <!-- Brand Panel (Left) - Hidden on Mobile -->
    <div class="hidden lg:flex lg:w-1/2 bg-primary relative overflow-hidden flex-col justify-between p-12 select-none">
      <!-- Background subtle geometric patterns -->
      <div class="absolute inset-0 opacity-10 pointer-events-none">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000" preserveAspectRatio="none">
          <defs>
            <radialGradient id="pattern" cx="50%" cy="50%" r="50%">
              <stop offset="0%" stop-color="#FFFFFF" />
              <stop offset="100%" stop-color="#EEF2FF" />
            </radialGradient>
          </defs>
          <circle cx="200" cy="200" r="300" fill="url(#pattern)" />
          <circle cx="800" cy="800" r="400" fill="url(#pattern)" />
          <path d="M 0,0 L 1000,1000 L 0,1000 Z" fill="#4F46E5" opacity="0.4" />
        </svg>
      </div>

      <!-- Header / Logo -->
      <div class="relative z-10 flex items-center gap-3">
        <div class="h-10 w-10 rounded-xl bg-white flex items-center justify-center shadow-lg shadow-primary-dark/20">
          <span class="font-display font-extrabold text-2xl text-primary">F</span>
        </div>
        <span class="font-display text-2xl font-extrabold tracking-tight text-white">
          Focus<span class="text-primary-light">Flow</span>
        </span>
      </div>

      <!-- Tagline / Pitch -->
      <div class="relative z-10 max-w-md my-auto">
        <h1 class="font-display text-4xl lg:text-5xl font-extrabold text-white leading-tight mb-4">
          Where great teams ship.
        </h1>
        <p class="text-white/80 text-lg leading-relaxed font-light">
          Streamline your development, sync tasks across your team in real-time, and manage work cycles in one unified platform.
        </p>
      </div>

      <!-- Footer info -->
      <div class="relative z-10 text-white/50 text-sm">
        &copy; {{ new Date().getFullYear() }} FocusFlow. All rights reserved.
      </div>
    </div>

    <!-- Form Panel (Right) -->
    <div class="w-full lg:w-1/2 flex items-center justify-center bg-surface-2 p-6 md:p-12">
      <div class="w-full max-w-md">
        <!-- Logo for Mobile only -->
        <div class="flex items-center gap-3 mb-8 justify-center lg:hidden">
          <div class="h-8 w-8 rounded-lg bg-primary flex items-center justify-center shadow-md">
            <span class="font-display font-extrabold text-lg text-white">F</span>
          </div>
          <span class="font-display text-xl font-extrabold tracking-tight text-text">
            Focus<span class="text-primary">Flow</span>
          </span>
        </div>
        
        <!-- Center Form Card -->
        <div class="bg-surface rounded-lg border border-border p-8 shadow-sm">
          <slot />
        </div>
      </div>
    </div>
  </div>
</template>
