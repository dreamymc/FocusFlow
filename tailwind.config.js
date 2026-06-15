/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: { DEFAULT: '#6366F1', dark: '#4F46E5', light: '#EEF2FF', text: '#FFFFFF' },
        secondary: { DEFAULT: '#8B5CF6', dark: '#7C3AED', light: '#F5F3FF' },
        surface: { DEFAULT: '#FFFFFF', 2: '#F8FAFC', 3: '#F1F5F9', sidebar: '#FAFBFF' },
        text: { DEFAULT: '#0F172A', secondary: '#475569', muted: '#94A3B8' },
        border: { DEFAULT: '#E2E8F0', strong: '#CBD5E1' },
        accent: {
          red: '#EF4444', orange: '#F97316', yellow: '#F59E0B', green: '#10B981',
          blue: '#3B82F6', purple: '#8B5CF6', pink: '#EC4899', gray: '#6B7280',
        },
        status: {
          backlog: '#6B7280', 'in-progress': '#3B82F6', review: '#F59E0B', done: '#10B981',
        },
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
        display: ['Plus Jakarta Sans', 'Inter', 'ui-sans-serif'],
        mono: ['JetBrains Mono', 'ui-monospace'],
      },
    },
  },
  plugins: [],
}
