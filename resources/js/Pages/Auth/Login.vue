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
      <div class="space-y-1.5">
        <Label for="email">Email address</Label>
        <Input
          id="email"
          type="email"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
          placeholder="name@example.com"
          :class="{'border-accent-red': form.errors.email}"
        />
        <p v-if="form.errors.email" class="text-accent-red text-xs mt-1">
          {{ form.errors.email }}
        </p>
      </div>

      <!-- Password -->
      <div class="space-y-1.5">
        <div class="flex items-center justify-between">
          <Label for="password">Password</Label>
        </div>
        <div class="relative">
          <Input
            id="password"
            :type="showPassword ? 'text' : 'password'"
            v-model="form.password"
            required
            autocomplete="current-password"
            placeholder="••••••••"
            class="pr-10"
            :class="{'border-accent-red': form.errors.password}"
          />
          <button
            type="button"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-text-secondary hover:text-text transition"
            @click="showPassword = !showPassword"
          >
            <Eye v-if="!showPassword" class="h-4.5 w-4.5" />
            <EyeOff v-else class="h-4.5 w-4.5" />
          </button>
        </div>
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
          class="w-full bg-primary hover:bg-primary-dark text-white font-medium py-2 rounded-md shadow-sm transition"
          :disabled="form.processing"
        >
          <span v-if="form.processing">Signing in...</span>
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
