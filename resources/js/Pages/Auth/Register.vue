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
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Sign Up" />

    <div class="mb-6 text-center">
      <h2 class="font-display text-2xl font-bold text-text">
        Create your account
      </h2>
      <p class="text-text-secondary text-sm mt-1">
        Join FocusFlow to manage workspace tasks.
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
      <!-- Name -->
      <div class="space-y-1.5">
        <Label for="name">Full name</Label>
        <Input
          id="name"
          type="text"
          v-model="form.name"
          required
          autofocus
          autocomplete="name"
          placeholder="John Doe"
          :class="{'border-accent-red': form.errors.name}"
        />
        <p v-if="form.errors.name" class="text-accent-red text-xs mt-1">
          {{ form.errors.name }}
        </p>
      </div>

      <!-- Email Address -->
      <div class="space-y-1.5">
        <Label for="email">Email address</Label>
        <Input
          id="email"
          type="email"
          v-model="form.email"
          required
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
        <Label for="password">Password</Label>
        <div class="relative">
          <Input
            id="password"
            :type="showPassword ? 'text' : 'password'"
            v-model="form.password"
            required
            autocomplete="new-password"
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

      <!-- Confirm Password -->
      <div class="space-y-1.5">
        <Label for="password_confirmation">Confirm Password</Label>
        <div class="relative">
          <Input
            id="password_confirmation"
            :type="showConfirmPassword ? 'text' : 'password'"
            v-model="form.password_confirmation"
            required
            autocomplete="new-password"
            placeholder="••••••••"
            class="pr-10"
            :class="{'border-accent-red': form.errors.password_confirmation}"
          />
          <button
            type="button"
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-text-secondary hover:text-text transition"
            @click="showConfirmPassword = !showConfirmPassword"
          >
            <Eye v-if="!showConfirmPassword" class="h-4.5 w-4.5" />
            <EyeOff v-else class="h-4.5 w-4.5" />
          </button>
        </div>
        <p v-if="form.errors.password_confirmation" class="text-accent-red text-xs mt-1">
          {{ form.errors.password_confirmation }}
        </p>
      </div>

      <!-- Submit Button -->
      <div class="pt-2">
        <Button
          type="submit"
          class="w-full bg-primary hover:bg-primary-dark text-white font-medium py-2 rounded-md shadow-sm transition"
          :disabled="form.processing"
        >
          <span v-if="form.processing">Creating account...</span>
          <span v-else>Create your account</span>
        </Button>
      </div>
    </form>

    <div class="mt-6 text-center text-sm text-text-secondary">
      Already have an account? 
      <Link href="/login" class="text-primary hover:text-primary-dark font-medium underline-offset-4 hover:underline">
        Sign in
      </Link>
    </div>
  </GuestLayout>
</template>
