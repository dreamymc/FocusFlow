<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Eye, EyeOff } from '@lucide/vue';

const page = usePage();
const flashError = computed(() => page.props.flash?.error);

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const showPassword = ref(false);

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Sign In" />

    <div class="mb-6 text-center">
      <h2 class="font-display text-2xl font-bold text-text">
        Sign in to FocusFlow
      </h2>
      <p class="text-text-secondary text-sm mt-1">
        Welcome back! Please enter your details.
      </p>
    </div>

    <!-- Flash Error Alert -->
    <div 
      v-if="flashError" 
      class="mb-4 p-3 rounded-md bg-accent-red/10 border border-accent-red/20 text-accent-red text-sm animate-in fade-in"
    >
      {{ flashError }}
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <!-- Email Address -->
      <div class="relative w-full">
        <input
          id="email"
          type="email"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
          placeholder=" "
          class="peer h-12 pt-5 pb-1 px-3.5 block w-full border border-border rounded-md text-text bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 text-sm transition-all"
          :class="{'border-accent-red': form.errors.email}"
        />
        <label
          for="email"
          class="absolute left-3.5 top-1.5 text-[10px] text-text-muted transition-all duration-200 pointer-events-none origin-[0]
                 peer-placeholder-shown:top-3.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-text-muted
                 peer-focus:top-1.5 peer-focus:text-[10px] peer-focus:text-primary font-medium"
        >
          Email address
        </label>
        <p v-if="form.errors.email" class="text-accent-red text-xs mt-1">
          {{ form.errors.email }}
        </p>
      </div>

      <!-- Password -->
      <div class="relative w-full">
        <input
          id="password"
          :type="showPassword ? 'text' : 'password'"
          v-model="form.password"
          required
          autocomplete="current-password"
          placeholder=" "
          class="peer h-12 pt-5 pb-1 px-3.5 pr-10 block w-full border border-border rounded-md text-text bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 text-sm transition-all"
          :class="{'border-accent-red': form.errors.password}"
        />
        <label
          for="password"
          class="absolute left-3.5 top-1.5 text-[10px] text-text-muted transition-all duration-200 pointer-events-none origin-[0]
                 peer-placeholder-shown:top-3.5 peer-placeholder-shown:text-sm peer-placeholder-shown:text-text-muted
                 peer-focus:top-1.5 peer-focus:text-[10px] peer-focus:text-primary font-medium"
        >
          Password
        </label>
        <button
          type="button"
          class="absolute top-1/2 -translate-y-1/2 right-0 flex items-center pr-3.5 text-text-secondary hover:text-text transition"
          @click="showPassword = !showPassword"
        >
          <Eye v-if="!showPassword" class="h-4.5 w-4.5" />
          <EyeOff v-else class="h-4.5 w-4.5" />
        </button>
        <p v-if="form.errors.password" class="text-accent-red text-xs mt-1">
          {{ form.errors.password }}
        </p>
      </div>

      <!-- Remember Me -->
      <div class="flex items-center">
        <input
          id="remember"
          type="checkbox"
          v-model="form.remember"
          class="h-4 w-4 rounded border-border text-primary focus:ring-primary/30"
        />
        <label for="remember" class="ml-2 block text-sm text-text-secondary select-none cursor-pointer">
          Remember me for 30 days
        </label>
      </div>

      <!-- Submit Button -->
      <div class="pt-2">
        <Button
          type="submit"
          class="w-full h-11 bg-primary hover:bg-primary-dark text-white font-medium rounded-md shadow-sm transition shimmer-btn active:scale-[0.98] flex items-center justify-center"
          :disabled="form.processing"
        >
          <span v-if="form.processing" class="flex items-center gap-2">
            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Signing in...
          </span>
          <span v-else>Sign in to FocusFlow</span>
        </Button>
      </div>
    </form>

    <div class="mt-6 text-center text-sm text-text-secondary">
      Don't have an account? 
      <Link href="/register" class="text-primary hover:text-primary-dark font-medium underline-offset-4 hover:underline">
        Sign up
      </Link>
    </div>
  </GuestLayout>
</template>
