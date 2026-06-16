import { ref, computed, watch } from 'vue';

const theme = ref(localStorage.getItem('theme') || 'dark'); // Default to dark mode for premium craft aesthetic

export function useTheme() {
  const toggleTheme = () => {
    theme.value = theme.value === 'dark' ? 'light' : 'dark';
  };

  const applyTheme = () => {
    if (typeof document !== 'undefined') {
      const root = document.documentElement;
      if (theme.value === 'dark') {
        root.classList.add('dark');
      } else {
        root.classList.remove('dark');
      }
      localStorage.setItem('theme', theme.value);
    }
  };

  // Watch and apply
  watch(theme, applyTheme, { immediate: true });

  return {
    theme,
    toggleTheme,
    applyTheme,
    isDark: computed(() => theme.value === 'dark'),
  };
}
